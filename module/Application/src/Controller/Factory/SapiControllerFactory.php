<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Application\Controller\SapiController;
use Application\Service\SupplierApi\AutoEuroManager;
use Admin\Service\AdminManager;


/**
 * Description of SapiControllerFactory
 *
 * @author Daddy
 */
class SapiControllerFactory implements FactoryInterface {
    
    public function __invoke(ContainerInterface $container, 
                     $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $adminManager = $container->get(AdminManager::class);
        $autoEuroManager = $container->get(AutoEuroManager::class);
        
        // Инстанцируем контроллер и внедряем зависимости.
        return new SapiController($entityManager, $adminManager, $autoEuroManager);
    }
}
