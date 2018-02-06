<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Entity\Raw;
use Application\Entity\Rawprice;

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
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $page = $this->params()->fromQuery('page', 1);
        
        $query = $this->entityManager->getRepository(Rawprice::class)
                    ->findRawRawprice($raw);
                
        $adapter = new DoctrineAdapter(new ORMPaginator($query, false));
        $paginator = new Paginator($adapter);
        $paginator->setDefaultItemCountPerPage(10);        
        $paginator->setCurrentPageNumber($page);        
        
        // Render the view template.
        return new ViewModel([
            'raw' => $raw,
            'rawManager' => $this->rawManager,
            'rawprice' => $paginator,
        ]);
    }      
}
