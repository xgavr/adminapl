<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Stock\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Stock\Controller\RevisionController;
//use Stock\Service\RevisionManager;


/**
 * Description of RevisionControllerFactory
 *
 * @author Daddy
 */
class RevisionControllerFactory implements FactoryInterface {
    
    public function __invoke(ContainerInterface $container, 
                     $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
//        $reviseManager = $container->get(ReviseManager::class);
        
        // Инстанцируем контроллер и внедряем зависимости.
        return new RevisionController($entityManager);
    }
}
