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
use Application\Service\PriceManager;
use Application\Service\RawManager;
use Application\Service\SupplierManager;
use Admin\Service\AdminManager;
use Application\Service\ParseManager;


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
        $priceManager = $container->get(PriceManager::class);
        $rawManager = $container->get(RawManager::class);
        $supplierManager = $container->get(SupplierManager::class);
        $adminManager = $container->get(AdminManager::class);
        $parseManager = $container->get(ParseManager::class);
        
        // Инстанцируем контроллер и внедряем зависимости.
        return new ProcessingController($entityManager, $postManager, $autoruManager, 
                $telegramManager, $aplService, $priceManager, $rawManager, $supplierManager, 
                $adminManager, $parseManager);
    }
}
