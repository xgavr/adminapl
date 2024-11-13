<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ApiSupplier\Service\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use ApiSupplier\Service\MikadoManager;
use Admin\Service\AdminManager;
use Stock\Service\PtuManager;
use Application\Service\BillManager;

/**
 * Description of MikadoManagerFactory
 *
 * @author Daddy
 */
class MikadoManagerFactory  implements FactoryInterface
{
                   
    public function __invoke(ContainerInterface $container, 
                    $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $adminManager = $container->get(AdminManager::class);
        $ptuManager = $container->get(PtuManager::class);
        $billManager = $container->get(BillManager::class);
        
        // Инстанцируем сервис и внедряем зависимости.
        return new MikadoManager($entityManager, $adminManager, $ptuManager,
                $billManager);
    }
}
