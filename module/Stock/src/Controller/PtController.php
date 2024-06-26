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
use Stock\Entity\Pt;
use Stock\Form\PtForm;
use Stock\Form\PtGoodForm;
use Application\Entity\Goods;
use Company\Entity\Office;
use Company\Entity\Legal;
use Application\Entity\Contact;
use Stock\Entity\PtSheduler;
use Stock\Form\PtShedulerForm;
use Stock\Entity\GoodBalance;
use Stock\Entity\Register;
use Stock\Entity\Movement;
use Stock\Entity\PtGood;

class PtController extends AbstractActionController
{

    /**
     * Менеджер сущностей.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * Менеджер пту.
     * @var \Stock\Service\PtManager
     */
    private $ptManager;

    public function __construct($entityManager, $ptManager) 
    {
        $this->entityManager = $entityManager;
        $this->ptManager = $ptManager;
    }   

    public function indexAction()
    {
        $offices = $this->entityManager->getRepository(Office::class)
                ->findAll();
        $companies = $this->entityManager->getRepository(Office::class)
                ->findAllCompanies();
        return new ViewModel([
            'offices' => $offices,
            'companies' => $companies,
            'allowDate' => $this->ptManager->getAllowDate(),
        ]);  
    }
        
    public function contentAction()
    {
        	        
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order', 'DESC');
        $officeId = $this->params()->fromQuery('office');
        $companyId = $this->params()->fromQuery('company');
        $year_month = $this->params()->fromQuery('month');
        
        $year = $month = null;
        if ($year_month){
            $year = date('Y', strtotime($year_month));
            $month = date('m', strtotime($year_month));
        }
        
        $params = [
            'q' => $q, 'sort' => $sort, 'order' => $order, 
            'officeId' => $officeId,
            'companyId' => $companyId,
            'year' => $year, 'month' => $month,
        ];
        $query = $this->entityManager->getRepository(Pt::class)
                        ->findAllPt($params);
        
        $total = $this->entityManager->getRepository(Pt::class)
                        ->findAllPtTotal($params);
        
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
        	        
        $ptId = $this->params()->fromRoute('id', -1);
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order');
        
        $query = $this->entityManager->getRepository(Pt::class)
                        ->findPtGoods($ptId, ['q' => $q, 'sort' => $sort, 'order' => $order]);
        
        $total = count($query->getResult(2));
        
        $result = $query->getResult(2);
        
        return new JsonModel($result);          
    }        
    
    public function repostAllPtAction()
    {                
        $this->ptManager->repostAllPt();
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);
    }        
    
    public function editFormAction()
    {
        $ptId = (int)$this->params()->fromRoute('id', -1);
        
        $pt = $office = $company = $office2 = $company2 = null;
        $notDisabled = true;        
        if ($ptId > 0){
            $pt = $this->entityManager->getRepository(Pt::class)
                    ->findOneById($ptId);
        }    
        
        if ($pt == null) {
            $officeId = (int)$this->params()->fromQuery('office', 1);
            $office = $this->entityManager->getRepository(Office::class)
                    ->findOneById($officeId);
            $office2Id = (int)$this->params()->fromQuery('office2', 1);
            $office2 = $this->entityManager->getRepository(Office::class)
                    ->findOneById($office2Id);
        } else {
            $office = $pt->getOffice();
            $company = $pt->getCompany();
            $office2 = $pt->getOffice2();
            $company2 = $pt->getCompany2();
        }       
        
        if ($this->getRequest()->isPost()){
            $data = $this->params()->fromPost();
            $office = $this->entityManager->getRepository(Office::class)
                    ->findOneById($data['office_id']);
            $company = $this->entityManager->getRepository(Legal::class)
                    ->findOneById($data['company']);
            $office2 = $this->entityManager->getRepository(Office::class)
                    ->findOneById($data['office2_id']);
            $company2 = $this->entityManager->getRepository(Legal::class)
                    ->findOneById($data['company2']);
        }
                
        $form = new PtForm($this->entityManager, $office, $company, $office2, $company2);

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {
                unset($data['csrf']);
                $ptGood = [];
                if (!empty($data['ptGood'])){
                    $ptGood = $data['ptGood'];
                    unset($data['ptGood']);
                }    
                $data['status_ex'] = Pt::STATUS_EX_NEW;
                $data['office'] = $office;
                $data['company'] = $company;
                $data['office2'] = $office2;
                $data['company2'] = $company2;
                $data['apl_id'] = 0;

                if ($pt){
                    $data['apl_id'] = $pt->getAplId();
                    $this->ptManager->updatePt($pt, $data);
                    $this->entityManager->refresh($pt);
                } else {
                    $pt = $this->ptManager->addPt($data);
                }    
                
                $this->ptManager->updatePtGoods($pt, $ptGood);
                
                $this->ptManager->repostPt($pt);
                
                return new JsonModel(
                   ['ok']
                );           
            }
        } else {
            if ($pt){
                $data = [
                    'office_id' => $pt->getOffice()->getId(),
                    'company' => $pt->getCompany()->getId(),
                    'office2_id' => $pt->getOffice2()->getId(),
                    'company2' => $pt->getCompany2()->getId(),
                    'doc_date' => $pt->getDocDate(),  
                    'doc_no' => $pt->getDocNo(),
                    'comment' => $pt->getComment(),
                    'status' => $pt->getStatus(),
                ];
                $form->setData($data);
                $notDisabled = $pt->getDocDate() > $this->ptManager->getAllowDate();
            }    
        }
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'pt' => $pt,
            'disabled' => !$notDisabled,
            'allowDate' => $this->ptManager->getAllowDate(),
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
        
        $form = new ptGoodForm($this->entityManager, $good);

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $form->setData($data);
            if (isset($data['good'])){
                $good = $this->entityManager->getRepository(Goods::class)
                        ->findOneById($data['good']);            
                if ($good){
                    $data['price'] = $good->getMeanPrice();
                    $data['amount'] = $good->getMeanPrice()*$data['quantity'];
                }
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
    
    public function combinedFormAction()
    {
        $goodId = $this->params()->fromRoute('id', -1);
        $officeId = (int)$this->params()->fromQuery('office', $this->ptManager->currentUser()->getOffice()->getId());
        $office = $this->entityManager->getRepository(Office::class)
                ->findOneById($officeId);
        $office2Id = (int)$this->params()->fromQuery('office2', $this->ptManager->currentUser()->getOffice()->getId());
        $office2 = $this->entityManager->getRepository(Office::class)
                ->findOneById($office2Id);
        
        $good = $company = $company2 = $balance = null;
        if ($goodId > 0){
            $good = $this->entityManager->getRepository(Goods::class)
                    ->find($goodId);
        }
        
        if ($good){
            $balance = $this->entityManager->getRepository(GoodBalance::class)
                    ->goodBalance($good);
            if ($balance){
                $office = $balance->getOffice();
                $company = $balance->getCompany();
            }
        }    
                
        if ($this->getRequest()->isPost()){
            $data = $this->params()->fromPost();
            $office = $this->entityManager->getRepository(Office::class)
                    ->findOneById($data['office_id']);
            $company = $this->entityManager->getRepository(Legal::class)
                    ->findOneById($data['company']);
            $office2 = $this->entityManager->getRepository(Office::class)
                    ->findOneById($data['office2_id']);
            $company2 = $this->entityManager->getRepository(Legal::class)
                    ->findOneById($data['company2']);
        }
        
        $form = new PtForm($this->entityManager, $office, $company, $office2, $company2);

        if ($balance){
            $form->get('office_id')->setValue($office->getId());
            $form->get('company')->setValue($company->getId());
            $form->get('quantity')->setValue(min($balance->getRest(), 2));
        }
        
        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {
                unset($data['csrf']);
                $ptGood = ['good_id' => $good->getId(), 'quantity' => $data['quantity'], 'amount' => $good->getMeanPrice() * $data['quantity']];
                unset($data['good']);
                unset($data['quantity']);
                $data['status_ex'] = Pt::STATUS_EX_NEW;
                $data['status'] = Pt::STATUS_ACTIVE;                
                $data['doc_no'] = $this->ptManager->handPtDocNo();                
                $data['office'] = $office;
                $data['company'] = $company;
                $data['office2'] = $office2;
                $data['company2'] = $company2;
                $data['apl_id'] = 0;

                $pt = $this->entityManager->getRepository(Pt::class)
                        ->findOneBy([
                            'docDate' => $data['doc_date'],
                            'office' => $office->getId(), 
                            'company' => $company->getId(),
                            'office2' => $office2->getId(), 
                            'company2' => $company2->getId(),
                            'docNo' => $this->ptManager->handPtDocNo(),
                            'status' => Pt::STATUS_ACTIVE,
                        ]);
                
                if ($pt){
                    $data['apl_id'] = $pt->getAplId();
                    $this->ptManager->updatePt($pt, $data);
                    $this->entityManager->refresh($pt);
                } else {
                    $pt = $this->ptManager->addPt($data);
                }    
                
                $rowNo = $pt->getPtGoods()->count() + 1;
                $this->ptManager->addPtGood($pt->getId(), $ptGood, $rowNo);
                
                $this->ptManager->updatePtAmount($pt);
                
                return new JsonModel(
                   ['ok']
                );           
            }
        }

        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'disabled' => false,
            'allowDate' => $this->ptManager->getAllowDate(),
            'good' => $good,
        ]);        
    }    
    
    public function deletePtAction()
    {
        $ptId = $this->params()->fromRoute('id', -1);
        $pt = $this->entityManager->getRepository(Pt::class)
                ->findOneById($ptId);        

        if ($pt == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->ptManager->removePt($pt);
        
        return new JsonModel(
           ['ok']
        );           
    }
        
    public function ptGeneratorAction()
    {
        $this->ptManager->ptGenerator();
        
        return new JsonModel(
           ['ok']
        );           
    }
        
    public function officePtGeneratorAction()
    {
        $ptShedulerId = $this->params()->fromRoute('id', -1);
        
        
        $ptSheduler = $this->entityManager->getRepository(PtSheduler::class)
                ->findOneById($ptShedulerId);        

        if ($ptSheduler == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->ptManager->ptGenerator($ptSheduler);
        
        return new JsonModel(
           ['ok']
        );           
    }

    public function generatorEditFormAction()
    {        
        $ptShedulerId = (int)$this->params()->fromRoute('id', -1);
        
        if ($ptShedulerId > 0){
            $ptSheduler = $this->entityManager->getRepository(PtSheduler::class)
                    ->find($ptShedulerId);
        }    
        
        if ($ptSheduler == null) {
            $officeId = (int)$this->params()->fromQuery('office', $this->ptManager->currentUser()->getOffice()->getId());
            $office = $this->entityManager->getRepository(Office::class)
                    ->find($officeId);
            $office2Id = (int)$this->params()->fromQuery('office2', $this->ptManager->currentUser()->getOffice()->getId());
            $office2 = $this->entityManager->getRepository(Office::class)
                    ->find($office2Id);
        } else {
            $office = $ptSheduler->getOffice();
            $office2 = $ptSheduler->getOffice2();
        }       
        
        if ($this->getRequest()->isPost()){
            $data = $this->params()->fromPost();
            $office = $this->entityManager->getRepository(Office::class)
                    ->find($data['office']);
            $office2 = $this->entityManager->getRepository(Office::class)
                    ->find($data['office2']);
        }
                
        $form = new PtShedulerForm($this->entityManager, $office, $office2);

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {
                unset($data['csrf']);
                $data['office'] = $office;
                $data['office2'] = $office2;

                if ($ptSheduler){
                    $this->ptManager->updatePtSheduler($ptSheduler, $data);
                } else {
                    $ptSheduler = $this->ptManager->addPtSheduler($data);
                }    
                                
                return new JsonModel(
                   ['ok']
                );           
            }
        } else {
            if ($ptSheduler){
                $form->setData($ptSheduler->toLog());
            }    
        }
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'ptSheduler' => $ptSheduler,
        ]);        
    }
    
    public function findBaseAction()
    {
        $ptId = $this->params()->fromRoute('id', -1);
        
        $pt = $this->entityManager->getRepository(Pt::class)
                ->find($ptId);        

        if ($pt == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $reg = $this->entityManager->getRepository(Register::class)
                ->findOneBy(['docId' => $pt->getId(), 'docType' => Movement::DOC_PT]);
        
        if ($reg){
            $docStamp = $reg->getDocStamp();
        } else {
            $docStamp = $this->entityManager->getRepository(Register::class)
                    ->ptRegister($pt);        
        }    
        $ptGoods = $pt->getPtGoods();
        $result = [0 => PtGood::BASE_KEY_AUTO];
        $keys = [];
        foreach ($ptGoods as $ptGood){
            $bases = $this->entityManager->getRepository(Movement::class)
                    ->findBases($ptGood->getGood()->getId(), $docStamp, $pt->getOffice()->getId());
            foreach ($bases as $base){
                if (!array_key_exists($base['baseKey'], $keys)){
                    $keys[$base['baseKey']] = $base['baseKey'];
                    $result[] = [
                        'value' => $base['baseKey'],
                        'text' => $base['baseKey'],
                    ];
                }
            }    
        }    
        
        return new JsonModel(
            $result
        );                   
    }    
    
    public function statusAction()
    {
        $ptId = $this->params()->fromRoute('id', -1);
        $status = $this->params()->fromQuery('status', Pt::STATUS_ACTIVE);
        $pt = $this->entityManager->getRepository(Pt::class)
                ->find($ptId);        

        if ($pt == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->ptManager->updatePtStatus($pt, $status);

        $query = $this->entityManager->getRepository(Pt::class)
                ->findAllPt(['ptId' => $pt->getId()]);
        $result = $query->getOneOrNullResult(2);
        
        return new JsonModel(
           $result
        );           
    }            
    
    public function repostAction()
    {
        $ptId = $this->params()->fromRoute('id', -1);

        $pt = $this->entityManager->getRepository(Pt::class)
                ->find($ptId);        

        if ($pt == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->ptManager->repostPt($pt);

        $query = $this->entityManager->getRepository(Pt::class)
                ->findAllPt(['ptId' => $pt->getId()]);
        $result = $query->getOneOrNullResult(2);
        
        return new JsonModel(
           $result
        );           
    }            
    
    public function clearTakeAction()
    {
        $ptId = $this->params()->fromRoute('id', -1);
        
        $pt = $this->entityManager->getRepository(Pt::class)
                ->find($ptId);        

        if ($pt == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->ptManager->checkAutoPt($pt);
        
        $query = $this->entityManager->getRepository(Pt::class)
                ->findAllPt(['ptId' => $pt->getId()]);
        $result = $query->getOneOrNullResult(2);
        
        return new JsonModel(
           $result
        );           
        
    }
}
