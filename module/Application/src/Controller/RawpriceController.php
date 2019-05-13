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
use Application\Entity\UnknownProducer;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;

class RawpriceController extends AbstractActionController
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
     * @var \Application\Service\RawManager 
     */
    private $rawManager;    
    
    /**
     * Менеджер.
     * @var \Application\Service\ParseManager 
     */
    private $parseManager;    
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $supplierManager, $rawManager, $parseManager) 
    {
        $this->entityManager = $entityManager;
        $this->supplierManager = $supplierManager;
        $this->rawManager = $rawManager;
        $this->parseManager = $parseManager;
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
        if ($supplier == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->rawManager->removeRaw($raw);
        
        // Перенаправляем пользователя на страницу "raw".
        return $this->redirect()->toRoute('raw', []);
    }    
    
    public function parseAction()
    {
        $rawpriceId = (int)$this->params()->fromRoute('id', -1);

        // Validate input parameter
        if ($rawpriceId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $rawprice = $this->entityManager->getRepository(Rawprice::class)
                ->findOneById($rawpriceId);
        
        $this->parseManager->updateRawprice($rawprice, null, true, Rawprice::STATUS_PARSED);
//        return $this->redirect()->toRoute('rawprice', ['action' => 'view', 'id' => $rawprice->getId()]);

        return new JsonModel(
           ['ok']
        );                   
    }        

    public function parseRawAction()
    {
        $rawId = (int)$this->params()->fromRoute('id', -1);

        // Validate input parameter
        if ($rawId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $raw = $this->entityManager->getRepository(Raw::class)
                ->findOneById($rawId);
        
        $this->parseManager->parseRaw($raw);

        return new JsonModel(
           ['ok']
        );                   
    }        

    public function oldRawAction()
    {
        $rawId = (int)$this->params()->fromRoute('id', -1);

        // Validate input parameter
        if ($rawId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $raw = $this->entityManager->getRepository(Raw::class)
                ->findOneById($rawId);
        
        $this->parseManager->setOldRaw($raw);

        return new JsonModel(
           ['ok']
        );                   
    }        

    public function unknownProducerAction()
    {
        $rawpriceId = (int)$this->params()->fromRoute('id', -1);

        // Validate input parameter
        if ($rawpriceId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $rawprice = $this->entityManager->getRepository(Rawprice::class)
                ->findOneById($rawpriceId);
        
        $this->rawManager->unknownProducerRawprice($rawprice);
        
        return $this->redirect()->toRoute('rawprice', ['action' => 'view', 'id' => $rawprice->getId()]);
        
    }        

    public function goodAction()
    {
        $rawpriceId = (int)$this->params()->fromRoute('id', -1);

        // Validate input parameter
        if ($rawpriceId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $rawprice = $this->entityManager->getRepository(Rawprice::class)
                ->findOneById($rawpriceId);
        
        $this->rawManager->addGoodRawprice($rawprice);
        
        return $this->redirect()->toRoute('rawprice', ['action' => 'view', 'id' => $rawprice->getId()]);
        
    }        

    public function priceAction()
    {
        $rawpriceId = (int)$this->params()->fromRoute('id', -1);

        // Validate input parameter
        if ($rawpriceId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $rawprice = $this->entityManager->getRepository(Rawprice::class)
                ->findOneById($rawpriceId);
        
        $this->rawManager->setPriceRawprice($rawprice);
        
        return $this->redirect()->toRoute('rawprice', ['action' => 'view', 'id' => $rawprice->getId()]);
        
    }        

    public function viewAction() 
    {       
        $rawpriceId = (int)$this->params()->fromRoute('id', -1);

        // Validate input parameter
        if ($rawpriceId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // Find the rawprice ID
        $rawprice = $this->entityManager->getRepository(Rawprice::class)
                ->findOneById($rawpriceId);
        
        if ($rawprice == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $articleTokens = null;
        if ($rawprice->getCode()){
            $articleTokens = $this->entityManager->getRepository(\Application\Entity\Article::class)
                    ->findArticleTokens($rawprice->getCode());
        }
        
        // Render the view template.
        return new ViewModel([
            'articleTokens' => $articleTokens,
            'rawprice' => $rawprice,
            'rawManager' => $this->rawManager,
        ]);
    }      
    
    public function contentAction()
    {
        $unknownProducerId = (int)$this->params()->fromRoute('id', -1);

        $offset = $this->params()->fromQuery('offset');
//        $search = $this->params()->fromQuery('search');
        $status = $this->params()->fromQuery('status', Rawprice::STATUS_PARSED);
        
        
        // Validate input parameter
        if ($unknownProducerId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $unknownProducer = $this->entityManager->getRepository(UnknownProducer::class)
                ->findOneById($unknownProducerId);

        if ($unknownProducer == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $limit = $this->params()->fromQuery('limit', max(UnknownProducer::CHECK_MAX_ROW, $unknownProducer->getSupplierCount() * UnknownProducer::CHECK_COUNT));
        $query = $this->entityManager->getRepository(Rawprice::class)
                        ->findPrice($unknownProducer, ['status' => $status, 'limit' => $limit]);

        $total = count($query->getResult(2));
        
        if ($offset) {
            $query->setFirstResult($offset);
        }
        if ($limit) {
            $query->setMaxResults($limit);
        }

        $result = $query->getResult(2);
        
        return new JsonModel([
            'total' => $total,
            'rows' => $result,
        ]);                  
    }    
    
    public function intersectContentAction()
    {
        $unknownProducerId = (int)$this->params()->fromRoute('id', -1);

        $offset = $this->params()->fromQuery('offset', 0);
        $limit = $this->params()->fromQuery('limit');
//        $search = $this->params()->fromQuery('search');
        $unknownProducerIntersectId = $this->params()->fromQuery('intersect', -1);        
        
        // Validate input parameter
        if ($unknownProducerId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        if ($unknownProducerIntersectId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $unknownProducer = $this->entityManager->getRepository(UnknownProducer::class)
                ->findOneById($unknownProducerId);

        if ($unknownProducer == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $unknownProducerIntersect = $this->entityManager->getRepository(UnknownProducer::class)
                ->findOneById($unknownProducerIntersectId);

        if ($unknownProducerIntersect == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
//        $result = $this->entityManager->getRepository(UnknownProducer::class)
//                        ->intersectesCode($unknownProducer, $unknownProducerIntersect);
//
//        $total = count($result);
//        
//        $result = array_slice($result, $offset, $limit);

        $query = $this->entityManager->getRepository(Rawprice::class)
                        ->intersectesArticle($unknownProducer, $unknownProducerIntersect);
        
        $total = count($query->getResult(2));
        
        if ($offset) {
            $query->setFirstResult($offset);
        }
        if ($limit) {
            $query->setMaxResults($limit);
        }

        $result = $query->getResult(2);        

        return new JsonModel([
            'total' => $total,
            'rows' => $result,
        ]);                  
    }    
    
}
