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
    
}
