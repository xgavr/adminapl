<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Admin\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Admin\Controller\SoapController;
use Admin\Service\SoapManager;


/**
 * Description of SoapControllerFactory
 *
 * @author Daddy
 */
class SoapControllerFactory implements FactoryInterface {
    
    public function __invoke(ContainerInterface $container, 
                     $requestedName, array $options = null)
    {
        $soapManager = $container->get(SoapManager::class);
        
        // Инстанцируем контроллер и внедряем зависимости.
        return new SoapController($soapManager);
    }
}
