<?php
namespace Bankapi\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Bankapi\Controller\IndexController;
use Bankapi\Service\Tochka\TochkaApi;
use Bankapi\Service\Tochka\Authenticate;
use Bankapi\Service\Tochka\Statement;
/**
 * This is the factory for IndexController. Its purpose is to instantiate the
 * controller and inject dependencies into it.
 */
class IndexControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $tochkaApi = $container->get(TochkaApi::class);
        $authManager = $container->get(Authenticate::class);
        $statementMaanger = $container->get(Statement::class);
        
        // Instantiate the controller and inject dependencies
        return new IndexController($tochkaApi, $authmanager, $statementMaanger);
    }
}