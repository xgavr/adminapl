<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Fin\Service\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Fin\Service\BalanceManager;
use Admin\Service\AdminManager;
use Fin\Service\DdsManager;
use Fin\Service\FinManager;

/**
 * Description of BalanceManagerFactory
 *
 * @author Daddy
 */
class BalanceManagerFactory  implements FactoryInterface
{
                   
    public function __invoke(ContainerInterface $container, 
                    $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $adminManager = $container->get(AdminManager::class);
        $ddsManager = $container->get(DdsManager::class);
        $finManager = $container->get(FinManager::class);
        
        // Инстанцируем сервис и внедряем зависимости.
        return new BalanceManager($entityManager, $adminManager, $ddsManager, 
                $finManager);
    }
}
