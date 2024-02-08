<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Zp\Service\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Zp\Service\ZpCalculator;
use Admin\Service\AdminManager;
use Company\Service\TaxManager;

/**
 * Description of ZpCalculatorFactory
 *
 * @author Daddy
 */
class ZpCalculatorFactory  implements FactoryInterface
{
                   
    public function __invoke(ContainerInterface $container, 
                    $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $adminManager = $container->get(AdminManager::class);
        $taxManager = $container->get(TaxManager::class);
        
        // Инстанцируем сервис и внедряем зависимости.
        return new ZpCalculator($entityManager, $adminManager, $taxManager);
    }
}
