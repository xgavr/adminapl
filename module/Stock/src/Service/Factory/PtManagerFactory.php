<?php
namespace Stock\Service\Factory;

use Interop\Container\ContainerInterface;
use Stock\Service\PtManager;
use Admin\Service\LogManager;
use Application\Service\OrderManager;
use Admin\Service\AdminManager;

/**
 * This is the factory class for PtManager service. The purpose of the factory
 * is to instantiate the service and pass it dependencies (inject dependencies).
 */
class PtManagerFactory
{
    /**
     * This method creates the UserManager service and returns its instance. 
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {        
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $logManager = $container->get(LogManager::class);
        $orderManager = $container->get(OrderManager::class);
        $adminManager = $container->get(AdminManager::class);
                        
        return new PtManager($entityManager, $logManager, $orderManager, $adminManager);
    }
}
