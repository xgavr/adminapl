<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Service\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Admin\Service\AplBankService;
use Admin\Service\AdminManager;

/**
 * Description of AplBankService
 *
 * @author Daddy
 */
class AplBankServiceFactory  implements FactoryInterface
{
                   
    public function __invoke(ContainerInterface $container, 
                    $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $adminManager = $container->get(AdminManager::class);  
        
        // Инстанцируем сервис и внедряем зависимости.
        return new AplBankService($entityManager, $adminManager);
    }
}
