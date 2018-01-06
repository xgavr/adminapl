<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Application\Controller\PriceController;
use Application\Service\SupplierManager;
use Application\Service\PriceManager;


/**
 * Description of SupplierControllerFactory
 *
 * @author Daddy
 */
class PriceControllerFactory implements FactoryInterface {
    
    public function __invoke(ContainerInterface $container, 
                     $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $supplierManager = $container->get(SupplierManager::class);
        $priceManager = $container->get(PriceManager::class);
        
        // Инстанцируем контроллер и внедряем зависимости.
        return new PriceController($entityManager, $supplierManager, $priceManager);
    }
}
