<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Application\Controller\EdoController;
use Application\Service\EdoManager;

/**
 * Description of EdoControllerFactory
 *
 * @author Daddy
 */
class EdoControllerFactory implements FactoryInterface {
    
    public function __invoke(ContainerInterface $container, 
                     $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $edoManager = $container->get(EdoManager::class);
        
        // Инстанцируем контроллер и внедряем зависимости.
        return new EdoController($entityManager, $edoManager);
    }
}
