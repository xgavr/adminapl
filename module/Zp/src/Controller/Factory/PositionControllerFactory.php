<?php
namespace Zp\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Zp\Controller\PositionController;
use Zp\Service\ZpManager;

/**
 * This is the factory for PositionController. Its purpose is to instantiate the
 * controller and inject dependencies into it.
 */
class PositionControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $zpManager = $container->get(ZpManager::class);
        
        // Instantiate the controller and inject dependencies
        return new PositionController($entityManager, $zpManager);
    }
}