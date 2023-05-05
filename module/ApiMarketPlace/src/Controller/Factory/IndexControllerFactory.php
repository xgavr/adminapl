<?php
namespace ApiMarketPlace\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use ApiMarketPlace\Controller\IndexController;
use ApiMarketPlace\Service\SberMarket;
use ApiMarketPlace\Service\MarketplaceService;
use ApiMarketPlace\Service\OzonService;
use ApiMarketPlace\Service\ReportManager;

/**
 * This is the factory for IndexController. Its purpose is to instantiate the
 * controller and inject dependencies into it.
 */
class IndexControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $sbermarketManager = $container->get(SberMarket::class);
        $marketplaceService = $container->get(MarketplaceService::class);
        $ozonService = $container->get(OzonService::class);
        $reportManager = $container->get(ReportManager::class);
        
        // Instantiate the controller and inject dependencies
        return new IndexController($entityManager, $sbermarketManager, $marketplaceService, 
                $ozonService, $reportManager);
    }
}