<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Service\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Application\Service\MarketManager;
use Admin\Service\FtpManager;
/**
 * Description of MarketManagerFactory
 *
 * @author Daddy
 */
class MarketManagerFactory  implements FactoryInterface
{
                   
    public function __invoke(ContainerInterface $container, 
                    $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $ftpManager = $container->get(FtpManager::class);
        
        if (!file_exists('./config/development.config.php')){ //если не отладка на локальной машине
            $cache  = $container->get('default_cache');
        } else {    
            $cache = $container->get('FilesystemCache');
        }         
        
        // Инстанцируем сервис и внедряем зависимости.
        return new MarketManager($entityManager, $ftpManager, $cache);
    }
}
