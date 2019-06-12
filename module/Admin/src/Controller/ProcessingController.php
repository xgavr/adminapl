<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Application\Entity\PriceGetting;
use Zend\Stdlib\RequestInterface as Request;
use Zend\Stdlib\ResponseInterface as Response;
use Zend\Mvc\MvcEvent;


class ProcessingController extends AbstractActionController
{
    
    /**
     * Менеджер сущностей.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * PostManager manager.
     * @var \Admin\Service\PostManager
     */
    private $postManager;    
    
    /**
     * AutoruManager manager.
     * @var \Admin\Service\AutoruManager
     */
    private $autoruManager;    
    
    /**
     * TelegramManager manager.
     * @var \Admin\Service\TelegrammManager
     */
    private $telegramManager;    
    
    /**
     * AplService manager.
     * @var \Admin\Service\AplService
     */
    private $aplService;    

    /**
     * AplBankService manager.
     * @var \Admin\Service\AplBankService
     */
    private $aplBankService;    

    /**
     * PriceManager manager.
     * @var \Application\Service\PriceManager
     */
    private $priceManager;    

    /**
     * RawManager manager.
     * @var \Application\Service\RawManager
     */
    private $rawManager;    

    /**
     * SupplierManager manager.
     * @var \Application\Service\SupplierManager
     */
    private $supplierManager;    

    /**
     * AdminManager manager.
     * @var \Admin\Service\AdminManager
     */
    private $adminManager;    

    /**
     * AdminManager manager.
     * @var \Application\Service\ParseManager
     */
    private $parseManager;    

    /**
     * BankManager manager.
     * @var \Bank\Service\BankManager
     */
    private $bankManager;    

    /**
     * ProducerManager manager.
     * @var \Application\Service\ProducerManager
     */
    private $producerManager;    

    /**
     * ArticleManager manager.
     * @var \Application\Service\ArticleManager
     */
    private $articleManager;    

    /**
     * OemManager manager.
     * @var \Application\Service\OemManager
     */
    private $oemManager;    

    /**
     * NameManager manager.
     * @var \Application\Service\NameManager
     */
    private $nameManager;    

    /**
     * AssemblyManager manager.
     * @var \Application\Service\AssemblyManager
     */
    private $assemblyManager;    

    /**
     * SettingManager manager.
     * @var \Admin\Service\SettingManager
     */
    private $settingManager;    

    /**
     * GoodsManager manager.
     * @var \Application\Service\GoodsManager
     */
    private $goodsManager;    

    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $postManager, $autoruManager, $telegramManager, 
            $aplService, $priceManager, $rawManager, $supplierManager, $adminManager,
            $parseManager, $bankManager, $aplBankService, $producerManager, $articleManager,
            $oemManager, $nameManager, $assemblyManager, $goodsManager, $settingManager) 
    {
        $this->entityManager = $entityManager;
        $this->postManager = $postManager;        
        $this->autoruManager = $autoruManager;
        $this->telegramManager = $telegramManager;
        $this->aplService = $aplService;
        $this->aplBankService = $aplBankService;
        $this->priceManager = $priceManager;
        $this->rawManager = $rawManager;
        $this->supplierManager = $supplierManager;
        $this->adminManager = $adminManager;
        $this->parseManager = $parseManager;
        $this->bankManager = $bankManager;
        $this->producerManager = $producerManager;
        $this->articleManager = $articleManager;
        $this->oemManager = $oemManager;
        $this->nameManager = $nameManager;
        $this->assemblyManager = $assemblyManager;
        $this->goodsManager = $goodsManager;
        $this->settingManager = $settingManager;
    }   

    public function dispatch(Request $request, Response $response = null)
    {
        $controllerName = $this->params('controller');
        $actionName = str_replace('-', '', lcfirst(ucwords($this->params('action'), '-')));

        if ($this->settingManager->canStart($controllerName, $actionName)){
            $this->settingManager->addProcess($controllerName, $actionName);
        } else {    
            exit;
        }
        return parent::dispatch($request, $response);
    }    

    public function indexAction()
    {
        set_time_limit(180);
        $this->autoruManager->postOrder();
        
        return new JsonModel(
            ['ok']
        );        
    }
    
    public function testAction()
    {
        sleep(10);
        return new ViewModel([
        ]);       
    }
    
    /**
     * Сообщения в телеграм
     * $post api_key, chat_id, text
     */
    public function telegramAction()
    {
        $data = [];
        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
            $this->aplService->sendTelegramMessage($data);
        }    
        
        return new JsonModel(
            $data
        );
    }
    
    public function telegramAutoCheckProxyAction()
    {
        $settings = $this->adminManager->getTelegramSettings();
        
        if ($settings['auto_check_proxy'] == 1){
            $this->telegramManager->checkEndChangeProxy();
        }    
        
        return new JsonModel(
            ['ok']
        );        
    }
    
    public function telegramPostponeAction()
    {
        $settings = $this->adminManager->getTelegramSettings();
        
        if ($settings['send_pospone_msg'] == 1){
            $this->telegramManager->sendPostponeMessage();
        }    
        
        return new JsonModel(
            ['ok']
        );
        
    }
    
    /*
     * Скачать все прайсы по ссылкам
     */
    public function pricesByLinkAction()
    {
        $settings = $this->adminManager->getPriceSettings();

        if ($settings['receiving_link'] == 1){
            $priceGettings = $this->entityManager->getRepository(PriceGetting::class)
                    ->findBy(['status' => PriceGetting::STATUS_ACTIVE]);

            foreach ($priceGettings as $priceGetting){
                $this->priceManager->getPriceByLink($priceGetting);
            }
        }    
        
        return new JsonModel(
            ['ok']
        );
        
    }
    
    /*
     * Чтение почтовых ящиков для прайсов
     */
    public function pricesByMailAction()
    {
        set_time_limit(180);
        
        $settings = $this->adminManager->getPriceSettings();

        if ($settings['receiving_mail'] == 1){
            $this->priceManager->readQueyeMailBox();
        }    
        
        return new JsonModel(
            ['ok']
        );
    }
    
    /*
     * Загрузка прайсов в базу
     */
    public function rawPricesAction()
    {
        set_time_limit(0);

        $settings = $this->adminManager->getPriceSettings();
        
        if ($settings['upload_raw'] == 1){

            $files = $this->supplierManager->getPriceFilesToUpload();
            if (count($files)){
                $this->rawManager->checkSupplierPrice($files[0]['priceGetting']->getSupplier());
            }                        
        }    
        
        return new JsonModel(
            ['ok']
        );
    }
    
    /*
     * Разборка прайсов
     */
    public function parseRawAction()
    {        
        $settings = $this->adminManager->getPriceSettings();

        if ($settings['parse_raw'] == 1){
            $this->parseManager->parseRaw();
        }    
        
        return new JsonModel(
            ['ok']
        );
    }
    
    /**
     * Удаление старых прайсов
     */
    public function deleteOldPricesAction()
    {
        
        $raws = $this->entityManager->getRepository(\Application\Entity\Raw::class)
                ->findRawForRemove();
        
        foreach ($raws as $raw){
            $this->rawManager->removeRaw($raw);
        }
        
        return new JsonModel(
            ['ok']
        );
    }
    
    /**
     * Обновление выписки банка Точка ро api
     */
    public function statementUpdateAction()
    {
        $settings = $this->adminManager->getBankTransferSettings();

        if ($settings['statement_by_api'] == 1){
        
            $result = $this->bankManager->tochkaStatement(date('Y-m-d', strtotime("-1 days")), date('Y-m-d'));

            $ok = 'ok-reload';
            $message = '';
            if ($result !== true){
                $message = 'Потерян доступ к банку Точка для обновления выписки'.PHP_EOL;
                $message .= $result.PHP_EOL;
                $message .= 'Проверить доступ к api:'.PHP_EOL.'http://adminapl.ru/bankapi/tochka-access';

//                $this->telegramManager->sendMessage(['text' => $message]);
                $this->telegramManager->addPostponeMesage([
                    'text' => $message,
                ]);

                $ok = 'error';
            } else {
                //$this->aplBankService->sendBankStatement(); //трансфер выписки в АПЛ
            }
        }    
        
        return new JsonModel([
            'result' => $ok,
            'message' => $message,
        ]);          
        
    }
    
    /**
     * Получение выписки по почте
     */
    public function statementFromPostAction()
    {
        $settings = $this->adminManager->getBankTransferSettings();

        if ($settings['statement_by_file'] == 1){
            $this->bankManager->getStatementsByEmail(); //проверить почту
            $this->bankManager->checkStatementFolder();//проверить папку с файлами
            $this->aplBankService->sendBankStatement(); //трансфер выписки в АПЛ
        }
        return new JsonModel(
            ['ok']
        );
    }
    
    
    /**
     * Обновление неизвестных производителей из прайса
     */
    public function unknownProducerFromRawpriceAction()
    {
        $settings = $this->adminManager->getPriceSettings();

        if ($settings['parse_producer'] == 1){
            
            $raw = $this->entityManager->getRepository(\Application\Entity\Raw::class)
                    ->findOneBy(['status' => \Application\Entity\Raw::STATUS_PARSED, 'parseStage' => \Application\Entity\Raw::STAGE_NOT]);
            
            if ($raw){
                $this->producerManager->grabUnknownProducerFromRaw($raw);
            }    
        }    
                
        return new JsonModel(
            ['ok']
        );
        
    }
    
    /**
     * Обновление количестка товаров у неизвестного производителя
     */
    public function unknownProducerRawpriceCountAction()
    {
        set_time_limit(900);
        
        $settings = $this->adminManager->getPriceSettings();

        if ($settings['parse_producer'] == 1){
            
            $unknownProducers = $this->entityManager->getRepository(\Application\Entity\UnknownProducer::class)
                    ->findBy([]);
            
            foreach ($unknownProducers as $unknownProducer){
                $this->producerManager->updateUnknownProducerRawpriceCount($unknownProducer, false);
            }   
            $this->entityManager->flush();
        }    
                
        return new JsonModel(
            ['ok']
        );        
    }

    /**
     * Обновление количестка поставщиков у неизвестного производителя
     */
    public function unknownProducerSupplierCountAction()
    {
        set_time_limit(900);
        
        $settings = $this->adminManager->getPriceSettings();

        if ($settings['parse_producer'] == 1){
            
            $unknownProducers = $this->entityManager->getRepository(\Application\Entity\UnknownProducer::class)
                    ->findBy([]);
            
            foreach ($unknownProducers as $unknownProducer){
                $this->producerManager->updateUnknownProducerSupplierCount($unknownProducer, false);
            }   
            $this->entityManager->flush();
        }    
                
        return new JsonModel(
            ['ok']
        );        
    }

    /**
     * Удаление пустых неизвестных производителей
     */
    public function deleteUnknownProducerAction()
    {
        $settings = $this->adminManager->getPriceSettings();

        if ($settings['parse_producer'] == 1){
            $this->producerManager->removeEmptyUnknownProducer();
        }    
                
        return new JsonModel(
            ['ok']
        );
        
    }

    /**
     * Обновление артикулов из прайса
     */
    public function articleFromRawpriceAction()
    {
        
        $settings = $this->adminManager->getPriceSettings();

        if ($settings['parse_article'] == 1){
            
            $raw = $this->entityManager->getRepository(\Application\Entity\Raw::class)
                    ->findOneBy(['status' => \Application\Entity\Raw::STATUS_PARSED, 'parseStage' => \Application\Entity\Raw::STAGE_PRODUCER_PARSED]);
            
            if ($raw){
                $this->articleManager->grabArticleFromRaw($raw);
            }    
        }    
                
        return new JsonModel(
            ['ok']
        );
        
    }

    /**
     * Удаление пустых артикулов производителей
     */
    public function deleteArticleAction()
    {
        $settings = $this->adminManager->getPriceSettings();

        if ($settings['parse_article'] == 1){
            $this->articleManager->removeEmptyArticles();
        }    
                
        return new JsonModel(
            ['ok']
        );
        
    }

    /**
     * Обновление номеров из прайса
     */
    public function oemFromRawpriceAction()
    {
        
        $settings = $this->adminManager->getPriceSettings();

        if ($settings['parse_oem'] == 1){
            
            $raw = $this->entityManager->getRepository(\Application\Entity\Raw::class)
                    ->findOneBy(['status' => \Application\Entity\Raw::STATUS_PARSED, 'parseStage' => \Application\Entity\Raw::STAGE_ARTICLE_PARSED]);
            
            if ($raw){
                $this->oemManager->grabOemFromRaw($raw);
            }    
        }    
                
        return new JsonModel(
            ['ok']
        );
        
    }

    /**
     * Удаление пустых номеров производителей
     */
    public function deleteOemAction()
    {
        $settings = $this->adminManager->getPriceSettings();

        if ($settings['parse_oem'] == 1){
            $this->oemManager->removeEmpty();
        }    
                
        return new JsonModel(
            ['ok']
        );
        
    }
    
    /**
     * Обновление токенов из прайса
     */
    public function tokenFromRawpriceAction()
    {
        $settings = $this->adminManager->getPriceSettings();

        if ($settings['parse_name'] == 1){
            
            $raw = $this->entityManager->getRepository(\Application\Entity\Raw::class)
                    ->findOneBy(['status' => \Application\Entity\Raw::STATUS_PARSED, 'parseStage' => \Application\Entity\Raw::STAGE_OEM_PARSED]);
            
            if ($raw){
                $this->nameManager->grabTokenFromRaw($raw);
            }    
        }    
                
        return new JsonModel(
            ['ok']
        );
        
    }

    /**
     * Удаление пустых номеров производителей
     */
    public function deleteTokenAction()
    {
        $settings = $this->adminManager->getPriceSettings();

        if ($settings['parse_name'] == 1){
            $this->nameManager->updateAllTokenArticleCount();
            $this->nameManager->removeEmptyToken();
        }    
                
        return new JsonModel(
            ['ok']
        );
        
    }
    
    /**
     * Обовление пересечения производителей
     * 
     * @return JsonModel
     */
    public function unknownProducerIntersectAction()
    {
        $settings = $this->adminManager->getPriceSettings();

        if ($settings['assembly_producer'] == 1){
            
            $this->producerManager->updateUnknownProducerIntersect();
        }    
                
        return new JsonModel(
            ['ok']
        );
                
    }
    
    /**
     * Обновление производителей из неизвестных производителей
     */
    public function producerFromUnknownProducerAction()
    {
        $settings = $this->adminManager->getPriceSettings();

        if ($settings['assembly_producer'] == 1){
            
            $raw = $this->entityManager->getRepository(\Application\Entity\Raw::class)
                    ->findOneBy(['status' => \Application\Entity\Raw::STATUS_PARSED, 'parseStage' => \Application\Entity\Raw::STAGE_TOKEN_PARSED]);
            
            if ($raw){
                $this->assemblyManager->assemblyProducerFromRaw($raw);
            }    
        }    
                
        return new JsonModel(
            ['ok']
        );
        
    }

    /**
     * Обновление наименований производителей
     */
    public function producerBestNameAction()
    {
        $settings = $this->adminManager->getPriceSettings();

        if ($settings['assembly_producer'] == 1){
            
            $producers = $this->entityManager->getRepository(\Application\Entity\Producer::class)
                    ->findBy([]);
            foreach ($producers as $producer){
                $this->producerManager->bestName($producer);
            }    
        }    
                
        return new JsonModel(
            ['ok']
        );
        
    }

    /**
     * Удаление пустых производителей
     */
    public function deleteProducerAction()
    {
        $settings = $this->adminManager->getPriceSettings();

        if ($settings['assembly_producer'] == 1){
            $this->producerManager->removeEmptyProducer();
        }    
                
        return new JsonModel(
            ['ok']
        );
        
    }
    
    /**
     * Обновление товаров из прайса
     */
    public function goodFromRawAction()
    {
        $settings = $this->adminManager->getPriceSettings();

        if ($settings['assembly_good'] == 1){
            
            $raw = $this->entityManager->getRepository(\Application\Entity\Raw::class)
                    ->findOneBy(['status' => \Application\Entity\Raw::STATUS_PARSED, 'parseStage' => \Application\Entity\Raw::STAGE_PRODUCER_ASSEMBLY]);
            
            if ($raw){
                $this->assemblyManager->assemblyGoodFromRaw($raw);
            }    
        }    
                
        return new JsonModel(
            ['ok']
        );
        
    }    
    
    /**
     * Удаление пустых товаров
     */
    public function deleteGoodsAction()
    {
        $settings = $this->adminManager->getPriceSettings();

        if ($settings['assembly_good'] == 1){
            $this->goodsManager->removeEmpty();
        }    
                
        return new JsonModel(
            ['ok']
        );
        
    }
    
    /**
     * Обновление цен товаров из прайса
     */
    public function updateGoodPriceRawAction()
    {
        $settings = $this->adminManager->getPriceSettings();

        if ($settings['update_good_price'] == 1){
            
            $raw = $this->entityManager->getRepository(\Application\Entity\Raw::class)
                    ->findOneBy(['status' => \Application\Entity\Raw::STATUS_PARSED, 'parseStage' => \Application\Entity\Raw::STAGE_GOOD_ASSEMBLY]);
            
            if ($raw){
                $this->goodsManager->updatePricesRaw($raw);
            }    
        }    
                
        return new JsonModel(
            ['ok']
        );
        
    }    
    
    
    /**
     * Обновление AplId производителей
     * 
     * @return JsonModel
     */
    public function updateProducerAplIdAction()
    {
        
        $settings = $this->adminManager->getAplExchangeSettings();

        if ($settings['get_producer_id'] == 1){
            $this->aplService->updateProducersAplId();
        }    
        
        return new JsonModel([
            ['ok']
        ]);
    }    
    
    /**
     * Обновление AplId товаров
     * 
     * @return JsonModel
     */
    public function updateGoodAplIdAction()
    {
        
        $settings = $this->adminManager->getAplExchangeSettings();

        if ($settings['get_good_id'] == 1){
            
            $this->aplService->updateGoodAplId();

        }    
        
        return new JsonModel([
            ['ok']
        ]);
    }    
    
    /**
     * Обновление AplId товаров
     * 
     * @return JsonModel
     */
    public function updateGoodRawpriceAction()
    {
        $settings = $this->adminManager->getAplExchangeSettings();
        
        if ($settings['rawprice'] == 1){
            
            $this->aplService->updateGoodsRawprice();
            
        }    
        
        return new JsonModel([
            ['ok']
        ]);
    }    
    
    /**
     * Обновление номеров товаров
     * 
     * @return JsonModel
     */
    public function updateGoodOemAction()
    {
        $settings = $this->adminManager->getAplExchangeSettings();
        
        if ($settings['oem'] == 1){            
            $this->aplService->updateGoodsOem();            
        }    
        
        return new JsonModel([
            ['ok']
        ]);
    }    

    /**
     * Обновление картинок товаров
     * 
     * @return JsonModel
     */
    public function updateGoodImgAction()
    {
        $settings = $this->adminManager->getAplExchangeSettings();
        
        if ($settings['image'] == 1){            
            $this->aplService->updateGoodsImg();            
        }    
        
        return new JsonModel([
            ['ok']
        ]);
    }    

    /**
     * Выгрузка эквайринга из апл
     * 
     * @return JsonModel
     */
    public function updateAplAcquiringAction()
    {
        
        $settings = $this->adminManager->getAplExchangeSettings();

        if ($settings['get_acquiring'] == 1){
            $this->aplService->updateAcquiringPayments();
        }    
        
        return new JsonModel([
            ['ok']
        ]);
    }    
    
    /**
     * Обновление групп наименований из прайса
     */
    public function tokenGroupFromRawpriceAction()
    {
        $settings = $this->adminManager->getPriceSettings();

        if ($settings['assembly_group_name'] == 1){
            
            $raw = $this->entityManager->getRepository(\Application\Entity\Raw::class)
                    ->findOneBy(['status' => \Application\Entity\Raw::STATUS_PARSED, 'parseStage' => \Application\Entity\Raw::STAGE_PRICE_UPDATET]);
            
            if ($raw){
                $this->nameManager->grabTokenGroupFromRaw($raw);
            }    
        }    
                
        return new JsonModel(
            ['ok']
        );
        
    }

    /**
     * Удаление пустых групп наименований производителей
     */
    public function deleteTokenGroupAction()
    {
        set_time_limit(900);
        
        $settings = $this->adminManager->getPriceSettings();

        if ($settings['assembly_group_name'] == 1){

            $this->nameManager->updateAllTokenGroupGoodCount();
            
            $this->nameManager->removeEmptyTokenGroup();
        }    
                
        return new JsonModel(
            ['ok']
        );
        
    }
    
    public function tdUpdateCarsAction()
    {
        $settings = $this->adminManager->getTdExchangeSettings();

        if ($settings['update_car'] == 1){

            $this->goodsManager->updateCars();            
        }    
                
        return new JsonModel(
            ['ok']
        );
        
    }
    
    public function updateCarStatusAction()
    {
        $settings = $this->adminManager->getTdExchangeSettings();

        if ($settings['update_car'] == 1){

            $this->entityManager->getRepository(\Application\Entity\Car::class)
                    ->updateAllCarStatus();

            $this->entityManager->getRepository(\Application\Entity\Car::class)
                    ->updateAllModelStatus();

            $this->entityManager->getRepository(\Application\Entity\Car::class)
                    ->updateAllMakeStatus();
            
        }    
                
        return new JsonModel(
            ['ok']
        );
        
    }
    
    public function updateGoodCarCountAction()
    {
        $settings = $this->adminManager->getTdExchangeSettings();

        if ($settings['update_car'] == 1){

            $this->entityManager->getRepository(\Application\Entity\Goods::class)
                    ->updateGoodCarCount();
            
        }    
                
        return new JsonModel(
            ['ok']
        );
        
    }
    
    public function tdUpdateOemAction()
    {
        $settings = $this->adminManager->getTdExchangeSettings();

        if ($settings['update_oe'] == 1){

            $this->goodsManager->updateOemTd();            
        }    
                
        return new JsonModel(
            ['ok']
        );
        
    }
    
    public function tdUpdateGroupAction()
    {
        $settings = $this->adminManager->getTdExchangeSettings();

        if ($settings['update_group'] == 1){

            $this->goodsManager->updateGroupTd();            
        }    
                
        return new JsonModel(
            ['ok']
        );
        
    }
    
    public function updateGroupGoodCountAction()
    {
        $settings = $this->adminManager->getTdExchangeSettings();

        if ($settings['update_group'] == 1){

            $this->entityManager->getRepository(\Application\Entity\GenericGroup::class)
                    ->updateGoodCount();
            
        }    
                
        return new JsonModel(
            ['ok']
        );
        
    }
    
    public function tdUpdateAttributeAction()
    {
        $settings = $this->adminManager->getTdExchangeSettings();

        if ($settings['update_description'] == 1){

            $this->goodsManager->updateDescriptionTd();            
        }    
                
        return new JsonModel(
            ['ok']
        );
        
    }
    
    
    public function tdUpdateImagesAction()
    {
        $settings = $this->adminManager->getTdExchangeSettings();

        if ($settings['update_image'] == 1){

            $this->goodsManager->updateImageTd();            
        }    
                
        return new JsonModel(
            ['ok']
        );
        
    }
    
}
