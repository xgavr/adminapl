<?php
namespace Fasade\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Fasade\Controller\IndexController;
use Fasade\Service\FasadeManager;

/**
 * This is the factory for IndexController. Its purpose is to instantiate the
 * controller and inject dependencies into it.
 */
class IndexControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $fasadeManager = $container->get(FasadeManager::class);
        
        // Instantiate the controller and inject dependencies
        return new IndexController($entityManager, $fasadeManager);
    }
}