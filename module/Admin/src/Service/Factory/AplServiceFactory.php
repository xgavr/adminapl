<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Service\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Admin\Service\AplService;
use User\Service\UserManager;
use Application\Service\ContactManager;
use Application\Service\SupplierManager;
use Company\Service\LegalManager;
use Admin\Service\TelegrammManager;
use Admin\Service\AdminManager;
use Application\Service\ClientManager;
/**
 * Description of ClientManagerFactory
 *
 * @author Daddy
 */
class AplServiceFactory  implements FactoryInterface
{
                   
    public function __invoke(ContainerInterface $container, 
                    $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $userManager = $container->get(UserManager::class);  
        $contactManager = $container->get(ContactManager::class);  
        $supplierManager = $container->get(SupplierManager::class);  
        $legalManager = $container->get(LegalManager::class);  
        $telegramManager = $container->get(TelegrammManager::class);  
        $adminManager = $container->get(AdminManager::class);
        $clientManager = $container->get(ClientManager::class);
        
        // Инстанцируем сервис и внедряем зависимости.
        return new AplService($entityManager, $userManager, $contactManager, 
                $supplierManager, $legalManager, $telegramManager, $adminManager,
                $clientManager);
    }
}
