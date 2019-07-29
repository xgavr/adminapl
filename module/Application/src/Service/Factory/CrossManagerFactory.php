<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Service\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Application\Service\CrossManager;
use Application\Service\ProducerManager;
use Application\Service\GoodsManager;
use Admin\Service\PostManager;
use Admin\Service\AdminManager;

/**
 * Description of CrossManagerFactory
 *
 * @author Daddy
 */
class CrossManagerFactory  implements FactoryInterface
{
                   
    public function __invoke(ContainerInterface $container, 
                    $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $producerManager = $container->get(ProducerManager::class);
        $goodManager = $container->get(GoodsManager::class);
        $postManager = $container->get(PostManager::class);
        $adminManager = $container->get(AdminManager::class);
        
        // Инстанцируем сервис и внедряем зависимости.
        return new CrossManager($entityManager, $producerManager, $goodManager, $postManager, $adminManager);
    }
}
