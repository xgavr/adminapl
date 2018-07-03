<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Application\Entity\PriceGetting;
use Application\Entity\Supplier;
use Application\Entity\Rawprice;


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

    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $postManager, $autoruManager, $telegramManager, 
            $aplService, $priceManager, $rawManager, $supplierManager, $adminManager,
            $parseManager) 
    {
        $this->entityManager = $entityManager;
        $this->postManager = $postManager;        
        $this->autoruManager = $autoruManager;
        $this->telegramManager = $telegramManager;
        $this->aplService = $aplService;
        $this->priceManager = $priceManager;
        $this->rawManager = $rawManager;
        $this->supplierManager = $supplierManager;
        $this->adminManager = $adminManager;
        $this->parseManager = $parseManager;
    }   

    
    public function indexAction()
    {
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
        $settings = $this->adminManager->getPriceSettings();

        if ($settings['receiving_mail'] == 1){
            $priceGettings = $this->entityManager->getRepository(PriceGetting::class)
                    ->findBy(['status' => PriceGetting::STATUS_ACTIVE]);

            foreach ($priceGettings as $priceGetting){
                $this->priceManager->getPriceByMail($priceGetting);
            }
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
        $settings = $this->adminManager->getPriceSettings();
        
        if ($settings['upload_raw'] == 1){
            
            $files = $this->supplierManager->getPriceFilesToUpload();
            if (count($files)){
                foreach ($files as $file){
                    $this->rawManager->checkSupplierPrice($file['priceGetting']->getSupplier());
                    break;
                }
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
            $rawprices = $this->entityManager->getRepository(Rawprice::class)
//                    ->findBy(['status' => Rawprice::STATUS_NEW], ['id' => 'ASC'], 10000)
                    ->findNewRawprice(10)
                    ;
            
            foreach ($rawprices as $rawprice){
                $this->parseManager->updateRawprice($rawprice, false, Rawprice::STATUS_PARSE);
            }
            
            $this->entityManager->flush();
            $this->entityManager->clear();
        }    
        
        return new JsonModel(
            ['ok']
        );
    }
    
}
