<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Entity\Producer;
use Application\Entity\Goods;
use Application\Entity\UnknownProducer;
use Application\Entity\Article;
use Application\Entity\Rate;
use Application\Form\ProducerForm;
use Zend\View\Model\JsonModel;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;

class ProducerController extends AbstractActionController
{
   
    /**
    * Менеджер сущностей.
    * @var \Doctrine\ORM\EntityManager
    */
    private $entityManager;
    
    /**
     * Менеджер производителей.
     * @var \Application\Service\ProducerManager 
     */
    private $producerManager;    
    
    /**
     * Менеджер артикулов производителей.
     * @var \Application\Service\ArticleManager 
     */
    private $articleManager; 
    
    /**
     * Менеджер наименований товаров
     * @var \Application\Service\NameManager
     */
    private $nameManager;
    
    /**
     * Менеджер создания товаров
     * @var \Application\Service\AssemblyManager
     */
    private $assemblyManager;
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $producerManager, $articleManager, $nameManager, $assemblyManager) 
    {
        $this->entityManager = $entityManager;
        $this->producerManager = $producerManager;
        $this->articleManager = $articleManager;
        $this->nameManager = $nameManager;
        $this->assemblyManager = $assemblyManager;
    }    
    
    public function indexAction()
    {
        $total = $this->entityManager->getRepository(Producer::class)
                ->count([]);
        
        return new ViewModel([
            'total' => $total,
        ]);  
    }
    
    public function contentAction()
    {
        	        
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order');
        
        $query = $this->entityManager->getRepository(Producer::class)
                        ->findAllProducer(['q' => $q, 'sort' => $sort, 'order' => $order]);
        
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
    
    public function addAction() 
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
                
                // Используем менеджер для добавления нового producer в базу данных.                
                $this->producerManager->addNewProducer($data);
                
                // Перенаправляем пользователя на страницу "producer".
                return $this->redirect()->toRoute('producer', []);
            }
        }        

        // Визуализируем шаблон представления.
        return new ViewModel([
            'form' => $form,
        ]);
    }   
    
   public function editAction()
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
                $this->producerManager->updateProducer($producer, $data);

                // Перенаправляем пользователя на страницу "producer".
                return $this->redirect()->toRoute('producer', []);
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
    
    public function deleteAction()
    {
        $producerId = $this->params()->fromRoute('id', -1);
        
        $producer = $this->entityManager->getRepository(Producer::class)
                ->findOneById($producerId);        
        if ($producer == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->producerManager->removeProducer($producer);
        
        // Перенаправляем пользователя на страницу "producer".
        return $this->redirect()->toRoute('producer', []);
    }    

    public function deleteFormAction()
    {
        $producerId = $this->params()->fromRoute('id', -1);
        
        $producer = $this->entityManager->getRepository(Producer::class)
                ->findOneById($producerId);   
        
        if ($producer == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->producerManager->removeProducer($producer);
        
        // Перенаправляем пользователя на страницу "producer".
        return new JsonModel(
           ['ok']
        );           
    }    

    public function viewAction() 
    {       
        $producerId = (int)$this->params()->fromRoute('id', -1);
        $page = $this->params()->fromQuery('page', 1);
        $unknownProducerId = $this->params()->fromQuery('unknownProducer');
        
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
        

        $prevQuery = $this->entityManager->getRepository(Producer::class)
                        ->findAllProducer(['prev1' => $producer->getName()]);
        $nextQuery = $this->entityManager->getRepository(Producer::class)
                        ->findAllProducer(['next1' => $producer->getName()]);
        
        $minPrice = $this->entityManager->getRepository(Goods::class)
                ->findMinPrice(['producer' => $producer->getId()]);
        $maxPrice = $this->entityManager->getRepository(Goods::class)
                ->findMaxPrice(['producer' => $producer->getId()]);
        
        $rate = $this->entityManager->getRepository(Rate::class)
                ->findRate(['producer' => $producer->getId()]);

        // Render the view template.
        return new ViewModel([
            'producer' => $producer,
            'prev' => $prevQuery->getResult(), 
            'next' => $nextQuery->getResult(),
            'producerManager' => $this->producerManager,
            'minPrice' => $minPrice,
            'maxPrice' =>$maxPrice,
            'rate' => $rate,
        ]);
    }
    
    
    public function deleteEmptyProducerAction()
    {
        $deleted = $this->producerManager->removeEmptyProducer();
                
        return new JsonModel([
            'result' => 'ok-reload',
            'message' => $deleted.' удалено!',
        ]);          
    }
    
    
    public function unknownAction()
    {
        $stages = $this->entityManager->getRepository(Article::class)
                ->findParseStageRawpriceCount(\Application\Entity\Raw::STAGE_PRODUCER_PARSED);
        $total = $this->entityManager->getRepository(UnknownProducer::class)
                ->count([]);
        
        return new ViewModel([
            'stages' => $stages,
            'total' => $total,
        ]);  
    }
    
    public function unknownContentAction()
    {
        	        
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order');
        
        $query = $this->entityManager->getRepository(Producer::class)
                        ->findAllUnknownProducer(['q' => $q, 'sort' => $sort, 'order' => $order]);
        
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
    
    public function intersectCountContentAction()
    {
        $unknownProducerId = (int)$this->params()->fromRoute('id', -1);

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

        $intersects = $this->entityManager->getRepository(UnknownProducer::class)
                ->unknownProducerIntersect($unknownProducer);
        
        return new JsonModel([
            'id' => $unknownProducerId,
            'intersectCount' => count($intersects),
        ]);          
    }
    
    public function unknownViewAction() 
    {       
        $unknownProducerId = (int)$this->params()->fromRoute('id', -1);

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
        
        if (!$unknownProducer->getRawpriceCount()){
            $this->producerManager->updateUnknownProducerRawpriceCount($unknownProducer, true);
        }    
                
        if (!$unknownProducer->getSupplierCount()){
            $this->producerManager->updateUnknownProducerSupplierCount($unknownProducer, true);
        }    
                
//        $rawpriceCount = $this->entityManager->getRepository(UnknownProducer::class)
//                ->rawpriceCount($unknownProducer);
//        $rawpriceCountBySupplier = $this->entityManager->getRepository(UnknownProducer::class)
//                ->rawpriceCountBySupplier($unknownProducer);
        
        $prevQuery = $this->entityManager->getRepository(Producer::class)
                        ->findAllUnknownProducer(['prev1' => $unknownProducer->getName()]);
        $nextQuery = $this->entityManager->getRepository(Producer::class)
                        ->findAllUnknownProducer(['next1' => $unknownProducer->getName()]); 
        
        $intersects = $this->entityManager->getRepository(Producer::class)
                ->unknownProducerIntersect($unknownProducer);
        
//        var_dump($intersects);
        // Render the view template.
        return new ViewModel([
            'unknownProducer' => $unknownProducer,
//            'rawpriceCountBySupplier' => $rawpriceCountBySupplier,
            'prev' => $prevQuery->getResult(), 
            'next' => $nextQuery->getResult(),
            'producerManager' => $this->producerManager,
            'intersects' => $intersects,
            'priceStatuses' => \Application\Entity\Rawprice::getStatusList(),
        ]);
    }
    
    public function articleAction()
    {
//        $bind = $this->entityManager->getRepository(Article::class)
//                ->findBindNoBindRawprice();
        $stages = $this->entityManager->getRepository(Article::class)
                ->findParseStageRawpriceCount(\Application\Entity\Raw::STAGE_ARTICLE_PARSED);
        
        $total = $this->entityManager->getRepository(Article::class)
                ->count([]);
                
        return new ViewModel([
            'stages' => $stages,
            'total' => $total,
        ]);  
    }
    
    public function articleContentAction()
    {
        ini_set('memory_limit', '512M');
        	        
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        
        $query = $this->entityManager->getRepository(Article::class)
                        ->findAllArticle(['q' => $q]);

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
    
    public function articleViewAction() 
    {       
        $articleId = (int)$this->params()->fromRoute('id', -1);
        $unknownProducerId = $this->params()->fromQuery('unknownProducer');
        $unknownProducerIntersectId = $this->params()->fromQuery('unknownProducerIntersect');

        if ($articleId<0) {
            $code = $this->params()->fromQuery('code');
            
            if ($code){
                $article = $this->entityManager->getRepository(Article::class)
                        ->findOneByCode($code);
                if ($article){
                    $articleId = $article->getId();
                }                            
            } else {
                $this->getResponse()->setStatusCode(404);
                return;
            }    
        }
        
        $article = $this->entityManager->getRepository(Article::class)
                ->findOneById($articleId);
        
        if ($article == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $rawpriceCountBySupplier = $this->entityManager->getRepository(Article::class)
                ->rawpriceCountBySupplier($article, ['unknownProducer' => $unknownProducerId, 'unknownProducerIntersect' => $unknownProducerIntersectId]);
        
        $prevQuery = $this->entityManager->getRepository(Article::class)
                        ->findAllArticle(['prev1' => $article->getCode()]);
        $nextQuery = $this->entityManager->getRepository(Article::class)
                        ->findAllArticle(['next1' => $article->getCode()]);        

        $articles = [];
        $unknownProducersName = [];
        foreach($rawpriceCountBySupplier as $row){
            $articles[] = $row['articleId'];
            $uP = $this->entityManager->getRepository(UnknownProducer::class)
                    ->findOneById($row['unknownProducerId']);
            if ($uP){
                $unknownProducersName[] = $uP->getName();
            }            
        }
//        var_dump($articles);
        // Render the view template.
        return new ViewModel([
            'article' => $article,
            'rawpriceCountBySupplier' => $rawpriceCountBySupplier,
            'prev' => $prevQuery->getResult(), 
            'next' => $nextQuery->getResult(),
            'articleManager' => $this->articleManager,
            'articles' => array_values(array_unique($articles)),
            'tokenIntersect' => '',
            'unknownProducersName' => array_values(array_unique($unknownProducersName)),
        ]);
    }
    
    public function parseUnknownProducerAction()
    {
        $rawpriceId = (int)$this->params()->fromRoute('id', -1);
        if ($rawpriceId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $rawprice = $this->entityManager->getRepository(\Application\Entity\Rawprice::class)
                ->findOneById($rawpriceId);
        
        if ($rawprice == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $this->producerManager->addNewUnknownProducerFromRawprice($rawprice);
        
        return new JsonModel([
            'ok',
        ]);          
    }
    
    public function parseArticleAction()
    {
        $rawpriceId = (int)$this->params()->fromRoute('id', -1);
        if ($rawpriceId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $rawprice = $this->entityManager->getRepository(\Application\Entity\Rawprice::class)
                ->findOneById($rawpriceId);
        
        if ($rawprice == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $this->articleManager->addNewArticleFromRawprice($rawprice);
        
        return new JsonModel([
            'ok',
        ]);          
    }
    
    public function updateFromRawAction()
    {
        set_time_limit(0);
        $rawId = $this->params()->fromRoute('id', -1);

        if ($rawId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $raw = $this->entityManager->getRepository(\Application\Entity\Raw::class)
                ->findOneById($rawId);

        if ($raw == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $this->producerManager->grabUnknownProducerFromRaw($raw);
                
        return new JsonModel([
            'ok',
        ]);          
    }
    
    public function updateArticleFromRawAction()
    {
        set_time_limit(0);
        $rawId = $this->params()->fromRoute('id', -1);

        if ($rawId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $raw = $this->entityManager->getRepository(\Application\Entity\Raw::class)
                ->findOneById($rawId);

        if ($raw == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $this->articleManager->grabArticleFromRaw($raw);
                
        return new JsonModel([
            'ok',
        ]);          
    }
    
    public function deleteEmptyUnknownProducerAction()
    {
        $deleted = $this->producerManager->removeEmptyUnknownProducer();
                
        return new JsonModel([
            'result' => 'ok-reload',
            'message' => $deleted.' удалено!',
        ]);          
    }
    
    public function rawpriceCountUnknownProducerAction()
    {
        $unknownProducers = $this->entityManager->getRepository(\Application\Entity\UnknownProducer::class)
                ->findBy([]);

        foreach ($unknownProducers as $unknownProducer){
            $this->producerManager->updateUnknownProducerRawpriceCount($unknownProducer, false);
        }   
        $this->entityManager->flush();
                
        return new JsonModel([
            'result' => 'ok-reload',
        ]);          
    }
    
    public function supplierCountUnknownProducerAction()
    {
        $unknownProducers = $this->entityManager->getRepository(\Application\Entity\UnknownProducer::class)
                ->findBy([]);

        foreach ($unknownProducers as $unknownProducer){
            $this->producerManager->updateUnknownProducerSupplierCount($unknownProducer, false);
        }   
        $this->entityManager->flush();
                
        return new JsonModel([
            'result' => 'ok-reload',
        ]);          
    }
    
    public function unknownProducerIntersectAction()
    {
        
        $this->producerManager->updateUnknownProducerIntersect();
                
        return new JsonModel([
            'ok',
        ]);          
    }
    
    public function assemblyProducerAction()
    {
        
        $unknownProducerId = (int)$this->params()->fromRoute('id', -1);

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
        $this->assemblyManager->addProducerFromUnknownProducer($unknownProducer);
                
        return new JsonModel([
            'result' => 'ok-reload',
        ]);          
    }
    
    public function assemblyProducersAction()
    {

        ini_set('memory_limit', '1024M');
        set_time_limit(300);
        
        $unknownProducers = $this->entityManager->getRepository(UnknownProducer::class)
                ->findUnknownProducerForAssembly();
        
        foreach ($unknownProducers as $unknownProducer){
            $this->assemblyManager->addProducerFromUnknownProducer($unknownProducer);
        }    
                
        return new JsonModel([
            'result' => 'ok-reload',
        ]);          
    }
    
    public function assemblyProducerFromRawpriceAction()
    {
        $rawpriceId = $this->params()->fromRoute('id', -1);

        if ($rawpriceId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $rawprice = $this->entityManager->getRepository(\Application\Entity\Rawprice::class)
                ->findOneById($rawpriceId);

        if ($rawprice == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $this->assemblyManager->assemblyProducerFromRawprice($rawprice);
                
        return new JsonModel([
           'ok'
        ]);          
    }

    public function assemblyProducerFromRawAction()
    {
        $rawId = $this->params()->fromRoute('id', -1);

        if ($rawId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $raw = $this->entityManager->getRepository(\Application\Entity\Raw::class)
                ->findOneById($rawId);

        if ($raw == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $this->assemblyManager->assemblyProducerFromRaw($raw);
                
        return new JsonModel([
            'ok',
        ]);          
    }
    
    

    public function deleteEmptyArticleAction()
    {
        $deleted = $this->articleManager->removeEmptyArticles();
                
        return new JsonModel([
            'result' => 'ok-reload',
            'message' => $deleted.' удалено!',
        ]);          
    }
    
    public function searchAssistantAction()
    {
        $q = $this->params()->fromQuery('q', '');

        $data = $this->producerManager->searchProducerNameAssistant($q);
        
        return new JsonModel(
           $data
        );        
    }    
    
    public function fromUnknownAction()
    {
        $page = $this->params()->fromQuery('page', 1);

        $unknownProducerId = $this->params()->fromRoute('id', -1);
        
        $unknownProducer = $this->entityManager->getRepository(UnknownProducer::class)
                ->findOneById($unknownProducerId);
        
        if ($unknownProducer == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->producerManager->addProducerFromUnknownProducer($unknownProducer);
        
        // Перенаправляем пользователя на страницу "producer".
        return $this->redirect()->toRoute('producer', ['action' => 'unknown'], ['query' => ['page' => $page]]);
        
    }
    
    public function editableUnknownAction()
    {
        if ($this->getRequest()->isPost()) {
            
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            
            $unknownProducer = $this->entityManager->getRepository(UnknownProducer::class)
                    ->findOneById($data['pk']);

            if ($unknownProducer == null) {
                $this->getResponse()->setStatusCode(404);
                exit;                        
            }        
            
            $this->producerManager->updateUnknownProducer($unknownProducer, ['producer_name' => $data['value']]);

        }  
        exit;
    }
    
    public function editUnknownProducerNameTdAction()
    {
        if ($this->getRequest()->isPost()) {
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            $unknownProducerId = $data['pk'];
            $unknownProducer = $this->entityManager->getRepository(UnknownProducer::class)
                    ->findOneById($unknownProducerId);
                    
            if ($unknownProducer){
                $this->producerManager->updateUnknownProducerNameTd($unknownProducer, $data['value']);
            }    
        }
        
        exit;
    }
    
    
    public function deleteUnknownAction()
    {
        $page = $this->params()->fromQuery('page', 1);

        $unknownProducerId = $this->params()->fromRoute('id', -1);
        
        $unknownProducer = $this->entityManager->getRepository(UnknownProducer::class)
                ->findOneById($unknownProducerId);        
        if ($unknownProducer == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->producerManager->removeUnknownProducer($unknownProducer);
        
        // Перенаправляем пользователя на страницу "producer".
        return $this->redirect()->toRoute('producer', ['action' => 'unknown'], ['query' => ['page' => $page]]);
    }    

    public function bestNameAction()
    {
        $producerId = (int)$this->params()->fromRoute('id', -1);

        if ($producerId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $producer = $this->entityManager->getRepository(Producer::class)
                ->findOneById($producerId);
        
        if ($producer == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        $this->producerManager->bestName($producer);
                
        return new JsonModel([
            'result' => 'ok-reload',
        ]);          
        
    }
    
    public function bestNamesAction()
    {
        $producers = $this->entityManager->getRepository(Producer::class)
                ->findBy([]);
        foreach ($producers as $producer){
            $this->producerManager->bestName($producer);
        }    
                
        return new JsonModel([
            'result' => 'ok-reload',
        ]);          
        
    }
    
}
