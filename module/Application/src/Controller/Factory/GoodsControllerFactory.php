<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Application\Controller\GoodsController;
use Application\Service\GoodsManager;
use Application\Service\AssemblyManager;
use Application\Service\ArticleManager;
use Application\Service\NameManager;
use Application\Service\ExternalManager;
use Application\Service\RateManager;
use Admin\Service\LogManager;

/**
 * Description of GoodsControllerFactory
 *
 * @author Daddy
 */
class GoodsControllerFactory implements FactoryInterface {
    
    public function __invoke(ContainerInterface $container, 
                     $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $goodsManager = $container->get(GoodsManager::class);
        $assemblyManager = $container->get(AssemblyManager::class);
        $articleManager = $container->get(ArticleManager::class);
        $nameManager = $container->get(NameManager::class);
        $externalManager = $container->get(ExternalManager::class);
        $rateManager = $container->get(RateManager::class);
        $logManager = $container->get(LogManager::class);
        
        // Инстанцируем контроллер и внедряем зависимости.
        return new GoodsController($entityManager, $goodsManager, $assemblyManager, 
                $articleManager, $nameManager, $externalManager, $rateManager,
                $logManager);
    }
}
