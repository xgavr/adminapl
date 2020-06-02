<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Bank\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Bank\Controller\IndexController;
use Bank\Service\BankManager;


/**
 * Description of IndexControllerFactory
 *
 * @author Daddy
 */
class IndexControllerFactory implements FactoryInterface {
    
    public function __invoke(ContainerInterface $container, 
                     $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $bankManager = $container->get(BankManager::class);
        
        // Инстанцируем контроллер и внедряем зависимости.
        return new IndexController($entityManager, $bankManager);
    }
}
