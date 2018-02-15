<?php
namespace Company\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Company\Controller\RegionController;
use Company\Service\RegionManager;

/**
 * This is the factory for RoleController. Its purpose is to instantiate the
 * controller and inject dependencies into it.
 */
class RegionControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $regionManager = $container->get(RegionManager::class);
        
        // Instantiate the controller and inject dependencies
        return new RegionController($entityManager, $regionManager);
    }
}

