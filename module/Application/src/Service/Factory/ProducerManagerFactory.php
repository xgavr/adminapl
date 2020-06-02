<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Service\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Application\Service\ProducerManager;
use Application\Service\GoodsManager;

/**
 * Description of PbManagerFactory
 *
 * @author Daddy
 */
class ProducerManagerFactory  implements FactoryInterface
{
                   
    public function __invoke(ContainerInterface $container, 
                    $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $goodsManager = $container->get(GoodsManager::class);
        // Инстанцируем сервис и внедряем зависимости.
        return new ProducerManager($entityManager, $goodsManager);
    }
}
