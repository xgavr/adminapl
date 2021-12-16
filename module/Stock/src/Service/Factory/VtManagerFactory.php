<?php
namespace Stock\Service\Factory;

use Interop\Container\ContainerInterface;
use Stock\Service\VtManager;
use Admin\Service\LogManager;
use Application\Service\OrderManager;

/**
 * This is the factory class for VtManager service. The purpose of the factory
 * is to instantiate the service and pass it dependencies (inject dependencies).
 */
class VtManagerFactory
{
    /**
     * This method creates the UserManager service and returns its instance. 
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {        
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $logManager = $container->get(LogManager::class);
        $orderManager = $container->get(OrderManager::class);
                        
        return new VtManager($entityManager, $logManager, $orderManager);
    }
}
