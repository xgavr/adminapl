<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Stock\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Stock\Controller\ReviseController;
use Stock\Service\ReviseManager;


/**
 * Description of ReviseControllerFactory
 *
 * @author Daddy
 */
class ReviseControllerFactory implements FactoryInterface {
    
    public function __invoke(ContainerInterface $container, 
                     $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $reviseManager = $container->get(ReviseManager::class);
        
        // Инстанцируем контроллер и внедряем зависимости.
        return new ReviseController($entityManager, $ptuManager);
    }
}
