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
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $orderManager, $authService, $rbacManager) 
    {
        $this->entityManager = $entityManager;
        $this->orderManager = $orderManager;
        $this->authService = $authService;
        $this->rbacManager = $rbacManager;
    }    
    
    public function indexAction()
    {
        $currentUser = $this->orderManager->currentUser();
        $offices = $this->entityManager->getRepository(Office::class)
                ->findBy([]);
        $users = $this->entityManager->getRepository(User::class)
                ->findBy(['status' => User::STATUS_ACTIVE, 'office' => $currentUser->getOffice()->getId()]);
        return new ViewModel([
            'users' =>  $users,
            'offices' =>  $offices,
            'currentUser' => $currentUser,
        ]);
    }
    
    public function contentAction()
    {       
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order', 'DESC');
        $officeId = $this->params()->fromQuery('office');
        $userId = $this->params()->fromQuery('user');
        $status = $this->params()->fromQuery('status');
        $dateOper = $this->params()->fromQuery('dateOper');
        
        $params = [
            'sort' => $sort, 'order' => $order, 'officeId' => $officeId,
            'userId' => $userId, 'status' => $status,
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
        
        return new JsonModel([
            'total' => $total,
            'rows' => $result,
        ]);                  
    }
    
    public function goodContentAction()
    {
        	        
        $orderId = $this->params()->fromRoute('id', -1);
        $result = [];
        
        if ($orderId>1) {
            $order = $this->entityManager->getRepository(Order::class)
                    ->find($orderId);        
            if ($order) {
                $query = $this->entityManager->getRepository(Bid::class)
                                ->findBidOrder($order);

                $total = count($query->getResult(2));

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
        
        $order = null;
        $office = $this->orderManager->currentUser()->getOffice();
        
        if ($orderId > 0){
            $order = $this->entityManager->getRepository(Order::class)
                    ->find($orderId);
        }    
        
        $form = new OrderForm($this->entityManager);
        
        $form->get('office')->setValue($office->getId());
        $form->get('shipping')->setValueOptions($this->entityManager->getRepository(Shipping::class)->shippingOptions($office));
        
        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();            
            $form->setData($data);

            if ($form->isValid()) {
                
                if ($order){
                    $this->orderManager->updateOrder($order, $data);
                } else {
                    $order = $this->orderManager->insOrder($office,$data);
                }    
                
                return new JsonModel(
                   ['id' => $order->getAplId()]
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
            'order' => $order,
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
                ->findOneById($orderId);
        
        if ($order == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
      
        $bids = $this->entityManager->getRepository(Order::class)
                    ->findBidOrder($order)->getResult();
        
        // Render the view template.
        return new ViewModel([
            'order' => $order,
            'bids' => $bids,
        ]);
    } 
    
    public function repostAllOrderAction()
    {                
        $this->orderManager->repostAllOrder();
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);
    }   
    
}
