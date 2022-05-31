<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Admin\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Admin\Controller\PostController;
use Admin\Service\PostManager;
use Admin\Service\AutoruManager;
use Admin\Service\HelloManager;
use Admin\Service\AdminManager;


/**
 * Description of ClientControllerFactory
 *
 * @author Daddy
 */
class PostControllerFactory implements FactoryInterface {
    
    public function __invoke(ContainerInterface $container, 
                     $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');        
        $postManager = $container->get(PostManager::class);
        $autoruManager = $container->get(AutoruManager::class);
        $helloManager = $container->get(HelloManager::class);
        $adminManager = $container->get(AdminManager::class);
        
        // Инстанцируем контроллер и внедряем зависимости.
        return new PostController($entityManager, $postManager, $autoruManager, $helloManager, $adminManager);
    }
}
