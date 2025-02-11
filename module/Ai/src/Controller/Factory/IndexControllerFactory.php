<?php
namespace Ai\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Ai\Controller\IndexController;
use Ai\Service\GigaManager;
use Ai\Service\DeepseekManager;

/**
 * This is the factory for IndexController. Its purpose is to instantiate the
 * controller and inject dependencies into it.
 */
class IndexControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $gigaManager = $container->get(GigaManager::class);
        $deepseekManager = $container->get(DeepseekManager::class);
        
        // Instantiate the controller and inject dependencies
        return new IndexController($entityManager, $gigaManager, $deepseekManager);
    }
}