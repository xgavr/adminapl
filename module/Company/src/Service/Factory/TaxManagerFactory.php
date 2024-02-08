<?php
namespace Company\Service\Factory;

use Interop\Container\ContainerInterface;
use Company\Service\TaxManager;

/**
 * This is the factory class for TaxManager service. The purpose of the factory
 * is to instantiate the service and pass it dependencies (inject dependencies).
 */
class TaxManagerFactory
{
    /**
     * This method creates the UserManager service and returns its instance. 
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {        
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
                        
        return new TaxManager($entityManager);
    }
}
