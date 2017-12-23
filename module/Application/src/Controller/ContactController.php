<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Entity\Contact;
use Application\Form\ContactForm;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;

class ContactController extends AbstractActionController
{
    
    /**
     * Менеджер сущностей.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Менеджер товаров.
     * @var Application\Service\ContactManager 
     */
    private $contactManager;    
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $contactManager) 
    {
        $this->entityManager = $entityManager;
        $this->contactManager = $contactManager;
    }    
    
    public function indexAction()
    {
        $page = $this->params()->fromQuery('page', 1);
        
        $query = $this->entityManager->getRepository(Contact::class)
                    ->findAllContact();
                
        $adapter = new DoctrineAdapter(new ORMPaginator($query, false));
        $paginator = new Paginator($adapter);
        $paginator->setDefaultItemCountPerPage(10);        
        $paginator->setCurrentPageNumber($page);        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'contact' => $paginator,
            'contactManager' => $this->contactManager
        ]);  
    }
    
    public function addAction() 
    {     
        // Создаем форму.
        $form = new ContactForm($this->entityManager);
        
        // Проверяем, является ли пост POST-запросом.
        if ($this->getRequest()->isPost()) {
            
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            
            // Заполняем форму данными.
            $form->setData($data);
            if ($form->isValid()) {
                                
                // Получаем валидированные данные формы.
                $data = $form->getData();
                
                // Используем менеджер contact для добавления нового good в базу данных.                
                $this->contactManager->addNewContact($data);
                
                // Перенаправляем пользователя на страницу "contact".
                return $this->redirect()->toRoute('contact', []);
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
        $form = new ContactForm($this->entityManager);
    
        // Получаем ID tax.    
        $contactId = $this->params()->fromRoute('id', -1);
        
        $supplierId = $this->params()->fromQuery('supplier', -1);
        $clientId = $this->params()->fromQuery('client', -1);
        $userId = $this->params()->fromQuery('user', -1);
    
        // Находим существующий пост в базе данных.    
        $contact = $this->entityManager->getRepository(Contact::class)
                ->findOneById($contactId);  
        	
        if ($contact == null) {
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
                $this->contactManager->updateContact($contact, $data);
                
                // Перенаправляем пользователя на страницу "contact".
                if ($supplierId > 0){
                    return $this->redirect()->toRoute('supplier', ['action' => 'view', 'id' => $supplierId]);
                } elseif ($clientId > 0){
                    return $this->redirect()->toRoute('client', ['action' => 'view', 'id' => $clientId]);
                } elseif ($userId > 0){
                    return $this->redirect()->toRoute('user', ['action' => 'view', 'id' => $userId]);
                } else {
                    return $this->redirect()->toRoute('contact', []);
                }    
            }
        } else {
            $data = [
               'name' => $contact->getName(),
               'description' => $contact->getDescription(),
               'status' => $contact->getStatus(),
            ];
            
            $form->setData($data);
        }
        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'form' => $form,
            'contact' => $contact
        ]);  
    }    
    
    public function deleteAction()
    {
        $contactId = $this->params()->fromRoute('id', -1);
        
        $contact = $this->entityManager->getRepository(Contact::class)
                ->findOneById($contactId);        
        if ($contact == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->contactManager->removeContact($contact);
        
        // Перенаправляем пользователя на страницу "rb/tax".
        return $this->redirect()->toRoute('contact', []);
    }    

    public function viewAction() 
    {       
        $contactId = (int)$this->params()->fromRoute('id', -1);
        
        // Validate input parameter
        if ($contactId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // Find the tax contact ID
        $contact = $this->entityManager->getRepository(Contact::class)
                ->findOneById($contactId);
        
        if ($contact == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        // Render the view template.
        return new ViewModel([
            'contact' => $contact,
        ]);
    }      
}
