<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Service\Factory\ExternalDB;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Application\Service\ExternalDB\PartsApiManager;
use Admin\Service\AdminManager;

/**
 * Description of ShopManagerFactory
 *
 * @author Daddy
 */
class PartsApiManagerFactory  implements FactoryInterface
{
                   
    public function __invoke(ContainerInterface $container, 
                    $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $config = $container->get('config');
        $adminManager = $container->get(AdminManager::class);

        // Инстанцируем сервис и внедряем зависимости.
        return new PartsApiManager($entityManager, $adminManager);
    }
}
