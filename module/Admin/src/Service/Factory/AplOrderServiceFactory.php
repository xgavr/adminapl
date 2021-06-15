<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Service\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Admin\Service\AplDocService;
use Admin\Service\AdminManager;
use Admin\Service\AplService;
use Admin\Service\AplDocService;

/**
 * Description of AplOrderService
 *
 * @author Daddy
 */
class AplOrderServiceFactory  implements FactoryInterface
{
                   
    public function __invoke(ContainerInterface $container, 
                    $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $adminManager = $container->get(AdminManager::class); 
        $aplService = $container->get(AplService::class);
        $aplDocService = $container->get(AplDocService::class);
        
        // Инстанцируем сервис и внедряем зависимости.
        return new AplOrderService($entityManager, $adminManager, $aplService,
                $aplDocService);
    }
}
