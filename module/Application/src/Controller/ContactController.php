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
use Application\Entity\Client;
use Application\Entity\Supplier;
use User\Entity\User;
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

        // Получаем ID tax.    
        $contactId = $this->params()->fromRoute('id', -1);
        
        // Находим существующий contact в базе данных.    
        $contact = $this->entityManager->getRepository(Contact::class)
                ->findOneById($contactId);  
        
        if ($contact == null) {
            $this->getResponse()->setStatusCode(401);
            return;                        
        } 
        
        $client = $supplier = $user = null;
        if ($contact->getClient()){
            $client = $this->entityManager->getRepository(Client::class)
                    ->findOneById($contact->getClient()->getId());
        }
        if ($contact->getSupplier()){
            $supplier = $this->entityManager->getRepository(Supplier::class)
                    ->findOneById($contact->getSupplier()->getId());
        }
        if ($contact->getUser()){
            $user = $this->entityManager->getRepository(User::class)
                    ->findOneById($contact->getUser()->getId());
        }

        // Создаем форму.
        $form = new ContactForm($this->entityManager, $user);
    
            	
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
                if ($client){
                    return $this->redirect()->toRoute('client', ['action' => 'view', 'id' => $client->getId()]);
                } elseif ($supplier){
                    return $this->redirect()->toRoute('supplier', ['action' => 'view', 'id' => $supplier->getId()]);
                } elseif ($user){
                    return $this->redirect()->toRoute('user', ['action' => 'view', 'id' => $user->getId()]);
                } else {
                    return $this->redirect()->toRoute('contact', []);
                }    
            }
        } else {
            $data = [
               'name' => $contact->getName(),
               'description' => $contact->getDescription(),
               'status' => $contact->getStatus(),
                'email' => $contact->getEmail()->getName(),
                'phone' => $contact->getPhone()->getName(),
            ];
            
            $form->setData($data);
        }
        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'form' => $form,
            'contact' => $contact,
            'client' => $client,
            'supplier'  => $supplier,
            'user' => $user,
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
