<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Service\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Admin\Service\SmsManager;
use Admin\Service\AdminManager;
use Admin\Service\LogManager;
use Application\Service\PrintManager;

/**
 * Description of ShopManagerFactory
 *
 * @author Daddy
 */
class SmsManagerFactory  implements FactoryInterface
{
                   
    public function __invoke(ContainerInterface $container, 
                    $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $adminManager = $container->get(AdminManager::class);
        $logManager = $container->get(LogManager::class);
        $printManager = $container->get(PrintManager::class);
        
        // Инстанцируем сервис и внедряем зависимости.
        return new SmsManager($entityManager, $adminManager, $logManager, $printManager);
    }
}
