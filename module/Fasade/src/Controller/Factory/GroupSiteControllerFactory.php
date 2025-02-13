<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Fasade\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Fasade\Controller\GroupSiteController;
use Fasade\Service\GroupSiteManager;


/**
 * Description of GroupSiteControllerFactory
 *
 * @author Daddy
 */
class GroupSiteControllerFactory implements FactoryInterface {
    
    public function __invoke(ContainerInterface $container, 
                     $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $groupSiteManager = $container->get(GroupSiteManager::class);
        
        // Инстанцируем контроллер и внедряем зависимости.
        return new GroupSiteController($entityManager, $groupSiteManager);
    }
}
