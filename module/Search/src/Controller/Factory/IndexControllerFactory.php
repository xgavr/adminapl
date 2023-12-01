<?php
namespace Search\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Search\Controller\IndexController;
use Search\Service\SearchManager;

/**
 * This is the factory for IndexController. Its purpose is to instantiate the
 * controller and inject dependencies into it.
 */
class IndexControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $searchManager = $container->get(SearchManager::class);
        
        // Instantiate the controller and inject dependencies
        return new IndexController($entityManager, $searchManager);
    }
}