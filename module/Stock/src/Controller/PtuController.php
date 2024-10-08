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
use Stock\Entity\Vtp;
use Stock\Entity\PtuGood;
use Application\Entity\Goods;
use Stock\Form\PtuForm;
use Stock\Form\PtuGoodForm;
use Stock\Form\PtuCostForm;
use Company\Entity\Office;
use Stock\Entity\Unit;
use Stock\Entity\Ntd;
use Company\Entity\Country;
use Application\Entity\Supplier;
use Company\Entity\Legal;
use Company\Entity\Contract;
use Company\Entity\Cost;

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
        $suppliers = $this->entityManager->getRepository(Supplier::class)
                ->findForFormPtu();
        $offices = $this->entityManager->getRepository(Office::class)
                ->findAll();
        $companies = $this->entityManager->getRepository(Legal::class)
                ->companies();
        return new ViewModel([
            'suppliers' => $suppliers,
            'offices' => $offices,
            'companies' => $companies,
            'allowDate' => date('Y-m-d', strtotime($this->ptuManager->getAllowDate().' + 1 day')),
        ]);  
    }
        
    public function contentAction()
    {
        ini_set('memory_limit', '1024M');
        	        
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order', 'DESC');
        $supplierId = $this->params()->fromQuery('supplier');
        $officeId = $this->params()->fromQuery('office');
        $companyId = $this->params()->fromQuery('company');
        $legalId = $this->params()->fromQuery('legal');
        $contractId = $this->params()->fromQuery('contract');
        $year_month = $this->params()->fromQuery('month');
        
        $year = $month = null;
        if ($year_month){
            $year = date('Y', strtotime($year_month));
            $month = date('m', strtotime($year_month));
        }
        
        $params = [
            'q' => $q, 'sort' => $sort, 'order' => $order, 
            'supplierId' => $supplierId, 'officeId' => $officeId,
            'companyId' => $companyId, 'legalId' => $legalId,
            'contractId' => $contractId,
            'year' => $year, 'month' => $month,
        ];
        $query = $this->entityManager->getRepository(Ptu::class)
                        ->findAllPtu($params);
        
        $total = $this->entityManager->getRepository(Ptu::class)
                        ->findAllPtuTotal($params);
        
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
            'allowDate' => $this->ptuManager->getAllowDate(),
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
        
//        if ($offset) {
//            $query->setFirstResult($offset);
//        }
//        if ($limit) {
//            $query->setMaxResults($limit);
//        }

        $result = $query->getResult(2);
        
        return new JsonModel($result);          
    }        

    public function costContentAction()
    {
        	        
        $ptuId = $this->params()->fromRoute('id', -1);
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order');
        
        $query = $this->entityManager->getRepository(Ptu::class)
                        ->findPtuCosts($ptuId, ['q' => $q, 'sort' => $sort, 'order' => $order]);
        
        $total = count($query->getResult(2));
        
        $result = $query->getResult(2);
        
        return new JsonModel($result);          
    }        
    
    public function fillContentAction()
    {
        	        
        $supplierId = $this->params()->fromRoute('id', -1);
        
        $query = $this->entityManager->getRepository(Ptu::class)
                        ->fillPtu($supplierId);
        
        $result = $query->getResult(2);
        
        return new JsonModel($result);          
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
        $supplierId = (int) $this->params()->fromQuery('supplier', -1);
        $legalId = (int) $this->params()->fromQuery('legal', -1);
        $contractId = (int) $this->params()->fromQuery('contract', -1);
        $copy = (int) $this->params()->fromQuery('copy', false);
        
        $ptu = $supplier = $legal = $company = $contract = null;
        $notDisabled = true;
        if ($ptuId > 0){
            $ptu = $this->entityManager->getRepository(Ptu::class)
                    ->findOneById($ptuId);
        }    
        
        if ($supplierId > 0){
            $supplier = $this->entityManager->getRepository(Supplier::class)
                    ->find($supplierId);
        }
        if ($legalId > 0){
            $legal = $this->entityManager->getRepository(Legal::class)
                    ->find($legalId);
        }
        if ($contractId > 0){
            $contract = $this->entityManager->getRepository(Contract::class)
                    ->find($contractId);
        }
        
        if ($ptu == null) {
            $officeId = (int)$this->params()->fromQuery('office', $this->ptuManager->currentUser()->getOffice()->getId());
            $office = $this->entityManager->getRepository(Office::class)
                    ->findOneById($officeId);
            $company = $this->entityManager->getRepository(Office::class)->findDefaultCompany($office);
            if ($supplier && !$legal){
                $legal = $this->entityManager->getRepository(Supplier::class)
                        ->findDefaultSupplierLegal($supplier);
                if ($legal && !$contract){
                    $contract = $this->entityManager->getRepository(Office::class)
                            ->findDefaultContract($office, $legal, null, Contract::PAY_CASHLESS);
                }    
            }
        } else {
            $supplier = $ptu->getSupplier();
            $office = $ptu->getOffice();
            $company = $ptu->getContract()->getCompany();
            $legal = $ptu->getLegal();            
        }       
        
        if ($this->getRequest()->isPost()){
            $data = $this->params()->fromPost();
            $supplier = $this->entityManager->getRepository(Supplier::class)
                    ->findOneById($data['supplier']);
            $office = $this->entityManager->getRepository(Office::class)
                    ->findOneById($data['office_id']);
            $contract = $this->entityManager->getRepository(Contract::class)
                    ->findOneById($data['contract_id']);
            $company = $contract->getCompany();
            $legal = $this->entityManager->getRepository(Legal::class)
                    ->findOneById($data['legal_id']);
        }
                
        $form = new PtuForm($this->entityManager, $office, $supplier, $company, $legal);
        
        $supplierList = ['--выбререте постащика--'];
        $suppliers = $this->entityManager->getRepository(Supplier::class)
                ->findForFormPtu();
        foreach ($suppliers as $formSupplier){
            $supplierList[$formSupplier->getId()] = $formSupplier->getName();
        }
        $form->get('supplier')->setValueOptions($supplierList);

        $contractList = ['--выбререте договор--'];
        if ($company && $legal){
            $contracts = $this->entityManager->getRepository(Contract::class)
                    ->findBy(['company' => $company->getId(), 'legal' => $legal->getId()], ['dateStart' => 'desc']);                                
            foreach ($contracts as $row){
                $contractList[$row->getId()] = $row->getContractPresentPay();
            }
        }    
        $form->get('contract_id')->setValueOptions($contractList);


        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {
                unset($data['company']);
//                unset($data['csrf']);
                $ptuGood = empty($data['ptuGood']) ? []:$data['ptuGood'];
                $ptuCost = empty($data['ptuCost']) ? []:$data['ptuCost'];
                unset($data['ptuGood']);
                $data['status_ex'] = Ptu::STATUS_EX_NEW;
                $data['contract'] = $contract;
                $data['supplier'] = $supplier;
                $data['legal'] = $legal;
                $data['office'] = $office;
                $data['apl_id'] = 0;
                
                if ($ptu){
                    $data['apl_id'] = $ptu->getAplId();
                    $this->ptuManager->updatePtu($ptu, $data);
                    $this->entityManager->refresh($ptu);
                } else {
                    $ptu = $this->ptuManager->addPtu($data);
                }    
                
//                var_dump($ptuGood); exit;
                $this->ptuManager->updatePtuGoods($ptu, $ptuGood, $ptuCost);
                
//                $this->ptuManager->repostPtu($ptu);
                
                return new JsonModel(
                   ['ok']
                );           
            }
        } else {
            $data = [
                'office_id' => $office->getId(),
                'company' => ($company) ? $company->getId():null,                
            ];
            if ($supplier){                
                $data['supplier'] = $supplier->getId();
                $data['legal_id'] = ($legal) ? $legal->getId():null;
                $data['contract_id'] = ($contract) ? $contract->getId():null;
            }    
            if ($ptu){
                $data = [
                    'office_id' => $ptu->getOffice()->getId(),
                    'company' => $ptu->getContract()->getCompany()->getId(),
                    'supplier' => $ptu->getSupplier()->getId(),
                    'legal_id' => $ptu->getLegal()->getId(),  
                    'contract_id' => $ptu->getContract()->getId(),  
                    'doc_date' => ($copy) ? date('Y-m-d'):$ptu->getDocDate(),  
                    'doc_no' => $ptu->getDocNo(),
                    'comment' => $ptu->getComment(),
                    'status' => $ptu->getStatus(),
                ];
                $notDisabled = $ptu->getDocDate() > $this->ptuManager->getAllowDate();
            }    
            $form->setData($data);
        }
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'ptu' => $ptu,
            'copy' => $copy,
            'allowDate' => $this->ptuManager->getAllowDate(),
            'disabled' => !$notDisabled,
            'supplier' => $supplier,
        ]);        
    }    
        
    public function goodEditFormAction()
    {        
        $params = $this->params()->fromQuery();
//        var_dump($params); exit;
        $good = $rowNo = $result = null;        
        if (isset($params['good'])){
            $good = $this->entityManager->getRepository(Goods::class)
                    ->find($params['good']['id']);            
        }
        if (isset($params['rowNo'])){
            $rowNo = $params['rowNo'];
        }
        
        $form = new PtuGoodForm($this->entityManager, $good);

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $form->setData($data);
            if (isset($data['good'])){
                $good = $this->entityManager->getRepository(Goods::class)
                        ->find($data['good']);            
            }

            if ($form->isValid()) {
                $result = 'ok';
                return new JsonModel([
                    'result' => $result,
                    'good' => [
                        'id' => $good->getId(),
                        'code' => $good->getCode(),
                        'name' => $good->getName(),
                        'producer' => $good->getProducer()->getName(),
                    ],
                ]);        
            }
        } else {
            if ($good){
                $data = [
                    'good' => $good->getId(),
                    'code' => $good->getCode(),
                    'goodInputName' => $good->getInputName(),
                    'quantity' => $params['quantity'],
                    'amount' => $params['amount'],
                    'price' => $params['amount']/$params['quantity'],
                    'unit' => (isset($params['unit']['name'])) ? $params['unit']['name']:null,
                    'country' => (isset($params['country']['name'])) ? $params['country']['name']:null,
                    'ntd' => (isset($params['ntd']['ntd'])) ? $params['ntd']['ntd']:null,
                ];
                $form->setData($data);
            }    
        }        

        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'rowNo' => $rowNo,
            'good' => $good,
        ]);        
    }
    
    public function costEditFormAction()
    {        
        $params = $this->params()->fromQuery();
//        var_dump($params); exit;
        $cost = $rowNo = $result = null;        
        if (isset($params['cost'])){
            $cost = $this->entityManager->getRepository(Cost::class)
                    ->find($params['cost']['id']);            
        }
        if (isset($params['rowNo'])){
            $rowNo = $params['rowNo'];
        }
        
        $form = new PtuCostForm($this->entityManager, $cost);
        
        $costList = ['--выберете услугу--'];
        $costs = $this->entityManager->getRepository(Cost::class)
                ->findBy([]);
        foreach ($costs as $row){
            $costList[$row->getId()] = $row->getName();
        }
        $form->get('cost')->setValueOptions($costList);

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $form->setData($data);
            if (isset($data['cost'])){
                $cost = $this->entityManager->getRepository(Cost::class)
                        ->find($data['cost']);            
            }

            if ($form->isValid()) {
                $result = 'ok';
                return new JsonModel([
                    'result' => $result,
                    'cost' => [
                        'id' => $cost->getId(),
                        'name' => $cost->getName(),
                    ],
                ]);        
            }
        } else {
            if ($cost){
                $data = [
                    'cost' => $cost->getId(),
                    'comment' => $params['comment'],
                    'quantity' => $params['quantity'],
                    'amount' => $params['amount'],
                    'price' => $params['amount']/$params['quantity'],
                ];
                $form->setData($data);
            }    
        }        

        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'rowNo' => $rowNo,
            'cost' => $cost,
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
        
        return new JsonModel(
           ['ok']
        );           
    }
    
    public function autocompleteGoodAction()
    {
        $result = [];
        $q = $this->params()->fromQuery('q');
        $aplId = $this->params()->fromQuery('aplId');
        
        if ($q){
            $query = $this->entityManager->getRepository(Goods::class)
                            ->autocompleteGood(['search' => $q]);
            $data = $query->getResult();
            foreach ($data as $row){
                $result[] = [
                    'id' => $row->getId(), 
                    'name' => $row->getInputName(), 
                    'code' => $row->getCode(),
                    'aplId' => $row->getAplId(),
                    'price' => $row->getFormatMeanPrice(),
                ];
            }
        }    
        
        if ($aplId){
            $good = $this->entityManager->getRepository(Goods::class)
                            ->findOneBy(['aplId' => $aplId]);
            if ($good){
                $result = [
                    'id' => $good->getId(), 
                    'name' => $good->getInputName(), 
                    'code' => $good->getCode(),
                    'aplId' => $good->getAplId(),
                    'price' => $good->getFormatMeanPrice(),
                ];
            }    
        }    
        
        return new JsonModel($result);
    }        
    
    public function autocompleteUnitAction()
    {
        $result = [];
        $q = $this->params()->fromQuery('q');
        
        if ($q){
            $query = $this->entityManager->getRepository(Unit::class)
                            ->autocompleteUnit(['search' => $q]);

            $data = $query->getResult();
            foreach ($data as $row){
                $result[] = $row->getName();
            }
        }    
        
        return new JsonModel($result);
    }        
    
    public function autocompleteNtdAction()
    {
        $result = [];
        $q = $this->params()->fromQuery('q');
        
        if ($q){
            $query = $this->entityManager->getRepository(Ntd::class)
                            ->autocompleteNtd(['search' => $q]);

            $data = $query->getResult();
            foreach ($data as $row){
                $result[] = $row->getNtd();
            }
        }    
        
        return new JsonModel($result);
    }        
    
    public function autocompleteCountryAction()
    {
        $result = [];
        $q = $this->params()->fromQuery('q');
        
        if ($q){
            $query = $this->entityManager->getRepository(Country::class)
                            ->autocompleteCountry(['search' => $q]);

            $data = $query->getResult();
            foreach ($data as $row){
                $result[] = $row->getName();
            }
        }    
        
        return new JsonModel($result);
    }        
    
    public function vtpCountAction()
    {
        $ptuId = (int)$this->params()->fromRoute('id', -1);
        $vtpCount = 0;

        if ($ptuId<0) {
            goto e;
        }
        
        $ptu = $this->entityManager->getRepository(Ptu::class)
                ->findOneById($ptuId);
        
        if ($ptu == null) {
            goto e;
        }        

        $vtpCount = $this->entityManager->getRepository(Vtp::class)
                ->count(['ptu' => $ptu->getId()]);
        
        e:        
        return new JsonModel([
            'id' => $ptuId,
            'vtpCount' => $vtpCount,
        ]);          
    }
    
    public function correctSupplierAction()
    {
        $this->ptuManager->correctSupplier();
        echo 'ok';
        exit;
    }
    
    public function repostExAction()
    {
        $ptuId = $this->params()->fromRoute('id', -1);
        $ptu = $this->entityManager->getRepository(Ptu::class)
                ->find($ptuId);        

        if ($ptu == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->ptuManager->repostEx($ptu);
        $query = $this->entityManager->getRepository(Ptu::class)
                ->findAllPtu(['ptuId' => $ptu->getId()]);
        $result = $query->getOneOrNullResult(2);
        
        return new JsonModel(
           $result
        );           
    }     
    
    public function statusAction()
    {
        $ptuId = $this->params()->fromRoute('id', -1);
        $status = $this->params()->fromQuery('status', Ptu::STATUS_ACTIVE);
        $ptu = $this->entityManager->getRepository(Ptu::class)
                ->find($ptuId);        

        if ($ptu == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->ptuManager->updatePtuStatus($ptu, $status);
        $query = $this->entityManager->getRepository(Ptu::class)
                ->findAllPtu(['ptuId' => $ptu->getId()]);
        $result = $query->getOneOrNullResult(2);
        
        return new JsonModel(
           $result
        );           
    }            
}
