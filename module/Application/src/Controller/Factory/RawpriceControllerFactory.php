<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Application\Controller\RawpriceController;
use Application\Service\SupplierManager;
use Application\Service\RawManager;


/**
 * Description of SupplierControllerFactory
 *
 * @author Daddy
 */
class RawpriceControllerFactory implements FactoryInterface {
    
    public function __invoke(ContainerInterface $container, 
                     $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $supplierManager = $container->get(SupplierManager::class);
        $rawManager = $container->get(RawManager::class);
        
        // Инстанцируем контроллер и внедряем зависимости.
        return new RawpriceController($entityManager, $supplierManager, $rawManager);
    }
}
