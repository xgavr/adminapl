<?php
namespace Application\Service\Factory;

use Interop\Container\ContainerInterface;
use Doctrine\Common\Cache\MemcacheCache;
use Doctrine\Common\Cache\MemcachedCache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\RedisCache;

/**
 * This is the factory class for RbacManager service. The purpose of the factory
 * is to instantiate the service and pass it dependencies (inject dependencies).
 */
class DoctrineCacheFactory
{
    /**
     * This method creates the service and returns its instance. 
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {        
//        if (extension_loaded('redis')) {
//            $redis = new \Redis();
//            $redis->connect('127.0.0.1', 6379);
//            // Важно: выбираем БД, отличную от сессий (например, 1), 
//            // чтобы очистка кэша не удалила сессии пользователей
//            $redis->select(1); 
//            
//            $cache = new RedisCache();
//            $cache->setRedis($redis);
//            // Добавляем префикс, как было в Memcached
//            $cache->setNamespace('_admin_apl_');
//            
//            return $cache;
//        }
        
        if (extension_loaded('memcached')){ //если отладка не на локальной машине
           $memcached = new \Memcached();
           $memcached->addServer('127.0.0.1', 11211);
           $memcached->setOption(\Memcached::OPT_PREFIX_KEY, '_admin_apl_');
           
           $cache = new MemcachedCache();
           $cache->setMemcached($memcached);
           
        } elseif(extension_loaded('memcache')) {    
            $memcache = new \Memcache();
            $memcache->connect('127.0.0.1', 11211);

            $cache = new MemcacheCache();
            $cache->setMemcache($memcache);
        } else {
            $cache = new ArrayCache();
        }   
                
        return $cache;
    }
}

