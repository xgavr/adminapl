<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Bank\Service\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Bank\Service\SbpManager;
use Bankapi\Service\Tochka\SbpManager as ApiSbpManager;
use Admin\Service\LogManager;
use Admin\Service\AdminManager;

/**
 * Description of SbpManagerFactory
 *
 * @author Daddy
 */
class SbpManagerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, 
                    $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $sbpManager = $container->get(ApiSbpManager::class);
        $logManager = $container->get(LogManager::class);
        $adminManager = $container->get(AdminManager::class);
        
        // Инстанцируем сервис и внедряем зависимости.
        return new SbpManager($entityManager, $sbpManager, $logManager, $adminManager);
    }
}
