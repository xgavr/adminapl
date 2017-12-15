<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Entity\Client;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;

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
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $clientManager) 
    {
        $this->entityManager = $entityManager;
        $this->clientManager = $clientManager;
    }    
    
    public function indexAction()
    {
        $page = $this->params()->fromQuery('page', 1);
        
        $query = $this->entityManager->getRepository(Client::class)
                    ->findAllClient();
                
        $adapter = new DoctrineAdapter(new ORMPaginator($query, false));
        $paginator = new Paginator($adapter);
        $paginator->setDefaultItemCountPerPage(10);        
        $paginator->setCurrentPageNumber($page);        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'client' => $paginator,
            'clientManager' => $this->clientManager
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
                $this->clientManager->addNewClient($data);
                
                // Перенаправляем пользователя на страницу "client".
                return $this->redirect()->toRoute('client', []);
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

    public function viewAction() 
    {       
        $clientId = (int)$this->params()->fromRoute('id', -1);
        
        // Validate input parameter
        if ($clientId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // Find the tax client ID
        $client = $this->entityManager->getRepository(Client::class)
                ->findOneById($clientId);
        
        if ($client == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        // Render the view template.
        return new ViewModel([
            'client' => $client,
        ]);
    }      
}
