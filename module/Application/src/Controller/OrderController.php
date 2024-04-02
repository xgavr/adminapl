<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Application\Entity\Order;
use User\Entity\User;
use Company\Entity\Office;
use Application\Form\OrderForm;
use Application\Entity\Shipping;
use Application\Entity\Bid;
use Application\Form\OrderGoodForm;
use Application\Entity\Goods;
use Application\Entity\Comment;
use Application\Entity\GoodSupplier;
use Application\Entity\Selection;
use Application\Form\SelectionForm;
use Application\Entity\Oem;
use Application\Entity\SupplierOrder;
use Stock\Entity\Movement;
use Stock\Entity\Register;
use ApiMarketPlace\Entity\Marketplace;
use Application\Form\OrderLegalForm;
use Application\Filter\OrderFromIdZ;
use Company\Entity\Legal;

class OrderController extends AbstractActionController
{
    
    /**
     * Менеджер сущностей.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Менеджер товаров.
     * @var \Application\Service\OrderManager 
     */
    private $orderManager;    
    
    /**
     *
     * @var \Laminas\Authentication\AuthenticationService
     */
    private $authService; 
    
    /**
     * RBAC manager.
     * @var \User\Service\RbacManager
     */
    private $rbacManager; 
    
    /**
     * Comment manager.
     * @var \Application\Service\CommentManager
     */
    private $commentManager; 

    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $orderManager, $authService, $rbacManager, $commentManager) 
    {
        $this->entityManager = $entityManager;
        $this->orderManager = $orderManager;
        $this->authService = $authService;
        $this->rbacManager = $rbacManager;
        $this->commentManager = $commentManager;
    }    
    
    public function indexAction()
    {
        $currentUser = $this->orderManager->currentUser();
        $offices = $this->entityManager->getRepository(Office::class)
                ->findBy([]);
        $users = $this->entityManager->getRepository(User::class)
                ->managers();
        $marketplaces = $this->entityManager->getRepository(Marketplace::class)
                ->findBy(['status' => Marketplace::STATUS_ACTIVE]);
        $marketplaceOptions = [];
        foreach ($marketplaces as $marketplace){
            $marketplaceOptions[] = '<option value="'.$marketplace->getId().'">'.$marketplace->getName().'</optiond>';
        }
        
        return new ViewModel([
            'users' =>  $users,
            'offices' =>  $offices,
            'currentUser' => $currentUser,
            'marketplaceOptions' => implode('', $marketplaceOptions),
            'allowDate' => $this->orderManager->getAllowDate(),
        ]);
    }
    
    public function contentAction()
    {       
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $sort = $this->params()->fromQuery('sort', 'dateOper');
        $order = $this->params()->fromQuery('order', 'DESC');
        $officeId = $this->params()->fromQuery('office');
        $userId = $this->params()->fromQuery('user');
        $clientId = $this->params()->fromQuery('client');
        $status = $this->params()->fromQuery('status');
        $dateOper = $this->params()->fromQuery('dateOper');
        $search = $this->params()->fromQuery('search');
        $dateStart = $this->params()->fromQuery('dateStart');
        $period = $this->params()->fromQuery('period');
        
        $startDate = '2012-01-01';
        $endDate = '2199-01-01';
        if (!empty($dateStart)){
            $startDate = date('Y-m-d', strtotime($dateStart));
            $endDate = $startDate;
            if ($period == 'week'){
                $endDate = date('Y-m-d 23:59:59', strtotime('+ 1 week - 1 day', strtotime($startDate)));
            }    
            if ($period == 'month'){
                $endDate = date('Y-m-d 23:59:59', strtotime('+ 1 month - 1 day', strtotime($startDate)));
            }    
            if ($period == 'number'){
                $startDate = $dateStart.'-01-01';
                $endDate = date('Y-m-d 23:59:59', strtotime('+ 1 year - 1 day', strtotime($startDate)));
            }    
        }    
        
        $params = [
            'sort' => $sort, 'order' => $order, 'officeId' => $officeId,
            'userId' => $userId, 'status' => $status, 'search' => $search,
            'clientId' => $clientId, 'startDate' => $startDate, 'endDate' => $endDate
        ];
        
        $query = $this->entityManager->getRepository(Order::class)
                        ->findAllOrder($params);
        
        $total = $this->entityManager->getRepository(Order::class)
                        ->findAllOrderTotal($params);
                
        if ($offset) {
            $query->setFirstResult($offset);
        }
        if ($limit) {
            $query->setMaxResults($limit);
        }

        $result = $query->getResult(2);
        if (!empty($search)){
            $total = count($result);
        }
        
        return new JsonModel([
            'total' => $total,
            'rows' => $result,
        ]);                  
    }
    
    public function goodContentAction()
    {
        	        
        $orderId = $this->params()->fromRoute('id', -1);
        $orderAplId = $this->params()->fromQuery('aplId', -1);
        
        if ($orderAplId > 0){
            $order = $this->entityManager->getRepository(Order::class)
                    ->findOneBy(['aplId' => $orderAplId]);
            $orderId = $order->getId();
        }
        
        $result = [];
        
        if ($orderId>0) {
            $order = $this->entityManager->getRepository(Order::class)
                    ->find($orderId);        
            if ($order) {
                $query = $this->entityManager->getRepository(Bid::class)
                                ->findBidOrder($order);
                
                $result = $query->getResult(2);
            }        
        }        
        
        return new JsonModel($result);          
    }            
    
    public function shippingsAction()
    {
        $officeId = (int)$this->params()->fromRoute('id', -1);
        if ($officeId<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $office = $this->entityManager->getRepository(Office::class)
                ->find($officeId);
        
        if ($office == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $result = $this->entityManager->getRepository(Shipping::class)
                ->shippingSelect($office);
        
        return new JsonModel([
            'rows' => $result,
        ]);                  
    }

    public function shippingAction()
    {
        $shippingId = (int)$this->params()->fromRoute('id', -1);
        if ($shippingId<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $shipping = $this->entityManager->getRepository(Shipping::class)
                ->find($shippingId);
        
        if ($shipping == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        return new JsonModel($shipping->toArray());                  
    }

    
    public function introAction()
    {
        $orderId = (int)$this->params()->fromRoute('id', -1);
        
        $order = $orderComments = null;
        $office = $this->orderManager->currentUser()->getOffice();
        
        if ($orderId > 0){
            $order = $this->entityManager->getRepository(Order::class)
                    ->find($orderId);
            $orderComments = $this->entityManager->getRepository(Comment::class)
                    ->findBy(['order' => $order->getId()], ['id' => 'DESC']);
            $office= $order->getOffice();                    
        }    
        
        $form = new OrderForm($this->entityManager);
        
        $form->get('office')->setValue($office->getId());
        $form->get('shipping')->setValueOptions($this->entityManager->getRepository(Shipping::class)->shippingOptions($office));
        if ($order){
            $form->get('orderId')->setValue($order->getId());
            $form->get('trackNumber')->setAttribute('disabled', $order->getShipping()->getRate() != Shipping::RATE_TK);
            $form->get('courier')->setAttribute('disabled', $order->getShipping()->getRate() != Shipping::RATE_TK);
            $form->get('shipmentDistance')->setAttribute('disabled', $order->getShipping()->getRate() != Shipping::RATE_DISTANCE);
        }    
        
        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost(); 
            if (!empty($data['office'])){
                if ($office->getId() != $data['office']){
                    $office = $this->entityManager->getRepository(Office::class)
                            ->find($data['office']);
                    $form->get('shipping')->setValueOptions($this->entityManager->getRepository(Shipping::class)->shippingOptions($office));
                }    
            }    
            
            $form->setData($data);

            if ($form->isValid()) {
                
                $contact = $this->orderManager->findContactByOrderData($data);
                $data['total'] = $data['shipmentTotal'];
                $data['statusEx'] = Order::STATUS_EX_NEW;
                
                if ($order){
                    if ($contact->getId() != $order->getContact()->getId()){
                        $this->orderManager->updateOrderContact($order, $contact);
                    }
                    $this->orderManager->updateOrder($order, $data);
                } else {
                    $order = $this->orderManager->addNewOrder($office, $contact, $data);
                }    
                if ($order){
                    $this->orderManager->updateSelectionsFromJson($order, $data['selections']);
                    if ($order && isset($data['comments'])){
                        foreach ($data['comments'] as $comment){
                            $this->commentManager->addOrderComment($order, $comment);
                        }    
                    }

                    if (!empty($data['orderGood'])){
                        $this->orderManager->updateBids($order, $data['orderGood']);
                    } else {
                        $this->orderManager->updOrderTotal($order);
                    }   
                    
                }
                                
                return new JsonModel(
                   [
                       'aplId' => $order->getAplId(), 
                       'id' => $order->getId(),
                       'contact' => $order->getContact()->getId(),
                       'legal' => ($order->getLegal()) ? $order->getLegal()->getId():null,
                       'recipient' => ($order->getRecipient()) ? $order->getRecipient()->getId():null,
                       'bankAccount' => ($order->getBankAccount()) ? $order->getBankAccount()->getId():null,
                       'user' => ($order->getUser()) ? $order->getUser()->getId():null,
                    ]
                );           
            } else {
                return new JsonModel(
                   ['error' => $form->getMessages()]
                );           
            }
        } else {
            if ($order){
                $data = $order->toArray();
//                var_dump($data);
                $form->setData($data);
            }    
        }
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'orderId' => ($order) ? $order->getId():null,
            'orderAplId' => ($order) ? $order->getAplId():null,
            'comments' => $orderComments,
        ]);        
    }        
        
    public function editFormAction()
    {
        $orderId = (int)$this->params()->fromRoute('id', -1);
        
        $order = null;
        
        if ($orderId > 0){
            $order = $this->entityManager->getRepository(Order::class)
                    ->find($orderId);
        }    
        
        $form = new OrderForm($this->entityManager);
        
        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();            
            $form->setData($data);

            if ($form->isValid()) {
                
                if ($order){
                    $this->orderManager->updateOrder($order, $data);
                } else {
                    $order = $this->orderManager->addNewOrder($data);
                }    
                
                return new JsonModel(
                   ['ok']
                );           
            } else {
//                var_dump($form->getMessages());
            }
        } else {
            if ($order){
                $data = $order->toArray();
//                var_dump($data);
                $form->setData($data);
            }    
        }
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'order' => $order,
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
        
        $form = new OrderGoodForm($this->entityManager, $good);

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
                    'quantity' => $params['num'],
                    'price' => $params['price'],
                    'amount' => $params['price']*$params['num'],
//                    'unit' => (isset($params['unit']['name'])) ? $params['unit']['name']:null,
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
    
    public function selectionFormAction()
    {        
        $params = $this->params()->fromQuery();
        $orderId = $this->params()->fromRoute('id', -1);
        
        $order = null;
        if ($orderId > 0){
            $order = $this->entityManager->getRepository(Order::class)
                    ->find($orderId);
        }
        
        $selections = null;
        
        $form = new SelectionForm($this->entityManager);

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
            if ($order){
                $selections = $this->entityManager->getRepository(Selection::class)
                        ->findByOrder($order->getId());
            }
//                $data = [
//                    'good' => $good->getId(),
//                    'code' => $good->getCode(),
//                    'goodInputName' => $good->getInputName(),
//                    'quantity' => $params['num'],
//                    'price' => $params['price'],
//                    'amount' => $params['price']*$params['num'],
////                    'unit' => (isset($params['unit']['name'])) ? $params['unit']['name']:null,
//                ];
//                $form->setData($data);
//            }    
        }        
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'selections' => $selections,
            'order' => $order,
        ]);        
    }

    public function autocompleteGoodAction()
    {
        $result = [];
        $q = $this->params()->fromQuery('q');
        
        if ($q){
            $query = $this->entityManager->getRepository(Goods::class)
                            ->autocompleteGood(['search' => $q]);

            $data = $query->getResult();
            foreach ($data as $row){
                $result[] = [
                    'id' => $row->getId(), 
                    'name' => $row->getInputName(), 
                    'code' => $row->getCode(),
                    'price' => $row->getMeanPrice(),
                    'retailPrice' => $row->getPrice(),
                    'opts' => $row->getOpts(),
                ];
            }
        }    
        
        return new JsonModel($result);
    }        
    
    public function orderLegalFormAction()
    {
        $orderId = (int)$this->params()->fromRoute('id', -1);
        
        $order = null;
        
        if ($orderId > 0){
            $order = $this->entityManager->getRepository(Order::class)
                    ->find($orderId);
        }    
        
        $form = new OrderLegalForm($this->entityManager);
        
        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost(); 
            $form->setData($data);

            if ($form->isValid()) {
                
                $this->orderManager->updOrderLegal($order, $data);
                
                $query = $this->entityManager->getRepository(Order::class)
                        ->findAllOrder(['orderId' => $order->getId()]);
                $result = $query->getOneOrNullResult(2);
                return new JsonModel([
                    'row' => $result,
                ]);
            } else {
                return new JsonModel(
                   ['error' => $form->getMessages()]
                );           
            }
        } else {
            if ($order){
                $data = [
                    'legalInn' => ($order->getLegal()) ? $order->getLegal()->getInn():null,
                    'legalName' => ($order->getLegal()) ? $order->getLegal()->getName():null,
                    'legalKpp' => ($order->getLegal()) ? $order->getLegal()->getKpp():null,
                    'legalOgrn' => ($order->getLegal()) ? $order->getLegal()->getOgrn():null,
                    'legalOkpo' => ($order->getLegal()) ? $order->getLegal()->getOkpo():null,
                    'legalAddress' => ($order->getLegal()) ? $order->getLegal()->getAddress():null,
                    'legalHead' => ($order->getLegal()) ? $order->getLegal()->getHead():null,
                    'recipientInn' => ($order->getRecipient()) ? $order->getRecipient()->getInn():null,
                    'recipientName' => ($order->getRecipient()) ? $order->getRecipient()->getName():null,
                    'recipientKpp' => ($order->getRecipient()) ? $order->getRecipient()->getKpp():null,
                    'recipientOgrn' => ($order->getRecipient()) ? $order->getRecipient()->getOgrn():null,
                    'recipientOkpo' => ($order->getRecipient()) ? $order->getRecipient()->getOkpo():null,
                    'recipientAddress' => ($order->getRecipient()) ? $order->getRecipient()->getAddress():null,
                    'recipientHead' => ($order->getRecipient()) ? $order->getRecipient()->getHead():null,
                    'rs' => ($order->getBankAccount()) ? $order->getBankAccount()->getRs():null,
                    'ks' => ($order->getBankAccount()) ? $order->getBankAccount()->getKs():null,
                    'bik' => ($order->getBankAccount()) ? $order->getBankAccount()->getBik():null,
                    'bankName' => ($order->getBankAccount()) ? $order->getBankAccount()->getName():null,
                    'bankCity' => ($order->getBankAccount()) ? $order->getBankAccount()->getCity():null,
                ];
                $form->setData($data);
            }    
        }
        // Render the view template.
        $this->layout()->setTemplate('layout/terminal');
        
        return new ViewModel([
            'form' => $form,
            'order' => $order,
        ]);        
    }        

    public function selectionsEditAction()
    {
        $orderId = (int)$this->params()->fromRoute('id', -1);
        
        $order = null;
        
        if ($orderId > 0){
            $order = $this->entityManager->getRepository(Order::class)
                    ->find($orderId);
        }    
        
        if ($this->getRequest()->isPost() && $order) {
            
            $data = $this->params()->fromPost(); 
            
//            var_dump($data); exit;
            if (empty($data['selections'])){
                $data['selections'] = [];
            }
            if (empty($data['bid'])){
                $data['bid'] = [];
            }
                
            $this->orderManager->updateSelections($order, $data['selections']);
            $this->orderManager->updateBids($order, $data['bid']);
        }    
                
        $query = $this->entityManager->getRepository(Order::class)
                ->findAllOrder(['orderId' => $order->getId()]);
        $result = $query->getOneOrNullResult(2);
                
        return new JsonModel([
            'row' => $result,
        ]);
    }        
    
    public function viewAction() 
    {       
        $orderId = (int)$this->params()->fromRoute('id', -1);
        
        // Validate input parameter
        if ($orderId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // Find the tax order ID
        $order = $this->entityManager->getRepository(Order::class)
                ->find($orderId);
        
        if ($order == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
      
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'order' => $order,
            'disabled' => $order->getStatus() == Order::STATUS_SHIPPED,
        ]);
    } 
    
    public function searchAction() 
    {       
        $orderAplId = strtoupper($this->params()->fromQuery('orderId', -1));
        
        $orderFilter = new OrderFromIdZ($this->entityManager);
        $order = $orderFilter->filter($orderAplId);
        
        if ($order) {
            return $this->redirect()->toRoute('order', ['action'=>'intro', 'id' => $order->getId()]);
        }        
        
        
        return $this->redirect()->toRoute('order', ['action'=>'index']);        
    } 

    public function goodOptsEditableFormatAction() 
    {       
        $goodId = (int)$this->params()->fromRoute('id', -1);
        
        // Validate input parameter
        if ($goodId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $good = $this->entityManager->getRepository(Good::class)
                ->find($goodId);
        
        if ($good == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
      
        return new JsonModel($good->getOptsJsonEditableFormat());
    } 

    public function repostAction()
    {
        $orderId = $this->params()->fromRoute('id', -1);
        $order = $this->entityManager->getRepository(Order::class)
                ->find($orderId);        

        if ($order == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->orderManager->repostOrder($order);
        $this->entityManager->refresh($order);
        
        return new JsonModel(
           $order->toLog()
        );           
    }        
    
    public function repostAllOrderAction()
    {                
        $this->orderManager->repostAllOrder();
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);
    }   
    
    public function goodSuppliersAction()
    {                
        $goodId = (int)$this->params()->fromRoute('id', -1);
        
        if ($goodId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $goodSuppliersQuery = $this->entityManager->getRepository(GoodSupplier::class)
                ->orderGoodSuppliers($goodId);
        $result = $goodSuppliersQuery->getResult();
        
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'goodSuppliers' => $result,
        ]);        
    }   

    public function supplierOrdersAction()
    {                
        $orderId = (int)$this->params()->fromRoute('id', -1);
        
        if ($orderId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $supplierOrders = $this->entityManager->getRepository(SupplierOrder::class)
                ->findByOrder($orderId);
        
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'supplierOrders' => $supplierOrders,
        ]);        
    }   
    
    public function goodOemAction()
    {                
        $goodId = (int)$this->params()->fromRoute('id', -1);
        
        if ($goodId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $oems = $this->entityManager->getRepository(Oem::class)
                ->findByGood($goodId, null, 50);
        
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'oems' => $oems,
        ]);        
    }   
    
    public function statusAction()
    {
        $orderId = $this->params()->fromRoute('id', -1);
        $status = $this->params()->fromQuery('status', Order::STATUS_NEW);
        $order = $this->entityManager->getRepository(Order::class)
                ->find($orderId);        

        if ($order == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->orderManager->updateOrderStatus($order, $status);
        
        $query = $this->entityManager->getRepository(Order::class)
                ->findAllOrder(['orderId' => $order->getId()]);
        $result = $query->getOneOrNullResult(2);
        
        return new JsonModel(
           $result
        );           
    }        
    
    public function updateBidsAction()
    {
        $orderId = $this->params()->fromRoute('id', -1);
        $order = $this->entityManager->getRepository(Order::class)
                ->find($orderId);        

        if ($order == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        if ($this->getRequest()->isPost()){
            $data = $this->params()->fromPost();
            if (isset($data['bids'])){
                $this->orderManager->updateBids($order, $data['bids']);
            }    
        }
        return new JsonModel([
            'result' => 'ok',
        ]);
    }    

    public function bidEditableAction()
    {
        if ($this->getRequest()->isPost()){
            $data = $this->params()->fromPost();
            if (!empty($data['name'] && !empty($data['pk']))){
                $bid = $this->entityManager->getRepository(Bid::class)
                        ->find($data['pk']);
                if ($bid){
                    $upd[$data['name']] = $data['value'];
                    if ($data['name'] == 'num'){
                        $value = (empty($data['value'])) ? 0:$data['value'];
                        $upd['num'] = $value;
                    }
                    if ($data['name'] == 'price'){
                        $value = (empty($data['value'])) ? 0:$data['value'];
                        $upd['price'] = $value;
                    }
                    if ($data['name'] == 'displayName'){
                        unset($upd[$data['name']]);
                        $upd['display_name'] = $data['value'];
                    }
                    if ($data['name'] == 'baseKey'){
                        unset($upd[$data['name']]);
                        if ($data['value'] == 'авто'){
                            unset($data['value']);                            
                        }
                        $upd['base_key'] = empty($data['value']) ? null:$data['value'];
                    }
                    $this->orderManager->updateBid($bid, $upd);
                }    
            }
            
        }
        return new JsonModel([
            'result' => 'ok',
        ]);
    }    
    
    public function infoAction()
    {
        $orderId = $this->params()->fromRoute('id', -1);
        $order = $this->entityManager->getRepository(Order::class)
                ->find($orderId);        

        if ($order == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        return new JsonModel(
           $order->toLog()
        );           
    }    
    
    public function dependAction()
    {
        ini_set('memory_limit', '4096M');
        set_time_limit(0);
        
        $orders = $this->entityManager->getRepository(Order::class)
                ->findBy(['dependInfo' => null]);
        
        foreach ($orders as $order){
            $this->orderManager->updateDependInfo($order, true);
            $this->entityManager->detach($order);
        }
        
        return new JsonModel(
           ['ok']
        );                   
    }
    
    public function findBaseAction()
    {
        $orderId = $this->params()->fromRoute('id', -1);
        
        $order = $this->entityManager->getRepository(Order::class)
                ->find($orderId);        

        if ($order == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $reg = $this->entityManager->getRepository(Register::class)
                ->findOneBy(['docId' => $order->getId(), 'docType' => Movement::DOC_ORDER]);
        
        if ($reg){
            $docStamp = $reg->getDocStamp();
        } else {
            $docStamp = $this->entityManager->getRepository(Register::class)
                    ->orderRegister($order);        
        }    
        $bids = $order->getBids();
        $result = [0 => 'авто'];
        foreach ($bids as $bid){
            $bases = $this->entityManager->getRepository(Movement::class)
                    ->findBases($bid->getGood()->getId(), $docStamp, $order->getOffice()->getId());
//            var_dump($bases);
            foreach ($bases as $base){
                $result[] = [
                    'value' => $base['baseKey'],
                    'text' => $base['baseKey'],
                ];
            }    
        }    
        
        return new JsonModel(
            $result
        );                   
    }

    public function duplicateAction()
    {
        $orderId = $this->params()->fromRoute('id', -1);
        
        $order = $this->entityManager->getRepository(Order::class)
                ->find($orderId);        

        if ($order == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $newOrder = $this->orderManager->duplicate($order);
        
        return new JsonModel(
            ['result' => $newOrder->getId()]
        );                   
    }
    
    public function changeContactByPhoneAction()
    {
        $orderId = $this->params()->fromRoute('id', -1);
        $phone = $this->params()->fromQuery('phone');
        
        $order = $this->entityManager->getRepository(Order::class)
                ->find($orderId);        

        if ($order == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->orderManager->changeContactByPhone($order, $phone);
        
        return new JsonModel(
            ['result' => 'ok']
        );                   
    }
    
    public function cancelOldAction()
    {
        $this->orderManager->cancelOld();

        return new JsonModel(
            ['ok']
        );                   
    }
    
    public function finAction()
    {
        $user = $this->params()->fromQuery('user');
        
        $companies = $this->entityManager->getRepository(Legal::class)
                ->companies();
        $users = $this->entityManager->getRepository(User::class)
                ->findBy(['status' => User::STATUS_ACTIVE]);
        
        return new ViewModel([
            'companies' => $companies,
            'users' => $users,
            'userId' => $user
        ]);
    }
 
    public function finContentAction()
    {
        	        
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $company = $this->params()->fromQuery('company');
        $user = $this->params()->fromQuery('user');
        $dateStart = $this->params()->fromQuery('dateStart');
        $period = $this->params()->fromQuery('period', 'month');
        $limit = $this->params()->fromQuery('limit');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order', 'DESC');
        
        $startDate = '2012-01-01';
        $endDate = '2199-01-01';
        if (!empty($dateStart)){
            $startDate = date('Y-m-d', strtotime($dateStart));
            $endDate = $startDate;
            if ($period == 'week'){
                $endDate = date('Y-m-d 23:59:59', strtotime('+ 1 week - 1 day', strtotime($startDate)));
            }    
            if ($period == 'month'){
                $endDate = date('Y-m-d 23:59:59', strtotime('+ 1 month - 1 day', strtotime($startDate)));
            }    
            if ($period == 'number'){
                $startDate = $dateStart.'-01-01';
                $endDate = date('Y-m-d 23:59:59', strtotime('+ 1 year - 1 day', strtotime($startDate)));
            }    
        }    
        
        $params = [
            'q' => $q, 'company' => $company, 'user' => $user,
            'startDate' => $startDate, 'endDate' => $endDate,             
            'sort' => $sort, 'order' => $order, 
        ];
        
        $query = $this->entityManager->getRepository(Order::class)
                        ->findRetails($params);
        
        $total = count($query->getResult());
        
        if ($offset) {
            $query->setFirstResult($offset);
        }
        if ($limit) {
            $query->setMaxResults($limit);
        }

//        $totalAmount = $this->entityManager->getRepository(Order::class)
//                        ->findRetailsTotal($params);
        
        $totalAmountResult = 0;
//        if ($totalAmount){
////            $totalAmountResult = $totalAmount['amount'];
//        }
        
        $result = $query->getResult(2);
        
        return new JsonModel([
            'total' => $total,
            'totalAmount' => $totalAmountResult,
            'rows' => $result,
        ]);          
    }        
    
}
