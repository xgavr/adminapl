<?php
namespace Zp\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Zp\Controller\IndexController;
use Zp\Service\ZpManager;
use Zp\Service\ZpCalculator;
use Admin\Service\LogManager;
use User\Service\RbacManager;

/**
 * This is the factory for IndexController. Its purpose is to instantiate the
 * controller and inject dependencies into it.
 */
class IndexControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $zpManager = $container->get(ZpManager::class);
        $zpCalculator = $container->get(ZpCalculator::class);
        $logManager = $container->get(LogManager::class);
        $rbacManager = $container->get(RbacManager::class);
        
        // Instantiate the controller and inject dependencies
        return new IndexController($entityManager, $zpManager, $zpCalculator, 
                $logManager, $rbacManager);
    }
}