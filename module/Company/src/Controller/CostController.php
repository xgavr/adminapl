<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Company\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Company\Entity\Cost;
use Company\Form\CostForm;
use Company\Entity\Legal;
use Laminas\View\Model\JsonModel;

class CostController extends AbstractActionController
{
    
    /**
     * Entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * Region manager.
     * @var \Company\Service\CostManager
     */
    private $costManager;

    /**
     * Constructor. 
     */
    public function __construct($entityManager, $costManager)
    {
        $this->entityManager = $entityManager;
        $this->costManager = $costManager;
    }
    
    public function indexAction()
    {
        $costs = $this->entityManager->getRepository(Cost::class)
                ->findBy([], ['name'=>'ASC']);
        
        return new ViewModel([
            'costs' => $costs
        ]);
    }
    
    /**
     * This action displays a page allowing to add a new cost.
     */
    public function addAction()
    {
        // Create form
        $form = new CostForm();
                
        // Check if user has submitted the form
        if ($this->getRequest()->isPost()) {
            
            // Fill in the form with POST data
            $data = $this->params()->fromPost();            
            
            $form->setData($data);
            
            // Validate form
            if($form->isValid()) {
                
                // Get filtered and validated data
                $data = $form->getData();
                
                // Add cost.
                $this->costManager->addCost($data);
                
                // Add a flash message.
                $this->flashMessenger()->addSuccessMessage('Добавлена новая статья.');
                
                // Redirect to "index" page
                return $this->redirect()->toRoute('cost', ['action'=>'index']);                
            }               
        } 
        
        return new ViewModel([
                'form' => $form
            ]);
    }    
    
    /**
     * The "view" action displays a page allowing to view cost's details.
     */
    public function viewAction() 
    {
        $id = (int)$this->params()->fromRoute('id', -1);
        if ($id<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // Find a cost with such ID.
        $cost = $this->entityManager->getRepository(Cost::class)
                ->find($id);
        
        if ($cost == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
                        
        return new ViewModel([
            'cost' => $cost,
        ]);
    }
    
    /**
     * This action displays a page allowing to edit an existing cost.
     */
    public function editAction()
    {
        $id = (int)$this->params()->fromRoute('id', -1);
        if ($id<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $cost = $this->entityManager->getRepository(Cost::class)
                ->find($id);
        
        if ($cost == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // Create form
        $form = new CostForm();
        
        // Check if user has submitted the form
        if ($this->getRequest()->isPost()) {
            
            // Fill in the form with POST data
            $data = $this->params()->fromPost();            
            
            $form->setData($data);
            
            // Validate form
            if($form->isValid()) {
                
                // Get filtered and validated data
                $data = $form->getData();
                
                // Update permission.
                $this->costManager->updateCost($cost, $data);
                
                // Add a flash message.
                $this->flashMessenger()->addSuccessMessage('Статья изменена.');
                
                // Redirect to "index" page
                return $this->redirect()->toRoute('cost', ['action'=>'index']);                
            }               
        } else {
            $form->setData(array(
                    'name'=>$cost->getName(),
                    'aplId'=>$cost->getAplId(),
                    'status' => $cost->getStatus(),
                    'kind' => $cost->getKind(),
                    'kindFin' => $cost->getKindFin(),
                ));
        }
        
        return new ViewModel([
                'form' => $form,
                'cost' => $cost
            ]);
    }
    
    /**
     * This action deletes a cost.
     */
    public function deleteAction()
    {
        $id = (int)$this->params()->fromRoute('id', -1);
        if ($id<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $cost = $this->entityManager->getRepository(Cost::class)
                ->find($id);
        
        if ($cost == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // Delete cost.
        $this->costManager->deleteCost($cost);
        
        // Add a flash message.
        $this->flashMessenger()->addSuccessMessage('Статья удалена.');

        // Redirect to "index" cost
        return $this->redirect()->toRoute('cost', ['action'=>'index']); 
    }
    
    public function docAction()
    {
        $cost = $this->params()->fromQuery('cost');
        
        $companies = $this->entityManager->getRepository(Legal::class)
                ->companies();
        $costs = $this->entityManager->getRepository(Cost::class)
                ->findBy(['status' => Cost::STATUS_ACTIVE]);
        
        return new ViewModel([
            'companies' => $companies,
            'costs' => $costs,
            'costId' => $cost
        ]);
    }
 
    public function docContentAction()
    {
        	        
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $company = $this->params()->fromQuery('company');
        $cost = $this->params()->fromQuery('cost');
        $dateStart = $this->params()->fromQuery('dateStart');
        $period = $this->params()->fromQuery('period', 'month');
        $limit = $this->params()->fromQuery('limit');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order', 'DESC');
        
        $startDate = '2012-01-01';
        $endDate = '2199-01-01';
        if (!empty($dateStart)){
            $startDate = date('Y-m-d', strtotime($dateStart));
            $endDate = $startDate;
            if ($period == 'week'){
                $endDate = date('Y-m-d 23:59:59', strtotime('+ 1 week - 1 day', strtotime($startDate)));
            }    
            if ($period == 'month'){
                $endDate = date('Y-m-d 23:59:59', strtotime('+ 1 month - 1 day', strtotime($startDate)));
            }    
            if ($period == 'number'){
                $startDate = $dateStart.'-01-01';
                $endDate = date('Y-m-d 23:59:59', strtotime('+ 1 year - 1 day', strtotime($startDate)));
            }    
        }    
        
        $params = [
            'q' => $q, 'company' => $company, 'cost' => $cost,
            'startDate' => $startDate, 'endDate' => $endDate,             
            'sort' => $sort, 'order' => $order, 
        ];
        
        $query = $this->entityManager->getRepository(Cost::class)
                        ->findMutuals($params);
        
        $total = count($query->getResult());
        
        if ($offset) {
            $query->setFirstResult($offset);
        }
        if ($limit) {
            $query->setMaxResults($limit);
        }

        $totalAmount = $this->entityManager->getRepository(Cost::class)
                        ->findMutualsTotal($params);
        $totalAmountResult = 0;
        if ($totalAmount){
            $totalAmountResult = $totalAmount['amount'];
        }
        
        $result = $query->getResult(2);
        
        return new JsonModel([
            'total' => $total,
            'totalAmount' => $totalAmountResult,
            'rows' => $result,
        ]);          
    }        
    
}
