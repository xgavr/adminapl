<?php
namespace Stock\Service\Factory;

use Interop\Container\ContainerInterface;
use Stock\Service\PtuManager;
use Admin\Service\LogManager;

/**
 * This is the factory class for RoleManager service. The purpose of the factory
 * is to instantiate the service and pass it dependencies (inject dependencies).
 */
class PtuManagerFactory
{
    /**
     * This method creates the UserManager service and returns its instance. 
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {        
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $logManager = $container->get(LogManager::class);
                        
        return new PtuManager($entityManager, $logManager);
    }
}
