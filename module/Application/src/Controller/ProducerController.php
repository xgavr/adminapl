<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Entity\Country;
use Application\Entity\Producer;
use Application\Entity\UnknownProducer;
use Application\Form\ProducerForm;
use Zend\Session\Container;
use Zend\View\Model\JsonModel;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;

class ProducerController extends AbstractActionController
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
    private $producerManager;    
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $producerManager) 
    {
        $this->entityManager = $entityManager;
        $this->producerManager = $producerManager;
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
    
    public function unknownAction()
    {
        $bind = $this->entityManager->getRepository(UnknownProducer::class)
                ->findBindNoBindRawprice();
        return new ViewModel([
            'binds' => $bind,
        ]);  
    }
    
    public function unknownContentAction()
    {
        	        
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        
        $query = $this->entityManager->getRepository(Producer::class)
                        ->findAllUnknownProducer(['q' => $q]);
        
        $total = count($query->getResult(2));
        
        if ($offset) $query->setFirstResult( $offset );
        if ($limit) $query->setMaxResults( $limit );

        $result = $query->getResult(2);
        
        return new JsonModel([
            'total' => $total,
            'rows' => $result,
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
        
//        $rawpriceCount = $this->entityManager->getRepository(UnknownProducer::class)
//                ->rawpriceCount($unknownProducer);
        $rawpriceCountBySupplier = $this->entityManager->getRepository(UnknownProducer::class)
                ->rawpriceCountBySupplier($unknownProducer);
        
        $prevQuery = $this->entityManager->getRepository(Producer::class)
                        ->findAllUnknownProducer(['prev1' => $unknownProducer->getName()]);
        $nextQuery = $this->entityManager->getRepository(Producer::class)
                        ->findAllUnknownProducer(['next1' => $unknownProducer->getName()]);        

        // Render the view template.
        return new ViewModel([
            'unknownProducer' => $unknownProducer,
            'rawpriceCountBySupplier' => $rawpriceCountBySupplier,
            'prev' => $prevQuery->getResult(), 
            'next' => $nextQuery->getResult(),
            'producerManager' => $this->producerManager,
        ]);
    }
    
    public function updateFromRawpriceAction()
    {
        $this->producerManager->grabUnknownProducerFromRawprice();
                
        return new JsonModel([
            'result' => 'ok-reload',
            'message' => 'ok',
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
