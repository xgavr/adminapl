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
use Stock\Entity\Vt;
use Stock\Entity\VtGood;
use Application\Entity\Order;
use Application\Entity\Goods;
use Stock\Form\VtForm;
use Stock\Form\VtGoodForm;
use Company\Entity\Office;
use Stock\Entity\Register;
use Stock\Entity\Movement;

class VtController extends AbstractActionController
{

    /**
     * Менеджер сущностей.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * Менеджер возвратов.
     * @var \Stock\Service\VtManager
     */
    private $vtManager;

    public function __construct($entityManager, $vtManager) 
    {
        $this->entityManager = $entityManager;
        $this->vtManager = $vtManager;
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
        	        
        $orderId = (int)$this->params()->fromRoute('id', -1);
        $orderEntity = null;
        if ($orderId > 0){
            $orderEntity = $this->entityManager->getRepository(Order::class)
                    ->find($orderId);
        }    
        
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order', 'DESC');
        $officeId = $this->params()->fromQuery('office');
        $clientId = $this->params()->fromQuery('client');
        $year_month = $this->params()->fromQuery('month');
        
        $year = $month = null;
        if ($year_month){
            $year = date('Y', strtotime($year_month));
            $month = date('m', strtotime($year_month));
        }
        
        $params = [
            'q' => $q, 'sort' => $sort, 'order' => $order, 
            'officeId' => $officeId, 'clientId' => $clientId,
            'year' => $year, 'month' => $month,
        ];
        
        if ($orderEntity){
            $params['orderId'] = $orderEntity->getId();
        }
        
        $query = $this->entityManager->getRepository(Vt::class)
                        ->findAllVt($params);
        
        $total = $this->entityManager->getRepository(Vt::class)
                        ->findAllVtTotal($params);
        
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
        	        
        $vtId = $this->params()->fromRoute('id', -1);
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order');
        
        $query = $this->entityManager->getRepository(Vt::class)
                        ->findVtGoods($vtId, ['q' => $q, 'sort' => $sort, 'order' => $order]);
        
        $total = count($query->getResult(2));
        
        $result = $query->getResult(2);
        
        return new JsonModel($result);          
    }        
    
    public function repostAllVtAction()
    {                
        $this->vtManager->repostAllVt();
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);
    }   
    
    public function orderFormAction()
    {
        $orderId = (int)$this->params()->fromRoute('id', -1);
        
        if ($orderId <= 0){
            $this->getResponse()->setStatusCode(404);
            return;
        }    
        
        $order = $this->entityManager->getRepository(Order::class)
                ->find($orderId);
        
        if ($order == null){
            $this->getResponse()->setStatusCode(404);
            return;            
        }
        
        $this->layout()->setTemplate('layout/terminal');
        return new ViewModel([
            'order' => $order,
        ]);                
    }
    
    public function editFormAction()
    {
        $vtId = (int)$this->params()->fromRoute('id', -1);
        $orderId = (int)$this->params()->fromQuery('order', -1);
        
        $order = $vt = $client = $legal = $company = null;
        $notDisabled = true;        
        if ($orderId > 0){
            $order = $this->entityManager->getRepository(Order::class)
                    ->find($orderId);
            $office = $order->getOffice();
            $company = $order->getCompany();
            $legal = $order->getLegal();            
        }    
        if ($vtId > 0){
            $vt = $this->entityManager->getRepository(Vt::class)
                    ->find($vtId);
            $order = $vt->getOrder();
        }    
        if ($vt && $order){
            $office = $vt->getOffice();
            $company = $order->getCompany();
            $legal = $order->getLegal();            
        }    
        
        if ($this->getRequest()->isPost()){
            $data = $this->params()->fromPost();
        }
                
        $form = new VtForm($this->entityManager, $office, $order, $company, $legal);

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {
                unset($data['client']);
                unset($data['company']);
                unset($data['csrf']);
                $vtGood = $data['vtGood'];
                unset($data['vtGood']);
                $data['status_ex'] = Vt::STATUS_EX_NEW;
                $data['legal'] = $legal;
                $data['office'] = $office;
                $data['apl_id'] = 0;
                
                if ($vt){
                    $data['apl_id'] = $vt->getAplId();
                    $this->vtManager->updateVt($vt, $data);
                    $this->entityManager->refresh($vt);
                } else {
                    $vt = $this->vtManager->addVt($office, $order, $data);
                }    
                
                $this->vtManager->updateVtGoods($vt, $vtGood);
                
                $this->vtManager->repostVt($vt);
                
                return new JsonModel(
                   ['ok']
                );           
            }
        } else {
            $data = [
                'office_id' => $office->getId(),
                'company' => $company->getId(),
                'legal_id' => ($legal) ? $legal->getId():null,  
            ];
            if ($vt){
                $data['doc_date'] = $vt->getDocDate();
                $data['doc_no'] = $vt->getDocNo();
                $data['comment'] = $vt->getComment();
                $data['status'] = $vt->getStatus();
                $notDisabled = $vt->getDocDate() > $this->vtManager->getAllowDate();
            }    
            $form->setData($data);
        }
        
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'vt' => $vt,
            'order' => $order,
            'allowDate' => max($this->vtManager->getAllowDate(), date('Y-m-d', strtotime($order->getDateShipment().' - 1 day'))),
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
                    ->findOneById($params['good']['id']);            
        }
        if (isset($params['rowNo'])){
            $rowNo = $params['rowNo'];
        }
        
        $form = new VtGoodForm($this->entityManager, $good);

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
    
    public function deleteVtAction()
    {
        $vtId = $this->params()->fromRoute('id', -1);
        $vt = $this->entityManager->getRepository(Vt::class)
                ->find($vtId);        

        if ($vt == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->vtManager->removeVt($vt);
        
        return new JsonModel(
           ['ok']
        );           
    }    
    
    public function findBaseAction()
    {
        $vtId = $this->params()->fromRoute('id', -1);
        
        $vt = $this->entityManager->getRepository(Vt::class)
                ->find($vtId);        

        if ($vt == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $vtGoods = $vt->getVtGoods();
        $result = [0 => VtGood::BASE_KEY_AUTO];
        $keys = [];
        foreach ($vtGoods as $vtGood){
            $params = ['docKey' => $vt->getOrder()->getLogKey(), 'good' => $vtGood->getGood()->getId()];
            $bases = $this->entityManager->getRepository(Movement::class)
                    ->findBy($params, ['quantity' => 'ASC']);
                            
            foreach ($bases as $base){
                if (!array_key_exists($base->getBaseKey(), $keys)){
                    $keys[$base->getBaseKey()] = $base->getBaseKey();
                    $result[] = [
                        'value' => $base->getBaseKey(),
                        'text' => $base->getBaseKey(),
                    ];
                }
            }    
        }    
        
        return new JsonModel(
            $result
        );                   
    }    
    
    public function fixUserRetailAction()
    {        
        $this->vtManager->fixVtRetail();
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);
    }        
    
    public function repostAction()
    {
        $vtId = $this->params()->fromRoute('id', -1);
        
        $vt = $this->entityManager->getRepository(Vt::class)
                ->find($vtId);        

        if ($vt == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->vtManager->repostVt($vt);
        
        $result = [];
        
        return new JsonModel(
           $result
        );           
    }          
    
    public function fixVtMovementAction()
    {        
        $this->vtManager->fixMovement();
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);
    }            
}
