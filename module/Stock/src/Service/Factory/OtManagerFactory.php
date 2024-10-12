<?php
namespace Stock\Service\Factory;

use Interop\Container\ContainerInterface;
use Stock\Service\OtManager;
use Admin\Service\LogManager;
use Admin\Service\AdminManager;
use GoodMap\Service\FoldManager;

/**
 * This is the factory class for OtManager service. The purpose of the factory
 * is to instantiate the service and pass it dependencies (inject dependencies).
 */
class OtManagerFactory
{
    /**
     * This method creates the UserManager service and returns its instance. 
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {        
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $logManager = $container->get(LogManager::class);
        $adminManager = $container->get(AdminManager::class);
        $foldManager = $container->get(FoldManager::class);
                        
        return new OtManager($entityManager, $logManager, $adminManager, $foldManager);
    }
}
