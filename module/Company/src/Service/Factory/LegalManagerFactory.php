<?php
namespace Company\Service\Factory;

use Interop\Container\ContainerInterface;
use Company\Service\LegalManager;
use Admin\Service\AdminManager;

/**
 * This is the factory class for RoleManager service. The purpose of the factory
 * is to instantiate the service and pass it dependencies (inject dependencies).
 */
class LegalManagerFactory
{
    /**
     * This method creates the UserManager service and returns its instance. 
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {        
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $adminManager = $container->get(AdminManager::class);
                        
        return new LegalManager($entityManager, $adminManager);
    }
}
