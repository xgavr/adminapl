<?php
namespace Stock\Service\Factory;

use Interop\Container\ContainerInterface;
use Stock\Service\VtManager;
use Admin\Service\LogManager;
use Application\Service\OrderManager;
use Admin\Service\AdminManager;
use Zp\Service\ZpCalculator;
use Cash\Service\CashManager;
use GoodMap\Service\FoldManager;

/**
 * This is the factory class for VtManager service. The purpose of the factory
 * is to instantiate the service and pass it dependencies (inject dependencies).
 */
class VtManagerFactory
{
    /**
     * This method creates the UserManager service and returns its instance. 
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {        
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $logManager = $container->get(LogManager::class);
        $orderManager = $container->get(OrderManager::class);
        $adminManager = $container->get(AdminManager::class);
        $zpManager = $container->get(ZpCalculator::class);
        $cashManager = $container->get(CashManager::class);
        $foldManager = $container->get(FoldManager::class);
                        
        return new VtManager($entityManager, $logManager, $orderManager, $adminManager,
                $zpManager, $cashManager, $foldManager);
    }
}
