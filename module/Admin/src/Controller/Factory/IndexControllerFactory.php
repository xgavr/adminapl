<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Admin\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Admin\Controller\IndexController;
use Admin\Service\TelegrammManager;
use Admin\Service\AdminManager;
use Admin\Service\SmsManager;
use Admin\Service\TamTamManager;
use Admin\Service\AnnManager;
use Admin\Service\ThreadManager;


/**
 * Description of IndexControllerFactory
 *
 * @author Daddy
 */
class IndexControllerFactory implements FactoryInterface {
    
    public function __invoke(ContainerInterface $container, 
                     $requestedName, array $options = null)
    {
        $telegrammManager = $container->get(TelegrammManager::class);
        $adminManager = $container->get(AdminManager::class);
        $smsManager = $container->get(SmsManager::class);
        $tamtamManager = $container->get(TamTamManager::class);
        $annManager = $container->get(AnnManager::class);
        $threadManager = $container->get(ThreadManager::class);
        
        // Инстанцируем контроллер и внедряем зависимости.
        return new IndexController($telegrammManager, $adminManager, $smsManager, $tamtamManager, $annManager, $threadManager);
    }
}
