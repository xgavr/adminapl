<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Service\Factory\ExternalDB;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Application\Service\ExternalDB\ZetasoftManager;
use Admin\Service\AdminManager;
use Laminas\Session\Container;

/**
 * Description of AbcpManagerFactory
 *
 * @author Daddy
 */
class ZetasoftManagerFactory  implements FactoryInterface
{
                   
    public function __invoke(ContainerInterface $container, 
                    $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $adminManager = $container->get(AdminManager::class);
        $sessionContainer = new Container('zf_namespace');
        
        // Инстанцируем сервис и внедряем зависимости.
        return new ZetasoftManager($entityManager, $adminManager, $sessionContainer);
    }
}
