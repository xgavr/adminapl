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
use Application\Entity\Rawprice;
use Application\Entity\Supplier;
use Application\Form\PriceDescriptionForm;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;

class RawController extends AbstractActionController
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
     * @var Application\Service\RawManager 
     */
    private $rawManager;    
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $supplierManager, $rawManager) 
    {
        $this->entityManager = $entityManager;
        $this->supplierManager = $supplierManager;
        $this->rawManager = $rawManager;
    }    
    
    public function indexAction()
    {
        $page = $this->params()->fromQuery('page', 1);
        
        $status = $this->params()->fromQuery('status');
        
        $query = $this->entityManager->getRepository(Raw::class)
                    ->findAllRaw($status);
                
        $adapter = new DoctrineAdapter(new ORMPaginator($query, false));
        $paginator = new Paginator($adapter);
        $paginator->setDefaultItemCountPerPage(10);        
        $paginator->setCurrentPageNumber($page);

        $statuses = $this->entityManager->getRepository(Rawprice::class)
                ->rawpriceStatuses();
        foreach ($statuses as $key => $status){
            $statuses[$key]['name'] = Rawprice::getStatusName($status['status']);
        }
        
        $rawStatuses = $this->entityManager->getRepository(Raw::class)
                ->rawStatuses();
        foreach ($rawStatuses as $key => $status){
            $rawStatuses[$key]['name'] = Raw::getStatusName($status['status']);
        }
        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'raws' => $paginator,
            'statuses' => $statuses,
            'rawStatuses' => $rawStatuses,
        ]);  
    }
    
    public function viewAction() 
    {       
        $rawId = (int)$this->params()->fromRoute('id', -1);

        // Validate input parameter
        if ($rawId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // Find the raw ID
        $raw = $this->entityManager->getRepository(Raw::class)
                ->findOneById($rawId);
        
        if ($raw == null) {
            return $this->redirect()->toRoute('raw');
//            $this->getResponse()->setStatusCode(404);
//            return;                        
        }        
        
        $page = $this->params()->fromQuery('page', 1);
        
        $statuses = $this->entityManager->getRepository(Raw::class)
                ->rawpriceStatuses($raw);
        
        foreach ($statuses as $key => $status){
            $statuses[$key]['name'] = Rawprice::getStatusName($status['status']);
        }
        
        $status = $this->params()->fromQuery('status');
        
        $query = $this->entityManager->getRepository(Rawprice::class)
                    ->findRawRawprice($raw, $status);
                
        $adapter = new DoctrineAdapter(new ORMPaginator($query, false));
        $paginator = new Paginator($adapter);
        $paginator->setDefaultItemCountPerPage(10);        
        $paginator->setCurrentPageNumber($page);
        
        $priceDescriptionForm = new PriceDescriptionForm();
        
        $otherRaws = $this->entityManager->getRepository(Raw::class)
            ->findAllRaw(null, $raw->getSupplier(), $raw)
            ->getResult()
                ;

        // Render the view template.
        return new ViewModel([
            'raw' => $raw,
            'rawManager' => $this->rawManager,
            'rawprice' => $paginator,
            'priceDescriptionElements' => $priceDescriptionForm->getElements(),
            'statuses' => $statuses,
            'status' => $status,
            'otherRaws' => $otherRaws,
        ]);
    }      
    
    public function uploadRawFormAction()
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
            $this->rawManager->uploadRawprice($supplier, $filename);
        }
        
        return new JsonModel(
           ['ok']
        );           
    }
    
    
    public function checkAction()
    {
        $this->rawManager->checkSupplierPrice();

        return $this->redirect()->toRoute('raw', []);
        
    }
        
    public function deleteAction()
    {
        $rawId = $this->params()->fromRoute('id', -1);
        
        $raw = $this->entityManager->getRepository(Raw::class)
                ->findOneById($rawId);        
        if ($raw == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->rawManager->removeRaw($raw);
        
        // Перенаправляем пользователя на страницу "raw".
        return $this->redirect()->toRoute('raw', []);
    }    

    public function deleteFormAction()
    {
        $rawId = $this->params()->fromRoute('id', -1);
        
        $raw = $this->entityManager->getRepository(Raw::class)
                ->findOneById($rawId);        
        if ($raw == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->rawManager->removeRaw($raw);
        
        return new JsonModel(
           ['ok']
        );           
    }    

    public function parseAction()
    {
        $rawId = (int)$this->params()->fromRoute('id', -1);

        // Validate input parameter
        if ($rawId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $raw = $this->entityManager->getRepository(Raw::class)
                ->findOneById($rawId);
        
        $this->rawManager->parseRaw($raw);
        
        return $this->redirect()->toRoute('raw', ['action' => 'view', 'id' => $raw->getId()]);
        
    }        

    public function newUnknownProducerAction()
    {
        $rawId = (int)$this->params()->fromRoute('id', -1);

        // Validate input parameter
        if ($rawId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $raw = $this->entityManager->getRepository(Raw::class)
                ->findOneById($rawId);
        
        $this->rawManager->addNewUnknownProducerRaw($raw);
        
        return $this->redirect()->toRoute('raw', ['action' => 'view', 'id' => $raw->getId()]);
        
    }        
    
    
    public function unknownProducerAction()
    {
        $rawId = (int)$this->params()->fromRoute('id', -1);

        // Validate input parameter
        if ($rawId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $raw = $this->entityManager->getRepository(Raw::class)
                ->findOneById($rawId);
        
        $this->rawManager->unknownProducerRaw($raw);
        
        return $this->redirect()->toRoute('raw', ['action' => 'view', 'id' => $raw->getId()]);
        
    }        
    
    public function newGoodsAction()
    {
        $rawId = (int)$this->params()->fromRoute('id', -1);

        // Validate input parameter
        if ($rawId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $raw = $this->entityManager->getRepository(Raw::class)
                ->findOneById($rawId);
        
        $this->rawManager->addNewGoodsRaw($raw);
        
        return $this->redirect()->toRoute('raw', ['action' => 'view', 'id' => $raw->getId()]);
        
    }        
    
    public function goodsAction()
    {
        $rawId = (int)$this->params()->fromRoute('id', -1);

        // Validate input parameter
        if ($rawId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $raw = $this->entityManager->getRepository(Raw::class)
                ->findOneById($rawId);
        
        $this->rawManager->addGoodRaw($raw);
        
        return $this->redirect()->toRoute('raw', ['action' => 'view', 'id' => $raw->getId()]);
        
    }        
    
    public function priceAction()
    {
        $rawId = (int)$this->params()->fromRoute('id', -1);

        // Validate input parameter
        if ($rawId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $raw = $this->entityManager->getRepository(Raw::class)
                ->findOneById($rawId);
        
        $this->rawManager->setPriceRaw($raw);
        
        return $this->redirect()->toRoute('raw', ['action' => 'view', 'id' => $raw->getId()]);
        
    }            
}
