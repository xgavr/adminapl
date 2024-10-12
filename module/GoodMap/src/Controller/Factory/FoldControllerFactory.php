<?php
namespace GoodMap\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use GoodMap\Controller\FoldController;
use GoodMap\Service\GoodMapManager;
use GoodMap\Service\FoldManager;

/**
 * This is the factory for FoldController. Its purpose is to instantiate the
 * controller and inject dependencies into it.
 */
class FoldControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $goodMapManager = $container->get(GoodMapManager::class);
        $foldManager = $container->get(FoldManager::class);
        
        // Instantiate the controller and inject dependencies
        return new FoldController($entityManager, $goodMapManager, $foldManager);
    }
}