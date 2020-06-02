<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Application\Entity\Client;
use Application\Entity\Contact;
use User\Entity\User;
use Application\Form\ClientForm;
use Application\Form\ContactForm;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Laminas\Paginator\Paginator;

class ClientController extends AbstractActionController
{
    
    /**
     * Менеджер сущностей.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Менеджер товаров.
     * @var Application\Service\ClientManager 
     */
    private $clientManager;    
    
    /**
     * Менеджер.
     * @var Application\Service\ContactManager 
     */
    private $contactManager;    
    
    /*
     * Менеджер сессий
     * @var Zend\Seesion
     */
    private $sessionContainer;
    
    /**
     * RBAC manager.
     * @var User\Service\RbacManager
     */
    private $rbacManager;    
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $clientManager, $contactManager, $sessionContainer, $rbacManger) 
    {
        $this->entityManager = $entityManager;
        $this->clientManager = $clientManager;
        $this->contactManager = $contactManager; 
        $this->sessionContainer = $sessionContainer;
        $this->rbacManager = $rbacManger;
        
    }   
    
    public function setCurrentClientAction()
    {
        $clientId = $this->params()->fromRoute('id', -1);
        $this->sessionContainer->currentClient = $clientId;
        return $this->redirect()->toRoute('client', []);        
    }
    
    public function indexAction()
    {
        
        $currentClientId = $this->sessionContainer->currentClient;
        if ($currentClientId){
            $currentClient = $this->entityManager->getRepository(Client::class)
                    ->findOneById($currentClientId);  
        } else {
            $currentClient = null;
        }   
        	        
        $page = $this->params()->fromQuery('page', 1);
        
        if (!$this->rbacManager->isGranted(null, 'client.any.manage')) {
            $query = $this->entityManager->getRepository(Client::class)
                        ->findAllClient($this->currentUser());
        } else {
            $query = $this->entityManager->getRepository(Client::class)
                        ->findAllClient();            
        }    
                
        $adapter = new DoctrineAdapter(new ORMPaginator($query, false));
        $paginator = new Paginator($adapter);
        $paginator->setDefaultItemCountPerPage(10);        
        $paginator->setCurrentPageNumber($page);        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'client' => $paginator,
            'clientManager' => $this->clientManager,
            'currentClient' => $currentClient 
        ]);  
    }
    
    public function addAction() 
    {     
        // Создаем форму.
        $form = new ClientForm($this->entityManager);
        
        
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
                $client = $this->clientManager->addNewClient($data);
                
                // Перенаправляем пользователя на страницу "client".
                return $this->redirect()->toRoute('client', ['action' => 'view', 'id' => $client->getId()]);
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
        $form = new ClientForm($this->entityManager);
    
        // Получаем ID tax.    
        $clientId = $this->params()->fromRoute('id', -1);
    
        // Находим существующий пост в базе данных.    
        $client = $this->entityManager->getRepository(Client::class)
                ->findOneById($clientId);  
        	
        if ($client == null) {
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
                $this->clientManager->updateClient($client, $data);
                
                // Перенаправляем пользователя на страницу "client".
                return $this->redirect()->toRoute('client', []);
            }
        } else {
            $data = [
               'name' => $client->getName(),
               'status' => $client->getStatus(),
            ];
            
            $form->setData($data);
        }
        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'form' => $form,
            'client' => $client
        ]);  
    }    
    
    public function deleteAction()
    {
        $clientId = $this->params()->fromRoute('id', -1);
        
        $client = $this->entityManager->getRepository(Client::class)
                ->findOneById($clientId);        
        if ($client == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->clientManager->removeClient($client);
        
        // Перенаправляем пользователя на страницу "rb/tax".
        return $this->redirect()->toRoute('client', []);
    }    

    public function deleteContactAction()
    {
        $contactId = $this->params()->fromRoute('id', -1);
        
        $contact = $this->entityManager->getRepository(Contact::class)
                ->findOneById($contactId);
        
        if ($contact == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $clientId = $contact->getClient()->getId();
        
        $this->contactManager->removeContact($contact);
        
        // Перенаправляем пользователя на страницу "supplier/view".
        return $this->redirect()->toRoute('client', ['action' => 'view', 'id' => $clientId]);
    }    
    
    public function viewAction() 
    {       
        $clientId = (int)$this->params()->fromRoute('id', -1);
        
        // Validate input parameter
        if ($clientId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // Find the client ID
        $client = $this->entityManager->getRepository(Client::class)
                ->findOneById($clientId);
        
        if ($client == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }      
        
        $form = new ContactForm($this->entityManager);
        // Проверяем, является ли пост POST-запросом.
        if($this->getRequest()->isPost()) {
            
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            
            // Заполняем форму данными.
            $form->setData($data);
            if($form->isValid()) {
                                
                // Получаем валадированные данные формы.
                $data = $form->getData();
              
                // Используем менеджер постов для добавления нового комментарий к посту.
                $this->clientManager->addContactToClient($client, $data);
                
                // Снова перенаправляем пользователя на страницу "view".
                return $this->redirect()->toRoute('client', ['action'=>'view', 'id'=>$clientId]);
            }
        }
        
        // Render the view template.
        return new ViewModel([
            'client' => $client,
            'form' => $form,
        ]);
    }      
    
    public function managerTransferAction()
    {
        $clientId = (int) $this->params()->fromQuery('clientId', -1);
        $userId = (int) $this->params()->fromRoute('id', -1);
        
        if ($clientId<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        if ($userId<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // Access control.
        if (!$this->access('member.transfer.manage')) {
            $this->getResponse()->setStatusCode(401);
            return;
        }
        
        $client = $this->entityManager->getRepository(Client::class)
                ->findOneById($clientId);
        
        if ($client == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $user = $this->entityManager->getRepository(User::class)
                ->findOneById($userId);
        
        if ($user == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $this->clientManager->transferToManager([$client], $user);
        
        // Снова перенаправляем пользователя на страницу "index".
        return $this->redirect()->toRoute('client');
                
        return new ViewModel([]);
                
    }
}
