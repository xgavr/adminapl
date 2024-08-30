<?php
namespace Bankapi\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Bankapi\Controller\IndexController;
use Bankapi\Service\Tochka\Authenticate;
use Bankapi\Service\Tochka\Statement;
use Bankapi\Service\Tochka\SbpManager;
use Bankapi\Service\Tochka\Webhook;
use Bankapi\Service\Sber\Statement as SberStatement;

/**
 * This is the factory for IndexController. Its purpose is to instantiate the
 * controller and inject dependencies into it.
 */
class IndexControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $authManager = $container->get(Authenticate::class);
        $statementManager = $container->get(Statement::class);
        $sbpManager = $container->get(SbpManager::class);
        $webhook = $container->get(Webhook::class);
        $sberStatement = $container->get(SberStatement::class);
        
        // Instantiate the controller and inject dependencies
        return new IndexController($authManager, $statementManager, $sbpManager,
                $webhook, $sberStatement);
    }
}