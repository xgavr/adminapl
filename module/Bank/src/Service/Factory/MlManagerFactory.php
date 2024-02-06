<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Bank\Service\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Bank\Service\MlManager;


/**
 * Description of MlManagerFactory
 *
 * @author Daddy
 */
class MlManagerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, 
                    $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        
        // Инстанцируем сервис и внедряем зависимости.
        return new MlManager($entityManager);
    }
}
