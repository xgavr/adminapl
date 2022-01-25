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

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Laminas\Paginator\Paginator;

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
            'sort' => $sort, 'order' => $order, 
            'cashId' => $cashId, 'kind' => $kind,
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
    
    public function addAction() 
    {     
        // Создаем форму.
        $form = new OrderForm($this->entityManager);
        
        // Проверяем, является ли пост POST-запросом.
        if ($this->getRequest()->isPost()) {
            
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            
            // Заполняем форму данными.
            $form->setData($data);
            if ($form->isValid()) {
                                
                // Получаем валидированные данные формы.
                $data = $form->getData();
                
                // Используем менеджер order для добавления нового good в базу данных.                
                $this->orderManager->addNewOrder($data);
                
                // Перенаправляем пользователя на страницу "order".
                return $this->redirect()->toRoute('order', []);
            }
        }
        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'form' => $form
        ]);
    }   
        
    public function deleteAction()
    {
        $orderId = $this->params()->fromRoute('id', -1);
        
        $order = $this->entityManager->getRepository(Order::class)
                ->findOneById($orderId);        
        if ($order == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->orderManager->removeOrder($order);
        
        // Перенаправляем пользователя на страницу "order".
        return $this->redirect()->toRoute('order', []);
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
