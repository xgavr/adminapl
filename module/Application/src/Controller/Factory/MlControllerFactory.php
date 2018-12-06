<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Application\Controller\MlController;
use Application\Service\MlManager;


/**
 * Description of MlControllerFactory
 *
 * @author Daddy
 */
class MlControllerFactory implements FactoryInterface {
    
    public function __invoke(ContainerInterface $container, 
                     $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $mlManager = $container->get(MlManager::class);
        
        // Инстанцируем контроллер и внедряем зависимости.
        return new MlController($entityManager, $mlManager);
    }
}
