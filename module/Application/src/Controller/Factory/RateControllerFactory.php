<?php
namespace Application\Controller\Factory;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Application\Controller\RateController;
use Application\Service\RateManager;
/**
 * This is the factory for RateController. Its purpose is to instantiate the
 * controller and inject dependencies into it.
 */
class RateControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $rateManager = $container->get(RateManager::class);
        
        // Instantiate the controller and inject dependencies
        return new RateController($entityManager, $rateManager);
    }
}