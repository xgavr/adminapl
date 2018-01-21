<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Entity\Order;
use User\Entity\User;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;

class OrderController extends AbstractActionController
{
    
    /**
     * Менеджер сущностей.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Менеджер товаров.
     * @var Application\Service\OrderManager 
     */
    private $orderManager;    
    
    
    private $authService; 
    
    /**
     * RBAC manager.
     * @var User\Service\RbacManager
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
        $page = $this->params()->fromQuery('page', 1);
        
        if (!$this->rbacManager->isGranted(null, 'order.any.manage')) {
            if (!$this->rbacManager->isGranted(null, 'order.own.manage')){
                if (!$this->rbacManager->isGranted(null, 'order.client.manage')){
                    return $this->redirect()->toRoute('not-authorized');
                } else {
                    $client = null;
                    $contacts = $this->currentUser()->getContacts();
                    foreach ($contacts as $contact){
                        $client = $contact->getClient();
                        if ($client) break;
                    }
                    if ($client){
                        $query = $this->entityManager->getRepository(Order::class)
                                    ->findClientOrder($client);
                    } else {
                        throw new \Exception('Не определен покупатель у пользователя ');
                    }   
                }
            } else {
                $query = $this->entityManager->getRepository(Order::class)
                            ->findAllOrder($this->currentUser());                
            }
        } else {
            $query = $this->entityManager->getRepository(Order::class)
                        ->findAllOrder();
        }    
                        
        $adapter = new DoctrineAdapter(new ORMPaginator($query, false));
        $paginator = new Paginator($adapter);
        $paginator->setDefaultItemCountPerPage(10);        
        $paginator->setCurrentPageNumber($page);        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'order' => $paginator,
            'orderManager' => $this->orderManager
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
    
}
