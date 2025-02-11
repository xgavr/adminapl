<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Service\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Application\Service\GroupSiteManager;
use Admin\Service\LogManager;

/**
 * Description of GroupSiteManagerFactory
 *
 * @author Daddy
 */
class GroupSiteManagerFactory  implements FactoryInterface
{
                   
    public function __invoke(ContainerInterface $container, 
                    $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $logManager = $container->get(LogManager::class);
        
        // Инстанцируем сервис и внедряем зависимости.
        return new GroupSiteManager($entityManager, $logManager);
    }
}
