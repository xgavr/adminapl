<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Stock\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Application\Entity\Supplier;
use Company\Entity\Legal;
use Company\Entity\Contract;
use Stock\Entity\Revise;
use Company\Entity\Office;
use Stock\Form\ReviseForm;

class ReviseController extends AbstractActionController
{

    /**
     * Менеджер сущностей.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * Менеджер revise.
     * @var \Stock\Service\ReviseManager
     */
    private $reviseManager;

    public function __construct($entityManager, $reviseManager) 
    {
        $this->entityManager = $entityManager;
        $this->reviseManager = $reviseManager;
    }   

    public function indexAction()
    {
        $suppliers = $this->entityManager->getRepository(Supplier::class)
                ->findForPtu();
        $offices = $this->entityManager->getRepository(Office::class)
                ->findAll();
        return new ViewModel([
            'suppliers' => $suppliers,
            'offices' => $offices,
            'years' => array_combine(range(date("Y"), 2014), range(date("Y"), 2014)),
            'monthes' => array_combine(range(1, 12), range(1, 12)),
        ]);  
    }
        
    public function contentAction()
    {
        	        
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order', 'DESC');
        $supplierId = $this->params()->fromQuery('supplier');
        $officeId = $this->params()->fromQuery('office');
        $year = $this->params()->fromQuery('year');
        $month = $this->params()->fromQuery('month');
        
        $params = [
            'q' => $q, 'sort' => $sort, 'order' => $order, 
            'supplierId' => $supplierId, 'officeId' => $officeId,
            'year' => $year, 'month' => $month,
        ];
        $query = $this->entityManager->getRepository(Revise::class)
                        ->findAllRevise($params);
        
        $total = $this->entityManager->getRepository(Revise::class)
                        ->findAllReviseTotal($params);
        
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
    
    public function repostAllReviseAction()
    {                
        $this->reviseManager->repostAllRevise();
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);
    }        
    
    public function editFormAction()
    {
        $reviseId = (int)$this->params()->fromRoute('id', -1);
        
        $revise = $supplier = $legal = $company = null;
        
        if ($reviseId > 0){
            $revise = $this->entityManager->getRepository(Revise::class)
                    ->find($reviseId);
        }    
        
        $form = new ReviseForm($this->entityManager);

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {
                
                if ($revise){
                    $this->reviseManager->updateRevise($revise, $data);
                } else {
                    $revise = $this->reviseManager->addRevise($data);
                }    
                
                return new JsonModel(
                   ['ok']
                );           
            }
        } else {
            if ($revise){
                $form->setData($revise->toArray());
            }    
        }
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'revise' => $revise,
        ]);        
    }    
        
    public function deleteReviseAction()
    {
        $reviseId = $this->params()->fromRoute('id', -1);
        $revise = $this->entityManager->getRepository(Revise::class)
                ->find($reviseId);        

        if ($revise == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->reviseManager->removeRevise($revise);
        
        return new JsonModel(
           ['ok']
        );           
    }
    
}
