<?php
namespace Company\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Company\Controller\TaxController;
use Company\Service\TaxManager;

/**
 * This is the factory for TaxController. Its purpose is to instantiate the
 * controller and inject dependencies into it.
 */
class TaxControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $taxManager = $container->get(TaxManager::class);
        
        // Instantiate the controller and inject dependencies
        return new TaxController($entityManager, $taxManager);
    }
}

