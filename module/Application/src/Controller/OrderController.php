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
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $orderManager) 
    {
        $this->entityManager = $entityManager;
        $this->orderManager = $orderManager;
    }    
    
    public function indexAction()
    {
        $page = $this->params()->fromQuery('page', 1);
        
        $query = $this->entityManager->getRepository(Order::class)
                    ->findAllOrder();
                
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
    
   public function editAction()
   {
        // Создаем форму.
        $form = new OrderForm($this->entityManager);
    
        // Получаем ID tax.    
        $orderId = $this->params()->fromRoute('id', -1);
    
        // Находим существующий пост в базе данных.    
        $order = $this->entityManager->getRepository(Order::class)
                ->findOneById($orderId);  
        	
        if ($order == null) {
            $this->getResponse()->setStatusCode(401);
            return;                        
        } 
        
        // Проверяем, является ли пост POST-запросом.
        if ($this->getRequest()->isPost()) {
            
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            
            // Заполняем форму данными.
            $form->setData($data);
            if ($form->isValid()) {
                                
                // Получаем валидированные данные формы.
                $data = $form->getData();
                
                // Используем менеджер постов, чтобы добавить новый пост в базу данных.                
                $this->orderManager->updateOrder($order, $data);
                
                // Перенаправляем пользователя на страницу "order".
                return $this->redirect()->toRoute('order', []);
            }
        } else {
            $data = [
               'name' => $order->getName(),
            ];
            
            $form->setData($data);
        }
        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'form' => $form,
            'order' => $order
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
        
        // Перенаправляем пользователя на страницу "rb/tax".
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
        
        // Render the view template.
        return new ViewModel([
            'order' => $order,
        ]);
    } 
    
}
