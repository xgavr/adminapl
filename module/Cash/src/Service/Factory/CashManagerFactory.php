<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cash\Service\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Cash\Service\CashManager;
use Admin\Service\LogManager;
use Company\Service\LegalManager;
use Zp\Service\ZpCalculator;
use Company\Service\CostManager;
use Admin\Service\AdminManager;
use Admin\Service\TelegrammManager;

/**
 * Description of CashManagerFactory
 *
 * @author Daddy
 */
class CashManagerFactory  implements FactoryInterface
{
                   
    public function __invoke(ContainerInterface $container, 
                    $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $logManager = $container->get(LogManager::class);
        $legalManager = $container->get(LegalManager::class);
        $zpManager = $container->get(ZpCalculator::class);
        $costManager = $container->get(CostManager::class);
        $adminManager = $container->get(AdminManager::class);
        $telegramManager = $container->get(TelegrammManager::class);
        
        // Инстанцируем сервис и внедряем зависимости.
        return new CashManager($entityManager, $logManager, $legalManager, $zpManager,
                $costManager, $adminManager, $telegramManager);
    }
}
