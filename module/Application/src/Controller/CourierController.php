<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Application\Entity\Courier;
use Application\Entity\Shipping;
use Company\Entity\Office;
use Application\Form\CourierForm;
use Application\Form\ShippingForm;
use Laminas\View\Model\JsonModel;

class CourierController extends AbstractActionController
{
    
    /**
     * Менеджер сущностей.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Менеджер courier.
     * @var \Application\Service\CourierManager 
     */
    private $courierManager;    
        
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $courierManager) 
    {
        $this->entityManager = $entityManager;
        $this->courierManager = $courierManager;
        
    }   
    
    public function indexAction()
    {
        $couriers = $this->entityManager->getRepository(Courier::class)
                ->findAll();
        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'couriers' => $couriers,
        ]);  
    }
    
    public function contentAction()
    {
        	        
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit', 10);
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order', 'ASC');
        
        $query = $this->entityManager->getRepository(Client::class)
                        ->findAllClient(['search' => $q, 'sort' => $sort, 'order' => $order]);
        
        $total = $limit;
        
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
        $form = new CourierForm($this->entityManager);
        
        
        // Проверяем, является ли пост POST-запросом.
        if ($this->getRequest()->isPost()) {
            
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            
            // Заполняем форму данными.
            $form->setData($data);
            if ($form->isValid()) {
                                
                // Получаем валидированные данные формы.
                $data = $form->getData();
                
                // Используем менеджер client для добавления нового good в базу данных.                
                $courier = $this->courierManager->addCourier($data);
                
                // Перенаправляем пользователя на страницу "client".
                return $this->redirect()->toRoute('courier', ['action' => 'view', 'id' => $courier->getId()]);
            }
        }
        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'form' => $form,
        ]);
    }   
    
   public function editAction()
   {
        // Создаем форму.
        $form = new CourierForm($this->entityManager);
    
        // Получаем ID tax.    
        $courierId = $this->params()->fromRoute('id', -1);
    
        // Находим существующий пост в базе данных.    
        $courier = $this->entityManager->getRepository(Courier::class)
                ->findOneById($courierId);  
        	
        if ($courier == null) {
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
                
                // Используем менеджер, чтобы добавить новый пост в базу данных.                
                $this->courierManager->updateCourier($courier, $data);
                
                // Перенаправляем пользователя на страницу "courier".
                return $this->redirect()->toRoute('courier', []);
            }
        } else {
            $data = [
               'aplId' => $courier->getAplId(),
               'name' => $courier->getName(),
               'comment' => $courier->getComment(),
               'status' => $courier->getStatus(),
               'site' => $courier->getSite(),
               'track' => $courier->getTrack(),
               'calculator' => $courier->getCalculator(),
            ];
            
            $form->setData($data);
        }
        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'form' => $form,
            'courier' => $courier
        ]);  
    }    
    
    public function deleteAction()
    {
        $courierId = $this->params()->fromRoute('id', -1);
        
        $courier = $this->entityManager->getRepository(Courier::class)
                ->findOneById($courierId);        
        if ($courier == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $result = $this->courierManager->removeCourier($courier);
        
        if ($result){
            // Перенаправляем пользователя на страницу "courier".        
            return $this->redirect()->toRoute('courier', []);
        } else {
            return $this->redirect()->toRoute('courier', ['action' => 'view', 'id' => $courier->getId()]);
        }    
    }    

    
    public function viewAction() 
    {       
        $courierId = (int)$this->params()->fromRoute('id', -1);
        
        // Validate input parameter
        if ($courierId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // Find the client ID
        $courier = $this->entityManager->getRepository(Courier::class)
                ->findOneById($courierId);
        
        if ($courier == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }      
        
        // Render the view template.
        return new ViewModel([
            'courier' => $courier,
        ]);
    }      
    
    public function addShippingAction() 
    {     
        $officeId = (int)$this->params()->fromRoute('id', -1);
        
        // Validate input parameter
        if ($officeId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // Find the office ID
        $office = $this->entityManager->getRepository(Office::class)
                ->findOneById($officeId);
        
        if ($office == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }      
        // Создаем форму.
        $form = new ShippingForm($this->entityManager);
        
        
        // Проверяем, является ли пост POST-запросом.
        if ($this->getRequest()->isPost()) {
            
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            
            // Заполняем форму данными.
            $form->setData($data);
            if ($form->isValid()) {
                                
                // Получаем валидированные данные формы.
                $data = $form->getData();
                
                // Используем менеджер client для добавления нового good в базу данных.                
                $shipping = $this->courierManager->addShipping($office, $data);
                
                // Перенаправляем пользователя на страницу "client".
                return $this->redirect()->toRoute('courier', ['action' => 'view-shipping', 'id' => $shipping->getId()]);
            }
        }
        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'form' => $form,
            'office' => $office,
        ]);
    }   

   public function editShippingAction()
   {
        // Создаем форму.
        $form = new ShippingForm($this->entityManager);
    
        // Получаем ID tax.    
        $shippingId = $this->params()->fromRoute('id', -1);
    
        // Находим существующий пост в базе данных.    
        $shipping = $this->entityManager->getRepository(Shipping::class)
                ->findOneById($shippingId);  
        	
        if ($shipping == null) {
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
                
                // Используем менеджер, чтобы добавить новый пост в базу данных.                
                $this->courierManager->updateShipping($shipping, $data);
                
                // Перенаправляем пользователя на страницу "courier".
                return $this->redirect()->toUrl("/offices/view/{$shipping->getOffice()->getId()}#section7");
            }
        } else {
            $data = [
               'aplId' => $shipping->getAplId(),
               'name' => $shipping->getName(),
               'comment' => $shipping->getComment(),
               'status' => $shipping->getStatus(),
               'rate' => $shipping->getRate(),
               'rateTrip' => $shipping->getRateTrip(),
               'rateDistance' => $shipping->getRateDistance(),
               'sorting' => $shipping->getSorting(),
            ];
            
            $form->setData($data);
        }
        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'form' => $form,
            'shipping' => $shipping,
            'office' =>$shipping->getOffice(),
        ]);  
    }    
    
    public function deleteShippingAction()
    {
        $shippingId = $this->params()->fromRoute('id', -1);
        
        $shipping = $this->entityManager->getRepository(Shipping::class)
                ->findOneById($shippingId);
        
        if ($shipping == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $result = $this->courierManager->removeShipping($shipping);
        
        if ($result){
            // Перенаправляем пользователя на страницу "courier".        
            return $this->redirect()->toUrl("/offices/view/{$shipping->getOffice()->getId()}#section7");
        } else {
            return $this->redirect()->toRoute('courier', ['action' => 'view-shipping', 'id' => $shipping->getId()]);
        }    
    }    

    public function viewShippingAction() 
    {       
        $shippingId = (int)$this->params()->fromRoute('id', -1);
        
        // Validate input parameter
        if ($shippingId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // Find the client ID
        $shipping = $this->entityManager->getRepository(Shipping::class)
                ->findOneById($shippingId);
        
        if ($shipping == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }      
        
        // Render the view template.
        return new ViewModel([
            'shipping' => $shipping,
            'office' => $shipping->getOffice(),
        ]);
    }      
    
    public function changeShippingAction() 
    {       
        $shippingId = (int)$this->params()->fromRoute('id', -1);
        $delta = $this->params()->fromQuery('delta');
        
        // Validate input parameter
        if ($shippingId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // Find the client ID
        $shipping = $this->entityManager->getRepository(Shipping::class)
                ->findOneById($shippingId);
        
        if ($shipping == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }      
        
        if ($delta){
            $this->courierManager->changeSorting($shipping, $delta);
        }
        
        return $this->redirect()->toUrl("/offices/view/{$shipping->getOffice()->getId()}#section7");
    }      
   
}
