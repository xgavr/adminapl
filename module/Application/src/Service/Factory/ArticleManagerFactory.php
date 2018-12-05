<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Service\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Application\Service\ArticleManager;
use Application\Service\NameManager;
use Application\Service\OemManager;

/**
 * Description of PbManagerFactory
 *
 * @author Daddy
 */
class ArticleManagerFactory  implements FactoryInterface
{
                   
    public function __invoke(ContainerInterface $container, 
                    $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $nameManager = $container->get(NameManager::class);
        $oemManager = $container->get(OemManager::class);
        
        // Инстанцируем сервис и внедряем зависимости.
        return new ArticleManager($entityManager, $nameManager, $oemManager);
    }
}
