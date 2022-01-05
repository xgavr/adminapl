<?php
namespace ApiSupplier\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use ApiSupplier\Controller\IndexController;
use ApiSupplier\Service\MskManager;

/**
 * This is the factory for IndexController. Its purpose is to instantiate the
 * controller and inject dependencies into it.
 */
class IndexControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $mskManager = $container->get(MskManager::class);
        
        // Instantiate the controller and inject dependencies
        return new IndexController($entityManager, $mskManager);
    }
}