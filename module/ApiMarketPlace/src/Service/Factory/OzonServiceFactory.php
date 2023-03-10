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
use ApiMarketPlace\Service\OzonService;
use Application\Service\MarketManager;
use ApiMarketPlace\Service\MarketplaceService;

/**
 * Description of OzonServiceFactory
 *
 * @author Daddy
 */
class OzonServiceFactory  implements FactoryInterface
{
                   
    public function __invoke(ContainerInterface $container, 
                    $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $adminManager = $container->get(AdminManager::class);
        $request = $container->get(Request::class);
        $updateManager = $container->get(Update::class);
        $marketManager = $container->get(MarketManager::class);
        $marketService = $container->get(MarketplaceService::class);
        
        // Инстанцируем сервис и внедряем зависимости.
        return new OzonService($entityManager, $adminManager, $request, $updateManager,
                $marketManager, $marketService);
    }
}
