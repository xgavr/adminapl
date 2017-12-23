<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Application\Controller\ShopController;
use Application\Service\ShopManager;
use Application\Service\GoodsManager;


/**
 * Description of ShopControllerFactory
 *
 * @author Daddy
 */
class ShopControllerFactory implements FactoryInterface {
    
    public function __invoke(ContainerInterface $container, 
                     $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $shopManager = $container->get(ShopManager::class);
        $goodsManager = $container->get(GoodsManager::class);
        
        // Инстанцируем контроллер и внедряем зависимости.
        return new ShopController($entityManager, $shopManager, $goodsManager);
    }
}
