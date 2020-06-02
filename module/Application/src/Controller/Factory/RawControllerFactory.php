<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Application\Controller\RawController;
use Application\Service\SupplierManager;
use Application\Service\RawManager;
use Application\Service\ParseManager;


/**
 * Description of SupplierControllerFactory
 *
 * @author Daddy
 */
class RawControllerFactory implements FactoryInterface {
    
    public function __invoke(ContainerInterface $container, 
                     $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $supplierManager = $container->get(SupplierManager::class);
        $rawManager = $container->get(RawManager::class);
        $parseManager = $container->get(ParseManager::class);
        
        // Инстанцируем контроллер и внедряем зависимости.
        return new RawController($entityManager, $supplierManager, $rawManager, $parseManager);
    }
}
