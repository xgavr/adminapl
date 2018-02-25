<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Admin\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Admin\Controller\AplController;
use Admin\Service\AplService;


/**
 * Description of ClientControllerFactory
 *
 * @author Daddy
 */
class AplControllerFactory implements FactoryInterface {
    
    public function __invoke(ContainerInterface $container, 
                     $requestedName, array $options = null)
    {
        $aplService = $container->get(AplService::class);
        
        // Инстанцируем контроллер и внедряем зависимости.
        return new AplController($aplService);
    }
}
