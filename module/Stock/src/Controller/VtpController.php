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
use Stock\Entity\Vtp;
use Stock\Entity\VtpGood;
use Stock\Entity\Ptu;
use Application\Entity\Goods;
use Stock\Form\VtpForm;
use Stock\Form\VtpGoodForm;
use Company\Entity\Office;
use Application\Entity\Supplier;

class VtpController extends AbstractActionController
{

    /**
     * Менеджер сущностей.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * Менеджер пту.
     * @var \Stock\Service\VtpManager
     */
    private $vtpManager;

    public function __construct($entityManager, $vtpManager) 
    {
        $this->entityManager = $entityManager;
        $this->vtpManager = $vtpManager;
    }   

    public function indexAction()
    {
        $suppliers = $this->entityManager->getRepository(Vtp::class)
                ->activeSuppliers();
        $offices = $this->entityManager->getRepository(Office::class)
                ->findAll();
        return new ViewModel([
            'suppliers' => $suppliers,
            'offices' => $offices,
            'years' => array_combine(range(date("Y"), 2014), range(date("Y"), 2014)),
            'monthes' => array_combine(range(1, 12), range(1, 12)),
            'allowDate' => $this->vtpManager->getAllowDate(),
        ]);  
    }
        
    public function contentAction()
    {
        	        
        $ptuId = (int)$this->params()->fromRoute('id', -1);
        $ptu = null;
        if ($ptuId > 0){
            $ptu = $this->entityManager->getRepository(Ptu::class)
                    ->find($ptuId);
        }    
        
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $sort = $this->params()->fromQuery('sort', 'docDate');
        $order = $this->params()->fromQuery('order', 'DESC');
        $supplierId = $this->params()->fromQuery('supplier');
        $officeId = $this->params()->fromQuery('office');
        $year_month = $this->params()->fromQuery('month');
        $statusDoc = $this->params()->fromQuery('statusDoc');
        $status = $this->params()->fromQuery('status');
        $vtpType = $this->params()->fromQuery('vtpType');
        
        $year = $month = null;
        if ($year_month){
            $year = date('Y', strtotime($year_month));
            $month = date('m', strtotime($year_month));
        }        
        $params = [
            'q' => trim($q), 'sort' => $sort, 'order' => $order, 
            'supplierId' => $supplierId, 'officeId' => $officeId,
            'year' => $year, 'month' => $month, 'statusDoc' => $statusDoc,
            'status' => $status, 'vtpType' => $vtpType,
        ];
        
        if ($ptu){
            $params['ptu'] = $ptu->getId();
        }
        
        $query = $this->entityManager->getRepository(Vtp::class)
                        ->findAllVtp($params);
        
        $total = $this->entityManager->getRepository(Vtp::class)
                        ->findAllVtpTotal($params);
        
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
            'allowDate' => $this->vtpManager->getAllowDate(),
        ]);          
    }        
    
    public function goodContentAction()
    {
        	        
        $vtpId = $this->params()->fromRoute('id', -1);
        $q = $this->params()->fromQuery('search');
//        $offset = $this->params()->fromQuery('offset');
//        $limit = $this->params()->fromQuery('limit');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order');
        
        $query = $this->entityManager->getRepository(Vtp::class)
                        ->findVtpGoods($vtpId, ['q' => $q, 'sort' => $sort, 'order' => $order]);
        
//        $total = count($query->getResult(2));
        
        $result = $query->getResult(2);
        
        return new JsonModel($result);          
    }        
    
    public function repostAllVtpAction()
    {                
        $this->vtpManager->repostAllVtp();
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);
    }   
    
    public function ptuFormAction()
    {
        $ptuId = (int)$this->params()->fromRoute('id', -1);
        
        if ($ptuId <= 0){
            $this->getResponse()->setStatusCode(404);
            return;
        }    
        
        $ptu = $this->entityManager->getRepository(Ptu::class)
                ->find($ptuId);
        
        if ($ptu == null){
            $this->getResponse()->setStatusCode(404);
            return;            
        }
        
        $this->layout()->setTemplate('layout/terminal');
        return new ViewModel([
            'ptu' => $ptu,
            'allowDate' => $this->vtpManager->getAllowDate(),
        ]);                
    }
    
    public function editFormAction()
    {
        $vtpId = (int)$this->params()->fromRoute('id', -1);
        $ptuId = (int)$this->params()->fromQuery('ptu', -1);
        
        $ptu = $vtp = $supplier = $legal = $company = null;
        $notDisabled = true; $ptuList = [];
        if ($ptuId > 0){
            $ptu = $this->entityManager->getRepository(Ptu::class)
                    ->find($ptuId);
            $ptuList[$ptu->getId()] = $ptu->getDocIdPresent();
        }    
        if ($vtpId > 0){
            $vtp = $this->entityManager->getRepository(Vtp::class)
                    ->find($vtpId);
            $ptu = $vtp->getPtu();
            $ptuList[$ptu->getId()] = $ptu->getDocIdPresent();
            $ptus = $this->entityManager->getRepository(Vtp::class)
                    ->availableBase($vtp);
            foreach ($ptus as $aptu) {
                $ptuList[$aptu->getId()] = $aptu->getDocIdPresent();
            }
        }    
        if ($ptu){
            $supplier = $ptu->getSupplier();
            $office = $ptu->getOffice();
            $contract = $ptu->getContract();
            $company = $contract->getCompany();
            $legal = $ptu->getLegal();            
        }    
        
        if ($this->getRequest()->isPost()){
            $data = $this->params()->fromPost();
        }
                
        $form = new VtpForm($this->entityManager, $office, $supplier, $company, $legal);        
        $form->get('ptu')->setValueOptions($ptuList);


        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {
                unset($data['supplier']);
                unset($data['company']);
                unset($data['csrf']);
                $vtpGood = $data['vtpGood'];
                unset($data['vtpGood']);
                $data['status_ex'] = Vtp::STATUS_EX_NEW;
                $data['contract'] = $contract;
                $data['legal'] = $legal;
                $data['office'] = $office;
                $data['apl_id'] = 0;
                
                if ($vtp){
                    if ($data['ptu'] != $ptu->getId()){
                        $data['ptuId'] = $data['ptu'];
                   }
                
                    $data['apl_id'] = $vtp->getAplId();
                    $this->vtpManager->updateVtp($vtp, $data);
                    $this->entityManager->refresh($vtp);
                } else {
                    $vtp = $this->vtpManager->addVtp($ptu, $data);
                }    
                
                $this->vtpManager->updateVtpGoods($vtp, $vtpGood);
                
                $this->vtpManager->repostVtp($vtp);
                
                return new JsonModel(
                   ['ok']
                );           
            }
        } else {
            $data = [
                'office_id' => $office->getId(),
                'company' => $company->getId(),
                'supplier' => $supplier->getId(),
                'legal_id' => $legal->getId(),  
                'contract_id' => $contract->getId(),
                'ptu' => $ptu->getId(),
            ];
            if ($vtp){
                $data['doc_date'] = $vtp->getDocDate();
                $data['doc_no'] = $vtp->getDocNo();
                $data['comment'] = $vtp->getComment();
                $data['cause'] = $vtp->getCause();
                $data['info'] = $vtp->getInfo();
                $data['status'] = $vtp->getStatus();
                $data['statusDoc'] = $vtp->getStatusDoc();
                $data['vtpType'] = $vtp->getVtpType();
                $notDisabled = $vtp->getDocDate() > $this->vtpManager->getAllowDate();
            }    
            $form->setData($data);
        }
        
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'vtp' => $vtp,
            'ptu' => $ptu,
            'allowDate' => max($this->vtpManager->getAllowDate(), date('Y-m-d', strtotime($ptu->getDocDate().' - 1 day'))),
            'disabled' => !$notDisabled,
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
        
        $form = new VtpGoodForm($this->entityManager, $good);

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
    
    public function deleteVtpAction()
    {
        $vtpId = $this->params()->fromRoute('id', -1);
        $vtp = $this->entityManager->getRepository(Vtp::class)
                ->find($vtpId);        

        if ($vtp == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->vtpManager->removeVtp($vtp);
        
        return new JsonModel(
           ['ok']
        );           
    }    
    
    public function statusAction()
    {
        $vtpId = $this->params()->fromRoute('id', -1);
        $status = $this->params()->fromQuery('status', Vtp::STATUS_ACTIVE);
        $vtp = $this->entityManager->getRepository(Vtp::class)
                ->find($vtpId);        

        if ($vtp == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->vtpManager->updateVtpStatus($vtp, $status);
        
        return new JsonModel(
           ['ok']
        );           
    }        
    
    public function statusDocAction()
    {
        $vtpId = $this->params()->fromRoute('id', -1);
        $statusDoc = $this->params()->fromQuery('status', Vtp::STATUS_DOC_NEW);
        $vtp = $this->entityManager->getRepository(Vtp::class)
                ->find($vtpId);        

        if ($vtp == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $result = $this->vtpManager->updateVtpDocStatus($vtp, $statusDoc);
        
        return new JsonModel(
           $result
        );           
    }        
    
    public function commentAction()
    {
        $vtpId = $this->params()->fromRoute('id', -1);
        $comment = $this->params()->fromQuery('comment');
        $vtp = $this->entityManager->getRepository(Vtp::class)
                ->find($vtpId);        

        if ($vtp == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->vtpManager->updateVtpComment($vtp, $comment);
        
        return new JsonModel(
           ['ok']
        );           
    }        
    
    public function vtpTypeAction()
    {
        $vtpId = $this->params()->fromRoute('id', -1);
        $vtpType = $this->params()->fromQuery('vtpType');
        $vtp = $this->entityManager->getRepository(Vtp::class)
                ->find($vtpId);        

        if ($vtp == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->vtpManager->updateVtpType($vtp, $vtpType);
        
        return new JsonModel(
           ['ok']
        );           
    }        
    
    public function updateAllInfoAction()
    {
        $this->vtpManager->updateAllInfo();

        return new JsonModel(
           ['ok']
        );           
    }
}
