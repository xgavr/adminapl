<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Application\Entity\ContactCar;
use Application\Entity\Contact;
use Application\Entity\Make;
use Application\Entity\Model;
use Application\Entity\Car;
use Application\Form\ContactCarForm;
use Laminas\View\Model\JsonModel;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Laminas\Paginator\Paginator;

class ContactCarController extends AbstractActionController
{
    
    /**
     * Менеджер сущностей.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Менеджер товаров.
     * @var \Application\Service\ContactCarManager 
     */
    private $contactCarManager;    
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $contactCarManager) 
    {
        $this->entityManager = $entityManager;
        $this->contactCarManager = $contactCarManager;
    }    
    
    public function indexAction()
    {
        $page = $this->params()->fromQuery('page', 1);
        
        $query = $this->entityManager->getRepository(ContactCar::class)
                    ->findAll();
                
        $adapter = new DoctrineAdapter(new ORMPaginator($query, false));
        $paginator = new Paginator($adapter);
        $paginator->setDefaultItemCountPerPage(10);        
        $paginator->setCurrentPageNumber($page);        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'contact' => $paginator,
            'contactCarManager' => $this->contactCarManager
        ]);  
    }
    
    public function addAction() 
    {     
        // Создаем форму.
        $form = new ContactCarForm($this->entityManager);
        
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
                $this->contactCarManager->Add($data);
                
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

        // Получаем ID contactCar.    
        $contactCarId = $this->params()->fromRoute('id', -1);
        
        // Находим существующий contact в базе данных.    
        $contactCar = $this->entityManager->getRepository(ContactCar::class)
                ->findOneById($contactCarId);  
        
        if ($contactCar == null) {
            $this->getResponse()->setStatusCode(401);
            return;                        
        } 
        
        // Создаем форму.
        $form = new ContactCarForm($this->entityManager);
    
            	
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
                $this->contactCarManager->update($contactCar, $data);
                
            }
        } else {
            $data = [
            ];
            
            $form->setData($data);
        }
        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'form' => $form,
            'contactCar' => $contactCar,
        ]);  
    }    
    
    public function editFormAction()
    {
        $contactCarId = (int) $this->params()->fromRoute('id', -1);
        $contactId = (int)$this->params()->fromQuery('contact', -1);
        
        $contactCar = null;
        if ($contactCarId > 0){
            $contactCar = $this->entityManager->getRepository(ContactCar::class)
                    ->find($contactCarId);
            if ($contactCar){
                $contactId = $contactCar->getContact()->getId();
            }            
        }    
        
        if ($contactId<1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $contact = $this->entityManager->getRepository(Contact::class)
                ->find($contactId);
        
        if ($contact == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        // Create form
        $form = new ContactCarForm($this->entityManager);
        
        // Check if user has submitted the form
        if ($this->getRequest()->isPost()) {
            // Fill in the form with POST data
            $data = $this->params()->fromPost();            
            
            $form->setData($data);
            // Validate form
            if($form->isValid()) {
                
                // Get filtered and validated data
                $data = $form->getData();
                
                // Update contact car.
                if ($contactCar){
                    $this->contactCarManager->update($contactCar, $data);
                } else {
                    $this->contactCarManager->add($contact, $data);
                }    
                
//                $this->entityManager->refresh($contact);
                return new JsonModel(
                   ['ok']
                );           
            } else {
                //var_dump($form->getMessages());
            }             
        } else {
            if ($contactCar){
                $form->setData($contactCar->formArray());
            }    
        }
        
        $this->layout()->setTemplate('layout/terminal');
        
        return new ViewModel([
                'form' => $form,
                'contact' => $contact,
                'contactCar' => $contactCar,
            ]);
    }    
    
    public function deleteAction()
    {
        $contactCarId = $this->params()->fromRoute('id', -1);
        
        $contactCar = $this->entityManager->getRepository(ContactCar::class)
                ->findOneById($contactCarId);        
        if ($contactCar == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->contactCarManager->remove($contactCar);
        
    }    

    public function viewAction() 
    {       
        $contactCarId = (int)$this->params()->fromRoute('id', -1);
        
        // Validate input parameter
        if ($contactCarId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // Find the contactCar ID
        $contactCar = $this->entityManager->getRepository(ContactCar::class)
                ->findOneById($contactCarId);
        
        if ($contactCar == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        // Render the view template.
        return new ViewModel([
            'contactCar' => $contactCar,
        ]);
    }    
    
}
