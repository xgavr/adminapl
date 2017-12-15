<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Application\Controller\OrderController;
use Application\Service\OrderManager;


/**
 * Description of OrderControllerFactory
 *
 * @author Daddy
 */
class OrderControllerFactory implements FactoryInterface {
    
    public function __invoke(ContainerInterface $container, 
                     $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $orderManager = $container->get(OrderManager::class);
        
        // Инстанцируем контроллер и внедряем зависимости.
        return new OrderController($entityManager, $orderManager);
    }
}
