<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Stock\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Stock\Controller\ComitentController;
use ApiMarketPlace\Service\ReportManager;


/**
 * Description of ComitentControllerFactory
 *
 * @author Daddy
 */
class ComitentControllerFactory implements FactoryInterface {
    
    public function __invoke(ContainerInterface $container, 
                     $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $reportManager = $container->get(ReportManager::class);
        
        // Инстанцируем контроллер и внедряем зависимости.
        return new ComitentController($entityManager, $reportManager);
    }
}
