<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Admin\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Admin\Controller\ProcessingController;
use Admin\Service\PostManager;
use Admin\Service\AutoruManager;
use Admin\Service\TelegrammManager;


/**
 * Description of ClientControllerFactory
 *
 * @author Daddy
 */
class ProcessingControllerFactory implements FactoryInterface {
    
    public function __invoke(ContainerInterface $container, 
                     $requestedName, array $options = null)
    {
        $postManager = $container->get(PostManager::class);
        $autoruManager = $container->get(AutoruManager::class);
        $telegramManager = $container->get(TelegrammManager::class);
        
        // Инстанцируем контроллер и внедряем зависимости.
        return new ProcessingController($postManager, $autoruManager, $telegramManager);
    }
}
