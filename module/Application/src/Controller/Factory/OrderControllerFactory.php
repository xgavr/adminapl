<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Application\Controller\OrderController;
use Application\Service\OrderManager;
use User\Service\RbacManager;
use Application\Service\CommentManager;

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
        $authService = $container->get(\Laminas\Authentication\AuthenticationService::class);
        $rbacManager = $container->get(RbacManager::class);
        $commentManager = $container->get(CommentManager::class);
        
        // Инстанцируем контроллер и внедряем зависимости.
        return new OrderController($entityManager, $orderManager, $authService, 
                $rbacManager, $commentManager);
    }
}
