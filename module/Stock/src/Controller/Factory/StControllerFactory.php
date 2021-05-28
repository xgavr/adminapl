<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Stock\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Stock\Controller\StController;
use Stock\Service\StManager;


/**
 * Description of StControllerFactory
 *
 * @author Daddy
 */
class StControllerFactory implements FactoryInterface {
    
    public function __invoke(ContainerInterface $container, 
                     $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $stManager = $container->get(StManager::class);
        
        // Инстанцируем контроллер и внедряем зависимости.
        return new StController($entityManager, $stManager);
    }
}
