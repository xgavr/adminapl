<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Company\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Company\Entity\Office;
use Company\Form\OfficeForm;
use Application\Entity\Contact;
use Company\Entity\Legal;
use Company\Entity\Commission;
use Company\Form\CommissionForm;

class OfficeController extends AbstractActionController
{
    
    /**
     * Entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * Office manager.
     * @var \Company\Service\OfficeManager
     */
    private $officeManager;

    /**
     * Contact manager.
     * @var Application\Service\ContactManager
     */
    private $contactManager;
    
    /**
     * Constructor. 
     */
    public function __construct($entityManager, $officeManager, $contactManager)
    {
        $this->entityManager = $entityManager;
        $this->officeManager = $officeManager;
        $this->contactManager = $contactManager;
    }
    
    public function indexAction()
    {
        $offices = $this->entityManager->getRepository(Office::class)
                ->findBy([], ['id'=>'ASC']);
        
        return new ViewModel([
            'offices' => $offices
        ]);
    }
    
    /**
     * This action displays a page allowing to add a new office.
     */
    public function addAction()
    {
        // Create form
        $form = new OfficeForm($this->entityManager);
                
        // Check if user has submitted the form
        if ($this->getRequest()->isPost()) {
            
            // Fill in the form with POST data
            $data = $this->params()->fromPost();            
            
            $form->setData($data);
            
            // Validate form
            if($form->isValid()) {
                
                // Get filtered and validated data
                $data = $form->getData();
                
                // Add role.
                $newOffice = $this->officeManager->addOffice($data);
                
                // Add a flash message.
                $this->flashMessenger()->addSuccessMessage('Добавлен новй офис.');
                
                // Redirect to "index" page
                return $this->redirect()->toRoute('offices', ['action'=>'view', 'id' => $newOffice->getId()]);                
            }               
        } 
        
        return new ViewModel([
                'form' => $form
            ]);
    }    
    
    /**
     * The "view" action displays a page allowing to view office's details.
     */
    public function viewAction() 
    {
        $id = (int)$this->params()->fromRoute('id', -1);
        if ($id<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // Find a role with such ID.
        $office = $this->entityManager->getRepository(Office::class)
                ->find($id);
        
        if ($office == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $forLegals = $office->getLegalContacts();

        if (!count($forLegals)){
            $data['full_name'] = $data['name'] = $office->getName();
            $data['status'] = Contact::STATUS_LEGAL;
            $this->contactManager->addNewContact($office, $data);
        }

        return new ViewModel([
            'office' => $office,
        ]);
    }
    
    /**
     * This action displays a page allowing to edit an existing office.
     */
    public function editAction()
    {
        $id = (int)$this->params()->fromRoute('id', -1);
        if ($id<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $office = $this->entityManager->getRepository(Office::class)
                ->find($id);
        
        if ($office == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // Create form
        $form = new OfficeForm($this->entityManager);
        
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
                $this->officeManager->updateOffice($office, $data);
                
                // Add a flash message.
                $this->flashMessenger()->addSuccessMessage('Офис изменен.');
                
                // Redirect to "index" page
                return $this->redirect()->toRoute('offices', ['action'=>'index']);                
            }               
        } else {
            $form->setData(array(
                    'name'=>$office->getName(),
                    'aplId'=>$office->getAplId(),     
                    'region'=>$office->getRegion(),     
                    'status'=>$office->getStatus(),     
                    'shippingLimit1'=>$office->getShippingLimit1(),     
                    'shippingLimit2'=>$office->getShippingLimit2(),     
                    'sbCard'=>$office->getSbCard(),     
                    'sbOwner'=>$office->getSbOwner(),
                    'address' => $office->getAddress(),
                    'addressSms' => $office->getAddressSms(),
                ));
        }
        
        return new ViewModel([
                'form' => $form,
                'office' => $office
            ]);
    }
    
    /**
     * This action displays a page allowing to edit an existing office.
     */
    public function editFormAction()
    {
        $id = (int)$this->params()->fromRoute('id', -1);
        if ($id<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $office = $this->entityManager->getRepository(Office::class)
                ->find($id);
        
        if ($office == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // Create form
        $form = new OfficeForm($this->entityManager);
        
        $offices = $this->entityManager->getRepository(Office::class)
                ->findBy(['status' => Office::STATUS_ACTIVE], ['name' => 'ASC']);
        $officeList = ['--нет--'];
        foreach ($offices as $value) {
            if ($office){
                if ($office->getId() == $value->getId()){
                    continue;
                }
            }
            $officeList[$value->getId()] = $value->getName();
        }
        $form->get('parent')->setValueOptions($officeList);        
        
        // Check if user has submitted the form
        if ($this->getRequest()->isPost()) {
            
            // Fill in the form with POST data
            $data = $this->params()->fromPost();            
            
            $form->setData($data);
            
            // Validate form
            if($form->isValid()) {
                
                // Get filtered and validated data
                $data = $form->getData();
                
                if (!empty($data['parent'])){
                    $parentOffice = $this->entityManager->getRepository(Office::class)
                            ->find($data['parent']);
                    if ($parentOffice){
                        $data['parent'] = $parentOffice;
                    }
                }
                
                // Update permission.
                $this->officeManager->updateOffice($office, $data);
                
                return new JsonModel(
                   ['ok']
                );           
            }               
        } else {
            $form->setData(array(
                    'name'=>$office->getName(),
                    'aplId'=>$office->getAplId(),     
                    'region'=>$office->getRegion(),     
                    'status'=>$office->getStatus(),     
                    'shippingLimit1'=>$office->getShippingLimit1(),     
                    'shippingLimit2'=>$office->getShippingLimit2(),     
                    'sbCard'=>$office->getSbCard(),     
                    'sbOwner'=>$office->getSbOwner(),     
                    'sbpMerchantId' => $office->getSbpMerchantId(),
                    'parent' => $office->getParentId(),
                    'linkReview' => $office->getLinkReview(),
                    'address' => $office->getAddress(),
                    'addressSms' => $office->getAddressSms(),
                ));
        }
        
        $this->layout()->setTemplate('layout/terminal');
        
        return new ViewModel([
                'form' => $form,
                'office' => $office
            ]);
    }

    /**
     * This action deletes a region.
     */
    public function deleteAction()
    {
        $id = (int)$this->params()->fromRoute('id', -1);
        if ($id<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $office = $this->entityManager->getRepository(Office::class)
                ->find($id);
        
        if ($office == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // Delete role.
        $this->officeManager->deleteOffice($office);
        
        // Add a flash message.
        $this->flashMessenger()->addSuccessMessage('Офис удален.');

        // Redirect to "index" region
        return $this->redirect()->toRoute('offices', ['action'=>'index']); 
    } 
    
    public function legalsAction()
    {
        $officeId = (int)$this->params()->fromRoute('id', -1);
        if ($officeId<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $office = $this->entityManager->getRepository(Office::class)
                ->find($officeId);
        
        if ($office == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $legals = $this->entityManager->getRepository(Legal::class)
                ->formOfficeLegals(['officeId' => $office->getId()]);
        
//        $result = [];
//        $legalContact = $office->getLegalContact();
//        $legals = $legalContact->getLegals();
        
        foreach ($legals as $legal){
            $result[$legal->getId()] = [
                'id' => $legal->getId(),
                'name' => $legal->getName(),                
            ];
        }
        
        return new JsonModel([
            'rows' => $result,
        ]);                  
    }
    
    public function displayOfficeAction()
    {
        $officeId = $this->params()->fromRoute('id', -1);
        
        $office = $this->entityManager->getRepository(Office::class)
                ->findOneById($officeId);
        
        if ($office == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        return new JsonModel([
            'name' => $office->getName(),
        ]);                   
    }

    public function editCommissionAction()
    {
        $commissionId = (int)$this->params()->fromRoute('id', -1);
        $officeId = (int)$this->params()->fromQuery('office', -1);
        
        $commission = null;
        if ($commissionId > 0){
            $commission = $this->entityManager->getRepository(Commission::class)
                    ->find($commissionId);
        }    
        if ($commission){
            $office = $commission->getOffice();
        } else {
            $office = $this->entityManager->getRepository(Office::class)
                    ->find($officeId);
        }    
        if ($office == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
                
        // Create form
        $form = new CommissionForm();
        
        // Check if user has submitted the form
        if ($this->getRequest()->isPost()) {
            
            // Fill in the form with POST data
            $data = $this->params()->fromPost();            
            
            $form->setData($data);
            
            // Validate form
            if($form->isValid()) {
                
                // Get filtered and validated data
                $data = $form->getData();
                
                if ($commission){
                    // Update commission.
                    $this->officeManager->updateCommissar($commission, $data);
                } else {
                    $this->officeManager->addCommissar($office, $data);                    
                }                   
                return new JsonModel(
                   ['ok']
                );           
            }              
        } else {
            if ($commission){
                $form->setData([
                        'name'=>$commission->getName(),
                        'position'=>$commission->getPosition(),     
                        'status'=>$commission->getStatus(),     
                    ]);
            }    
        }
        $this->layout()->setTemplate('layout/terminal');
        
        return new ViewModel([
                'form' => $form,
                'commission' => $commission,
                'office' => $office
            ]);
    }
    
    public function deleteCommissionAction()
    {
        $commissionId = (int)$this->params()->fromRoute('id', -1);
        
        $commission = null;
        if ($commissionId > 0){
            $commission = $this->entityManager->getRepository(Commission::class)
                    ->find($commissionId);
        }    
        if ($commission == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $officeId = $commission->getOffice()->getId();
        
        $this->officeManager->removeCommissar($commission);
                
        return new JsonModel(
           ['ok']
        );           
    }    
}
