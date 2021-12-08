<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ApiMarketPlace\Service\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Admin\Service\AdminManager;
use ApiMarketPlace\Service\Request;
use ApiMarketPlace\Service\Update;
use ApiMarketPlace\Service\SberMarket;

/**
 * Description of SberMarketFactory
 *
 * @author Daddy
 */
class SberMarketFactory  implements FactoryInterface
{
                   
    public function __invoke(ContainerInterface $container, 
                    $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $adminManager = $container->get(AdminManager::class);
        $request = $container->get(Request::class);
        $updateManager = $container->get(Update::class);
        
        // Инстанцируем сервис и внедряем зависимости.
        return new SberMarket($entityManager, $adminManager, $request, $updateManager);
    }
}
