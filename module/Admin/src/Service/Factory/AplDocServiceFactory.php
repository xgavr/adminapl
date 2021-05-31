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
use Stock\Service\VtpManager;
use Stock\Service\OtManager;
use Stock\Service\PtManager;
use Stock\Service\StManager;
use Company\Service\LegalManager;
use Application\Service\ProducerManager;
use Application\Service\AssemblyManager;

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
        $vtpManager = $container->get(VtpManager::class);
        $otManager = $container->get(OtManager::class);
        $stManager = $container->get(StManager::class);
        $ptManager = $container->get(PtManager::class);
        $legalManager = $container->get(LegalManager::class);
        $producerManager = $container->get(ProducerManager::class);
        $assemblyManager = $container->get(AssemblyManager::class);
        
        // Инстанцируем сервис и внедряем зависимости.
        return new AplDocService($entityManager, $adminManager, $ptuManager,
                $legalManager, $producerManager, $assemblyManager, $vtpManager, 
                $otManager, $stManager, $ptManager);
    }
}
