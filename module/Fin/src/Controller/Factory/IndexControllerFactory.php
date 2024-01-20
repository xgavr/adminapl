<?php
namespace Fin\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Fin\Controller\IndexController;
use Fin\Service\FinManager;

/**
 * This is the factory for IndexController. Its purpose is to instantiate the
 * controller and inject dependencies into it.
 */
class IndexControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $finManager = $container->get(FinManager::class);
        
        // Instantiate the controller and inject dependencies
        return new IndexController($entityManager, $finManager);
    }
}