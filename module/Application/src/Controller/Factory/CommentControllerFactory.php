<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Application\Controller\CommentController;
use Application\Service\CommentManager;

/**
 * Description of CommentControllerFactory
 *
 * @author Daddy
 */
class CommentControllerFactory implements FactoryInterface {
    
    public function __invoke(ContainerInterface $container, 
                     $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $commentManager = $container->get(CommentManager::class);
        
        // Инстанцируем контроллер и внедряем зависимости.
        return new CommentController($entityManager, $commentManager);
    }
}
