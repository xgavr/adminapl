<?php
namespace Company\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Company\Controller\ContractController;
use Company\Service\ContractManager;

/**
 * This is the factory for ContractController. Its purpose is to instantiate the
 * controller and inject dependencies into it.
 */
class ContractControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $contractManager = $container->get(ContractManager::class);
        
        // Instantiate the controller and inject dependencies
        return new ContractController($entityManager, $contractManager);
    }
}

