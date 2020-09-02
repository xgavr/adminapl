<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Service\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Admin\Service\LogManager;
use Admin\Service\SettingManager;

/**
 * Description of LogManagerFactory
 *
 * @author Daddy
 */
class LogManagerFactory  implements FactoryInterface
{
                   
    public function __invoke(ContainerInterface $container, 
                    $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $authService = $container->get(\Laminas\Authentication\AuthenticationService::class);
        $settingManager = $container->get(SettingManager::class);

        
        // Инстанцируем сервис и внедряем зависимости.
        return new LogManager($entityManager, $authService, $settingManager);
    }
}
