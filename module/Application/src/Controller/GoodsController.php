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
use Application\Entity\Images;
use Application\Form\GoodsForm;
use Application\Form\GoodSettingsForm;
use Application\Form\UploadForm;

use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;

class GoodsController extends AbstractActionController
{
    
    /**
     * Менеджер сущностей.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Менеджер товаров.
     * @var \Application\Service\GoodsManager 
     */
    private $goodsManager;    
    
    /**
     * Менеджер создания товаров.
     * @var \Application\Service\AssemblyManager 
     */
    private $assemblyManager;
    
    /**
     * Менеджер создания товаров.
     * @var \Application\Service\ArticleManager 
     */
    private $articleManager;
    
    /**
     * Менеджер создания наименований.
     * @var \Application\Service\NameManager 
     */
    private $nameManager;
    
    /**
     * Менеджер внешних баз.
     * @var \Application\Service\ExternalManager 
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
    
    public function updatePricesRawAction()
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
        
        $this->goodsManager->updatePricesRaw($raw);
                
        return new JsonModel([
            'ok',
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
//        $totalCar = $this->entityManager->getRepository(\Application\Entity\Make::class)
//                ->findGoods();
        $aplIds = $this->entityManager->getRepository(Goods::class)
                ->findAplIds();
        $aplGroups = $this->entityManager->getRepository(Goods::class)
                ->findAplGroups();
        $aplGroupsEx = $this->entityManager->getRepository(Goods::class)
                ->count(['statusGroupEx' => Goods::GROUP_EX_TRANSFERRED]);
        $totalCar = $this->entityManager->getRepository(Goods::class)
                ->count(['statusCar' => Goods::CAR_UPDATED]);
        $totalOem = $this->entityManager->getRepository(Goods::class)
                ->count(['statusOem' => Goods::OEM_UPDATED]);
        $totalGroup = $this->entityManager->getRepository(Goods::class)
                ->count(['statusGroup' => Goods::GROUP_UPDATED]);
        $totalDesc = $this->entityManager->getRepository(Goods::class)
                ->count(['statusDescription' => Goods::DESCRIPTION_UPDATED]);
        $totalImage = $this->entityManager->getRepository(Goods::class)
                ->count(['statusImage' => Goods::IMAGE_UPDATED]);
        
        $totalRawpriceEx = $this->entityManager->getRepository(Goods::class)
                ->count(['statusRawpriceEx' => Goods::RAWPRICE_EX_TRANSFERRED]);
//        $totalRawpriceCompare = $this->entityManager->getRepository(Goods::class)
//                ->count(['statusRawpriceEx' => Goods::RAWPRICE_EX_TO_TRANSFER]);
        $totalOemEx = $this->entityManager->getRepository(Goods::class)
                ->count(['statusOemEx' => Goods::OEM_EX_TRANSFERRED]);
        $totalImgEx = $this->entityManager->getRepository(Goods::class)
                ->count(['statusImgEx' => Goods::IMG_EX_TRANSFERRED]);
        $totalPriceEx = $this->entityManager->getRepository(Goods::class)
                ->count(['statusPriceEx' => Goods::PRICE_EX_TRANSFERRED]);
        $totalCarEx = $this->entityManager->getRepository(Goods::class)
                ->count(['statusCarEx' => Goods::CAR_EX_TRANSFERRED]);
        $totalAttrEx = $this->entityManager->getRepository(Goods::class)
                ->count(['statusAttrEx' => Goods::ATTR_EX_TRANSFERRED]);
                
        // Визуализируем шаблон представления.
        return new ViewModel([
            'goodsManager' => $this->goodsManager,
            'total' => $total,
            'totalCar' => $totalCar,
            'aplIds' => $aplIds,
            'aplGroups' => $aplGroups,
            'totalOem' => $totalOem,
            'totalGroup' => $totalGroup,
            'totalDesc' => $totalDesc,
            'totalImage' => $totalImage,
            'totalRawpriceEx' => $totalRawpriceEx,
//            'totalRawpriceCompare' => $totalRawpriceCompare,
            'totalOemEx' => $totalOemEx,
            'totalImgEx' => $totalImgEx,
            'totalPriceEx' => $totalPriceEx,
            'totalCarEx' => $totalCarEx,
            'statusGroupEx' => $aplGroupsEx,
            'totalAttrEx' => $totalAttrEx,
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
        $producer = $this->params()->fromQuery('producer');
        $group = $this->params()->fromQuery('group');
        
        $query = $this->entityManager->getRepository(Goods::class)
                        ->findAllGoods(['q' => $q, 'sort' => $sort, 'order' => $order, 'producerId' => $producer, 'groupId' => $group]);
        
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
        
        $images = $this->entityManager->getRepository(Images::class)
                ->findByGood($goods->getId());

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
            'images' => $images,
            'oemStatuses' => \Application\Entity\Oem::getStatusList(),
            'oemSources' => \Application\Entity\Oem::getSourceList(),
            'priceStatuses' => Rawprice::getStatusList(),
        ]);
    }      
    
    public function carContentAction()
    {
        
        $goodsId = (int)$this->params()->fromRoute('id', -1);

        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order');
        
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
                        ->findCars($goods, ['sort' => $sort, 'order' => $order]);

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
    
    public function priceContentAction()
    {
        
        $goodsId = (int)$this->params()->fromRoute('id', -1);

        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $search = $this->params()->fromQuery('search');
        $status = $this->params()->fromQuery('status', Rawprice::STATUS_PARSED);
        $unknownProducer = $this->params()->fromQuery('unknownProducer');
        
        
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
                        ->findPrice($goods, ['status' => $status]);

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
        
//        if ($bestname){
//            $this->goodsManager->updateGoodName($goods, $bestname);
//        }    
        
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
        if (!$data){
            $data = $this->externalManager->autoDb('getSimilarInfo', ['good' => $goods]);            
        }
        
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

        $data = $this->externalManager->addImageToGood($goods);
        
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
    
    public function tdImageAction()
    {
        $this->goodsManager->updateImageTd();            
                
        return new JsonModel(
            ['ok']
        );
        
    }
    
    public function resetTdGroupAction()
    {
        $this->entityManager->getRepository(Goods::class)
                ->resetUpdateGroupTd();
        
        return new JsonModel(
            ['ok']
        );
        
    }
    
    public function resetTdCarAction()
    {
        $this->entityManager->getRepository(Goods::class)
                ->resetUpdateCarTd();
        
        return new JsonModel(
            ['ok']
        );
        
    }
    
    public function resetTdOemAction()
    {
        $this->entityManager->getRepository(Goods::class)
                ->resetUpdateOemTd();
        
        return new JsonModel(
            ['ok']
        );
        
    }
    
    public function resetTdImageAction()
    {
        $this->entityManager->getRepository(Goods::class)
                ->resetUpdateImageTd();
        
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
    
    public function resetGoodGenericTokenGroupAction()
    {
        $genericGroupId = $this->params()->fromQuery('generic', -1);
        $tokenGroupId = $this->params()->fromQuery('token', -1);
        
        $genericGroup = $this->entityManager->getRepository(\Application\Entity\GenericGroup::class)
                ->findOneById($genericGroupId);
        
        if ($genericGroup == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $tokenGroup = $this->entityManager->getRepository(\Application\Entity\TokenGroup::class)
                ->findOneById($tokenGroupId);
        
        if ($tokenGroup == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $this->entityManager->getRepository(Goods::class)
                ->resetGoodGenericTokenGroup($genericGroup, $tokenGroup);
        
        return new JsonModel(
            ['ok']
        );
        
    }
    
    public function deleteImageAction()
    {
        $imageId = (int)$this->params()->fromRoute('id', -1);
        
        // Validate input parameter
        if ($imageId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $image = $this->entityManager->getRepository(Images::class)
                ->findOneById($imageId);
        
        if ($image == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $image = $this->entityManager->getRepository(Images::class)
                ->removeImage($image);
                
        return new JsonModel([
            'ok',
        ]);
        
    }
    
    public function uploadImageFormAction()
    {
        $goodId = (int)$this->params()->fromRoute('id', -1);

        // Validate input parameter
        if ($goodId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // Find the supplier ID
        $good = $this->entityManager->getRepository(Goods::class)
                ->findOneById($goodId);
        
        if ($good == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $this->entityManager->getRepository(Images::class)
                ->addImageFolder($good, Images::STATUS_HAND);
        
        $imageFolder = $this->entityManager->getRepository(Images::class)
                ->getImageFolder($good, Images::STATUS_HAND);
        
        $form = new UploadForm($imageFolder);

        if($this->getRequest()->isPost()) {
            
            $data = array_merge_recursive(
                $this->params()->fromPost(),
                $this->params()->fromFiles()
            );            
            //var_dump($data); exit;

            // Заполняем форму данными.
            $form->setData($data);
            if($form->isValid()) {
                                
                // Получаем валадированные данные формы.
                $data = $form->getData();
                $this->entityManager->getRepository(Images::class)
                        ->uploadImageGood($good, $data['name']['tmp_name'], Images::STATUS_HAND, Images::SIMILAR_MATCH);
              
                return new JsonModel(
                   ['ok']
                );           
            }
            
        }
        
        $this->layout()->setTemplate('layout/terminal');
        
        return new ViewModel([
            'good' => $good,
            'form' => $form,
        ]);
        
    }
    
    public function updatePricesAction()
    {
        $goodId = $this->params()->fromRoute('id', -1);
        
        $good = $this->entityManager->getRepository(Goods::class)
                ->findOneById($goodId);        
        if ($good == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $this->goodsManager->updatePrices($good);
        
        // Перенаправляем пользователя на страницу "goods".
        return new JsonModel([
            'result' => 'ok-reload',
        ]);           
                
    }
    
    public function inSigmaContentAction()
    {
        $goodId = $this->params()->fromRoute('id', -1);
        
        $good = $this->entityManager->getRepository(Goods::class)
                ->findOneById($goodId);        
        if ($good == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $rawpriceId = (int)$this->params()->fromQuery('rawprice');

        if (!$rawpriceId) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $rawprice = $this->entityManager->getRepository(Rawprice::class)
                ->findOneById($rawpriceId);
        
        if ($rawprice == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $prices = $this->goodsManager->rawpricesPrices($good);
        $inSigma = $this->goodsManager->inSigma($rawprice, $prices);
        
        return new JsonModel([
            'id' => $rawpriceId,
            'inSigma' => $inSigma,
        ]);          
        
    }
    
    public function attributeEditAction()
    {
        if ($this->getRequest()->isPost()) {
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            $attributeId = $data['pk'];
            $attribute = $this->entityManager->getRepository(\Application\Entity\Attribute::class)
                    ->findOneById($attributeId);
                    
            if ($attribute){
                $this->goodsManager->updateAttribute($attribute, ['name' => $data['value']]);                    
            }    
        }
        
        exit;
    }
    
    public function attributeSimilarGoodEditAction()
    {
        if ($this->getRequest()->isPost()) {
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            $attributeId = $data['pk'];
            $attribute = $this->entityManager->getRepository(\Application\Entity\Attribute::class)
                    ->findOneById($attributeId);
//            var_dump($data); exit;
            $similarGood = ($data['value'] == 'true') ? \Application\Entity\Attribute::FOR_SIMILAR_GOOD:\Application\Entity\Attribute::FOR_SIMILAR_NO_GOOD;
                    
            if ($attribute){
                $this->goodsManager->updateAttributeSimilarGood($attribute, ['similarGood' => $similarGood]);                    
            }    
        }
        
        exit;
    }
    
    public function updateAttributeFormAction()
    {
        $goodId = (int)$this->params()->fromRoute('id', -1);
        
        if ($goodId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $good = $this->entityManager->getRepository(Goods::class)
                ->findOneById($goodId);
        
        if ($good == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $attributeId = (int)$this->params()->fromQuery('attr', -1);
        
        // Validate input parameter
        if ($attributeId>0) {
            $attribute = $this->entityManager->getRepository(\Application\Entity\Attribute::class)
                    ->findOneById($attributeId);
        } else {
            $attribute = null;
        }        
        $form = new \Application\Form\AttributeForm($this->entityManager, $attribute);

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {

                if ($attribute){
                    $this->goodsManager->updateAttribute($attribute, ['name' => $data['name']]);                    
                } else {
//                    $this->goodsManager->addAttribute($good, $data['name']);
                }    
                
                return new JsonModel(
                   ['ok']
                );           
            }
        } else {
            if ($attribute){
                $data = [
                    'name' => $attribute->getName(),  
                ];
                $form->setData($data);
            }  
        }    
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'good' => $good,
            'attribute' => $attribute,
        ]);                                
    }
    
    public function deleteAttributeAction()
    {
        $attributeId = $this->params()->fromRoute('id', -1);
        
        $attribute = $this->entityManager->getRepository(\Application\Entity\Attribute::class)
                ->findOneById($attributeId);
        
        if ($attribute == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $status = \Application\Entity\Attribute::STATUS_RETIRED;
        if ($attribute->getStatus() == \Application\Entity\Attribute::STATUS_RETIRED){
            $status = \Application\Entity\Attribute::STATUS_ACTIVE;
        }
        
        $this->goodsManager->updateAttribute($attribute, ['status' => $status]);
        
        return new JsonModel(
           ['ok']
        );           
        
        exit;
        
    }
    
    public function compareRawpriceAction()
    {
        $goodId = $this->params()->fromRoute('id', -1);
    
        // Находим существующий пост в базе данных.    
        $good = $this->entityManager->getRepository(\Application\Entity\Goods::class)
                ->findOneById($goodId);  
        	
        if ($good == null) {
            $this->getResponse()->setStatusCode(401);
            return;                        
        } 
        
        $this->goodsManager->compareRawprices($good);
        
        return new JsonModel([
            'oke'
        ]);
        
    }
    
    public function compareGoodRawpriceAction()
    {
        
        $this->goodsManager->compareGoodsRawprice();
        
        return new JsonModel([
            'result' => 'ok-reload',
        ]);
    }        
    
    public function addMyCodeAction()
    {
        $this->goodsManager->addOeAsMyCode();
        echo 'ok';
        exit;
    }
}
