<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Entity\Goods;
use Application\Entity\Cart;
use Application\Entity\Client;
use Zend\View\Model\JsonModel;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;

class ShopController extends AbstractActionController
{
    
    /**
     * Менеджер сущностей.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Менеджер товаров.
     * @var Application\Service\GoodsManager 
     */
    private $goodsManager;   
    
    /*
     * Менеджер сессий
     * @var Zend\Seesion
     */
    private $sessionContainer;
    
    /**
     * Менеджер товаров.
     * @var Application\Service\ShopManager 
     */
    private $shopManager;    
    
    /**
     * Doctrine entity manager.
     * @var Application\Service\OrderManager
     */
    private $orderManager;
    
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $shopManager, $goodsManager, $sessionContainer, $orderManager) 
    {
        $this->entityManager = $entityManager;
        $this->goodsManager = $goodsManager;
        $this->shopManager = $shopManager;
        $this->sessionContainer = $sessionContainer;
        $this->orderManager = $orderManager;
    }    
    
    public function indexAction()
    {
        $currentClient = $this->shopManager->currentClient();
        if ($currentClient == null){
            return $this->redirect()->toRoute('client', []);
        }        
        
        
        $page = $this->params()->fromQuery('page', 1);
        $q = $this->params()->fromQuery('q', '');
        
        if (strlen($q) > 2){
            $query = $this->entityManager->getRepository(Goods::class)
                        ->searchByName($q);            
        } else {
            $query = $this->entityManager->getRepository(Goods::class)
                        ->findAllGoods();
        }    
                
        $adapter = new DoctrineAdapter(new ORMPaginator($query, false));
        $paginator = new Paginator($adapter);
        $paginator->setDefaultItemCountPerPage(10);        
        $paginator->setCurrentPageNumber($page);        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'goods' => $paginator,
            'goodsManager' => $this->goodsManager,
            'shopManager' => $this->shopManager,
            'search' => $q,
            'currentClient' => $currentClient
        ]);  
    }
    
    public function shopContentAction()
    {
        
        $currentClient = $this->shopManager->currentClient();
        
        $q = $this->params()->fromQuery('search', '');
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        
        if (strlen($q) > 1){
            $query = $this->entityManager->getRepository(Goods::class)
                        ->searchByName($q);            
        } else {
            $query = $this->entityManager->getRepository(Goods::class)
                        ->findAllGoods();
        }    
        
        $total = count($query->getResult(2));
        
        if ($offset) $query->setFirstResult( $offset );
        if ($limit) $query->setMaxResults( $limit );
        
        $result = $query->getResult(2);
        
        foreach ($result as $key => $row){
            $result[$key]['incart'] = $this->shopManager->getGoodInCart($row['id']);
        }

        return new JsonModel([
            'total' => $total,
            'rows' => $result,
        ]);          
    }
    
    public function shopAction()
    {
        $currentClient = $this->shopManager->currentClient();
        if ($currentClient == null){
            return $this->redirect()->toRoute('client', []);
        }        
        
        return new ViewModel([
            'currentClient' => $currentClient,
        ]);          
    }
    
    
    public function searchAssistantAction()
    {
        $q = $this->params()->fromQuery('q', '');

        $data = $this->shopManager->searchGoodNameAssistant($q);
        
        return new JsonModel(
           $data
        );        
    }
    
    public function cartAction()
    {
        $currentClient = $this->shopManager->currentClient();
        if ($currentClient == null){
            return $this->redirect()->toRoute('client', []);
        }        
        
        $num = $total = 0;
        $cart = null;
        
        if ($currentClient){
            $cart = $this->entityManager->getRepository(Cart::class)
                        ->findClientCart($currentClient)->getResult();

            $result = $this->entityManager->getRepository(Cart::class)
                ->getClientNum($currentClient);

            if (is_array($result) && count($result)){
                if (array_key_exists('num', $result[0])){
                    $num = $result[0]['num'];
                }
                if (array_key_exists('total', $result[0])){
                    $total = $result[0]['total'];
                }
            }
        }    
        // Визуализируем шаблон представления.
        return new ViewModel([
            'cart' => $cart,
            'currentClient' => $currentClient,
            'num' => $num,
            'total' => $total,
        ]);  
        
    }
    
    public function addCartAction()
    {
        if ($this->getRequest()->isPost()) {
            
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            
            if(!array_key_exists('client', $data) || !$data['client']) {
                $data['client'] = $this->sessionContainer->currentClient; 
            }    
            
            $this->shopManager->addCart($data);            
            
            $client = $this->entityManager->getRepository(Client::class)
                    ->findOneById($data['client']); 

            $result = $this->entityManager->getRepository(Cart::class)
                ->getClientNum($client);
        }
        
        $num = $total = 0;
        if (is_array($result) && count($result)){
            if (array_key_exists('num', $result[0])){
                $num = $result[0]['num'];
            }
            if (array_key_exists('total', $result[0])){
                $total = $result[0]['total'];
            }
        }
                        
        return new JsonModel([
            'num' => $num,
            'good' => $data['good'],
        ]);        
    }
    
    public function editCartAction()
    {
        $cartId = $this->params()->fromRoute('id', -1);
    
        // Находим существующий пост в базе данных.    
        $cart = $this->entityManager->getRepository(Cart::class)
                ->findOneById($cartId);  
        	
        if ($cart == null) {
            $this->getResponse()->setStatusCode(401);
            return;                        
        } 
        
        $rowTotal = 0;
        
        if ($this->getRequest()->isPost()) {
            
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            
            $this->shopManager->updateCart($cart, $data); 
            
            $rowTotal = $cart->getPrice()*$data['num'];
            
        }
        
        $currentClient = $this->entityManager->getRepository(Client::class)
                ->findOneById($this->sessionContainer->currentClient);  
        
        if ($currentClient == null) {
            return;                        
        } 
        
        $result = $this->entityManager->getRepository(Cart::class)
            ->getClientNum($currentClient);
        
        $num = $total = 0;
        if (is_array($result) && count($result)){
            if (array_key_exists('num', $result[0])){
                $num = $result[0]['num'];
            }
            if (array_key_exists('total', $result[0])){
                $total = $result[0]['total'];
            }
        }
                        
        return new JsonModel([
            'id' => $cartId,
            'rowtotal' => round($rowTotal, 2),
            'num' => round($num, 2),
            'total' => round($total, 2)
        ]);        
    }
    
    public function deleteCartAction()
    {
        $cartId = $this->params()->fromRoute('id', -1);
        
        $cart = $this->entityManager->getRepository(Cart::class)
                ->findOneById($cartId);
        
        if ($cart == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->shopManager->removeCart($cart);
        
        // Перенаправляем пользователя на страницу "cart".
        return $this->redirect()->toRoute('shop', ['action' => 'cart']);        
    }
    
    public function checkoutAction()
    {
        $currentClient = $this->shopManager->currentClient();
        if ($currentClient == null){
            return $this->redirect()->toRoute('client', []);
        }        
        
        if ($currentClient == null) {
            $this->getResponse()->setStatusCode(401);
            return;                        
        } 
        
        $order = $this->orderManager->checkoutClient($currentClient);
        
        if ($order == null){
            return $this->redirect()->toRoute('shop', ['action' => 'cart']);                    
        }
        
        return $this->redirect()->toRoute('order', ['action' => 'view', 'id' => $order->getId()]);        
    }

    public function numAction()
    {
        $currentClient = $this->entityManager->getRepository(Client::class)
                ->findOneById($this->sessionContainer->currentClient);  
        
        if ($currentClient == null) {
            return;                        
        } 
        
        $result = $this->entityManager->getRepository(Cart::class)
            ->getClientNum($currentClient);
                        
        $num = $total = 0;
        if (is_array($result) && count($result)){
            if (array_key_exists('num', $result[0])){
                $num = $result[0]['num'];
            }
            if (array_key_exists('total', $result[0])){
                $total = $result[0]['total'];
            }
        }
        
        return new JsonModel([
           'num' => $num,
        ]);        
    }
}
