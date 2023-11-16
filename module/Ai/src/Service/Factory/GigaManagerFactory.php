<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Ai\Service\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Admin\Service\AdminManager;
use Ai\Service\GigaManager;

/**
 * Description of GigaManagerFactory
 *
 * @author Daddy
 */
class GigaManagerFactory  implements FactoryInterface
{
                   
    public function __invoke(ContainerInterface $container, 
                    $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $adminManager = $container->get(AdminManager::class);
        
        // Инстанцируем сервис и внедряем зависимости.
        return new GigaManager($entityManager, $adminManager);
    }
}
