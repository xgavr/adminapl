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
        return new ViewModel([
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
        $officeId = $this->params()->fromQuery('office');
        $year = $this->params()->fromQuery('year');
        $month = $this->params()->fromQuery('month');
        
        $params = [
            'q' => $q, 'sort' => $sort, 'order' => $order, 
            'officeId' => $officeId,
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
                $ptGood = $data['ptGood'];
                unset($data['ptGood']);
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
            }    
        }
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'pt' => $pt,
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
        
}
