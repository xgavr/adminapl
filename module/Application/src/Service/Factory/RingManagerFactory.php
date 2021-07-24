<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Service\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Application\Service\RingManager;
use Application\Service\ContactManager;
use Application\Service\ClientManager;
use Application\Service\ContactCarManager;

/**
 * Description of RingManagerFactory
 *
 * @author Daddy
 */
class RingManagerFactory  implements FactoryInterface
{
                   
    public function __invoke(ContainerInterface $container, 
                    $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $authService = $container->get(\Laminas\Authentication\AuthenticationService::class);
        $contactManager = $container->get(ContactManager::class);
        $clientManager = $container->get(ClientManager::class);
        $contactCarManager = $container->get(ContactCarManager::class);
        
        // Инстанцируем сервис и внедряем зависимости.
        return new RingManager($entityManager, $authService, $contactManager,
                $clientManager, $contactCarManager);
    }
}
