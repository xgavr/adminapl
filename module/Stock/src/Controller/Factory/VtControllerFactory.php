<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Stock\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Stock\Controller\VtController;
use Stock\Service\VtManager;


/**
 * Description of VtControllerFactory
 *
 * @author Daddy
 */
class VtControllerFactory implements FactoryInterface {
    
    public function __invoke(ContainerInterface $container, 
                     $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $vtManager = $container->get(VtManager::class);
        
        // Инстанцируем контроллер и внедряем зависимости.
        return new VtController($entityManager, $vtManager);
    }
}
