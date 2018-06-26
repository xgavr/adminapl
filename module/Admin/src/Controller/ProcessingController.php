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
    private $rawManager;    

    // Метод конструктора, используемый для внедрения зависимостей в контроллер.
    public function __construct($entityManager, $postManager, $autoruManager, $telegramManager, $aplService, $priceManager, $rawManager, $supplierManager) 
    {
        $this->entityManager = $entityManager;
        $this->postManager = $postManager;        
        $this->autoruManager = $autoruManager;
        $this->telegramManager = $telegramManager;
        $this->aplService = $aplService;
        $this->priceManager = $priceManager;
        $this->rawManager = $rawManager;
        $this->supplierManager = $supplierManager;
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
        $priceGettings = $this->entityManager->getRepository(PriceGetting::class)
                ->findBy(['status' => PriceGetting::STATUS_ACTIVE]);
        
        foreach ($priceGettings as $priceGetting){
            $this->priceManager->getPriceByLink($priceGetting);
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
        $priceGettings = $this->entityManager->getRepository(PriceGetting::class)
                ->findBy(['status' => PriceGetting::STATUS_ACTIVE]);
        
        foreach ($priceGettings as $priceGetting){
            $this->priceManager->getPriceByMail($priceGetting);
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
        $suppliers = $this->entityManager->getRepository(Supplier::class)
                ->findBy([]);
        foreach ($suppliers as $supplier){
            $files = $supplierManager->getLastPriceFile($supplier);
            if (count($files)){
                return $this->rawManager->checkSupplierPrice($supplier);
            }
        }
        
        return;
    }
    
}
