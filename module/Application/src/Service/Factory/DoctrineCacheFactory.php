<?php
namespace Application\Service\Factory;

use Interop\Container\ContainerInterface;
use Doctrine\Common\Cache\MemcacheCache;
use Doctrine\Common\Cache\MemcachedCache;

/**
 * This is the factory class for RbacManager service. The purpose of the factory
 * is to instantiate the service and pass it dependencies (inject dependencies).
 */
class DoctrineCacheFactory
{
    /**
     * This method creates the RbacManager service and returns its instance. 
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {        
        if (extension_loaded('memcached')){ //если отладка не на локальной машине
           $memcached = new \Memcached();
           $memcached->addServer('127.0.0.1', 11211);
           
           $cache = new MemcachedCache();
           $cache->setMemcached($memcached);
           
        } else {    
            $memcache = new \Memcache();
            $memcache->connect('127.0.0.1', 11211);

            $cache = new MemcacheCache();
            $cache->setMemcache($memcache);
        }    
                
        return $cache;
    }
}

