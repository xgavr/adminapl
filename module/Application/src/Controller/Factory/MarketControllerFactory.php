<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Application\Controller\MarketController;
use Application\Service\MarketManager;
/**
 * Description of MarketControllerFactory
 *
 * @author Daddy
 */
class MarketControllerFactory implements FactoryInterface {
    
    public function __invoke(ContainerInterface $container, 
                     $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $marketManager = $container->get(MarketManager::class);
        
        // Инстанцируем контроллер и внедряем зависимости.
        return new MarketController($entityManager, $marketManager);
    }
}
