<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Company\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Company\Entity\Office;
use Company\Entity\Legal;
use Company\Entity\BankAccount;
use Company\Entity\Contract;
use Company\Form\LegalForm;
use Company\Form\BankAccountForm;
use Company\Form\ContractForm;
use Application\Entity\Contact;
use Zend\View\Model\JsonModel;

class LegalController extends AbstractActionController
{
    
    /**
     * Entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * Office manager.
     * @var Company\Service\OfficeManager
     */
    private $officeManager;

    /**
     * Contact manager.
     * @var Application\Service\ContactManager
     */
    private $contactManager;
    
    /**
     * Legal manager.
     * @var Company\Service\LegalManager
     */
    private $legalManager;
    
    /**
     * Constructor. 
     */
    public function __construct($entityManager, $legalManager, $officeManager, $contactManager)
    {
        $this->entityManager = $entityManager;
        $this->officeManager = $officeManager;
        $this->contactManager = $contactManager;
        $this->legalManager = $legalManager;
    }
    
    public function indexAction()
    {
        $legals = $this->entityManager->getRepository(Legal::class)
                ->findBy([], ['id'=>'ASC']);
        
        return new ViewModel([
            'legals' => $legals
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

        $contacts = $office->getContacts();

        if (!count($contacts)){
            $data['full_name'] = $data['name'] = $office->getName();
            $data['status'] = Contact::STATUS_ACTIVE;
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
                ));
        }
        
        return new ViewModel([
                'form' => $form,
                'office' => $office
            ]);
    }
    
    public function legalAction()
    {
        $contactId = (int)$this->params()->fromRoute('id', -1);
        
        // Validate input parameter
        if ($contactId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $contact = $this->entityManager->getRepository(Contact::class)
                ->findOneById($contactId);
        
        if ($contact == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $legalform = new LegalForm($this->entityManager);

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $legalform->setData($data);

            if ($legalform->isValid()) {

                $this->legalManager->addLegal($contact, $data, true);
                $this->flashMessenger()->addSuccessMessage('Юридическое лицо сохранено');
            
                
            } else {
                var_dump($legalform->getMessages());
            }
        }            
        
        // Render the view template.
        return new ViewModel([
            'legalForm' => $legalform,
            'contact' => $contact,
            'parent' => $this->contactManager->getParent($contact),
        ]);
    }
    
    public function formAction()
    {
        $contactId = (int)$this->params()->fromRoute('id', -1);
        
        // Validate input parameter
        if ($contactId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $contact = $this->entityManager->getRepository(Contact::class)
                ->findOneById($contactId);
        
        if ($contact == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $legalform = new LegalForm($this->entityManager);

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $legalform->setData($data);

            if ($legalform->isValid()) {

                $this->legalManager->addLegal($contact, $data, true);
                
                return new JsonModel(
                   ['ok']
                );           
            }
        } else {           

            $legalId = $this->params()->fromQuery('legal', null);

            if ($legalId){
                $legal = $this->entityManager->getRepository(Legal::class)
                        ->findOneById($legalId);
                
                if ($legal){
                    $data = [
                        'name' => $legal->getName(),  
                        'inn' => $legal->getInn(),  
                        'kpp' => $legal->getKpp(),  
                        'ogrn' => $legal->getOgrn(),  
                        'okpo' => $legal->getOkpo(),  
                        'head' => $legal->getHead(),  
                        'chiefAccount' => $legal->getChiefAccount(),  
                        'info' => $legal->getInfo(),  
                        'address' => $legal->getAddress(),  
                        'status' => $legal->getStatus(),  
                        'dateStart' => $legal->getDateStart(),  
                    ];
                    $legalform->setData($data);
                }    
            }
        }
        
        
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'legalForm' => $legalform,
            'contact' => $contact,
        ]);
    }
    
    public function findAction()
    {
        $inn = $this->params()->fromRoute('id', null);
        $kpp = $this->params()->fromQuery('kpp', null);
        
        $result = [];

        if ($inn){
            $legal = $this->entityManager->getRepository(Legal::class)
                ->findOneByInnKpp($inn, $kpp, 2);            
            
            if ($legal){
                $result = $legal[0];
            }
        }
        
        return new JsonModel(
           $result
        );           
    }
    
    public function deleteAssociationAction()
    {
        $contactId = (int)$this->params()->fromRoute('id', -1);
        
        // Validate input parameter
        if ($contactId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $contact = $this->entityManager->getRepository(Contact::class)
                ->findOneById($contactId);
        
        if ($contact == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        $legalId = $this->params()->fromQuery('legal', -1);
        
        $legal = $this->entityManager->getRepository(Legal::class)
                ->findOneById($legalId);
        
        if ($legal == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->legalManager->removeLegalAssociation($legal);
        
        // Перенаправляем пользователя на страницу "legal".
        return $this->redirect()->toRoute('legals', ['action' => 'legal', 'id' => $contact->getId()]);
    }
        
    public function deleteAssociationFormAction()
    {
        $legalId = $this->params()->fromRoute('id', -1);
        
        $legal = $this->entityManager->getRepository(Legal::class)
                ->findOneById($legalId);
        
        if ($legal == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->legalManager->removeLegalAssociation($legal);
        
        return new JsonModel(
           ['ok']
        );           
        
        exit;
    }

    public function bankAccountFormAction()
    {
        $legalId = (int)$this->params()->fromRoute('id', -1);
        
        // Validate input parameter
        if ($legalId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $legal = $this->entityManager->getRepository(Legal::class)
                ->findOneById($legalId);
        
        if ($legal == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $bankAccountId = (int)$this->params()->fromQuery('bankAccount', -1);
        
        // Validate input parameter
        if ($bankAccountId>0) {
            $bankAccount = $this->entityManager->getRepository(BankAccount::class)
                    ->findOneById($bankAccountId);
        } else {
            $bankAccount = null;
        }
        
        
        $form = new BankAccountForm();

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {

                if ($bankAccount){
                    $this->legalManager->updateBankAccount($bankAccount, $data, true);                    
                } else{
                    $this->legalManager->addBankAccount($legal, $data, true);
                }    
                
                return new JsonModel(
                   ['ok']
                );           
            }
        } else {
            if ($bankAccount){
                $data = [
                    'name' => $bankAccount->getName(),  
                    'city' => $bankAccount->getCity(),  
                    'bik' => $bankAccount->getBik(),  
                    'rs' => $bankAccount->getRs(),  
                    'ks' => $bankAccount->getKs(),  
                    'status' => $bankAccount->getStatus(),  
                    'api' => $bankAccount->getApi(),  
                    'statement' => $bankAccount->getStatement(),  
                ];
                $form->setData($data);
            }    
        }        
        
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'legal' => $legal,
            'bankAccount' => $bankAccount,
        ]);        
    }
        
    public function deleteBankAccountAction()
    {
        $bankAccountId = (int) $this->params()->fromRoute('id', -1);
        
        // Validate input parameter
        if ($bankAccountId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $bankAccount = $this->entityManager->getRepository(BankAccount::class)
                ->findOneById($bankAccountId);
        
        if ($bankAccount == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $legal = $bankAccount->getLegal();

        $contacts = $legal->getContacts();

        $this->legalManager->removeBankAccount($bankAccount);
        
        // Перенаправляем пользователя на страницу "legal".
        foreach ($contacts as $contact){
            return $this->redirect()->toRoute('legals', ['action' => 'legal', 'id' => $contact->getId()]);
        }
        
        return;
    }
    
    public function deleteBankAccountFormAction()
    {
        $bankAccountId = $this->params()->fromRoute('id', -1);
        
        $bankAccount = $this->entityManager->getRepository(BankAccount::class)
                ->findOneById($bankAccountId);
        
        if ($bankAccount == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->legalManager->removeBankAccount($bankAccount);
        
        return new JsonModel(
           ['ok']
        );           
        
        exit;
    }
        
    
    
    public function bikInfoAction()
    {
        $bik = (int) $this->params()->fromRoute('id', '');
        
        if ($bik){
            $data = $this->legalManager->bikInfo($bik);
        } else {
            $data = [];
        }    
        
        if (!is_array($data)){
            $data = [];
        }
        return new JsonModel(
           $data
        );           
    }
    
    public function contractFormAction()
    {
        $legalId = (int)$this->params()->fromRoute('id', -1);
        
        // Validate input parameter
        if ($legalId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $legal = $this->entityManager->getRepository(Legal::class)
                ->findOneById($legalId);
        
        if ($legal == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $contractId = (int)$this->params()->fromQuery('contract', -1);
        
        // Validate input parameter
        if ($contractId>0) {
            $contract = $this->entityManager->getRepository(Contract::class)
                    ->findOneById($contractId);
        } else {
            $contract = null;
        }
        
        
        $form = new ContractForm();

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {

                if ($contract){
                    $this->legalManager->updateContract($contract, $data, true);                    
                } else{
                    $this->legalManager->addContract($legal, $data, true);
                }    
                
                return new JsonModel(
                   ['ok']
                );           
            }
        } else {
            if ($contract){
                $data = [
                    'name' => $contract->getName(),  
                    'act' => $contract->getAct(),  
                    'dateStart' => $contract->getDateStart(),  
                    'status' => $contract->getStatus(),  
                ];
                $form->setData($data);
            }    
        }        
        
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'legal' => $legal,
            'contract' => $contract,
        ]);        
    }
        
    public function deleteContractAction()
    {
        $contractId = (int) $this->params()->fromRoute('id', -1);
        
        // Validate input parameter
        if ($contractId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $contract = $this->entityManager->getRepository(Contract::class)
                ->findOneById($contractId);
        
        if ($contract == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $legal = $contract->getLegal();

        $contacts = $legal->getContacts();

        $this->legalManager->removeContract($contract);
        
        // Перенаправляем пользователя на страницу "legal".
        foreach ($contacts as $contact){
            return $this->redirect()->toRoute('legals', ['action' => 'legal', 'id' => $contact->getId()]);
        }
        
        return;
    }
    
    public function deleteContractFormAction()
    {
        $contractId = $this->params()->fromRoute('id', -1);
        
        $contract = $this->entityManager->getRepository(Contract::class)
                ->findOneById($contractId);
        
        if ($contract == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->legalManager->removeContract($contract);
        
        return new JsonModel(
           ['ok']
        );           
        
        exit;
    }
    
}
