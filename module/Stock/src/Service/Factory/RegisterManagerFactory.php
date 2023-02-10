<?php
namespace Stock\Service\Factory;

use Interop\Container\ContainerInterface;
use Stock\Service\RegisterManager;
use Admin\Service\LogManager;
use Stock\Service\OtManager;
use Stock\Service\PtManager;
use Stock\Service\PtuManager;
use Stock\Service\StManager;
use Stock\Service\VtManager;
use Stock\Service\VtpManager;
use Application\Service\OrderManager;
use Cash\Service\CashManager;
use Stock\Service\ReviseManager;

/**
 * This is the factory class for RegisterManager service. The purpose of the factory
 * is to instantiate the service and pass it dependencies (inject dependencies).
 */
class RegisterManagerFactory
{
    /**
     * This method creates the UserManager service and returns its instance. 
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {        
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $logManager = $container->get(LogManager::class);
        $otManager = $container->get(OtManager::class);
        $ptManager = $container->get(PtManager::class);
        $ptuManager = $container->get(PtuManager::class);
        $stManager = $container->get(StManager::class);
        $vtManager = $container->get(VtManager::class);
        $vtpManager = $container->get(VtpManager::class);
        $orderManager = $container->get(OrderManager::class);
        $cashManager = $container->get(CashManager::class);
        $reviseManager = $container->get(ReviseManager::class);
                        
        return new RegisterManager($entityManager, $logManager, $otManager,
                $ptManager, $ptuManager, $stManager, $vtManager, $vtpManager,
                $orderManager, $cashManager, $reviseManager);
    }
}
