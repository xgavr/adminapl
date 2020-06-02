<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Application\Controller\SupplierController;
use Application\Service\SupplierManager;
use Application\Service\ContactManager;


/**
 * Description of SupplierControllerFactory
 *
 * @author Daddy
 */
class SupplierControllerFactory implements FactoryInterface {
    
    public function __invoke(ContainerInterface $container, 
                     $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $supplierManager = $container->get(SupplierManager::class);
        $contactManager = $container->get(ContactManager::class);
        
        // Инстанцируем контроллер и внедряем зависимости.
        return new SupplierController($entityManager, $supplierManager, $contactManager);
    }
}
