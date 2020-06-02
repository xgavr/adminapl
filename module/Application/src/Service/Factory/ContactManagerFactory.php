<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Service\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Application\Service\ContactManager;
use User\Service\UserManager;
/**
 * Description of ContactManagerFactory
 *
 * @author Daddy
 */
class ContactManagerFactory  implements FactoryInterface
{
                   
    public function __invoke(ContainerInterface $container, 
                    $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $userManager = $container->get(UserManager::class);
        
        // Инстанцируем сервис и внедряем зависимости.
        return new ContactManager($entityManager, $userManager);
    }
}
