<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Application\Controller\CourierController;
use Application\Service\CourierManager;


/**
 * Description of CourierControllerFactory
 *
 * @author Daddy
 */
class CourierControllerFactory implements FactoryInterface {
    
    public function __invoke(ContainerInterface $container, 
                     $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $courierManager = $container->get(CourierManager::class);
        
        // Инстанцируем контроллер и внедряем зависимости.
        return new CourierController($entityManager, $courierManager);
    }
}
