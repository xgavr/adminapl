<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Application\Entity\PriceGetting;


class ProcessingController extends AbstractActionController
{
    
    /**
     * Менеджер сущностей.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * PostManager manager.
     * @var Admin\Service\PostManager
     */
    private $postManager;    
    
    /**
     * AutoruManager manager.
     * @var Admin\Service\AutoruManager
     */
    private $autoruManager;    
    
    /**
     * TelegramManager manager.
     * @var Admin\Service\TelegrammManager
     */
    private $telegramManager;    
    
    /**
     * AplService manager.
     * @var Admin\Service\AplService
     */
    private $aplService;    

    /**
     * AplBankService manager.
     * @var Admin\Service\AplBankService
     */
    private $aplBankService;    

    /**
     * PriceManager manager.
     * @var Application\Service\PriceManager
     */
    private $priceManager;    

    /**
     * RawManager manager.
     * @var Application\Service\RawManager
     */
    private $rawManager;    

    /**
     * SupplierManager manager.
     * @var Application\Service\SupplierManager
     */
    private $supplierManager;    

    /**
     * AdminManager manager.
     * @var Admin\Service\AdminManager
     */
    private $adminManager;    

    /**
     * AdminManager manager.
     * @var Application\Service\ParseManager
     */
    private $parseManager;    

    /**
     * BankManager manager.
     * @var Bank\Service\BankManager
     */
    private $bankManager;    

    /**
     * ProducerManager manager.
     * @var Application\Service\ProducerManager
     */
    private $producerManager;    

    /**
     * ArticleManager manager.
     * @var Application\Service\ArticleManager
     */
    private $articleManager;    

    /**
     * OemManager manager.
     * @var Application\Service\OemManager
     */
    private $oemManager;    

    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $postManager, $autoruManager, $telegramManager, 
            $aplService, $priceManager, $rawManager, $supplierManager, $adminManager,
            $parseManager, $bankManager, $aplBankService, $producerManager, $articleManager,
            $oemManager) 
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
    }   

    
    public function indexAction()
    {
        set_time_limit(180);
        $this->autoruManager->postOrder();
        
        return [];
    }
    
    /*
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
        ini_set('memory_limit', '512M');
        set_time_limit(0);
        
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
        set_time_limit(0);
        
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

                $this->telegramManager->sendMessage(['text' => $message]);
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
        set_time_limit(1200);
        
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
        set_time_limit(1200);
        
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
        set_time_limit(1200);
        
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
        set_time_limit(1200);
        
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
        set_time_limit(1200);
        
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
}
