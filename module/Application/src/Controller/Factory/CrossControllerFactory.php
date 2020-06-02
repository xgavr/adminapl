<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Application\Controller\CrossController;
use Application\Service\SupplierManager;
use Application\Service\CrossManager;
use Application\Service\ParseManager;


/**
 * Description of CrossControllerFactory
 *
 * @author Daddy
 */
class CrossControllerFactory implements FactoryInterface {
    
    public function __invoke(ContainerInterface $container, 
                     $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $supplierManager = $container->get(SupplierManager::class);
        $crossManager = $container->get(CrossManager::class);
        $parseManager = $container->get(ParseManager::class);
        
        // Инстанцируем контроллер и внедряем зависимости.
        return new CrossController($entityManager, $supplierManager, $crossManager, $parseManager);
    }
}
