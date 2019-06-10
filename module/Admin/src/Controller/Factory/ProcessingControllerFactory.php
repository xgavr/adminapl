<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Admin\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Admin\Controller\ProcessingController;
use Admin\Service\PostManager;
use Admin\Service\AutoruManager;
use Admin\Service\TelegrammManager;
use Admin\Service\AplService;
use Admin\Service\AplBankService;
use Application\Service\PriceManager;
use Application\Service\RawManager;
use Application\Service\SupplierManager;
use Admin\Service\AdminManager;
use Application\Service\ParseManager;
use Bank\Service\BankManager;
use Application\Service\ProducerManager;
use Application\Service\ArticleManager;
use Application\Service\OemManager;
use Application\Service\NameManager;
use Application\Service\AssemblyManager;
use Application\Service\GoodsManager;
use Admin\Service\SettingManager;


/**
 * Description of ClientControllerFactory
 *
 * @author Daddy
 */
class ProcessingControllerFactory implements FactoryInterface {
    
    public function __invoke(ContainerInterface $container, 
                     $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $postManager = $container->get(PostManager::class);
        $autoruManager = $container->get(AutoruManager::class);
        $telegramManager = $container->get(TelegrammManager::class);
        $aplService = $container->get(AplService::class);
        $aplBankService = $container->get(AplBankService::class);
        $priceManager = $container->get(PriceManager::class);
        $rawManager = $container->get(RawManager::class);
        $supplierManager = $container->get(SupplierManager::class);
        $adminManager = $container->get(AdminManager::class);
        $parseManager = $container->get(ParseManager::class);
        $bankManager = $container->get(BankManager::class);
        $producerManager = $container->get(ProducerManager::class);
        $articleManager = $container->get(ArticleManager::class);
        $oemManager = $container->get(OemManager::class);
        $nameManager = $container->get(NameManager::class);
        $assemblyManager = $container->get(AssemblyManager::class);
        $goodsManager = $container->get(GoodsManager::class);
        $settingManager = $container->get(SettingManager::class);
        
        // Инстанцируем контроллер и внедряем зависимости.
        return new ProcessingController($entityManager, $postManager, $autoruManager, 
                $telegramManager, $aplService, $priceManager, $rawManager, $supplierManager, 
                $adminManager, $parseManager, $bankManager, $aplBankService, $producerManager,
                $articleManager, $oemManager, $nameManager, $assemblyManager, $goodsManager,
                $settingManager);
    }
}
