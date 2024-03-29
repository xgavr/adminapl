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
use Application\Entity\Client;

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
        $kind = (int)$this->params()->fromQuery('kind', Revise::KIND_REVISE_SUPPLIER);

        $suppliers = $this->entityManager->getRepository(Supplier::class)
                ->findForPtu();
        $offices = $this->entityManager->getRepository(Office::class)
                ->findAll();
        return new ViewModel([
            'suppliers' => $suppliers,
            'offices' => $offices,
            'years' => array_combine(range(date("Y"), 2014), range(date("Y"), 2014)),
            'monthes' => array_combine(range(1, 12), range(1, 12)),
            'kind' => $kind,
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
        $kind = $this->params()->fromQuery('kind');
        $year_month = $this->params()->fromQuery('month');
        
        $year = $month = null;
        if ($year_month){
            $year = date('Y', strtotime($year_month));
            $month = date('m', strtotime($year_month));
        }
        
        $params = [
            'q' => $q, 'sort' => $sort, 'order' => $order, 
            'supplierId' => $supplierId, 'officeId' => $officeId,
            'year' => $year, 'month' => $month, 'kind' => $kind,
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

    /**
     * Подготовка формы
     * 
     * @param ReviseForm $form
     * @param Revise $revise
     * @param Supplier $supplier
     * @param Client $client
     * @param Legal $legal
     * @param Contract $contract
     * @param int $kind
     * @return null
     */
    protected function prepareForm($form, $revise = null, $supplier= null, $client= null,
            $legal = null, $contract=null, $kind = Revise::KIND_REVISE_SUPPLIER)
    {
        $currentUser = $this->reviseManager->currentUser();
        $legals = [];
        $legalList = [];
        $contracts = [];
        $contractList = [];

        if ($revise){
            $office = $revise->getOffice();
            if ($revise->getLegal()){
                $contracts = $this->entityManager->getRepository(Contract::class)
                        ->findBy(['company' => $revise->getCompany()->getId(), 'legal' => $revise->getLegal()->getId()], ['dateStart' => 'desc']);                                
            }
        } else {
            $office = $currentUser->getOffice();
        }        
            
        if ($supplier && $kind == Revise::KIND_REVISE_SUPPLIER){
            $legalContact = $supplier->getLegalContact();
            $legals = $legalContact->getLegals();
        }    
        
        if ($client && $kind == Revise::KIND_REVISE_CLIENT){
            $form->get('phone')->setValue($client->getContactPhone());
            $legals = $this->entityManager->getRepository(Client::class)
                    ->findLegals($client);
            $legalList[] = '---';                
        }    

        foreach ($legals as $legal){
            $legalList[$legal->getId()] = $legal->getName();                
        }            
        $form->get('legal')->setValueOptions($legalList);

        foreach ($contracts as $contract){
            $contractList[$contract->getId()] = $contract->getContractPresentPay();                
        }            
        $form->get('contract')->setValueOptions($contractList); 
                    
        $offices = $this->entityManager->getRepository(Office::class)
                ->findBy([], ['status' => 'ASC', 'name' => 'ASC']);
        $officeList = ['--не выбран--'];
        foreach ($offices as $bo) {
            $officeList[$bo->getId()] = $bo->getName();
        }
        $form->get('office')->setValueOptions($officeList);        
        $form->get('office')->setValue($office->getId());

        $companies = $this->entityManager->getRepository(Legal::class)
                ->companies();
        $companyList = [];
        foreach ($companies as $row) {
            $companyList[$row->getId()] = $row->getName();
        }
        $form->get('company')->setValueOptions($companyList);
        
        if ($kind == Revise::KIND_REVISE_SUPPLIER){
            $suppliers = $this->entityManager->getRepository(Supplier::class)
                    ->findBy([], ['status' => 'ASC', 'name' => 'ASC']);
            $supplierList = ['--не выбран--'];
            foreach ($suppliers as $row) {
                $supplierList[$row->getId()] = $row->getName();
            }
            $form->get('supplier')->setValueOptions($supplierList);
        }    
    }
    
    public function editFormAction()
    {
        $reviseId = (int)$this->params()->fromRoute('id', -1);
        $kind = (int)$this->params()->fromQuery('kind');
        $supplierId = (int) $this->params()->fromQuery('supplier', -1);
        $clientId = (int) $this->params()->fromQuery('client', -1);
        $legalId = (int) $this->params()->fromQuery('legal', -1);
        $contractId = (int) $this->params()->fromQuery('contract', -1);
        
        $revise = $contactName = $client = $supplier = $contact = $legal = $contract = null;
        $notDisabled = true;
        
        if ($supplierId > 0){
            $supplier = $this->entityManager->getRepository(Supplier::class)
                    ->find($supplierId);
        }
        
        if ($clientId > 0){
            $client = $this->entityManager->getRepository(Client::class)
                    ->find($clientId);
            $contact = $client->getContact();
        }

        if ($legalId > 0){
            $legal = $this->entityManager->getRepository(Legal::class)
                    ->find($legalId);
        }

        if ($contractId > 0){
            $contract = $this->entityManager->getRepository(Contract::class)
                    ->find($contractId);
            $legal = $contract->getLegal();
        }
        
        if ($reviseId > 0){
            $revise = $this->entityManager->getRepository(Revise::class)
                    ->find($reviseId);
            $kind = $revise->getKind();
            $supplier = $revise->getSupplier();
            $contact = $revise->getContact();
            $legal = $revise->getLegal();
            $contract = $revise->getContract();
        }    
        
        if ($contact){
            $contactName = $contact->getName();
            $client = $contact->getClient();
        }    
        
        $form = new ReviseForm($this->entityManager);
        $this->prepareForm($form, $revise, $supplier, $client, $legal, $contract, $kind);
        
        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $data['kind'] = $kind;
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
                $notDisabled = $revise->getDocDate() > $this->reviseManager->getAllowDate();
            }    
        }
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'revise' => $revise,
            'kind' => $kind,
            'contactName' => $contactName,
            'allowDate' => $this->reviseManager->getAllowDate(),
            'disabled' => !$notDisabled,
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
    
    public function resetClientBalanceAction()
    {
        $clientId = $this->params()->fromRoute('id', -1);
        $client = $this->entityManager->getRepository(Client::class)
                ->find($clientId);        

        if ($client == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->reviseManager->resetClientBalance($client);
        
        return new JsonModel(
           ['result' => 'ok-reload']
        );           
    }
    
    public function resetClientBalancesAction()
    {
        $year = $this->params()->fromQuery('year');
        $this->reviseManager->resetClientBalances($year);
        
        return new JsonModel(
           ['result' => 'ok']
        );           
    }
    
    public function statusAction()
    {
        $reviseId = $this->params()->fromRoute('id', -1);
        $status = $this->params()->fromQuery('status', Revise::STATUS_ACTIVE);
        
        $revise = $this->entityManager->getRepository(Revise::class)
                ->find($reviseId);        

        if ($revise == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->reviseManager->updateReviseStatus($revise, $status);
        
        $result = [];
        
//        if ($revise->getKind() == Revise::KIND_REVISE_CLIENT){
//            $query = $this->entityManager->getRepository(Client::class)
//                            ->retails($revise->getContact()->getClient(), [
//                                'docKey' => $revise->getLogKey(),
//                            ]);            
//            $result = $query->getOneOrNullResult(2);
//        }
                
        return new JsonModel(
           $result
        );           
    }            
    
}
