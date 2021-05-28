<?php
namespace Company\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Company\Controller\CostController;
use Company\Service\CostManager;

/**
 * This is the factory for CostController. Its purpose is to instantiate the
 * controller and inject dependencies into it.
 */
class CostControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $costManager = $container->get(CostManager::class);
        
        // Instantiate the controller and inject dependencies
        return new CostController($entityManager, $costManager);
    }
}

