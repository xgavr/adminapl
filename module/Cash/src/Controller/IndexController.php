<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Cash\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Cash\Entity\Cash;
use Cash\Entity\CashDoc;
use Cash\Form\CashForm;
use Company\Entity\Office;
use Application\Entity\Order;

class IndexController extends AbstractActionController
{
    
    /**
     * Entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
        
    /**
     * Request manager.
     * @var \Cash\Service\CashManager
     */
    private $cashManager;
        
    /**
     * Constructor. Its purpose is to inject dependencies into the controller.
     */
    public function __construct($entityManager, $cashManager) 
    {
       $this->entityManager = $entityManager;
       $this->cashManager = $cashManager;
    }

    
    public function indexAction()
    {
        return new ViewModel([
        ]);
    }
    
    public function editCashAction()
    {
        $cashId = (int)$this->params()->fromRoute('id', -1);
        $officeId = (int) $this->params()->fromQuery('office');
        
        $cash = $office = null;
        
        if ($cashId > 0){
            $cash = $this->entityManager->getRepository(Cash::class)
                    ->find($cashId);
            $office = $cash->getOffice();
        }    
        
        if ($officeId > 0){
            $office = $this->entityManager->getRepository(Office::class)
                    ->find($officeId);
        }    
        
        $form = new CashForm($this->entityManager);
        
        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();            
            $form->setData($data);

            if ($form->isValid()) {
                
                if ($cash){
                    $this->cashManager->updateCash($cash, $data);
                } else {
                    if ($office){
                        $cash = $this->cashManager->addCash($office, $data);
                    } else {    
                        throw new \Exception('Офис не указан');
                    }    
                }    
                
                return new JsonModel(
                   ['ok']
                );           
            } else {
                //var_dump($form->getMessages());
            }
        } else {
            if ($cash){
                $data = $cash->toArray();
                $form->setData($data);
            }    
        }
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'cash' => $cash,
            'office' => $office,
        ]);        
    }            
    
    public function updateAcquiringAction()
    {
        
        $this->cashManager->updateAcquiringPayments();
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);
    }

    public function changeContactByPhoneAction()
    {
        $cashDocId = $this->params()->fromRoute('id', -1);
        $phone = $this->params()->fromQuery('phone');
        
        $cashDoc = $this->entityManager->getRepository(CashDoc::class)
                ->find($cashDocId);        

        if ($cashDoc == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->cashManager->changeContactByPhone($cashDoc, $phone);
        
        return new JsonModel(
            ['result' => 'ok']
        );                   
    }  
    
    public function fixUserRetailAction()
    {        
        $this->cashManager->fixUserRetail();
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);
    }  
    
    public function orderFormAction()
    {
        $orderId = (int)$this->params()->fromRoute('id', -1);
        
        if ($orderId <= 0){
            $this->getResponse()->setStatusCode(404);
            return;
        }    
        
        $order = $this->entityManager->getRepository(Order::class)
                ->find($orderId);
        
        if ($order == null){
            $this->getResponse()->setStatusCode(404);
            return;            
        }
        
        $this->layout()->setTemplate('layout/terminal');
        return new ViewModel([
            'order' => $order,
        ]);                
    }

    public function orderContentAction()
    {       
        $orderId = $this->params()->fromRoute('id', -1);
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order', 'DESC');
        
        if ($orderId <= 0){
            $this->getResponse()->setStatusCode(404);
            return;
        }    
        
        $params = [
            'sort' => $sort, 'order' => $order, 'orderId' => $orderId,
        ];
        
        $query = $this->entityManager->getRepository(CashDoc::class)
                        ->cashDocQuery($params);
        
        $fullResult = $query->getResult(2);
        $total = count($fullResult);
                
        if ($offset) {
            $query->setFirstResult($offset);
        }
        if ($limit) {
            $query->setMaxResults($limit);
        }

        $result = $query->getResult(2);
        
        return new JsonModel([
            'total' => $total,
            'rows' => $result,
        ]);                  
    }
    
}

