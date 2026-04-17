<?php
namespace Stock\Service\Factory;

use Interop\Container\ContainerInterface;
use Stock\Service\MarkManager;
use Admin\Service\LogManager;
use Admin\Service\AdminManager;

/**
 * This is the factory class for MarkManager service. The purpose of the factory
 * is to instantiate the service and pass it dependencies (inject dependencies).
 */
class MarkManagerFactory
{
    /**
     * This method creates the UserManager service and returns its instance. 
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {        
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $logManager = $container->get(LogManager::class);
        $adminManager = $container->get(AdminManager::class);
        
        if (!file_exists('./config/development.config.php')){ //если не отладка на локальной машине
            $cache  = $container->get('default_cache');
        } else {    
            $cache = $container->get('FilesystemCache');
        }         
                        
        return new MarkManager($entityManager, $logManager, $adminManager, $cache);
    }
}
