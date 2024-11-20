<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Application\Entity\Raw;
use Application\Entity\PriceGetting;
use Application\Entity\Supplier;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Laminas\Paginator\Paginator;

class PriceController extends AbstractActionController
{
    
    /**
     * Менеджер сущностей.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Менеджер.
     * @var \Application\Service\SupplierManager 
     */
    private $supplierManager;    
    
    /**
     * Менеджер.
     * @var \Application\Service\PriceManager 
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
    
    public function commonMailAction()
    {
        $result = $this->priceManager->getNewPriceByMail();
        
        return new JsonModel(
           ['ok']
        );           
    }
    
    public function byLinkAction()
    {
        $priceGettingId = $this->params()->fromRoute('id', -1);
        // Находим существующий supplier в базе данных.    
        $priceGetting = $this->entityManager->getRepository(PriceGetting::class)
                ->findOneById($priceGettingId);  
        	
        if ($priceGetting == null) {
            $this->getResponse()->setStatusCode(401);
            exit;                        
        } 
        
        $result = $this->priceManager->getPriceByLink($priceGetting);
        
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
        setlocale(LC_ALL,'ru_RU.UTF-8');
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
    
    
    public function priceGettingAction()
    {
        return new ViewModel([]);                  
    }
    
    public function priceGettingContentAction()
    {
        
        $supplierId = $this->params()->fromRoute('supplier', -1);
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        
        $query = $this->entityManager->getRepository(PriceGetting::class)
                    ->findBy([]);            
        
        if ($offset) $query->setFirstResult( $offset );
        if ($limit) $query->setMaxResults( $limit );
        
        $result = $query->getResult(2);
        
        return new JsonModel([
            'rows' => $result,
        ]);          
    }
    
     /*
     * Очередь файлов с прайсам для загрузки
     */
    public function queueAction()
    {        
        return new ViewModel([
            'supplierManager' => $this->supplierManager,
            'files' => $this->supplierManager->getPriceFilesToUpload(),
        ]);
                    
    }

    public function changeRawSupplierAction()
    {
        $rawId = $supplierId = -1;
        
        if ($this->getRequest()->isPost()) {
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            $rawId = $data['pk'];
            $supplierId = $data['value'];            
        }    
//        var_dump($data); exit;
        if ($rawId<0 || $supplierId < 0) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }           
        $raw = $this->entityManager->getRepository(Raw::class)
                ->find($rawId);
        
        $supplier = $this->entityManager->getRepository(Supplier::class)
                ->find($supplierId);
        
        $this->priceManager->updateRawSupplier($raw, $supplier); 
        
        return new JsonModel([
            'ok'
        ]);         
    }
    
}
