<?php
namespace Cash\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Cash\Controller\TillController;
use Cash\Service\CashManager;

/**
 * This is the factory for IndexController. Its purpose is to instantiate the
 * controller and inject dependencies into it.
 */
class TillControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $cashManager = $container->get(CashManager::class);
        
        // Instantiate the controller and inject dependencies
        return new TillController($entityManager, $cashManager);
    }
}