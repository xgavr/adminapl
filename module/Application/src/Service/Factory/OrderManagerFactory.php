<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Service\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Application\Service\OrderManager;
use Admin\Service\LogManager;
use Company\Service\LegalManager;
use Admin\Service\AdminManager;
use Application\Service\ContactManager;
use Application\Service\ClientManager;
use Admin\Service\AplService;

/**
 * Description of OrderManagerFactory
 *
 * @author Daddy
 */
class OrderManagerFactory  implements FactoryInterface
{
                   
    public function __invoke(ContainerInterface $container, 
                    $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $authService = $container->get(\Laminas\Authentication\AuthenticationService::class);
        $logManager = $container->get(LogManager::class);
        $legalManager = $container->get(LegalManager::class);
        $adminManager = $container->get(AdminManager::class);
        $contactManager = $container->get(ContactManager::class);
        $clientManager = $container->get(ClientManager::class);
        $aplManager = $container->get(AplService::class);
        
        // Инстанцируем сервис и внедряем зависимости.
        return new OrderManager($entityManager, $authService, $logManager,
                $legalManager, $adminManager, $contactManager, $clientManager,
                $aplManager);
    }
}
