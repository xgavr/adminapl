<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Service\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Application\Service\ClientManager;
use Application\Service\ContactManager;
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
        
        // Инстанцируем сервис и внедряем зависимости.
        return new ClientManager($entityManager, $contactManager);
    }
}
