<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Application\Controller\RingController;
use Application\Service\RingManager;


/**
 * Description of RingControllerFactory
 *
 * @author Daddy
 */
class RingControllerFactory implements FactoryInterface {
    
    public function __invoke(ContainerInterface $container, 
                     $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $ringManager = $container->get(RingManager::class);
        
        // Инстанцируем контроллер и внедряем зависимости.
        return new RingController($entityManager, $ringManager);
    }
}
