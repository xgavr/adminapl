<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Stock\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Stock\Controller\PtController;
use Stock\Service\PtManager;


/**
 * Description of PtControllerFactory
 *
 * @author Daddy
 */
class PtControllerFactory implements FactoryInterface {
    
    public function __invoke(ContainerInterface $container, 
                     $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $ptManager = $container->get(PtManager::class);
        
        // Инстанцируем контроллер и внедряем зависимости.
        return new PtController($entityManager, $ptManager);
    }
}
