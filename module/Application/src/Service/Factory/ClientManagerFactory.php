<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Service\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Application\Service\ClientManager;
use Application\Service\ContactManager;
use User\Service\UserManager;
use Admin\Service\AplOrderService;

/**
 * Description of ClientManagerFactory
 *
 * @author Daddy
 */
class ClientManagerFactory  implements FactoryInterface
{
                   
    public function __invoke(ContainerInterface $container, 
                    $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $contactManager = $container->get(ContactManager::class);        
        $userManager = $container->get(UserManager::class);  
        $authService = $container->get(\Laminas\Authentication\AuthenticationService::class);
        $aplOrderService = $contactManager->get(AplOrderService::class);
        
        // Инстанцируем сервис и внедряем зависимости.
        return new ClientManager($entityManager, $contactManager, $userManager, 
                $authService, $aplOrderService);
    }
}
