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
use Stock\Service\PtuManager;

/**
 * Description of AplDocService
 *
 * @author Daddy
 */
class AplDocServiceFactory  implements FactoryInterface
{
                   
    public function __invoke(ContainerInterface $container, 
                    $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $adminManager = $container->get(AdminManager::class); 
        $ptuManager = $container->get(PtuManager::class);
        
        // Инстанцируем сервис и внедряем зависимости.
        return new AplDocService($entityManager, $adminManager, $ptuManager);
    }
}
