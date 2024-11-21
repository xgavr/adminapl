<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Admin\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Admin\Controller\SmsController;
use Admin\Service\SmsManager;
use Admin\Service\AdminManager;
use Admin\Service\AplService;
use Bank\Service\SbpManager;


/**
 * Description of SmsControllerFactory
 *
 * @author Daddy
 */
class SmsControllerFactory implements FactoryInterface {
    
    public function __invoke(ContainerInterface $container, 
                     $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');        
        $smsManager = $container->get(SmsManager::class);
        $adminManager = $container->get(AdminManager::class);
        $aplService = $container->get(AplService::class);
        $sbpManager = $container->get(SbpManager::class);
        
        // Инстанцируем контроллер и внедряем зависимости.
        return new SmsController($entityManager, $smsManager, $adminManager, 
                $aplService, $sbpManager);
    }
}
