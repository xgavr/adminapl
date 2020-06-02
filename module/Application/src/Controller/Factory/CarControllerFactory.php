<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Application\Controller\CarController;
use Application\Service\CarManager;


/**
 * Description of CarControllerFactory
 *
 * @author Daddy
 */
class CarControllerFactory implements FactoryInterface {
    
    public function __invoke(ContainerInterface $container, 
                     $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $carManager = $container->get(CarManager::class);
        
        // Инстанцируем контроллер и внедряем зависимости.
        return new CarController($entityManager, $carManager);
    }
}
