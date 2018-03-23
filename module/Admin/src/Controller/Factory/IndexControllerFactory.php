<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Admin\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Admin\Controller\IndexController;
use Admin\Service\TelegrammManager;


/**
 * Description of IndexControllerFactory
 *
 * @author Daddy
 */
class IndexControllerFactory implements FactoryInterface {
    
    public function __invoke(ContainerInterface $container, 
                     $requestedName, array $options = null)
    {
        $telegrammManager = $container->get(TelegrammManager::class);
        
        // Инстанцируем контроллер и внедряем зависимости.
        return new IndexController($telegrammManager);
    }
}
