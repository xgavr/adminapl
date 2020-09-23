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
use Stock\Entity\Ptu;
use Stock\Form\PtuForm;
use Company\Entity\Office;

class PtuController extends AbstractActionController
{

    /**
     * Менеджер сущностей.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * Менеджер пту.
     * @var \Stock\Service\PtuManager
     */
    private $ptuManager;

    public function __construct($entityManager, $ptuManager) 
    {
        $this->entityManager = $entityManager;
        $this->ptuManager = $ptuManager;
    }   

    public function indexAction()
    {
        return [];
    }
        
    public function contentAction()
    {
        	        
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order');
        
        $query = $this->entityManager->getRepository(Ptu::class)
                        ->findAllPtu(['q' => $q, 'sort' => $sort, 'order' => $order]);
        
        $total = count($query->getResult(2));
        
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
    
    public function goodContentAction()
    {
        	        
        $ptuId = $this->params()->fromRoute('id', -1);
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order');
        
        $query = $this->entityManager->getRepository(Ptu::class)
                        ->findPtuGoods($ptuId, ['q' => $q, 'sort' => $sort, 'order' => $order]);
        
        $total = count($query->getResult(2));
        
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
    
    public function repostAllPtuAction()
    {                
        $this->ptuManager->repostAllPtu();
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);
    }        
    
    public function editFormAction()
    {
        $ptuId = (int)$this->params()->fromRoute('id', -1);
        
        $ptu = $supplier = $legal = $company = null;
        
        if ($ptuId > 0){
            $ptu = $this->entityManager->getRepository(Ptu::class)
                    ->findOneById($ptuId);
        }    
        
        if ($ptu == null) {
            $supplier = $legal = $company = null;
        } else {
            $supplier = $ptu->getSupplier();
            $company = $ptu->getContract()->getCompany();
            $legal = $ptu->getLegal();
        }       

        
        $officeId = (int)$this->params()->fromQuery('office', 1);
        $office = $this->entityManager->getRepository(Office::class)
                ->findOneById($officeId);
        
        $form = new PtuForm($this->entityManager, $office, $supplier, $company, $legal);

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {

                $this->ptuManager->updatePtu($ptu, $data);
                
                return new JsonModel(
                   ['ok']
                );           
            }
        } else {
            if ($ptu){
                $data = [
                    'office' => $ptu->getContract()->getOffice()->getId(),
                    'company' => $ptu->getContract()->getCompany()->getId(),
                    'supplier' => $ptu->getSupplier()->getId(),
                    'legal' => $ptu->getLegal()->getId(),  
                    'contract' => $ptu->getContract()->getId(),  
                    'docDate' => $ptu->getDocDate(),  
                    'docNo' => $ptu->getDocNo(),
                    'comment' => $ptu->getComment(),
                    'status' => $ptu->getStatus(),
                ];
                $form->setData($data);
            }    
        }        
        
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'ptu' => $ptu,
        ]);        
    }
    
    public function deletePtuAction()
    {
        $ptuId = $this->params()->fromRoute('id', -1);
        $ptu = $this->entityManager->getRepository(Ptu::class)
                ->findOneById($ptuId);        

        if ($ptu == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->ptuManager->removePtu($ptu);
        
        // Перенаправляем пользователя на страницу "rb/tax".
        return new JsonModel(
           ['ok']
        );           
    }
    
}
