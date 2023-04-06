<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Laminas\Stdlib\RequestInterface as Request;
use Laminas\Stdlib\ResponseInterface as Response;
use Application\Entity\FpTree;
use Application\Entity\TokenGroupToken;
use Application\Entity\TokenGroupBigram;


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
     * AplDocService manager.
     * @var \Admin\Service\AplDocService
     */
    private $aplDocService;    

    /**
     * AplOrderService manager.
     * @var \Admin\Service\AplOrderService
     */
    private $aplOrderService;    

    /**
     * AplBankService manager.
     * @var \Admin\Service\AplBankService
     */
    private $aplBankService;    

    /**
     * AplCashService manager.
     * @var \Admin\Service\AplCashService
     */
    private $aplCashService;    

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

    /**
     * MarketManager manager.
     * @var \Application\Service\MarketManager
     */
    private $marketManager;    

    /**
     * CarManager manager.
     * @var \Application\Service\CarManager
     */
    private $carManager;    

    /**
     * Hello manager.
     * @var \Admin\Service\HelloManager
     */
    private $helloManager;    

    /**
     * Bill manager.
     * @var \Application\Service\BillManager
     */
    private $billManager;    

    /**
     * Register manager.
     * @var \Stock\Service\RegisterManager
     */
    private $registerManager;    

    /**
     * Pt manager.
     * @var \Stock\Service\PtManager
     */
    private $ptManager;    

    /**
     * Job manager.
     * @var \Admin\Service\JobManager
     */
    private $jobManager;    

    /**
     * Ozon manager.
     * @var \ApiMarketPlace\Service\OzonService
     */
    private $ozonManager;    

    /**
     * User manager.
     * @var \User\Service\UserManager
     */
    private $userManager;    

    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $postManager, $autoruManager, $telegramManager, 
            $aplService, $priceManager, $rawManager, $supplierManager, $adminManager,
            $parseManager, $bankManager, $aplBankService, $producerManager, $articleManager,
            $oemManager, $nameManager, $assemblyManager, $goodsManager, $settingManager,
            $aplDocService, $marketManager, $carManager, $helloManager, $aplOrderService,
            $aplCashService, $billManager, $registerManager, $ptManager, $jobManager, 
            $ozonService, $userManager) 
    {
        $this->entityManager = $entityManager;
        $this->postManager = $postManager;        
        $this->autoruManager = $autoruManager;
        $this->telegramManager = $telegramManager;
        $this->aplService = $aplService;
        $this->aplBankService = $aplBankService;
        $this->aplDocService = $aplDocService;
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
        $this->marketManager = $marketManager;
        $this->carManager = $carManager;
        $this->helloManager = $helloManager;
        $this->aplOrderService = $aplOrderService;
        $this->aplCashService = $aplCashService;
        $this->billManager = $billManager;
        $this->registerManager = $registerManager;
        $this->ptManager = $ptManager;
        $this->jobManager = $jobManager;
        $this->ozonManager = $ozonService;
        $this->userManager = $userManager;
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
        $settings = $this->adminManager->getSettings();
        
        if ($settings['hello_check'] == 1){
            //$this->autoruManager->postOrder();
        }    
        
        return new JsonModel(
            ['ok']
        );        
    }
    
    public function helloAction()
    {       
        $settings = $this->adminManager->getSettings();
        
        if ($settings['hello_check'] == 1){
            $this->billManager->billsByMail();
            $this->helloManager->checkingMail();
        }    
        return new JsonModel(
            ['ok']
        );        
    }
    
    public function varactAction()
    {       
        $settings = $this->adminManager->getSettings();
        
        if ($settings['doc_actualize'] == 1){
            $this->registerManager->actualize();
        }    
        return new JsonModel(
            ['oke']
        );        
    }
    
    public function idocsAction()
    {       
        $settings = $this->adminManager->getSettings();
        
        if ($settings['hello_check'] == 1){
            $this->billManager->tryIdocs();
        }    
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
            $this->priceManager->getPricesByLink();
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
    
    /**
     * Обновить сумму поставок поставщиков
     */
    public function updateSupplierAmountAction()
    {        
        $settings = $this->adminManager->getPriceSettings();

        if ($settings['parse_raw'] == 1){
            $this->supplierManager->updateAmounts();
        }    
        
        return new JsonModel(
            ['ok']
        );
    }

    /**
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
        $settings = $this->adminManager->getPriceSettings();
        if ($settings['upload_raw'] == 1){
            $this->rawManager->removeOldRaws();
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

        $ok = 'ok-reload';
        $message = null;
        if ($settings['statement_by_api'] == 1){
        
            $this->bankManager->tochkaStatementV2(date('Y-m-d', strtotime("-1 days")), date('Y-m-d'));
            
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
     * Обновление количестка товаров у неизвестного производителя
     */
    public function unknownProducerRawpriceCountAction()
    {
        $settings = $this->adminManager->getPriceSettings();

        if ($settings['parse_producer'] == 1){
            $this->producerManager->unknownProducerRawpriceCount();            
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
        $settings = $this->adminManager->getPriceSettings();

        if ($settings['parse_producer'] == 1){
            $this->producerManager->unknownProducerSupplierCount();                        
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
     * Не работает
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
     * Удаление пустых токенов
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
     * Заполнение дерева токенов
     */
    public function fillFpTreeAction()
    {
        $settings = $this->adminManager->getPriceSettings();

        if ($settings['parse_name'] == 1){
            $this->entityManager->getRepository(FpTree::class)
                    ->fillFromArticles();
        }    
                
        return new JsonModel(
            ['ok']
        );        
    }
    
    /**
     * Пересчет поддержки дерева токенов
     */
    public function supportFpTreeAction()
    {
        $settings = $this->adminManager->getPriceSettings();

        if ($settings['parse_name'] == 1){
            $this->entityManager->getRepository(FpTree::class)
                    ->updateSupportCount();
            $this->entityManager->getRepository(FpTree::class)
                    ->deleteEmpty();
        }    
                
        return new JsonModel(
            ['ok']
        );        
    }
    
    /**
     * Заполнение токенов групп наименований
     * ОТКЛЮЧЕНО
     */
    public function fillTokenGroupTokenAction()
    {
        $settings = $this->adminManager->getPriceSettings();

        if ($settings['parse_name'] == 10){
            $this->entityManager->getRepository(TokenGroupToken::class)
                    ->fillTokenGroupToken(); 
        }    
                
        return new JsonModel(
            ['ok']
        );        
    }
    
    /**
     * Заполнение биграм групп наименований
     * ОТКЛЮЧЕНО
     */
    public function fillTokenGroupBigramAction()
    {
        $settings = $this->adminManager->getPriceSettings();

        if ($settings['parse_name'] == 10){
            $this->entityManager->getRepository(TokenGroupBigram::class)
                    ->fillTokenGroupBigram(); 
        }    
                
        return new JsonModel(
            ['ok']
        );        
    }
    
    /**
     * Удаление пустых биграм
     */
    public function deleteBigramAction()
    {
        $settings = $this->adminManager->getPriceSettings();

        if ($settings['parse_name'] == 1){
            if ($this->nameManager->updateAllBigramArticleCount()){
                $this->nameManager->removeEmptyBigram();
            }    
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
     * 1 -> 2
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
     * 2 -> 3
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
     * 3 -> 4
     * Обновление производителей из неизвестных производителей
     */
    public function producerFromUnknownProducerAction()
    {
        $settings = $this->adminManager->getPriceSettings();

        if ($settings['assembly_producer'] == 1){
            
            $raw = $this->entityManager->getRepository(\Application\Entity\Raw::class)
                    ->findOneBy(['status' => \Application\Entity\Raw::STATUS_PARSED, 'parseStage' => \Application\Entity\Raw::STAGE_ARTICLE_PARSED]);
            
            if ($raw){
                $this->assemblyManager->assemblyProducerFromRaw($raw);
            }    
        }    
                
        return new JsonModel(
            ['ok']
        );
        
    }

    /**
     * 4 -> 5
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
     * 5 -> 6
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
     * 6 -> 7
     * Обновление номеров из прайса
     */
    public function oemFromRawpriceAction()
    {
        
        $settings = $this->adminManager->getPriceSettings();

        if ($settings['parse_oem'] == 1){
            
            $raw = $this->entityManager->getRepository(\Application\Entity\Raw::class)
                    ->findOneBy(['status' => \Application\Entity\Raw::STATUS_PARSED, 'parseStage' => \Application\Entity\Raw::STAGE_PRICE_UPDATET]);
            
            if ($raw){
                $this->oemManager->grabOemFromRaw($raw);
            }    
        }    
                
        return new JsonModel(
            ['ok']
        );        
    }
    
    /**
     * 7 -> 8
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
     * 8 -> 10
     * Обновление групп наименований из прайса
     */
    public function tokenGroupFromRawpriceAction()
    {
        $settings = $this->adminManager->getPriceSettings();

        if ($settings['assembly_group_name'] == 1){
            
            $raw = $this->entityManager->getRepository(\Application\Entity\Raw::class)
                    ->findOneBy(['status' => \Application\Entity\Raw::STATUS_PARSED, 'parseStage' => \Application\Entity\Raw::STAGE_TOKEN_PARSED]);
            
            if ($raw){
                $this->nameManager->grabTokenGroupFromRaw($raw);
            }    
        }    
                
        return new JsonModel(
            ['ok']
        );
        
    }

    /**
     * 10 -> 11
     * Обновление описания товаров из прайса
     */
    public function updateDescriptionAction()
    {
        $settings = $this->adminManager->getPriceSettings();

        if ($settings['update_good_name'] == 1){
            
            $raw = $this->entityManager->getRepository(\Application\Entity\Raw::class)
                    ->findOneBy(['status' => \Application\Entity\Raw::STATUS_PARSED, 'parseStage' => \Application\Entity\Raw::STAGE_TOKEN_GROUP_PARSED]);
            
            if ($raw){
                $this->nameManager->descriptionFromRaw($raw);
            }    
        }    
                
        return new JsonModel(
            ['ok']
        );        
    }    
    
    /**
     * 11 -> 12
     * Обновление наименований товаров из прайса
     */
    public function updateBestNameAction()
    {
        $settings = $this->adminManager->getPriceSettings();

        if ($settings['update_good_name'] == 1){
            
            $raw = $this->entityManager->getRepository(\Application\Entity\Raw::class)
                    ->findOneBy(['status' => \Application\Entity\Raw::STATUS_PARSED, 'parseStage' => \Application\Entity\Raw::STAGE_DESCRIPTION]);
            
            if ($raw){
                $this->nameManager->bestNameFromRaw($raw);
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
     * Обновление количества товаров и движений производителей
     */
    public function updateProducersGoodCountAction()
    {
        $settings = $this->adminManager->getPriceSettings();

        if ($settings['assembly_producer'] == 1){
            $this->producerManager->updateProducersGoodCount();
            $this->producerManager->updateProducersMovement();
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
     * Обновление группы Apl товаров
     * 
     * @return JsonModel
     */
    public function updateGroupAplIdAction()
    {
        
        $settings = $this->adminManager->getAplExchangeSettings();

        if ($settings['get_group_apl'] == 1){
            
//            $this->aplService->updateGroupAplId();

        }    
        
        return new JsonModel([
            ['ok']
        ]);
    }    
    
    /**
     * Обновление наименований Apl товаров
     * 
     * @return JsonModel
     */
    public function updateGoodNamesAction()
    {
        
        $settings = $this->adminManager->getAplExchangeSettings();

        if ($settings['good_name'] == 1){
            
            $this->aplService->updateGoodNames();

        }    
        
        return new JsonModel([
            ['ok']
        ]);
    }    
    
    /**
     * Обновление цен Apl товаров
     * 
     * @return JsonModel
     */
    public function updateGoodPricesAction()
    {
        
        $settings = $this->adminManager->getAplExchangeSettings();

        if ($settings['good_price'] == 1){
            
            $this->aplService->updateGoodPrices();

        }    
        
        return new JsonModel([
            ['ok']
        ]);
    }    
    
    /**
     * Обновление группы Apl общих групп
     * 
     * @return JsonModel
     */
    public function updateGenericGroupAplIdAction()
    {
        
        $settings = $this->adminManager->getAplExchangeSettings();

        if ($settings['get_group_apl'] == 1){
            
            $groups = $this->entityManager->getRepository(\Application\Entity\GenericGroup::class)
                    ->findBy(['status' => \Application\Entity\GenericGroup::STATUS_ACTIVE]);
            foreach ($groups as $group){
                if ($group->getTdId()){
                    $this->entityManager->getRepository(\Application\Entity\GenericGroup::class)
                            ->updateGroupApl($group);
                }    
            }
        }    
        
        return new JsonModel([
            ['ok']
        ]);
    }    
    
    /**
     * НЕИСПОЛЬЗУЕТСЯ
     * Обновление строк прайсов товаров
     * 
     * @return JsonModel
     */
    public function updateGoodRawpriceAction()
    {
        $settings = $this->adminManager->getAplExchangeSettings();
        
        if ($settings['rawprice'] == 1){
            
//            $this->aplService->updateGoodsRawprice();
            
        }    
        
        return new JsonModel([
            ['ok']
        ]);
    }    
    
    /**
     * НЕИСПОЛЬЗУЕТСЯ
     * Сравнение строк прайсов товаров
     * 
     * @return JsonModel
     */
    public function compareGoodRawpriceAction()
    {
        $settings = $this->adminManager->getAplExchangeSettings();
        
        if ($settings['rawprice'] == 1){
            
//            $this->goodsManager->compareGoodsRawprice();
            
        }    
        
        return new JsonModel([
            ['ok']
        ]);
    }    
    
    /**
     * Обновление строк прайсов товаров
     * 
     * @return JsonModel
     */
    public function updateRawpricesAction()
    {
        $settings = $this->adminManager->getAplExchangeSettings();
        
        if ($settings['rawprice'] == 1){
            
            $this->aplService->updateRawprices();
            
        }    
        
        return new JsonModel([
            ['ok']
        ]);
    }    
    
    /**
     * НЕИСПОЛЬЗУЕТСЯ
     * Удаление старых прайсов в АПЛ
     */
    public function deleteOldRawAction()
    {
        
        $settings = $this->adminManager->getAplExchangeSettings();
        
        if ($settings['rawprice'] == 1){
//            $this->aplService->deleteRaws();
        }    
        
        return new JsonModel(
            ['ok']
        );
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
     * Обновление машин товаров
     * 
     * @return JsonModel
     */
    public function updateGoodCarAction()
    {
        $settings = $this->adminManager->getAplExchangeSettings();
        
        if ($settings['car'] == 1){            
            $this->aplService->updateGoodsCar();            
        }    
        
        return new JsonModel([
            ['ok']
        ]);
    }    

    /**
     * Обновление групп товаров
     * 
     * @return JsonModel
     */
    public function updateGoodGroupAction()
    {
        $settings = $this->adminManager->getAplExchangeSettings();
        
        if ($settings['group'] == 1){            
            $this->aplService->updateGoodsGroup();            
        }    
        
        return new JsonModel([
            ['ok']
        ]);
    }    

    /**
     * Обновление aplId машин
     * 
     * @return JsonModel
     */
    public function updateMakeAplIdAction()
    {
        $settings = $this->adminManager->getAplExchangeSettings();
        
        if ($settings['get_car_id'] == 1){            
            $this->aplService->updateMakeAplId();            
        }    
        
        return new JsonModel([
            ['ok']
        ]);
    }    

    /**
     * Обновление aplId машин
     * 
     * @return JsonModel
     */
    public function updateModelAplIdAction()
    {
        $settings = $this->adminManager->getAplExchangeSettings();
        
        if ($settings['get_car_id'] == 1){            
            $this->aplService->updateModelAplId();            
        }    
        
        return new JsonModel([
            ['ok']
        ]);
    }    

    /**
     * Обновление aplId машин
     * 
     * @return JsonModel
     */
    public function updateCarAplIdAction()
    {
        $settings = $this->adminManager->getAplExchangeSettings();
        
        if ($settings['get_car_id'] == 1){            
            $this->aplService->updateCarAplId();            
        }    
        
        return new JsonModel([
            ['ok']
        ]);
    }    

    /**
     * Обновление car fillVolumes
     * 
     * @return JsonModel
     */
    public function updateFillVolumesAction()
    {
        $settings = $this->adminManager->getAplExchangeSettings();
        
        if ($settings['get_car_id'] == 1){            
            $this->aplService->updateFillVolumes();            
        }    
        
        return new JsonModel([
            ['ok']
        ]);
    }    

    /**
     * Загрузка car fillVolumes
     * 
     * @return JsonModel
     */
    public function downloadFillVolumesAction()
    {
        $settings = $this->adminManager->getAplExchangeSettings();
        
        if ($settings['get_car_id'] == 1){            
            $this->carManager->carFillVolumes();            
        }    
        
        return new JsonModel([
            ['ok']
        ]);
    }    

    /**
     * Обновление aplId атрибутов
     * 
     * @return JsonModel
     */
    public function updateAttributeAplIdAction()
    {
        $settings = $this->adminManager->getAplExchangeSettings();
        
        if ($settings['attribute'] == 1){            
            $this->aplService->updateAttributeAplId();            
        }    
        
        return new JsonModel([
            ['ok']
        ]);
    }    

    /**
     * Обновление aplId значений атрибутов
     * 
     * @return JsonModel
     */
    public function updateAttributeValueAplIdAction()
    {
        $settings = $this->adminManager->getAplExchangeSettings();
        
        if ($settings['attribute'] == 1){            
            $this->aplService->updateAttributeValueAplId();            
        }    
        
        return new JsonModel([
            ['ok']
        ]);
    }    

    /**
     * Обновление атрибутов товаров
     * 
     * @return JsonModel
     */
    public function updateGoodAttributeAction()
    {
        $settings = $this->adminManager->getAplExchangeSettings();
        
        if ($settings['attribute'] == 1){            
            $this->aplService->updateGoodsAttribute();            
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
     * Выгрузка поступлений из апл
     * 
     * @return JsonModel
     */
    public function updateAplPtuAction()
    {
        
        $settings = $this->adminManager->getAplExchangeSettings();

        if ($settings['ptu'] == 1){
            $this->aplDocService->unloadDocs();
        }    
        
        return new JsonModel([
            ['ok']
        ]);
    }    
    
    /**
     * Выгрузка заказов из апл
     * 
     * @return JsonModel
     */
    public function updateAplOrderAction()
    {
        
        $settings = $this->adminManager->getAplExchangeSettings();

        if ($settings['order'] == 1){
            $this->aplOrderService->uploadOrders();
        }    
        
        return new JsonModel([
            ['ok']
        ]);
    }    
    
    /**
     * Проверка выгрузки заказов из апл
     * 
     * @return JsonModel
     */
    public function checkAplOrderAction()
    {
        
        $settings = $this->adminManager->getAplExchangeSettings();

        if ($settings['order'] == 1){
            $this->aplOrderService->checkOrders();
        }    
        
        return new JsonModel([
            ['ok']
        ]);
    }    
    
    /**
     * Выгрузка комментариев из апл
     * 
     * @return JsonModel
     */
    public function commentsAction()
    {        
        $settings = $this->adminManager->getAplExchangeSettings();
        
        if ($settings['order'] == 1){
            $this->aplOrderService->uploadComments();
        }    
        
        return new JsonModel([
            ['ok']
        ]);
    }    
    
    /**
     * Выгрузка платежей из апл
     * 
     * @return JsonModel
     */
    public function updateAplCashAction()
    {
        
        $settings = $this->adminManager->getAplExchangeSettings();

        if ($settings['cash'] == 1){
            $this->aplCashService->unloadPayments();
        }    
        
        return new JsonModel([
            ['ok']
        ]);
    }    
    
    /**
     * Выгрузка клентов из апл
     * 
     * @return JsonModel
     */
    public function updateAplUsersAction()
    {
        
        $settings = $this->adminManager->getAplExchangeSettings();

        if ($settings['order'] == 1){
            $this->aplService->uploadUsers();
        }    
        
        return new JsonModel([
            ['ok']
        ]);
    }    
    
    /**
     * Не используется!
     * Выгрузка прайслистов для ТП
     * 
     * @return JsonModel
     */
    public function marketPricesAction()
    {
        
        $settings = $this->adminManager->getAplExchangeSettings();

        if ($settings['market'] == 1){
            $this->marketManager->aplToZzap();
        }    
        
        return new JsonModel([
            ['ok']
        ]);
    }    
    
    /**
     * Поддержка токенов наименований
     */
    public function supportTitleTokensAction()
    {
        $settings = $this->adminManager->getPriceSettings();

        if ($settings['update_good_name'] == 1){            
            $this->entityManager->getRepository(TokenGroupToken::class)
                    ->supporTitleTokens(); 
        }    
                
        return new JsonModel(
            ['ok']
        );        
    }

    /**
     * Поддержка биграм наименований
     */
    public function supportTitleBigramsAction()
    {
        $settings = $this->adminManager->getPriceSettings();

        if ($settings['update_good_name'] == 1){            
            $this->entityManager->getRepository(TokenGroupBigram::class)
                    ->supporTitleBigrams(); 
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
            $this->nameManager->updateTokenGroupsMovement();
            
            $this->nameManager->removeEmptyTokenGroup();
        }    
                
        return new JsonModel(
            ['ok']
        );
        
    }
    
    /**
     * Обновление машин из текдока
     * @return JsonModel
     */
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
    
    /**
     * Обновление номеров из Текдока
     * @return JsonModel
     */
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
    
    /**
     * Обновление пересечений номеров
     * @return JsonModel
     */
    public function updateOemIntersectAction()
    {
        $settings = $this->adminManager->getTdExchangeSettings();

        if ($settings['update_oe'] == 1){

            $this->goodsManager->updateOemIntersect();            
        }    
                
        return new JsonModel(
            ['ok']
        );        
    }
    
    /**
     * Обновление групп из текдока
     * @return JsonModel
     */
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

            $groups = $this->entityManager->getRepository(\Application\Entity\GenericGroup::class)
                ->findBy([]);
            foreach ($groups as $group){
                $this->entityManager->getRepository(\Stock\Entity\Movement::class)
                        ->groupMovementCount($group);
            }

        }    
                
        return new JsonModel(
            ['ok']
        );
        
    }
    
    /**
     * Обновление описаний из текдока
     * @return JsonModel
     */
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
    
    /**
     * Обновление картинок из текдока
     * @return JsonModel
     */
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
    
    /**
     * Обновление токено из писем
     * @return JsonModel
     */
    public function updateMailTokensAction()
    {
        $settings = $this->adminManager->getSettings();

        if ($settings['mail_token'] == 1){

            $this->helloManager->logsToTokens();            
        }    
                
        return new JsonModel(
            ['ok']
        );
        
    }

    /**
     * 
     * Запрос чека эквайринга
     */
    public function asqrrnAction()
    {
        $aplPaymentId = $this->params()->fromRoute('id', -1);
        
        if ($aplPaymentId <= 0){
            $this->getResponse()->setStatusCode(401);
            goto r;                                    
        }
    
        $aplPayments = $this->entityManager->getRepository(\Bank\Entity\AplPayment::class)
                ->findBy(['aplPaymentId' => $aplPaymentId]);
        	
        if ($aplPayments == null) {
            $this->getResponse()->setStatusCode(401);
            goto r;                        
        } 
        
        $result = [];
        foreach ($aplPayments as $aplPayment){
            $asquirings = $aplPayment->getAcquirings();
            foreach ($asquirings as $asquiring){
                if ($asquiring->getAmount() > 0){
                    $result[] = [
                        'cart' => $asquiring->getCart(),
                        'cartType' => $asquiring->getСartType(),
                        'amount' => $asquiring->getAmount(),
                        'transDate' => $asquiring->getTransDate(),
                        'rrn' => $asquiring->getRrn(),
                    ];
                }    
            }
        }
        
        r:
        return new JsonModel($result);
    }
    
    /**
     * Выгрузить прайсы для ТП
     * @return JsonModel
     */
    public function unloadMarketPricesAction()
    {
        $settings = $this->adminManager->getApiMarketPlaces();

        if ($settings['market_unload'] == 1){

            $markets = $this->marketManager->unloadNext();             
            $this->ozonManager->updateMarkets($markets);
        }    
                
        return new JsonModel(
            ['ok']
        );        
    }    
    
    /**
     * Обновить заказы поставщикам
     * @return JsonModel
     */
    public function updateSupplierOrderAction()
    {
        $settings = $this->adminManager->getAplExchangeSettings();

        if ($settings['ptu'] == 1){

            $this->aplDocService->unloadSuppliersOrder();            
        }    
                
        return new JsonModel(
            ['ok']
        );        
    }    
    
    /**
     * Генерация перемещений между офисами
     * @return JsonModel
     */
    public function ptGeneratorAction()
    {
        $settings = $this->adminManager->getAplExchangeSettings();

        if ($settings['ptu'] == 1){

            $this->ptManager->ptGenerators();            
        }    
                
        return new JsonModel(
            ['ok']
        );        
    }    
    
    /**
     * Обновление user
     * @return JsonModel
     */
    public function updateUserAction()
    {
        $settings = $this->adminManager->getSettings();

        if ($settings['job'] == 1){

            $this->userManager->updateOrderCounts();            
        }    
                
        return new JsonModel(
            ['ok']
        );        
    }    

    /**
     * Выполнение заданий
     * @return JsonModel
     */
    public function jobRunAction()
    {
        $settings = $this->adminManager->getSettings();

        if ($settings['job'] == 1){

            $this->jobManager->run();            
        }    
                
        return new JsonModel(
            ['ok']
        );        
    }    
    
}
