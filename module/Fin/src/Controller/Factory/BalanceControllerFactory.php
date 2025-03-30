<?php
namespace Fin\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Fin\Controller\BalanceController;
use Fin\Service\BalanceManager;
use Fin\Service\DdsManager;

/**
 * This is the factory for BalanceController. Its purpose is to instantiate the
 * controller and inject dependencies into it.
 */
class BalanceControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $ddsManager = $container->get(DdsManager::class);
        $balanceManager = $container->get(BalanceManager::class);
        
        // Instantiate the controller and inject dependencies
        return new BalanceController($entityManager, $ddsManager, $balanceManager);
    }
}