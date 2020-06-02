<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Service\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Application\Service\SupplierManager;
use Application\Service\ContactManager;
use Application\Service\PriceManager;
use Application\Service\RawManager;

/**
 * Description of SupplierManagerFactory
 *
 * @author Daddy
 */
class SupplierManagerFactory  implements FactoryInterface
{
                   
    public function __invoke(ContainerInterface $container, 
                    $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $contactManager = $container->get(ContactManager::class);
        $priceManager = $container->get(PriceManager::class);
        $rawManager = $container->get(RawManager::class);
        
        // Инстанцируем сервис и внедряем зависимости.
        return new SupplierManager($entityManager, $contactManager, $priceManager, $rawManager);
    }
}
