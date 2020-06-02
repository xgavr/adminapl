<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Application\Controller\RbController;
use Application\Service\RbManager;
/**
 * Description of RbControllerFactory
 *
 * @author Daddy
 */
class RbControllerFactory implements FactoryInterface {
    
    public function __invoke(ContainerInterface $container, 
                     $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $rbManager = $container->get(RbManager::class);
        
        // Инстанцируем контроллер и внедряем зависимости.
        return new RbController($entityManager, $rbManager);
    }
}
