<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Application\Entity\Raw;
use Application\Entity\PriceGetting;
use Application\Entity\Supplier;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;

class PriceController extends AbstractActionController
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
    
    /**
     * Менеджер.
     * @var Application\Service\PriceManager 
     */
    private $priceManager;    
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $supplierManager, $priceManager) 
    {
        $this->entityManager = $entityManager;
        $this->supplierManager = $supplierManager;
        $this->priceManager = $priceManager;
    }    
    
    public function indexAction()
    {
        $page = $this->params()->fromQuery('page', 1);
        
        $query = $this->entityManager->getRepository(Raw::class)
                    ->findAllRaw();
                
        $adapter = new DoctrineAdapter(new ORMPaginator($query, false));
        $paginator = new Paginator($adapter);
        $paginator->setDefaultItemCountPerPage(10);        
        $paginator->setCurrentPageNumber($page);        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'raws' => $paginator,
        ]);  
    }
    
    public function checkAction()
    {
        $this->priceManager->checkSupplierPrice();

        return $this->redirect()->toRoute('price', []);
        
    }
    
    public function byMailAction()
    {
        $priceGettingId = $this->params()->fromRoute('id', -1);
        // Находим существующий supplier в базе данных.    
        $priceGetting = $this->entityManager->getRepository(PriceGetting::class)
                ->findOneById($priceGettingId);  
        	
        if ($priceGetting == null) {
            $this->getResponse()->setStatusCode(401);
            exit;                        
        } 
        
        $result = $this->priceManager->getPriceByMail($priceGetting);
        
        return new JsonModel(
           ['ok']
        );           
        
        exit;
        
    }
    
    public function addAction() 
    {     
        // Создаем форму.
        $form = new PriceForm($this->entityManager);
        
        // Проверяем, является ли пост POST-запросом.
        if ($this->getRequest()->isPost()) {
            
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            
            // Заполняем форму данными.
            $form->setData($data);
            if ($form->isValid()) {
                                
                // Получаем валидированные данные формы.
                $data = $form->getData();
                
                // Используем менеджер supplier для добавления нового good в базу данных.                
                $this->supplierManager->addNewPrice($data);
                
                // Перенаправляем пользователя на страницу "supplier".
                return $this->redirect()->toRoute('supplier', []);
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
        $form = new PriceForm($this->entityManager);
    
        // Получаем ID tax.    
        $supplierId = $this->params()->fromRoute('id', -1);
    
        // Находим существующий supplier в базе данных.    
        $supplier = $this->entityManager->getRepository(Price::class)
                ->findOneById($supplierId);  
        	
        if ($supplier == null) {
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
                $this->supplierManager->updatePrice($supplier, $data);
                
                // Перенаправляем пользователя на страницу "supplier".
                return $this->redirect()->toRoute('supplier', []);
            }
        } else {
            $data = [
               'name' => $supplier->getName(),
               'address' => $supplier->getAddress(),
               'info' => $supplier->getInfo(),
               'status' => $supplier->getStatus(),
            ];
            
            $form->setData($data);
        }
        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'form' => $form,
            'supplier' => $supplier
        ]);  
    }    
    
    public function deleteAction()
    {
        $priceId = $this->params()->fromRoute('id', -1);
        
        $price = $this->entityManager->getRepository(Price::class)
                ->findOneById($priceId);        
        if ($price == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->supplierManager->removePrice($price);
        
        return $this->redirect()->toRoute('supplier', []);
    }    

    public function viewAction() 
    {       
        $rawId = (int)$this->params()->fromRoute('id', -1);
        
        // Validate input parameter
        if ($rawId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // Find the tax supplier ID
        $supplier = $this->entityManager->getRepository(Price::class)
                ->findOneById($supplierId);
        
        if ($supplier == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        // Render the view template.
        return new ViewModel([
            'supplier' => $supplier,
            'supplierManager' => $this->supplierManager,
        ]);
    }      
    
    public function deletePriceFileFormAction()
    {
        $filename = $this->params()->fromQuery('filename');
        
        if (file_exists(realpath($filename))){
            unlink(realpath($filename));
        }
        
        return new JsonModel(
           ['ok']
        );           
    }
    
    public function uploadPriceFileToAplFormAction()
    {
        $supplierId = $this->params()->fromRoute('id', -1);
        
        $supplier = $this->entityManager->getRepository(Supplier::class)
                ->findOneById($supplierId);        

        if ($supplier == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $filename = $this->params()->fromQuery('filename');
        
        if (file_exists(realpath($filename))){
            $this->priceManager->putPriceFileToApl($supplier, $filename);
        }
        
        return new JsonModel(
           ['ok']
        );           
    }
    
    public function downloadPriceFileFormAction()
    {
        $filename = $this->params()->fromQuery('filename');
        
        $file = realpath($filename);
        
        if (file_exists($file)){
            if (ob_get_level()) {
              ob_end_clean();
            }
            // заставляем браузер показать окно сохранения файла
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . basename($file));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            // читаем файл и отправляем его пользователю
            readfile($file);
        }
        exit;          
    }    
}
