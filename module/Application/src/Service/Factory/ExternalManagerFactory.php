<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Service\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Application\Service\ExternalDB\AutodbManager;
use Application\Service\ExternalManager;

/**
 * Description of AssemblyManagerFactory
 *
 * @author Daddy
 */
class ExternalManagerFactory  implements FactoryInterface
{
                   
    public function __invoke(ContainerInterface $container, 
                    $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $autoDbManager = $container->get(AutodbManager::class);
        
        // Инстанцируем сервис и внедряем зависимости.
        return new ExternalManager($entityManager, $autoDbManager);
    }
}
