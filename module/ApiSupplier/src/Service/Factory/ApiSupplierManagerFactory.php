<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ApiSupplier\Service\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use ApiSupplier\Service\MikadoManager;

/**
 * Description of ApiSupplierManagerFactory
 *
 * @author Daddy
 */
class ApiSupplierManagerFactory  implements FactoryInterface
{
                   
    public function __invoke(ContainerInterface $container, 
                    $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $mikadoManager = $container->get(MikadoManager::class);
        
        // Инстанцируем сервис и внедряем зависимости.
        return new ApiSupplierManager($entityManager, $mikadoManager);
    }
}
