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
use Application\Entity\Goods;
use Application\Entity\Rawprice;
use Application\Entity\Raw;
use Application\Form\GoodsForm;
use Application\Form\GoodSettingsForm;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;

class GoodsController extends AbstractActionController
{
    
    /**
     * Менеджер сущностей.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Менеджер товаров.
     * @var Application\Service\GoodsManager 
     */
    private $goodsManager;    
    
    /**
     * Менеджер создания товаров.
     * @var Application\Service\AssemblyManager 
     */
    private $assemblyManager;
    
    /**
     * Менеджер создания товаров.
     * @var Application\Service\ArticleManager 
     */
    private $articleManager;
    
    /**
     * Менеджер создания наименований.
     * @var Application\Service\NameManager 
     */
    private $nameManager;
    
    /**
     * Менеджер внешних баз.
     * @var Application\Service\ExternalManager 
     */
    private $externalManager;
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $goodsManager, $assemblyManager, 
            $articleManager, $nameManager, $externalManager) 
    {
        $this->entityManager = $entityManager;
        $this->goodsManager = $goodsManager;
        $this->assemblyManager = $assemblyManager;
        $this->articleManager = $articleManager;
        $this->nameManager = $nameManager;
        $this->externalManager = $externalManager;
    }  
    
    public function assemblyAction()
    {
        $rawpriceId = $this->params()->fromRoute('id', -1);
        
        if ($rawpriceId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $rawprice = $this->entityManager->getRepository(Rawprice::class)
                ->findOneById($rawpriceId);
        
        if ($rawprice == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->assemblyManager->addNewGoodFromRawprice($rawprice);
                
        return new JsonModel([
            'ok',
        ]);                  
    }
    
    public function assemblyRawAction()
    {
        $rawId = $this->params()->fromRoute('id', -1);
        if ($rawId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $raw = $this->entityManager->getRepository(Raw::class)
                ->findOneById($rawId);
        
        if ($raw == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->assemblyManager->assemblyGoodFromRaw($raw);
                
        return new JsonModel([
            'ok',
        ]);                  
    }
    
    public function assemblyQueueAction()
    {
        $raw = $this->entityManager->getRepository(\Application\Entity\Raw::class)
                ->findOneBy(['status' => \Application\Entity\Raw::STATUS_PARSED, 'parseStage' => \Application\Entity\Raw::STAGE_TOKEN_PARSED]);

        if ($raw){
            $this->assemblyManager->assemblyGoodFromRaw($raw);
        }    
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);                  
    }
    
    public function settingsAction()
    {
        $form = new GoodSettingsForm($this->entityManager);
    
        $settings = $this->goodsManager->getSettings();
        
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
                $this->goodsManager->setSettings($data);
                
                // Перенаправляем пользователя на страницу "goods".
                return $this->redirect()->toRoute('goods', []);
            }
        } else {
            $form->setData($settings);
        }
        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'form' => $form,
        ]);  
        
    }
    
    public function indexAction()
    {
        
        $total = $this->entityManager->getRepository(Goods::class)
                ->count([]);
        $totalCar = $this->entityManager->getRepository(\Application\Entity\Make::class)
                ->findGoods();
        $aplIds = $this->entityManager->getRepository(Goods::class)
                ->findAplIds();
        $totalOem = $this->entityManager->getRepository(Goods::class)
                ->count(['statusOem' => Goods::OEM_UPDATED]);
        $totalGroup = $this->entityManager->getRepository(Goods::class)
                ->count(['statusGroup' => Goods::GROUP_UPDATED]);
        $totalDesc = $this->entityManager->getRepository(Goods::class)
                ->count(['statusDescription' => Goods::DESCRIPTION_UPDATED]);
        $totalImage = $this->entityManager->getRepository(Goods::class)
                ->count(['statusImage' => Goods::IMAGE_UPDATED]);
        
                
        // Визуализируем шаблон представления.
        return new ViewModel([
            'goodsManager' => $this->goodsManager,
            'total' => $total,
            'totalCar' => $totalCar,
            'aplIds' => $aplIds,
            'totalOem' => $totalOem,
            'totalGroup' => $totalGroup,
            'totalDesc' => $totalDesc,
            'totalImage' => $totalImage,
        ]);  
    }
    
    public function contentAction()
    {
        	        
        ini_set('memory_limit', '512M');
        
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order');
        
        $query = $this->entityManager->getRepository(Goods::class)
                        ->findAllGoods(['q' => $q, 'sort' => $sort, 'order' => $order]);
        
        $total = count($query->getResult(2));
        
        if ($offset) $query->setFirstResult( $offset );
        if ($limit) $query->setMaxResults( $limit );

        $result = $query->getResult(2);
        
        return new JsonModel([
            'total' => $total,
            'rows' => $result,
        ]);          
    }    
    
    
    public function addAction() 
    {     
        // Создаем форму.
        $form = new GoodsForm($this->entityManager);
        
        // Проверяем, является ли пост POST-запросом.
        if ($this->getRequest()->isPost()) {
            
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            
            // Заполняем форму данными.
            $form->setData($data);
            if ($form->isValid()) {
                                
                // Получаем валидированные данные формы.
                $data = $form->getData();
                
                // Используем менеджер goods для добавления нового good в базу данных.                
                $this->goodsManager->addNewGoods($data);
                
                // Перенаправляем пользователя на страницу "goods".
                return $this->redirect()->toRoute('goods', []);
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
        $form = new GoodsForm($this->entityManager);
    
        // Получаем ID tax.    
        $goodsId = $this->params()->fromRoute('id', -1);
    
        // Находим существующий пост в базе данных.    
        $goods = $this->entityManager->getRepository(Goods::class)
                ->findOneById($goodsId);  
        	
        if ($goods == null) {
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
                $this->goodsManager->updateGoods($goods, $data);
                
                // Перенаправляем пользователя на страницу "goods".
                return $this->redirect()->toRoute('goods', []);
            }
        } else {
            $data = [
               'name' => $goods->getName(),
               'code' => $goods->getCode(),
               'producer' => $goods->getProducer(),
               'tax' => $goods->getTax(),
               'available' => $goods->getAvailable(),
               'description' => $goods->getDescription(),
            ];
            
            $form->setData($data);
        }
        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'form' => $form,
            'goods' => $goods
        ]);  
    }    
    
    public function deleteAction()
    {
        $goodsId = $this->params()->fromRoute('id', -1);
        
        $goods = $this->entityManager->getRepository(Goods::class)
                ->findOneById($goodsId);        
        if ($goods == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->goodsManager->removeGoods($goods);
        
        // Перенаправляем пользователя на страницу "goods".
        return $this->redirect()->toRoute('goods', []);
    }    

    public function deleteFormAction()
    {
        $goodsId = $this->params()->fromRoute('id', -1);
        
        $goods = $this->entityManager->getRepository(Goods::class)
                ->findOneById($goodsId);        
        if ($goods == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->goodsManager->removeGood($goods);
        
        // Перенаправляем пользователя на страницу "goods".
        return new JsonModel(
           ['ok']
        );           
    }    

    public function viewAction() 
    {       
        $goodsId = (int)$this->params()->fromRoute('id', -1);
        $page = $this->params()->fromQuery('page', 1);        
        
        // Validate input parameter
        if ($goodsId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $goods = $this->entityManager->getRepository(Goods::class)
                ->findOneById($goodsId);
        
        if ($goods == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $rawprices = $this->entityManager->getRepository(Goods::class)
                ->rawprices($goods);
        
        $prevQuery = $this->entityManager->getRepository(Goods::class)
                        ->findAllGoods(['prev1' => $goods->getCode()]);
        $nextQuery = $this->entityManager->getRepository(Goods::class)
                        ->findAllGoods(['next1' => $goods->getCode()]);        

        $carQuery = $this->entityManager->getRepository(Goods::class)
                        ->findCars($goods);
        
        $carAdapter = new DoctrineAdapter(new ORMPaginator($carQuery, false));
        $carPaginator = new Paginator($carAdapter);
        $carPaginator->setDefaultItemCountPerPage(10);        
        $carPaginator->setCurrentPageNumber($page);

        $totalCars = $carPaginator->getTotalItemCount();

        // Render the view template.
        return new ViewModel([
            'goods' => $goods,
            'cars' => $carPaginator,
            'totalCars' => $totalCars,
            'rawprices' => $rawprices,
            'prev' => $prevQuery->getResult(), 
            'next' => $nextQuery->getResult(),
            'articleManager' => $this->articleManager,
            'goodsManager' => $this->goodsManager,
            'bestName' => $this->nameManager->findBestName($goods),
            'images' => $this->goodsManager->images($goods),
            'oemStatuses' => \Application\Entity\Oem::getStatusList(),
            'oemSources' => \Application\Entity\Oem::getSourceList(),
        ]);
    }      
    
    public function carContentAction()
    {
        
        $goodsId = (int)$this->params()->fromRoute('id', -1);

        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        
        // Validate input parameter
        if ($goodsId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $goods = $this->entityManager->getRepository(Goods::class)
                ->findOneById($goodsId);

        if ($goods == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $query = $this->entityManager->getRepository(Goods::class)
                        ->findCars($goods);

        $total = count($query->getResult(2));
        
        if ($offset) $query->setFirstResult( $offset );
        if ($limit) $query->setMaxResults( $limit );
        
        $result = $query->getResult(2);
        
        return new JsonModel([
            'total' => $total,
            'rows' => $result,
        ]);                  
    }
    
    public function oemContentAction()
    {
        
        $goodsId = (int)$this->params()->fromRoute('id', -1);

        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $search = $this->params()->fromQuery('search');
        
        // Validate input parameter
        if ($goodsId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $goods = $this->entityManager->getRepository(Goods::class)
                ->findOneById($goodsId);

        if ($goods == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $query = $this->entityManager->getRepository(Goods::class)
                        ->findOems($goods, ['q' => $search]);

        $total = count($query->getResult(2));
        
        if ($offset) $query->setFirstResult( $offset );
        if ($limit) $query->setMaxResults( $limit );
        
        $result = $query->getResult(2);
        
        return new JsonModel([
            'total' => $total,
            'rows' => $result,
        ]);                  
    }
    
    public function updateBestnameAction()
    {
        $goodsId = $this->params()->fromRoute('id', -1);
        
        $goods = $this->entityManager->getRepository(Goods::class)
                ->findOneById($goodsId);        
        if ($goods == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        $bestname = $this->nameManager->findBestName($goods);
        
        if ($bestname){
            $this->goodsManager->updateGoodName($goods, $bestname);
        }    
        
        // Перенаправляем пользователя на страницу "goods".
        return new JsonModel([
            'result' => 'ok-reload',
        ]);           
    }  
    
    public function externalApiAction()
    {
        $goodsId = $this->params()->fromRoute('id', -1);
        
        $goods = $this->entityManager->getRepository(Goods::class)
                ->findOneById($goodsId);        
        if ($goods == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $data = $this->externalManager->autoDb('getInfo', ['good' => $goods]);
        
        // Перенаправляем пользователя на страницу "goods".
        return new JsonModel([
            'result' => 'ok-reload',
            'message' => $data,
        ]);           
        
    }

    public function externalApiSearchAction()
    {
        $goodsId = $this->params()->fromRoute('id', -1);
        
        $goods = $this->entityManager->getRepository(Goods::class)
                ->findOneById($goodsId);        
        if ($goods == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $data = $this->externalManager->autoDb('getArticle', ['good' => $goods]);
        
        // Перенаправляем пользователя на страницу "goods".
        return new JsonModel([
            'result' => 'ok-reload',
            'message' => $data,
        ]);           
        
    }


    public function externalApiImageAction()
    {
        $goodsId = $this->params()->fromRoute('id', -1);
        
        $goods = $this->entityManager->getRepository(Goods::class)
                ->findOneById($goodsId);        
        if ($goods == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $data = $this->externalManager->autoDb('getImages', ['good' => $goods]);
        
        // Перенаправляем пользователя на страницу "goods".
        return new JsonModel([
            'result' => 'ok-reload',
        ]);           
        
    }

    public function externalApiCarAction()
    {
        $goodsId = $this->params()->fromRoute('id', -1);
        
        $goods = $this->entityManager->getRepository(Goods::class)
                ->findOneById($goodsId);        
        if ($goods == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $data = $this->externalManager->autoDb('getLinked', ['good' => $goods]);
        
        // Перенаправляем пользователя на страницу "goods".
        return new JsonModel([
            'result' => 'ok-reload',
            'message' => $data,
        ]);           
        
    }

    public function goodCarsAction()
    {
        $goodsId = $this->params()->fromRoute('id', -1);
        
        $goods = $this->entityManager->getRepository(Goods::class)
                ->findOneById($goodsId);        
        if ($goods == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $this->externalManager->addCarsToGood($goods);
        
        // Перенаправляем пользователя на страницу "goods".
        return new JsonModel([
            'result' => 'ok-reload',
        ]);           
        
    }

    public function updateCarsAction()
    {

        $this->goodsManager->updateCars();
        
        // Перенаправляем пользователя на страницу "goods".
        return new JsonModel([
            'result' => 'ok-reload',
        ]);           
        
    }



    public function deleteEmptyAction()
    {
        $deleted = $this->goodsManager->removeEmpty();
                
        return new JsonModel([
            'result' => 'ok-reload',
            'message' => $deleted.' удалено!',
        ]);          
    }
    
    public function updateCarCountAction()
    {

        $this->entityManager->getRepository(Goods::class)
                ->updateGoodCarCount();
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);                  
    }

    public function goodOemAction()
    {
        $goodsId = $this->params()->fromRoute('id', -1);
        
        $goods = $this->entityManager->getRepository(Goods::class)
                ->findOneById($goodsId);        
        if ($goods == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $this->externalManager->addOemsToGood($goods);
        
        // Перенаправляем пользователя на страницу "goods".
        return new JsonModel([
            'result' => 'ok-reload',
        ]);           
        
    }

    public function genericGroupAction()
    {
        $goodsId = $this->params()->fromRoute('id', -1);
        
        $goods = $this->entityManager->getRepository(Goods::class)
                ->findOneById($goodsId);        
        if ($goods == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $this->externalManager->updateGoodGenericGroup($goods);
        
        // Перенаправляем пользователя на страницу "goods".
        return new JsonModel([
            'result' => 'ok-reload',
        ]);           
        
    }

    public function attributeAction()
    {
        $goodsId = $this->params()->fromRoute('id', -1);
        
        $goods = $this->entityManager->getRepository(Goods::class)
                ->findOneById($goodsId);        
        if ($goods == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $this->externalManager->addAttributesToGood($goods);
        
        // Перенаправляем пользователя на страницу "goods".
        return new JsonModel([
            'result' => 'ok-reload',
        ]);           
        
    }

    public function tdOemAction()
    {
        $this->goodsManager->updateOemTd();            
                
        return new JsonModel(
            ['ok']
        );
        
    }
    
    public function tdGroupAction()
    {
        $this->goodsManager->updateGroupTd();            
                
        return new JsonModel(
            ['ok']
        );
        
    }
    
    public function tdDescriptionAction()
    {
        $this->goodsManager->updateDescriptionTd();            
                
        return new JsonModel(
            ['ok']
        );
        
    }
    
    public function resetTdDescriptionAction()
    {
        $this->entityManager->getRepository(Goods::class)
                ->resetUpdateAttributeTd();
        
        return new JsonModel(
            ['ok']
        );
        
    }
    
    public function tdImageAction()
    {
        $this->goodsManager->updateImageTd();            
                
        return new JsonModel(
            ['ok']
        );
        
    }
    
}
