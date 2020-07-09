<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Stock\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Stock\Controller\PtuController;
use Stock\Service\PtuManager;


/**
 * Description of PtuControllerFactory
 *
 * @author Daddy
 */
class PtuControllerFactory implements FactoryInterface {
    
    public function __invoke(ContainerInterface $container, 
                     $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $ptuManager = $container->get(PtuManager::class);
        
        // Инстанцируем контроллер и внедряем зависимости.
        return new PtuController($entityManager, $ptuManager);
    }
}
