<?php
namespace Cash\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Cash\Controller\UserController;
use Cash\Service\CashManager;

/**
 * This is the factory for UserController. Its purpose is to instantiate the
 * controller and inject dependencies into it.
 */
class UserControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $cashManager = $container->get(CashManager::class);
        
        // Instantiate the controller and inject dependencies
        return new UserController($entityManager, $cashManager);
    }
}