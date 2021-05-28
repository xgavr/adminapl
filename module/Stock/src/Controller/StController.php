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
use Stock\Entity\St;
use Stock\Form\StForm;
use Stock\Form\StGoodForm;
use Application\Entity\Goods;
use Company\Entity\Office;
use Company\Entity\Legal;
use User\Entity\User;
use Company\Entity\Cost;

class StController extends AbstractActionController
{

    /**
     * Менеджер сущностей.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * Менеджер пту.
     * @var \Stock\Service\StManager
     */
    private $stManager;

    public function __construct($entityManager, $stManager) 
    {
        $this->entityManager = $entityManager;
        $this->stManager = $stManager;
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
        $query = $this->entityManager->getRepository(St::class)
                        ->findAllSt($params);
        
        $total = $this->entityManager->getRepository(St::class)
                        ->findAllStTotal($params);
        
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
        	        
        $stId = $this->params()->fromRoute('id', -1);
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order');
        
        $query = $this->entityManager->getRepository(St::class)
                        ->findStGoods($stId, ['q' => $q, 'sort' => $sort, 'order' => $order]);
        
        $total = count($query->getResult(2));
        
        $result = $query->getResult(2);
        
        return new JsonModel($result);          
    }        
    
    public function repostAllStAction()
    {                
        $this->stManager->repostAllSt();
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);
    }        
    
    public function editFormAction()
    {
        $stId = (int)$this->params()->fromRoute('id', -1);
        
        $st = $office = $company = $user = $cost = null;
        
        if ($stId > 0){
            $st = $this->entityManager->getRepository(St::class)
                    ->findOneById($stId);
        }    
        
        if ($st == null) {
            $officeId = (int)$this->params()->fromQuery('office', 1);
            $office = $this->entityManager->getRepository(Office::class)
                    ->findOneById($officeId);
        } else {
            $office = $st->getOffice();
            $company = $st->getCompany();
            $user = $st->getUser();
            $cost = $st->getCost();
        }       
        
        if ($this->getRequest()->isPost()){
            $data = $this->params()->fromPost();
            $office = $this->entityManager->getRepository(Office::class)
                    ->findOneById($data['office_id']);
            $company = $this->entityManager->getRepository(Legal::class)
                    ->findOneById($data['company']);
            $user = $this->entityManager->getRepository(User::class)
                    ->findOneById($data['user']);
            $cost = $this->entityManager->getRepository(Cost::class)
                    ->findOneById($data['cost']);
        }
                
        $form = new StForm($this->entityManager, $office, $company, $user, $cost);

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {
                unset($data['csrf']);
                $stGood = $data['stGood'];
                unset($data['stGood']);
                $data['status_ex'] = St::STATUS_EX_NEW;
                $data['office'] = $office;
                $data['company'] = $company;
                $data['user'] = $user;
                $data['cost'] = $cost;
                if ($data['writeOff'] != St::WRITE_COST){
                    $data['cost'] = null;
                }
                if ($data['writeOff'] != St::WRITE_PAY){
                    $data['user'] = null;
                }
                $data['apl_id'] = 0;

                if ($st){
                    $data['apl_id'] = $st->getAplId();
                    $this->stManager->updateSt($st, $data);
                    $this->entityManager->refresh($st);
                } else {
                    $st = $this->stManager->addSt($data);
                }    
                
                $this->stManager->updateStGoods($st, $stGood);
                
                $this->stManager->repostSt($st);
                
                return new JsonModel(
                   ['ok']
                );           
            }
        } else {
            if ($st){
                $data = [
                    'office_id' => $st->getOffice()->getId(),
                    'company' => $st->getCompany()->getId(),
                    'doc_date' => $st->getDocDate(),  
                    'doc_no' => $st->getDocNo(),
                    'comment' => $st->getComment(),
                    'status' => $st->getStatus(),
                    'writeOff' => $st->getWriteOff(),
                ];
                if ($st->getUser()){
                    $data['user'] = $st->getUser()->getId();
                }
                if ($st->getCost()){
                    $data['cost'] = $st->getCost()->getId();
                }
                $form->setData($data);
            }    
        }
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'st' => $st,
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
        
        $form = new StGoodForm($this->entityManager, $good);

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
    
    public function deleteStAction()
    {
        $stId = $this->params()->fromRoute('id', -1);
        $st = $this->entityManager->getRepository(St::class)
                ->findOneById($stId);        

        if ($st == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->stManager->removeSt($st);
        
        return new JsonModel(
           ['ok']
        );           
    }
        
}
