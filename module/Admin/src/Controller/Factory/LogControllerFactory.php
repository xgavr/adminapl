<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Admin\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Admin\Controller\LogController;
use Admin\Service\LogManager;


/**
 * Description of LogControllerFactory
 *
 * @author Daddy
 */
class LogControllerFactory implements FactoryInterface {
    
    public function __invoke(ContainerInterface $container, 
                     $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $logManager = $container->get(LogManager::class);
        
        // Инстанцируем контроллер и внедряем зависимости.
        return new LogController($entityManager, $logManager);
    }
}
