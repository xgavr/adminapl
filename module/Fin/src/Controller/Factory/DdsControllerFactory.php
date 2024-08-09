<?php
namespace Fin\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Fin\Controller\DdsController;
use Fin\Service\DdsManager;

/**
 * This is the factory for DdsController. Its purpose is to instantiate the
 * controller and inject dependencies into it.
 */
class DdsControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $ddsManager = $container->get(DdsManager::class);
        
        // Instantiate the controller and inject dependencies
        return new DdsController($entityManager, $ddsManager);
    }
}