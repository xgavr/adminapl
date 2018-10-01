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
use Application\Entity\UnknownProducer;
use Application\Entity\Article;
use Application\Form\ProducerForm;
use Zend\View\Model\JsonModel;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;

class OemController extends AbstractActionController
{
   
    /**
    * Менеджер сущностей.
    * @var Doctrine\ORM\EntityManager
    */
    private $entityManager;
    
    /**
     * Менеджер производителей.
     * @var Application\Service\ProducerManager 
     */
    private $producerManager;    
    
    /**
     * Менеджер артикулов производителей.
     * @var Application\Service\ArticleManager 
     */
    private $articleManager;    
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $producerManager, $articleManager, $oemManager) 
    {
        $this->entityManager = $entityManager;
        $this->producerManager = $producerManager;
        $this->articleManager = $articleManager;
        $this->oemManager = $oemManager;
    }    
    
    public function indexAction()
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
            'producerManager' => $this->producerManager,
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

    public function viewAction() 
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
    
    public function articleAction()
    {
        $bind = $this->entityManager->getRepository(Article::class)
                ->findBindNoBindRawprice();
        $total = $this->entityManager->getRepository(Article::class)
                ->count([]);
                
        return new ViewModel([
            'binds' => $bind,
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
        
        if ($offset) $query->setFirstResult( $offset );
        if ($limit) $query->setMaxResults( $limit );

        $result = $query->getResult(2);
        
        return new JsonModel([
            'total' => $total,
            'rows' => $result,
        ]);          
    }    
    
    public function articleViewAction() 
    {       
        $articleId = (int)$this->params()->fromRoute('id', -1);

        if ($articleId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $article = $this->entityManager->getRepository(Article::class)
                ->findOneById($articleId);
        
        if ($article == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $rawpriceCountBySupplier = $this->entityManager->getRepository(Article::class)
                ->rawpriceCountBySupplier($article);
        
        $prevQuery = $this->entityManager->getRepository(Article::class)
                        ->findAllArticle(['prev1' => $article->getCode()]);
        $nextQuery = $this->entityManager->getRepository(Article::class)
                        ->findAllArticle(['next1' => $article->getCode()]);        

        // Render the view template.
        return new ViewModel([
            'article' => $article,
            'rawpriceCountBySupplier' => $rawpriceCountBySupplier,
            'prev' => $prevQuery->getResult(), 
            'next' => $nextQuery->getResult(),
            'articleManager' => $this->articleManager,
        ]);
    }
    
    public function parseAction()
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

        $this->oemManager->addNewOemRawFromRawprice($rawprice);
        
        return new JsonModel([
            'ok',
        ]);          
    }
    
    public function updateOemFromRawAction()
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

        $this->oemManager->grabOemFromRaw($raw);
                
        return new JsonModel([
            'ok',
        ]);          
    }
    
    public function deleteEmptyOemAction()
    {
        $deleted = $this->articleManager->removeEmptyArticles();
                
        return new JsonModel([
            'result' => 'ok-reload',
            'message' => $deleted.' удалено!',
        ]);          
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

    
}
