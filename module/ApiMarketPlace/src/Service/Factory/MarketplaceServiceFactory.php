<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ApiMarketPlace\Service\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use ApiMarketPlace\Service\MarketplaceService;
use Application\Service\OrderManager;

/**
 * Description of MarketplaceServiceFactory
 *
 * @author Daddy
 */
class MarketplaceServiceFactory  implements FactoryInterface
{
                   
    public function __invoke(ContainerInterface $container, 
                    $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $orderManager = $container->get(OrderManager::class);
        
        // Инстанцируем сервис и внедряем зависимости.
        return new MarketplaceService($entityManager, $orderManager);
    }
}
