<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace GoodMap\Service\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Admin\Service\AdminManager;
use GoodMap\Service\FoldManager;
use Admin\Service\LogManager;

/**
 * Description of FoldManagerFactory
 *
 * @author Daddy
 */
class FoldManagerFactory  implements FactoryInterface
{
                   
    public function __invoke(ContainerInterface $container, 
                    $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $adminManager = $container->get(AdminManager::class);
        $logManager = $container->get(LogManager::class);
        
        // Инстанцируем сервис и внедряем зависимости.
        return new FoldManager($entityManager, $adminManager, $logManager);
    }
}
