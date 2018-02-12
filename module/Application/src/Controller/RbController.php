<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Entity\Tax;
use Application\Entity\Country;
use Application\Entity\Producer;
use Application\Form\TaxForm;
use Application\Form\CountryForm;
use Application\Form\ProducerForm;
use Zend\Session\Container;
use Zend\View\Model\JsonModel;
use Application\Filter\BikFilter;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;

class RbController extends AbstractActionController
{
   
    /**
    * Менеджер сущностей.
    * @var Doctrine\ORM\EntityManager
    */
    private $entityManager;
    
    /**
     * Менеджер справочников.
     * @var Application\Service\RbManager 
     */
    private $rbManager;    
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $rbManager) 
    {
        $this->entityManager = $entityManager;
        $this->rbManager = $rbManager;
    }    
    
    public function indexAction()
    {
        return new ViewModel();
    }
    
    public function taxAction()
    {
        $tax = $this->entityManager->getRepository(Tax::class)
               ->findBy([], []);
        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'tax' => $tax,
            'rbManager' => $this->rbManager
        ]);  
    }
    
    public function taxAddAction() 
    {     
        // Создаем форму.
        $form = new TaxForm();
        
        // Проверяем, является ли пост POST-запросом.
        if ($this->getRequest()->isPost()) {
            
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            
            // Заполняем форму данными.
            $form->setData($data);
            if ($form->isValid()) {
                                
                // Получаем валидированные данные формы.
                $data = $form->getData();
                
                // Используем менеджер rb для добавления нового tax в базу данных.                
                $this->rbManager->addNewTax($data);
                
                // Перенаправляем пользователя на страницу "/rb/tax".
                return $this->redirect()->toRoute('rb', ['action'=>'tax']);
            }
        }
        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'form' => $form
        ]);
    }   
    
   public function taxEditAction()
   {
        // Создаем форму.
        $form = new TaxForm();
    
        // Получаем ID tax.    
        $taxId = $this->params()->fromRoute('id', -1);
    
        // Находим существующий пост в базе данных.    
        $tax = $this->entityManager->getRepository(Tax::class)
                ->findOneById($taxId);  
        	
        if ($tax == null) {
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
                $this->rbManager->updateTax($tax, $data);
                
                // Перенаправляем пользователя на страницу "rb/tax".
                return $this->redirect()->toRoute('rb', ['action'=>'tax']);
            }
        } else {
            $data = [
               'name' => $tax->getName(),
               'amount' => $tax->getAmount(),
            ];
            
            $form->setData($data);
        }
        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'form' => $form,
            'tax' => $tax
        ]);  
    }    
    
    public function taxDeleteAction()
    {
        $taxId = $this->params()->fromRoute('id', -1);
        
        $tax = $this->entityManager->getRepository(Tax::class)
                ->findOneById($taxId);        
        if ($tax == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->rbManager->removeTax($tax);
        
        // Перенаправляем пользователя на страницу "rb/tax".
        return $this->redirect()->toRoute('rb', ['action'=>'tax']);
    }    

    public function taxViewAction() 
    {       
        $taxId = (int)$this->params()->fromRoute('id', -1);
        
        // Validate input parameter
        if ($taxId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // Find the tax by ID
        $tax = $this->entityManager->getRepository(Tax::class)
                ->findOneById($taxId);
        
        if ($tax == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        // Render the view template.
        return new ViewModel([
            'tax' => $tax,
        ]);
    }  
     
    public function countryAction()
    {
        $page = $this->params()->fromQuery('page', 1);
        
//        $country = $this->entityManager->getRepository(Country::class)
//               ->findBy([], []);

        $query = $this->entityManager->getRepository(Country::class)
                    ->findAllCountry();
                
        $adapter = new DoctrineAdapter(new ORMPaginator($query, false));
        $paginator = new Paginator($adapter);
        $paginator->setDefaultItemCountPerPage(10);        
        $paginator->setCurrentPageNumber($page);        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'country' => $paginator,
            'rbManager' => $this->rbManager
        ]);  
    }
    
    public function countrySelectAction()
    {
        $page = $this->params()->fromQuery('page', 1);
        $action = $this->params()->fromQuery('action', 'producer-add');
        $id = $this->params()->fromQuery('id');
        
//        $country = $this->entityManager->getRepository(Country::class)
//               ->findBy([], []);

        $query = $this->entityManager->getRepository(Country::class)
                    ->findAllCountry();
                
        $adapter = new DoctrineAdapter(new ORMPaginator($query, false));
        $paginator = new Paginator($adapter);
        $paginator->setDefaultItemCountPerPage(10);        
        $paginator->setCurrentPageNumber($page);        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'country' => $paginator,
            'rbManager' => $this->rbManager,
            'action' => $action,
            'id' => $id,
        ]);  
    }
    
    public function countryAddAction() 
    {     
        // Создаем форму.
        $form = new CountryForm();
        
        // Проверяем, является ли пост POST-запросом.
        if ($this->getRequest()->isPost()) {
            
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            
            // Заполняем форму данными.
            $form->setData($data);
            if ($form->isValid()) {
                                
                // Получаем валидированные данные формы.
                $data = $form->getData();
                
                // Используем менеджер rb для добавления нового country в базу данных.                
                $this->rbManager->addNewCountry($data);
                
                // Перенаправляем пользователя на страницу "/rb/country".
                return $this->redirect()->toRoute('rb', ['action'=>'country']);
            }
        }
        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'form' => $form
        ]);
    }   
    
   public function countryEditAction()
   {
        // Создаем форму.
        $form = new CountryForm();
    
        // Получаем ID tax.    
        $countryId = $this->params()->fromRoute('id', -1);
    
        // Находим существующий пост в базе данных.    
        $country = $this->entityManager->getRepository(Country::class)
                ->findOneById($countryId);  
        	
        if ($country == null) {
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
                $this->rbManager->updateCountry($country, $data);
                
                // Перенаправляем пользователя на страницу "rb/country".
                return $this->redirect()->toRoute('rb', ['action'=>'country']);
            }
        } else {
            $data = [
               'name' => $country->getName(),
               'fullname' => $country->getFullname(),
               'code' => $country->getCode(),
               'alpha2' => $country->getAlpha2(),
               'alpha3' => $country->getAlpha3(),
            ];
            
            $form->setData($data);
        }
        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'form' => $form,
            'country' => $country
        ]);  
    }    
    
    public function countryDeleteAction()
    {
        $countryId = $this->params()->fromRoute('id', -1);
        
        $country = $this->entityManager->getRepository(Country::class)
                ->findOneById($countryId);        
        if ($country == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->rbManager->removeCountry($country);
        
        // Перенаправляем пользователя на страницу "rb/country".
        return $this->redirect()->toRoute('rb', ['action'=>'country']);
    }    
   
    public function countryUploadAction()
    {
        
        $var = $this->rbManager->uploadCountry();
        // Перенаправляем пользователя на страницу "rb/country".
        return $this->redirect()->toRoute('rb', ['action'=>'country']);
    }    
   
    public function countryViewAction() 
    {       
        $countryId = (int)$this->params()->fromRoute('id', -1);
        
        // Validate input parameter
        if ($countryId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // Find the country by ID
        $country = $this->entityManager->getRepository(Country::class)
                ->findOneById($countryId);
        
        if ($country == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        // Render the view template.
        return new ViewModel([
            'country' => $country,
        ]);
    }  
     
    public function producerAction()
    {
        $page = $this->params()->fromQuery('page', 1);
        
        $query = $this->entityManager->getRepository(Producer::class)
                    ->findAllProducer();
                
        $adapter = new DoctrineAdapter(new ORMPaginator($query, false));
        $paginator = new Paginator($adapter);
        $paginator->setDefaultItemCountPerPage(10);        
        $paginator->setCurrentPageNumber($page);        
        // Визуализируем шаблон представления.
        
        return new ViewModel([
            'producer' => $paginator,
            'rbManager' => $this->rbManager,
        ]);  
    }
    
    public function producerAddAction() 
    {     
        // Создаем форму.
        $form = new ProducerForm($this->entityManager);
                
        // Проверяем, является ли пост POST-запросом.        
        if ($this->getRequest()->isPost()) {
            
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
                        
            // Заполняем форму данными.
            $form->setData($data);
            if ($form->isValid()) {
                                
                // Получаем валидированные данные формы.
                $data = $form->getData();
                
                // Используем менеджер rb для добавления нового producer в базу данных.                
                $this->rbManager->addNewProducer($data);
                
                // Перенаправляем пользователя на страницу "/rb/producer".
                return $this->redirect()->toRoute('rb', ['action'=>'producer']);
            }
        }        

        // Визуализируем шаблон представления.
        return new ViewModel([
            'form' => $form,
        ]);
    }   
    
   public function producerEditAction()
   {
        // Создаем форму.
        $form = new ProducerForm($this->entityManager);

        // Получаем ID producer.    
        $producerId = $this->params()->fromRoute('id', -1);
    
        // Находим существующий пост в базе данных.    
        $producer = $this->entityManager->getRepository(Producer::class)
                ->findOneById($producerId);  
        	
        if ($producer == null) {
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
                $this->rbManager->updateProducer($producer, $data);

                // Перенаправляем пользователя на страницу "rb/producer".
                return $this->redirect()->toRoute('rb', ['action'=>'producer']);
            }
        } else {
            $data = [
               'name' => $producer->getName(),
               'country' => $producer->getCountry(),
            ];
            
            $form->setData($data);
        }
        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'form' => $form,
        ]);  
    }    
    
    public function producerDeleteAction()
    {
        $producerId = $this->params()->fromRoute('id', -1);
        
        $producer = $this->entityManager->getRepository(Producer::class)
                ->findOneById($producerId);        
        if ($producer == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->rbManager->removeProducer($producer);
        
        // Перенаправляем пользователя на страницу "rb/tax".
        return $this->redirect()->toRoute('rb', ['action'=>'producer']);
    }    

    public function producerViewAction() 
    {       
        $producerId = (int)$this->params()->fromRoute('id', -1);
        
        // Validate input parameter
        if ($producerId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // Find the tax by ID
        $producer = $this->entityManager->getRepository(Producer::class)
                ->findOneById($producerId);
        
        if ($producer == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        // Render the view template.
        return new ViewModel([
            'producer' => $producer,
        ]);
    }   
    
    public function bikAction()
    {
        $bik = $this->params()->fromRoute('id', -1);
        
        $filter = new BikFilter();
        
        return new JsonModel([
            'data' => $filter->filter($bik),
        ]);
        
    }
}
