<?php
namespace Zp\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Zp\Controller\ReviseController;
use Zp\Service\ZpCalculator;

/**
 * This is the factory for ReviseController. Its purpose is to instantiate the
 * controller and inject dependencies into it.
 */
class ReviseControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $zpCalculator = $container->get(ZpCalculator::class);
        
        // Instantiate the controller and inject dependencies
        return new ReviseController($entityManager, $zpCalculator);
    }
}