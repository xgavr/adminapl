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
use Application\Entity\Goods;
use Stock\Form\PtuForm;
use Stock\Form\PtuGoodForm;
use Application\Entity\Supplier;
use Company\Entity\Legal;
use Company\Entity\Contract;
use Stock\Entity\Revise;
use Company\Entity\Office;

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
            $officeId = (int)$this->params()->fromQuery('office', 1);
            $office = $this->entityManager->getRepository(Office::class)
                    ->findOneById($officeId);
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

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {
                unset($data['supplier']);
                unset($data['company']);
                unset($data['csrf']);
                $ptuGood = $data['ptuGood'];
                unset($data['ptuGood']);
                $data['status_ex'] = Ptu::STATUS_EX_NEW;
                $data['contract'] = $contract;
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
                
                $this->ptuManager->updatePtuGoods($ptu, $ptuGood);
                
                $this->ptuManager->repostPtu($ptu);
                
                return new JsonModel(
                   ['ok']
                );           
            }
        } else {
            if ($ptu){
                $data = [
                    'office_id' => $ptu->getContract()->getOffice()->getId(),
                    'company' => $ptu->getContract()->getCompany()->getId(),
                    'supplier' => $ptu->getSupplier()->getId(),
                    'legal_id' => $ptu->getLegal()->getId(),  
                    'contract_id' => $ptu->getContract()->getId(),  
                    'doc_date' => $ptu->getDocDate(),  
                    'doc_no' => $ptu->getDocNo(),
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
        
    public function goodEditFormAction()
    {        
        $params = $this->params()->fromQuery();
//        var_dump($params); exit;
        $good = $rowNo = $result = null;        
        if (isset($params['good'])){
            $good = $this->entityManager->getRepository(Goods::class)
                    ->findOneById($params['good']['id']);            
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
                        ->findOneById($data['good']);            
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
                    'good' => $params['good']['id'],
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
    
    public function autocompeteUnitAction()
    {
        $result = [];
        $q = $this->params()->fromQuery('q');
        
        if ($q){
            $query = $this->entityManager->getRepository(Unit::class)
                            ->autocompeteUnit(['search' => $q]);

            $data = $query->getResult();
            foreach ($data as $row){
                $result[] = $row->getName();
            }
        }    
        
        return new JsonModel($result);
    }        
    
    public function autocompeteNtdAction()
    {
        $result = [];
        $q = $this->params()->fromQuery('q');
        
        if ($q){
            $query = $this->entityManager->getRepository(Ntd::class)
                            ->autocompeteNtd(['search' => $q]);

            $data = $query->getResult();
            foreach ($data as $row){
                $result[] = $row->getName();
            }
        }    
        
        return new JsonModel($result);
    }        
    
    public function autocompeteCountryAction()
    {
        $result = [];
        $q = $this->params()->fromQuery('q');
        
        if ($q){
            $query = $this->entityManager->getRepository(Country::class)
                            ->autocompeteCountry(['search' => $q]);

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
    
}
