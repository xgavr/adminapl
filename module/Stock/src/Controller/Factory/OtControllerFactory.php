<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Stock\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Stock\Controller\OtController;
use Stock\Service\OtManager;


/**
 * Description of OtControllerFactory
 *
 * @author Daddy
 */
class OtControllerFactory implements FactoryInterface {
    
    public function __invoke(ContainerInterface $container, 
                     $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $otManager = $container->get(OtManager::class);
        
        // Инстанцируем контроллер и внедряем зависимости.
        return new OtController($entityManager, $otManager);
    }
}
