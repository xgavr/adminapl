<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Company\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Company\Entity\Tax;
use Company\Form\TaxForm;

class TaxController extends AbstractActionController
{
    
    /**
     * Entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * Tax manager.
     * @var \Company\Service\TaxManager
     */
    private $taxManager;

    /**
     * Constructor. 
     */
    public function __construct($entityManager, $taxManager)
    {
        $this->entityManager = $entityManager;
        $this->taxManager = $taxManager;
    }
    
    public function indexAction()
    {
        $taxes = $this->entityManager->getRepository(Tax::class)
                ->findBy([], ['id'=>'ASC']);
        
        return new ViewModel([
            'taxes' => $taxes
        ]);
    }
    
    /**
     * This action displays a page allowing to add a new role.
     */
    public function addAction()
    {
        // Create form
        $form = new TaxForm();
                
        // Check if user has submitted the form
        if ($this->getRequest()->isPost()) {
            
            // Fill in the form with POST data
            $data = $this->params()->fromPost();            
            
            $form->setData($data);
            
            // Validate form
            if($form->isValid()) {
                
                // Get filtered and validated data
                $data = $form->getData();
                
                // Add tax.
                $this->taxManager->addTax($data);
                
                // Add a flash message.
                $this->flashMessenger()->addSuccessMessage('Добавлен новsй налог.');
                
                // Redirect to "index" page
                return $this->redirect()->toRoute('tax', ['action'=>'index']);                
            }               
        } 
        
        return new ViewModel([
                'form' => $form
            ]);
    }    
    
    /**
     * The "view" action displays a page allowing to view tax's details.
     */
    public function viewAction() 
    {
        $id = (int)$this->params()->fromRoute('id', -1);
        if ($id<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // Find a role with such ID.
        $region = $this->entityManager->getRepository(Tax::class)
                ->find($id);
        
        if ($region == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
                        
        return new ViewModel([
            'tax' => $tax,
        ]);
    }
    
    /**
     * This action displays a page allowing to edit an existing tax.
     */
    public function editAction()
    {
        $id = (int)$this->params()->fromRoute('id', -1);
        if ($id<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $tax = $this->entityManager->getRepository(Tax::class)
                ->find($id);
        
        if ($tax == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // Create form
        $form = new TaxForm();
        
        // Check if user has submitted the form
        if ($this->getRequest()->isPost()) {
            
            // Fill in the form with POST data
            $data = $this->params()->fromPost();            
            
            $form->setData($data);
            
            // Validate form
            if($form->isValid()) {
                
                // Get filtered and validated data
                $data = $form->getData();
                
                // Update tax.
                $this->taxManager->updateTax($tax, $data);
                
                // Add a flash message.
                $this->flashMessenger()->addSuccessMessage('Налог изменен.');
                
                // Redirect to "index" page
                return $this->redirect()->toRoute('tax', ['action'=>'index']);                
            }               
        } else {
            $form->setData(array(
                    'name'=>$tax->getName(),
                    'amount'=>$tax->getAmount(),     
                    'dateStart'=>$tax->getDateStart(),     
                    'status'=>$tax->getStatus(),     
                    'kind'=>$tax->getKind(),     
                ));
        }
        
        return new ViewModel([
                'form' => $form,
                'tax' => $tax
            ]);
    }
    
    /**
     * This action deletes a tax.
     */
    public function deleteAction()
    {
        $id = (int)$this->params()->fromRoute('id', -1);
        if ($id<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $region = $this->entityManager->getRepository(Tax::class)
                ->find($id);
        
        if ($region == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // Delete tax.
        $this->taxManager->deleteTx($tax);
        
        // Add a flash message.
        $this->flashMessenger()->addSuccessMessage('Налог удален.');

        // Redirect to "index" region
        return $this->redirect()->toRoute('tax', ['action'=>'index']); 
    }
    
}
