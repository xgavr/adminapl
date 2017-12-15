<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Application\Controller\ClientController;
use Application\Service\ClientManager;


/**
 * Description of ClientControllerFactory
 *
 * @author Daddy
 */
class ClientControllerFactory implements FactoryInterface {
    
    public function __invoke(ContainerInterface $container, 
                     $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $clientManager = $container->get(ClientManager::class);
        
        // Инстанцируем контроллер и внедряем зависимости.
        return new ClientController($entityManager, $clientManager);
    }
}
