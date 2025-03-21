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
use Application\Entity\Goods;
use Application\Entity\Rawprice;
use Application\Entity\Raw;
use Application\Entity\Images;
use Application\Entity\Rate;
use Application\Entity\TitleToken;
use Application\Form\GoodsForm;
use Application\Form\GoodSettingsForm;
use Application\Form\UploadForm;
use Application\Entity\Oem;
use Application\Entity\UnknownProducer;
use Application\Filter\ArticleCode;
use Stock\Entity\Movement;
use Company\Entity\Office;
use Stock\Entity\GoodBalance;
use Application\Entity\GoodSupplier;
use Stock\Entity\Reserve;
use Application\Entity\GoodToken;
use GoodMap\Entity\FoldBalance;

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
     * Менеджер расценки.
     * @var \Application\Service\RateManager 
     */
    private $rateManager;
    
    /**
     * Менеджер внешних баз.
     * @var \Application\Service\ExternalManager 
     */
    private $externalManager;
    
    /**
     * Log manager.
     * @var \Admin\Service\LogManager 
     */
    private $logManager;

    /**
     * Rbac manager.
     * @var \User\Service\RbacManager
     */
    private $rbacManager;
    
    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $goodsManager, $assemblyManager, 
            $articleManager, $nameManager, $externalManager, $rateManager, 
            $logManager, $rbacManager) 
    {
        $this->entityManager = $entityManager;
        $this->goodsManager = $goodsManager;
        $this->assemblyManager = $assemblyManager;
        $this->articleManager = $articleManager;
        $this->nameManager = $nameManager;
        $this->externalManager = $externalManager;
        $this->logManager = $logManager;
        $this->rateManager = $rateManager;
        $this->rbacManager = $rbacManager;
    }  
    
    public function autocompleteGoodAction()
    {
        $result = [];
        $q = $this->params()->fromQuery('q');
        
        if ($q){
            $query = $this->entityManager->getRepository(Goods::class)
                            ->autocompleteGood(['search' => $q]);

            $data = $query->getResult();
            foreach ($data as $row){
                $result[] = [
                    'id' => $row->getId(), 
                    'name' => $row->getInputName(), 
                    'nameShort' => $row->getNameShort(), 
                    'code' => $row->getCode(),
                    'producer' => $row->getProducer()->getName()
                ];
            }
        }    
        
        return new JsonModel($result);
    }        
    
    public function autocompleteProducerAction()
    {
        $result = [];
        $q = $this->params()->fromQuery('q');
        
        if ($q){
            $query = $this->entityManager->getRepository(UnknownProducer::class)
                            ->autocompleteProducer(['search' => $q]);

            $data = $query->getResult();
            foreach ($data as $row){
                $result[] = [
                    'id' => $row->getId(), 
                    'name' => $row->getName(), 
                ];
            }
        }    
        
        return new JsonModel($result);
    }        
    
    public function editFormAction()
    {
        $goodId = (int)$this->params()->fromRoute('id', -1);
        
        $good = $producer = null;
        if ($goodId > 0){
            $good = $this->entityManager->getRepository(Goods::class)
                    ->find($goodId);
        }    
        
        $form = new GoodsForm($this->entityManager);

        if ($this->getRequest()->isPost()) {
            
            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {
                $producer = $this->assemblyManager->addProducer($data['producer']);
                var_dump($producer); exit;
                if ($producer){
                    $codeFilter = new ArticleCode();
                    $codeFiltered = $codeFilter->filter($data['code']);
                    $good = $this->entityManager->getRepository(Goods::class)
                            ->findBy(['code' => $codeFiltered, 'producer' => $producer->getId()]);
                    if (empty($good)){
                        $good = $this->assemblyManager->addNewGood($codeFiltered, $producer, null, 0, $data['name']);
                    }    
                }    
                
                return new JsonModel(
                   ['result' => $good->toArray()]
                );           
            } else {
                var_dump($form->getMessages());
            }
        } else {
            if ($good){
                $data = [
                    'name' => $good->getName(),
                    'producer' => $good->getProducer()->getName(),
                    'code' => $good->getCode(),
                ];
                $form->setData($data);
            }    
        }
        $this->layout()->setTemplate('layout/terminal');
        // Render the view template.
        return new ViewModel([
            'form' => $form,
            'good' => $good,
        ]);        
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
                ->findOneBy(['status' => \Application\Entity\Raw::STATUS_PARSED, 'parseStage' => \Application\Entity\Raw::STAGE_PRODUCER_ASSEMBLY]);

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
        
        $offices = $this->entityManager->getRepository(Office::class)
                ->findBy([]);
        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'goodsManager' => $this->goodsManager,
            'offices' => $offices,
         ]);  
    }
    
    public function invAction()
    {
        
        // Визуализируем шаблон представления.
        return new ViewModel([
            'goodsManager' => $this->goodsManager,
         ]);  
    }
    
    public function totalFeatureAction()
    {
        $feature = $this->params()->fromQuery('feature');
        
        switch ($feature){
            case 'totalOem': $result = $this->entityManager->getRepository(Goods::class)
                ->count(['statusOem' => Goods::OEM_UPDATED]); break;
            case 'totalDesc': $result = $this->entityManager->getRepository(Goods::class)
                ->count(['statusDescription' => Goods::DESCRIPTION_UPDATED]); break;
            case 'totalCar': $result = $this->entityManager->getRepository(Goods::class)
                ->count(['statusCar' => Goods::CAR_UPDATED]); break;
            case 'totalImage': $result = $this->entityManager->getRepository(Goods::class)
                ->count(['statusImage' => Goods::IMAGE_UPDATED]); break;
            case 'totalGroup': $result = $this->entityManager->getRepository(Goods::class)
                ->count(['statusGroup' => Goods::GROUP_UPDATED]); break;
            case 'aplIds': $result = $this->entityManager->getRepository(Goods::class)
                ->findAplIds(); break;
            case 'aplIds': $result = $this->entityManager->getRepository(Goods::class)
                ->findAplIds(); break;
            case 'aplGroups': $result = $this->entityManager->getRepository(Goods::class)
                ->findAplGroups(); break;
            case 'statusGroupEx': $result = $this->entityManager->getRepository(Goods::class)
                ->count(['statusGroupEx' => Goods::GROUP_EX_TRANSFERRED]); break;
            case 'totalOemEx': $result = $this->entityManager->getRepository(Goods::class)
                ->count(['statusOemEx' => Goods::OEM_EX_TRANSFERRED]); break;
            case 'totalImgEx': $result = $this->entityManager->getRepository(Goods::class)
                ->count(['statusImgEx' => Goods::IMG_EX_TRANSFERRED]); break;
            case 'totalCarEx': $result = $this->entityManager->getRepository(Goods::class)
                ->count(['statusCarEx' => Goods::CAR_EX_TRANSFERRED]); break;
            case 'totalAttrEx': $result = $this->entityManager->getRepository(Goods::class)
                ->count(['statusAttrEx' => Goods::ATTR_EX_TRANSFERRED]); break;
            case 'totalRawpriceEx': $result = $this->entityManager->getRepository(Goods::class)
                ->countDateEx(); break;        
            case 'totalPriceEx': $result = $this->entityManager->getRepository(Goods::class)
                ->count(['statusPriceEx' => Goods::PRICE_EX_TRANSFERRED]); break;        
            case 'totalNameEx': $result = $this->entityManager->getRepository(Goods::class)
                ->count(['statusNameEx' => Goods::NAME_EX_TRANSFERRED]); break;        
            case 'total': $result = $this->entityManager->getRepository(Goods::class)
                ->count([]); break;        
            default: $result = 0;
        }
        
        return new JsonModel([
            'total' => $result,
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
        $accurate = $this->params()->fromQuery('accurate');
        $opts = $this->params()->fromQuery('opts', false);
        
        $query = $this->entityManager->getRepository(Goods::class)
                        ->findAllGoods([
                            'q' => $q, 
                            'sort' => $sort, 
                            'order' => $order, 
                            'producerId' => $producer,
                            'groupId' => $group,
                            'groupId' => $group,
                            'accurate' => $accurate,
                            ]);
        
        $total = count($query->getResult(2));
        
        if ($offset) {
            $query->setFirstResult($offset);
        }
        if ($limit) {
            $query->setMaxResults($limit);
        }

        $result = $query->getResult(2);
        if ($opts){
            foreach ($result as $key => $value){
                $result[$key]['opts'] = Goods::optPrices($value['price'], empty($value['meanPrice']) ? 0:$value['meanPrice']);
            }
        }
        return new JsonModel([
            'total' => $total,
            'rows' => $result,
        ]);          
    }    
    
    public function presenceAction()
    {
        ini_set('memory_limit', '512M');
        	        
        $q = $this->params()->fromQuery('search');
        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order');
        $producer = $this->params()->fromQuery('producer');
        $group = $this->params()->fromQuery('group');
        $accurate = $this->params()->fromQuery('accurate');
        $office = $this->params()->fromQuery('office');
        $foldCode = $this->params()->fromQuery('foldCode');
        $rest = $this->params()->fromQuery('rest');
        $opts = $this->params()->fromQuery('opts', false);
        
        $params = [
            'q' => $q, 
            'sort' => $sort, 
            'order' => $order, 
            'producerId' => $producer,
            'groupId' => $group,
            'office' => $office,
            'foldCode' => $foldCode,
            'accurate' => $accurate,            
            'rest' => $rest,            
        ];
        
        if($accurate == Goods::SEARCH_TP){
            $query = $this->entityManager->getRepository(Goods::class)
                            ->presenceComitent($params);            
        } else {
            $query = $this->entityManager->getRepository(Goods::class)
                            ->presence($params);
        }    
        
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
//            'total' => 10000,
            'rows' => $result,
        ]);          
    }    

    public function liveSearchAction()
    {
        $total = 0;
        $result = [];
        if ($this->getRequest()->isPost()) {	   
            $data = $this->params()->fromPost();

            $query = $this->entityManager->getRepository(Goods::class)
                            ->liveSearch($data);

            $total = count($query->getResult(2));

            $result = $query->getResult(2);
        }    
        
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
        
        $this->goodsManager->removeGood($goods);
        
        $good = $this->entityManager->getRepository(Goods::class)
                ->findOneById($goodsId);
        if ($good){
            return $this->redirect()->toRoute('goods', ['action' => 'view', 'id' => $good->getId()]);            
        } else {        
            // Перенаправляем пользователя на страницу "goods".
            return $this->redirect()->toRoute('goods', []);
        }    
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

    public function viewingAction() 
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
        
        $prevQuery = $this->entityManager->getRepository(Goods::class)
                        ->findAllGoods(['prev1' => $goods->getCode()]);
        $nextQuery = $this->entityManager->getRepository(Goods::class)
                        ->findAllGoods(['next1' => $goods->getCode()]);        

        $images = $this->entityManager->getRepository(Images::class)
                ->findByGood($goods->getId());
        
        $rate = $this->entityManager->getRepository(Rate::class)
                ->findGoodRate($goods);
        
        $titleFeatures = $this->entityManager->getRepository(TitleToken::class)
                ->goodTitleFeatures($goods);

        // Render the view template.
        return new ViewModel([
            'goods' => $goods,
            'prev' => $prevQuery->getResult(), 
            'next' => $nextQuery->getResult(),
            'articleManager' => $this->articleManager,
            'goodsManager' => $this->goodsManager,
            'images' => $images,
            'oemStatuses' => \Application\Entity\Oem::getStatusList(),
            'oemSources' => \Application\Entity\Oem::getSourceList(),
            'priceStatuses' => Rawprice::getStatusList(),
            'rate' => $rate,
            'titleFeatures' => $titleFeatures,
        ]);
    }      
    
    public function viewAction() 
    {       
        $goodsId = (int)$this->params()->fromRoute('id', -1);
        $tab = $this->params()->fromQuery('tab');

        // Validate input parameter
        if ($goodsId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $goods = $this->entityManager->getRepository(Goods::class)
                ->find($goodsId);
        
        if ($goods == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $images = $this->entityManager->getRepository(Images::class)
                ->findByGood($goods->getId());
        
//        $rate = $this->entityManager->getRepository(Rate::class)
//                ->findGoodRate($goods);
        
        $offices = $this->entityManager->getRepository(Office::class)
                ->findBy([]);
        
//        $titleFeatures = $this->entityManager->getRepository(TitleToken::class)
//                ->goodTitleFeatures($goods);
        
//        $rests = $this->entityManager->getRepository(GoodBalance::class)
//                ->findBy(['good' => $goods->getId()]);
        
        $goodSuppliers = $this->entityManager->getRepository(Goods::class)
                ->findGoodSuppliers($goods);
        
        $isApl = $this->entityManager->getRepository(GoodSupplier::class)
                    ->isApl($goods);
        
        $base = $this->entityManager->getRepository(Movement::class)
                ->availableBasePtu($goods->getId());
        
        $tokens = $this->entityManager->getRepository(GoodToken::class)
                ->findBy(['good' => $goods->getId()]);
//        $tokens = [];

        // Render the view template.
        return new ViewModel([
            'goods' => $goods,
            'articleManager' => $this->articleManager,
            'goodsManager' => $this->goodsManager,
            'images' => $images,
            'oemStatuses' => \Application\Entity\Oem::getStatusList(),
            'oemSources' => \Application\Entity\Oem::getSourceList(),
            'priceStatuses' => [Rawprice::STATUS_PARSED => 'Последние'],
//            'rate' => $rate,
//            'titleFeatures' => $titleFeatures,
            'offices' => $offices,
//            'rests' => $rests,
            'currentUser' => $this->logManager->currentUser(),
            'goodSuppliers' => $goodSuppliers,
            'isApl' => $isApl,
            'base' => $base,    
            'entityManager' => $this->entityManager,
            'tokens' => $tokens,
            'rbacManager' => $this->rbacManager,
            'tab' => $tab,
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
                        ->findCars($goods, ['sort' => $sort, 'order' => $order, 'constructionFrom' => date('Y') - 30]);

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
        $source = $this->params()->fromQuery('source');
        
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
                        ->findOems($goods->getId(), ['q' => $search, 'source' => $source]);

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
    
    public function movementsAction()
    {
        
        $goodsId = (int)$this->params()->fromRoute('id', -1);

        $offset = $this->params()->fromQuery('offset');
        $limit = $this->params()->fromQuery('limit');
        $search = $this->params()->fromQuery('search');
        $source = $this->params()->fromQuery('source');
        $office = $this->params()->fromQuery('office', $this->logManager->currentUser()->getOffice()->getId());
        $sort = $this->params()->fromQuery('sort', 'docStamp');
        $order = $this->params()->fromQuery('order', 'ASC');
        $dateStart = $this->params()->fromQuery('dateStart');
        $period = $this->params()->fromQuery('period');
        
        $startDate = '2012-01-01';
        $endDate = '2199-01-01';
        if (!empty($dateStart)){
            $startDate = date('Y-m-d', strtotime($dateStart));
            $endDate = $startDate;
            if ($period == 'week'){
                $endDate = date('Y-m-d 23:59:59', strtotime('+ 1 week - 1 day', strtotime($startDate)));
            }    
            if ($period == 'month'){
                $endDate = date('Y-m-d 23:59:59', strtotime('+ 1 month - 1 day', strtotime($startDate)));
            }    
            if ($period == 'number'){
                $startDate = $dateStart.'-01-01';
                $endDate = date('Y-m-d 23:59:59', strtotime('+ 1 year - 1 day', strtotime($startDate)));
            }    
        }    
        
        // Validate input parameter
        if ($goodsId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $goods = $this->entityManager->getRepository(Goods::class)
                ->find($goodsId);

        if ($goods == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $query = $this->entityManager->getRepository(Goods::class)
                        ->movements($goods, ['q' => $search, 'source' => $source, 
                            'sort' => $sort, 'order' => $order, 'office' => $office,
                            'startDate' => $startDate, 'endDate' => $endDate]);

        $fullResult = $query->getResult(2);
        $totalIn = $totalOut = $totalRest = 0;
        $total = count($fullResult);
        foreach ($fullResult as $key=>$value){
            $totalIn += ($value['quantity'] >= 0) ? $value['quantity']:0;
            $totalOut += ($value['quantity'] <= 0) ? -$value['quantity']:0;
        }
        
        if ($offset) {
            $query->setFirstResult($offset);
        }
        if ($limit) {
            $query->setMaxResults($limit);
        }

        $result = $query->getResult(2);
        foreach ($result as $key=>$value){
            $result[$key]['rest'] = $this->entityManager->getRepository(Movement::class)
                ->stampRest($goodsId, $value['docType'], $value['docId'], $office);
        }
        
        return new JsonModel([
            'total' => $total,
            'rows' => $result,
            'totalIn' => $totalIn,
            'totalOut' => $totalOut,
        ]);                  
    }

    public function balanceAction()
    {
        
        $goodsId = (int)$this->params()->fromRoute('id', -1);
        
        // Validate input parameter
        if ($goodsId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $goods = $this->entityManager->getRepository(Goods::class)
                ->find($goodsId);

        if ($goods == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        
        
        $params = [
            'q' => $goods->getId(), 
            'accurate' => Goods::SEARCH_ID,            
        ];
        
        $query = $this->entityManager->getRepository(Goods::class)
                        ->presence($params);
        $rests = $query->getResult();

        $result = [];
        foreach ($rests as $rest){
//            var_dump($rest); exit;
            $result[] = [
                'office' => $rest['officeName'].' ('.$rest['companyName'].')',
                'officeId' => $rest['officeId'],
                'rest' => $rest['rest'],
                'reserve' => $rest['reserve'],
                'delivery' => $rest['delivery'],
                'vozvrat' => $rest['vozvrat'],
                'available' => $rest['available'],                    
                'foldCode' => $rest['foldCode'],                    
                'foldName' => $rest['foldName'],                    
            ];
        }
        
        $total = count($result);
        
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
        $status = $this->params()->fromQuery('status');
        $supplier = $this->params()->fromQuery('supplier');
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
                        ->findPrice($goods, ['status' => $status, 'supplier' => $supplier]);

        $total = count($query->getResult(2));
        
        if ($offset) $query->setFirstResult( $offset );
        if ($limit) $query->setMaxResults( $limit );
        
        $result = $query->getResult(2);
        
        return new JsonModel([
            'total' => $total,
            'rows' => $result,
        ]);                  
    }

    public function optsAction()
    {
        
        $goodId = (int)$this->params()->fromRoute('id', -1);        
        
        // Validate input parameter
        if ($goodId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $good = $this->entityManager->getRepository(Goods::class)
                ->find($goodId);

        if ($good == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $rate = $this->entityManager->getRepository(Rate::class)
                ->findGoodRate($good);
        
        $priceCols = $this->goodsManager->priceCols($good);
        
        $result[] = [
            'name' =>  'Минимальная закупка',
            'percent' => '',
            'value' => $good->getFormatMinPrice(),
        ];
        $result[] = [
            'name' =>  'Средняя закупка',
            'percent' => '',
            'value' => $good->getFormatMeanPrice(),
        ];
        $result[] = [
            'name' =>  'Фиксированная цена',
            'percent' => '',
            'value' =>  '<a href="#" class="editable" data-type="text" data-pk="'.$good->getId().'" data-name="fixPrice" data-url="/goods/update-fix-price">'.$good->getFixPrice().'</a>',
        ];
        $result[] = [
            'name' =>  'Цена для торговых площадок',
            'percent' => '',
            'value' =>  '<a href="#" class="editable" data-type="text" data-pk="'.$good->getId().'" data-name="marketPlacePrice" data-url="/goods/update-market-place-price">'.$good->getMarketPlacePrice().'</a>',
        ];
        $result[] = [
            'name' =>  'Расценка',
            'percent' => '',
            'value' => ($rate) ? $rate->getLink():'нет',
        ];
        $result[] = [
            'name' =>  'Розница',
            'percent' => round($priceCols[0]['percent']).'%',
            'value' => $good->getPrice(),
        ];
        $result[] = [
            'name' =>  'VIP',
            'percent' => round($priceCols[1]['percent']).'%',
            'value' => $priceCols[1]['price'],
        ];
        $result[] = [
            'name' =>  'ОПТ2',
            'percent' => round($priceCols[2]['percent']).'%',
            'value' => $priceCols[2]['price'],
        ];
        $result[] = [
            'name' =>  'ОПТ3',
            'percent' => round($priceCols[3]['percent']).'%',
            'value' => $priceCols[3]['price'],
        ];
        $result[] = [
            'name' =>  'ОПТ4',
            'percent' => round($priceCols[4]['percent']).'%',
            'value' => $priceCols[4]['price'],
        ];
        $result[] = [
            'name' =>  'ОПТ5',
            'percent' => round($priceCols[5]['percent']).'%',
            'value' => $priceCols[5]['price'],
        ];
        
        return new JsonModel([
            'total' => count($result),
            'rows' => $result,
        ]);                  
    }

    public function bestNameAction()
    {
        $goodId = (int)$this->params()->fromRoute('id', -1);
        if ($goodId<0) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $good = $this->entityManager->getRepository(\Application\Entity\Goods::class)
                ->findOneById($goodId);        
        
        if ($good == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $bestname = $this->nameManager->findBestName($good);
        
        return new JsonModel([
            'result' => 'ok-reload',
            'message' => $bestname,
        ]);          
    }
    
    public function updateBestNameAction()
    {
        $goodsId = $this->params()->fromRoute('id', -1);
        
        $goods = $this->entityManager->getRepository(Goods::class)
                ->findOneById($goodsId);        
        if ($goods == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $bestname = $this->nameManager->findBestName($goods, true);
        
        // Перенаправляем пользователя на страницу "goods".
        return new JsonModel([
            'result' => 'ok-reload',
        ]);           
    }  

    public function updateGoodTokenAction()
    {
        $goodsId = $this->params()->fromRoute('id', -1);
        
        $goods = $this->entityManager->getRepository(Goods::class)
                ->findOneById($goodsId);        
        if ($goods == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $this->entityManager->getRepository(Goods::class)
                ->updateGoodToken($goods);
        
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

        $data = $this->externalManager->zetasoft('getInfo', ['good' => $goods]);
        if (!$data){
            $data = $this->externalManager->zetasoft('getSimilarInfo', ['good' => $goods]);            
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

        $data = $this->externalManager->zetasoft('vendorCode', ['good' => $goods]);
        
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

        $data = $this->externalManager->zetasoft('getLinked', ['good' => $goods]);
        
        // Перенаправляем пользователя на страницу "goods".
        return new JsonModel([
            'result' => 'ok-reload',
            'message' => $data,
        ]);           
        
    }

    public function updateRestAction()
    {
        $goodsId = $this->params()->fromRoute('id', -1);
        
        $goods = $this->entityManager->getRepository(Goods::class)
                ->find($goodsId); 
        
        if ($goods == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        
        // Перенаправляем пользователя на страницу "goods".
        return new JsonModel([
            'result' => 'ok-reload',
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

        $this->externalManager->addOemsToGood($goods->getId(), $goods->getCode(), $goods->getGenericGroup()->getTdId(), $goods->getTokenGroupId());
        
        // Перенаправляем пользователя на страницу "goods".
        return new JsonModel([
            'result' => 'ok',
        ]);                   
    }

    public function goodOemSupCrossAction()
    {
        $goodsId = $this->params()->fromRoute('id', -1);
        
        $goods = $this->entityManager->getRepository(Goods::class)
                ->findOneById($goodsId);        
        if ($goods == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $this->entityManager->getRepository(Oem::class)
                ->addSupOem($goods->getId());
        $this->entityManager->getRepository(Oem::class)
                ->addCrossOem($goods->getId());    
        
        // Перенаправляем пользователя на страницу "goods".
        return new JsonModel([
            'result' => 'ok',
        ]);                   
    }

    public function goodOemIntersectAction()
    {
        $goodsId = $this->params()->fromRoute('id', -1);
        
        $goods = $this->entityManager->getRepository(Goods::class)
                ->findOneById($goodsId);        
        if ($goods == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $this->entityManager->getRepository(Oem::class)
                        ->addIntersectGood($goods);
        
        // Перенаправляем пользователя на страницу "goods".
        return new JsonModel([
            'result' => 'ok',
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

        //$this->goodsManager->updatePrices($good);
        $this->goodsManager->updatePricesFromGoodSupplier([
            'goodId' => $good->getId(), 
            'meanPrice' => $good->getMeanPrice(), 
            'price' => $good->getPrice(), 
            'fixPrice' => $good->getFixPrice(), 
            'tokenGroupId' => ($good->getTokenGroup()) ? $good->getTokenGroup()->getId():null, 
            'genericGroupId' => ($good->getGenericGroup()) ? $good->getGenericGroup()->getId():null, 
            'producerId' => $good->getProducer()->getId(),
        ]);
        
        // Перенаправляем пользователя на страницу "goods".
        return new JsonModel([
            'result' => 'ok',
        ]);           
                
    }
    
    public function updateReserveAction()
    {
        $goodId = $this->params()->fromRoute('id', -1);
        
        $good = $this->entityManager->getRepository(Goods::class)
                ->find($goodId);   
        
        if ($good == null) {
            $this->getResponse()->setStatusCode(404);
            return;                        
        }        

        $this->entityManager->getRepository(Reserve::class)
                ->updateGoodBalance($good->getId());
        
        // Перенаправляем пользователя на страницу "goods".
        return new JsonModel([
            'result' => 'ok',
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
    
    public function nameEditAction()
    {
        if ($this->getRequest()->isPost()) {
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            $goodId = $data['pk'];
            $good = $this->entityManager->getRepository(Goods::class)
                    ->find($goodId);
                    
            if ($good && $data['value']){
                $this->goodsManager->updateGoodName($good, $data['value']);                    
            }    
        }
        
        exit;
    }  
    
    public function descriptionEditAction()
    {
        if ($this->getRequest()->isPost()) {
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            $goodId = $data['pk'];
            $good = $this->entityManager->getRepository(Goods::class)
                    ->find($goodId);
                    
            if ($good && $data['value']){
                $this->goodsManager->updateGoodDescription($good, $data['value']);                    
            }    
        }
        
        exit;
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
    
    public function attributeToBestNameAction()
    {
        if ($this->getRequest()->isPost()) {
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            $attributeId = $data['pk'];
            $attribute = $this->entityManager->getRepository(\Application\Entity\Attribute::class)
                    ->findOneById($attributeId);
//            var_dump($data); exit;
            $toBestName = ($data['value'] == 'true') ? \Application\Entity\Attribute::TO_BEST_NAME:\Application\Entity\Attribute::NO_BEST_NAME;
                    
            if ($attribute){
                $this->goodsManager->updateAttributeToBestName($attribute, ['toBestName' => $toBestName]);                    
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
    
    public function updateFixPriceAction()
    {
        if ($this->getRequest()->isPost()) {
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            $goodId = $data['pk'];
            $good = $this->entityManager->getRepository(Goods::class)
                    ->find($goodId);
                    
            if ($good){
                $this->rateManager->updateFixPrice($good, (float) $data['value']);                    
            }    
        }
        
        exit;
    }
    
    public function updateMarketPlacePriceAction()
    {
        if ($this->getRequest()->isPost()) {
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            $goodId = $data['pk'];
            $good = $this->entityManager->getRepository(Goods::class)
                    ->find($goodId);
                    
            if ($good){
                $this->goodsManager->updateMarketPlacePrice($good, (float) $data['value']);                    
            }    
        }
        
        return new JsonModel(['ok']);          
    }

    public function updateInStoreAction()
    {
        if ($this->getRequest()->isPost()) {
            // Получаем POST-данные.
            $data = $this->params()->fromPost();
            $goodId = $data['pk'];
            $good = $this->entityManager->getRepository(Goods::class)
                    ->find($goodId);
                    
            if ($good){
                $this->goodsManager->updateInStore($good, $data['value']);                    
            }    
        }
        
        return new JsonModel(['ok']);          
    }

    public function restAction()
    {
        $goodId = (int)$this->params()->fromRoute('id', -1);
        $dateOper = $this->params()->fromQuery('dateOper', date('Y-m-d'));
        $officeId = $this->params()->fromQuery('office', $this->logManager->currentUser()->getOffice()->getId());
        $companyId = $this->params()->fromQuery('company');
        $rest = 0;

        if ($goodId<0) {
            goto e;
        }
        
        $rest = $this->entityManager->getRepository(Movement::class)
                ->goodRest($goodId, $dateOper, $officeId, $companyId);
        
        e:        
        return new JsonModel([
            'id' => $goodId,
            'rest' => $rest,
        ]);          
    }    

    public function reserveShowAction()
    {
        $goodId = (int)$this->params()->fromRoute('id', -1);
        $status = (int)$this->params()->fromQuery('status', Reserve::STATUS_RESERVE);

        $result = [];
        
        if ($goodId<0) {
            goto e;
        }
        
        $reserves = $this->entityManager->getRepository(Reserve::class)
                ->findBy(['good' => $goodId, 'status' => $status]);
        foreach ($reserves as $reserve){
            $doc = $this->entityManager->getRepository(Movement::class)
                    ->docFromLogKey($reserve->getDocKey());
            $result[] = [
                'rest' => $reserve->getRest(),
                'link' => $doc->getOpenLink(),
            ];
        }
        
        e:        
        return new JsonModel(
            $result
        );          
    }    
}
