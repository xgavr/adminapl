<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Service\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Application\Service\ShopManager;
use User\Service\RbacManager;

/**
 * Description of ShopManagerFactory
 *
 * @author Daddy
 */
class ShopManagerFactory  implements FactoryInterface
{
                   
    public function __invoke(ContainerInterface $container, 
                    $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $authService = $container->get(\Zend\Authentication\AuthenticationService::class);
        $sessionContainer = $container->get('ContainerNamespace');
        $rbacManager = $container->get(RbacManager::class);
        
        // Инстанцируем сервис и внедряем зависимости.
        return new ShopManager($entityManager, $authService, $sessionContainer, $rbacManager);
    }
}
