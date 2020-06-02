<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Service\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Application\Service\CarManager;
use Application\Service\ExternalManager;

/**
 * Description of CarManagerFactory
 *
 * @author Daddy
 */
class CarManagerFactory  implements FactoryInterface
{
                   
    public function __invoke(ContainerInterface $container, 
                    $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $externalManager = $container->get(ExternalManager::class);
        
        // Инстанцируем сервис и внедряем зависимости.
        return new CarManager($entityManager, $externalManager);
    }
}
