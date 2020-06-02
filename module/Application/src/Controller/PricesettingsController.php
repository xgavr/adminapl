<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Application\Entity\Supplier;
use Application\Entity\Pricesettings;
use Application\Form\PricesettingsForm;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Laminas\Paginator\Paginator;

class PricesettingsController extends AbstractActionController
{
    
    /**
     * Менеджер сущностей.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Менеджер.
     * @var Application\Service\SupplierManager 
     */
    private $supplierManager;    
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $supplierManager) 
    {
        $this->entityManager = $entityManager;
        $this->supplierManager = $supplierManager;
    }    
    
    public function indexAction()
    {
        $page = $this->params()->fromQuery('page', 1);
        
        $query = $this->entityManager->getRepository(Supplier::class)
                    ->findAllSupplier();
                
        $adapter = new DoctrineAdapter(new ORMPaginator($query, false));
        $paginator = new Paginator($adapter);
        $paginator->setDefaultItemCountPerPage(10);        
        $paginator->setCurrentPageNumber($page);        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'supplier' => $paginator,
            'supplierManager' => $this->supplierManager
        ]);  
    }
    
    public function addAction() 
    {     
        $supplierId = $this->params()->fromQuery('supplier', -1);
    
        // Находим существующий supplier в базе данных.    
        $supplier = $this->entityManager->getRepository(Supplier::class)
                ->findOneById($supplierId);  
        	
        if ($supplier == null) {
            $this->getResponse()->setStatusCode(401);
            return;                        
        } 
        
        // Создаем форму.
        $form = new PricesettingsForm();
        
        // Проверяем, является ли пост POST-запросом.
        if ($this->getRequest()->isPost()) {
            
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            
            // Заполняем форму данными.
            $form->setData($data);
            if ($form->isValid()) {
                                
                // Получаем валидированные данные формы.
                $data = $form->getData();
                
                $this->supplierManager->addNewPricesettings($supplier, $data);
                
                // Перенаправляем пользователя на страницу "supplier".
                return $this->redirect()->toRoute('supplier', ['action' => 'view', 'id' => $supplier->getId()]);
            }
        }
        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'form' => $form,
            'supplier' => $supplier,    
        ]);
    }   
    
   public function editAction()
   {
       
        $pricesettingsId = $this->params()->fromRoute('id', -1);
    
        // Находим существующий supplier в базе данных.    
        $pricesettings = $this->entityManager->getRepository(Pricesettings::class)
                ->findOneById($pricesettingsId);  
        	
        if ($pricesettings == null) {
            $this->getResponse()->setStatusCode(401);
            return;                        
        } 
       
        $supplier = $pricesettings->getSupplier();
        // Создаем форму.
        $form = new PricesettingsForm();
    
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
                $this->supplierManager->updatePricesettings($pricesettings, $data);
                
                // Перенаправляем пользователя на страницу "supplier".
                return $this->redirect()->toRoute('supplier', ['action' => 'view', 'id' => $supplier->getId()]);
            }
        } else {
            $data = [
               'name' => $pricesettings->getName(),
               'article' => $pricesettings->getArticle(),
               'iid' => $pricesettings->getIid(),
               'price' => $pricesettings->getPrice(),
               'producer' => $pricesettings->getProducer(),
               'rest' => $pricesettings->getRest(),
               'title' => $pricesettings->getTitle(),
               'status' => $pricesettings->getStatus(),
            ];
            
            $form->setData($data);
        }
        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'form' => $form,
            'supplier' => $supplier,
        ]);  
    }    
    
    public function deleteAction()
    {
        $pricesettingsId = $this->params()->fromRoute('id', -1);
        
        $pricesettings = $this->entityManager->getRepository(Pricesettings::class)
                ->findOneById($pricesettingsId);        
        if ($pricesettings == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $supplierId = $pricesettings->getSupplier()->getId();
        
        $this->supplierManager->removePricesettings($pricesettings);
        
        // Перенаправляем пользователя на страницу "supplier/view".
        return $this->redirect()->toRoute('supplier', ['action' => 'view', 'id' => $supplierId]);
    }    

    public function viewAction() 
    {       
        $supplierId = (int)$this->params()->fromRoute('id', -1);
        
        // Validate input parameter
        if ($supplierId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // Find the tax supplier ID
        $supplier = $this->entityManager->getRepository(Supplier::class)
                ->findOneById($supplierId);
        
        if ($supplier == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $form = new ContactForm();
        // Проверяем, является ли пост POST-запросом.
        if($this->getRequest()->isPost()) {
            
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            
            // Заполняем форму данными.
            $form->setData($data);
            if($form->isValid()) {
                                
                // Получаем валадированные данные формы.
                $data = $form->getData();
              
                $this->supplierManager->addContactToSupplier($supplier, $data);
                
                // Снова перенаправляем пользователя на страницу "view".
                return $this->redirect()->toRoute('supplier', ['action'=>'view', 'id'=>$supplierId]);
            }
        }
        
        // Render the view template.
        return new ViewModel([
            'supplier' => $supplier,
            'form' => $form,
            'supplierManager' => $this->supplierManager,
        ]);
    }      
}
