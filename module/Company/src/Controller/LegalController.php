<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Company\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Company\Entity\Office;
use Company\Entity\Legal;
use Company\Entity\BankAccount;
use Company\Entity\Contract;
use Company\Form\LegalForm;
use Company\Form\BankAccountForm;
use Company\Form\ContractForm;
use Application\Entity\Contact;
use Laminas\View\Model\JsonModel;
use Cash\Entity\Cash;
use Company\Entity\EdoOperator;
use Company\Form\EdoOperatorForm;
use Company\Entity\LegalLocation;
use Company\Form\LocationForm;

class LegalController extends AbstractActionController
{
    
    /**
     * Entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * Office manager.
     * @var \Company\Service\OfficeManager
     */
    private $officeManager;

    /**
     * Contact manager.
     * @var \Application\Service\ContactManager
     */
    private $contactManager;
    
    /**
     * Legal manager.
     * @var \Company\Service\LegalManager
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
    
    public function edoOperatorsAction()
    {
        
        return new ViewModel([
        ]);  
    }

    public function edoOperatorContentAction()
    {
        	        
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order');
        $limit = $this->params()->fromQuery('limit');
        $status = $this->params()->fromQuery('status');
        
        $query = $this->entityManager->getRepository(EdoOperator::class)
                        ->findAllEdoOperator(['q' => $q, 'sort' => $sort, 'order' => $order, 'status' => $status]);

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
    
    public function edoOperatorFormAction()
    {
        $edoOperatorId = (int)$this->params()->fromRoute('id', -1);
        
        if ($edoOperatorId>0) {
            $edoOperator = $this->entityManager->getRepository(EdoOperator::class)
                    ->find($edoOperatorId);
        } else {
            $edoOperator = null;
        }
                
        $form = new EdoOperatorForm();
        
        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {

                if ($edoOperator){
                    $this->legalManager->updateEdoOperator($edoOperator, $data);                    
                } else{
                    $this->legalManager->addEdoOperator($data);
                }    
                
                return new JsonModel(
                   ['ok']
                );           
            } else {
                var_dump($form->getMessages());
            }
        } else {
            if ($edoOperator){
                $data = [
                    'name' => $edoOperator->getName(),  
                    'code' => $edoOperator->getCode(),
                    'inn' =>$edoOperator->getInn(),
                    'info' => $edoOperator->getInfo(),  
                    'status' => $edoOperator->getStatus(),
                    'site' => $edoOperator->getSite(),
                ];
                $form->setData($data);
            }    
        }  

        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'edoOperator' => $edoOperator,
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

                $this->legalManager->addLegal($contact, $data);
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
        
        $edoOperatorlist = [0 => ''];
        $edoOperators = $this->entityManager->getRepository(EdoOperator::class)
                    ->findBy(['status' => EdoOperator::STATUS_ACTIVE]);
        foreach ($edoOperators as $edoOperator) {
            $edoOperatorlist[$edoOperator->getId()] = $edoOperator->getName();
        }
        $legalform->get('edoOperator')->setValueOptions($edoOperatorlist);


        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $legalform->setData($data);

            if ($legalform->isValid()) {

                $this->legalManager->addLegal($contact, $data);
                
                return new JsonModel(
                   ['result' => 'ok']
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
                        'okato' => $legal->getOkato(),  
                        'oktmo' => $legal->getOktmo(),  
                        'head' => $legal->getHead(),  
                        'chiefAccount' => $legal->getChiefAccount(),  
                        'info' => $legal->getInfo(),  
                        'address' => $legal->getAddress(),  
                        'status' => $legal->getStatus(),  
                        'dateStart' => $legal->getDateStart(),
                        'edoOperator' => ($legal->getEdoOperator()) ? $legal->getEdoOperator()->getId():null,
                        'edoAddress' => $legal->getEdoAddress(),
                        'sbpLegalId' => $legal->getSbpLegalId(),
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
        
        $this->legalManager->removeLegalAssociation($legal, $contact);
        
        // Перенаправляем пользователя на страницу "legal".
        return $this->redirect()->toRoute('legals', ['action' => 'legal', 'id' => $contact->getId()]);
    }
        
    public function deleteAssociationFormAction()
    {
        $legalId = $this->params()->fromRoute('id', -1);

        $legal = $this->entityManager->getRepository(Legal::class)
                ->find($legalId);

        if ($legal == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $contactId = (int)$this->params()->fromQuery('contact', -1);
        
        // Validate input parameter
        if ($contactId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $contact = $this->entityManager->getRepository(Contact::class)
                ->find($contactId);
        
        if ($contact == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        
        $this->legalManager->removeLegalAssociation($legal, $contact);
        
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
                ->find($legalId);
        
        if ($legal == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $bankAccountId = (int)$this->params()->fromQuery('bankAccount', -1);
        
        // Validate input parameter
        if ($bankAccountId>0) {
            $bankAccount = $this->entityManager->getRepository(BankAccount::class)
                    ->find($bankAccountId);
        } else {
            $bankAccount = null;
        }
                
        $form = new BankAccountForm();

        $cashList = [0 => 'нет'];
        $form->get('cash')->setAttribute('disabled', 'true');
        $form->get('cashSbp')->setAttribute('disabled', 'true');
        if ($legal->isOfficeLegal()){
            $offices = $legal->getOffices();
            foreach ($offices as $office){
                foreach ($office->getCashes() as $cash){
                    if ($cash->getPayment() == Cash::PAYMENT_CASHLESS){
                        $cashList[$cash->getId()] = '('.$office->getName().') '.$cash->getName();
                    }    
                }
            }    
            $form->get('cash')->removeAttribute('disabled');
            $form->get('cashSbp')->removeAttribute('disabled');
        }
        
        $form->get('cash')->setValueOptions($cashList);
        $form->get('cashSbp')->setValueOptions($cashList);
        
        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {
                if (!empty($data['cash'])){
                    $data['cash'] = $this->entityManager->getRepository(Cash::class)
                            ->find($data['cash']);
                }
                if (!empty($data['cashSbp'])){
                    $data['cashSbp'] = $this->entityManager->getRepository(Cash::class)
                            ->find($data['cashSbp']);
                }

                if ($bankAccount){
                    $this->legalManager->updateBankAccount($bankAccount, $data, true);                    
                } else{
                    $this->legalManager->addBankAccount($legal, $data, true);
                }    
                
                return new JsonModel(
                   ['ok']
                );           
            } else {
                var_dump($form->getMessages());
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
                    'accountType' => $bankAccount->getAccountType(),
                    'api' => $bankAccount->getApi(),  
                    'statement' => $bankAccount->getStatement(),
                    'cash' => ($bankAccount->getCash()) ? $bankAccount->getCash()->getId():null,
                    'cashSbp' => ($bankAccount->getCashSbp()) ? $bankAccount->getCashSbp()->getId():null,
                    'dateStart' => $bankAccount->getDateStart(),
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
    
    public function bankInfoAction()
    {
        $bik = $this->params()->fromQuery('bik', '');
        
        $result = [];
        
        if ($bik){
            $data = $this->legalManager->bankInfo($bik);
        } else {
            $data = [];
        }    
        
        if (!is_array($data)){
            $data = [];
        }
        
        if (is_array($data)){
            foreach ($data as $row){
                $result['data'] = $row;
                $result['name'] = $row['value'];
                $result['city'] = $row['data']['payment_city'];
                $result['ks'] = $row['data']['correspondent_account'];
                if (empty($result['ks'])){
                    if (!empty($row['data']['treasury_accounts'])){
                        foreach ($row['data']['treasury_accounts'] as $ts){
                            $result['ks'] = $ts;
                            break;
                        }
                    }
                }    
                break;
            }
        }    
        
        return new JsonModel(
           $result
        );           
    }
    
    public function innInfoAction()
    {
        $inn = $this->params()->fromQuery('inn', '');
        
        if ($inn){
            $data = $this->legalManager->innInfo($inn);
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
    
    public function contactByInnAction()
    {
        $inn = $this->params()->fromQuery('inn', '');
        
        if ($inn){
            $legal = $this->entityManager->getRepository(Legal::class)
                    ->findOneBy(['inn' => $inn]);
            if ($legal){
                $contacts = $legal->getContacts();
                foreach ($contacts as $contact){
                    return $this->redirect()->toRoute('contact', ['action' => 'view', 'id' => $contact->getId()]);                
                }    
            }
        }
        
        $this->getResponse()->setStatusCode(404);
        return;                        
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

        $officeId = (int)$this->params()->fromQuery('office', 1);
        $office = $this->entityManager->getRepository(Office::class)
                ->find($officeId);

        $contractId = (int)$this->params()->fromQuery('contract', -1);
        
        // Validate input parameter
        if ($contractId>0) {
            $contract = $this->entityManager->getRepository(Contract::class)
                    ->find($contractId);
            $office = $contract->getOffice();
        } else {
            $contract = null;
        }
                
        $form = new ContractForm($this->entityManager, $office);

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            
            if (empty($data['office'])){
                $data['office'] = $office->getId();
            }    
            if (empty($data['company'])){
                $company = $this->entityManager->getRepository(Office::class)
                        ->findDefaultCompany($office);
                if ($company){
                    $data['company'] = $company->getId();
                }            
            }
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
                    'office' => $contract->getOffice(),
                    'company' => $contract->getCompany(),
                    'kind' =>$contract->getKind(),
                    'pay' => $contract->getPay(),
                    'nds' => $contract->getNds(),
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
    
    public function displayLegalAction()
    {
        $legalId = $this->params()->fromRoute('id', -1);
        
        $legal = $this->entityManager->getRepository(Legal::class)
                ->findOneById($legalId);
        
        if ($legal == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        return new JsonModel([
            'name' => $legal->getName(),
        ]);                   
    }
    
    public function locationFormAction()
    {
        $legalId = (int)$this->params()->fromRoute('id', -1);
        
        // Validate input parameter
        if ($legalId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $legal = $this->entityManager->getRepository(Legal::class)
                ->find($legalId);
        
        if ($legal == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $locationId = (int)$this->params()->fromQuery('location', -1);
        
        $location = null;
        if ($locationId>0) {
            $location = $this->entityManager->getRepository(LegalLocation::class)
                    ->find($locationId);
        }
                
        $form = new LocationForm($this->entityManager);

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            
            $form->setData($data);

            if ($form->isValid()) {

                if ($location){
                    $this->legalManager->updateLegalLocation($location, $data);                    
                } else{
                    $this->legalManager->addLegalLocation($legal, $data);
                }    
                
                return new JsonModel(
                   ['ok']
                );           
            }
        } else {
            if ($location){
                $data = [
                    'address' => $location->getAddress(),  
                    'dateStart' => $location->getDateStart(),
                    'status' => $location->getStatus(),
                    'kpp' => $location->getKpp(),
                ];
                $form->setData($data);
            }    
        }        
        
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'legal' => $legal,
            'location' => $location,
        ]);        
    }    
    
    public function deleteLocationFormAction()
    {
        $locationId = $this->params()->fromRoute('id', -1);
        
        $location = $this->entityManager->getRepository(LegalLocation::class)
                ->find($locationId);
        
        if ($location == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->legalManager->removeLegalLocation($location);
        
        return new JsonModel(
           ['ok']
        );           
        
        exit;
    }    
    
    public function updateBalancesAction()
    {
        $this->legalManager->contractsBalance();        
        
        return new JsonModel(
           ['result' => 'ok']
        );                   
    }
    
}
