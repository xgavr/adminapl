<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Service\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Admin\Service\TelegrammManager;
use Admin\Service\AdminManager;
use Admin\Service\SmsManager;
use Admin\Service\MaxNotifierService;

/**
 * Description of ShopManagerFactory
 *
 * @author Daddy
 */
class TelegrammManagerFactory  implements FactoryInterface
{
                   
    public function __invoke(ContainerInterface $container, 
                    $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $adminManager = $container->get(AdminManager::class);
        $smsManager = $container->get(SmsManager::class);
        $maxManager = $container->get(MaxNotifierService::class);
        
        // Инстанцируем сервис и внедряем зависимости.
        return new TelegrammManager($entityManager, $adminManager, $smsManager,
                $maxManager);
    }
}
