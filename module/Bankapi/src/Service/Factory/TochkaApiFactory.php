<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Bankapi\Service\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\Session\SessionManager;
use Bankapi\Service\TochkaApi;

/**
 * Description of ShopManagerFactory
 *
 * @author Daddy
 */
class TochkaApiFactory  implements FactoryInterface
{
                   
    public function __invoke(ContainerInterface $container, 
                    $requestedName, array $options = null)
    {
        
        $sessionManager = $container->get(SessionManager::class);
        
        $config = $container->get('config');
        $authParams = $config['bankapi']['tochka'];
        
        // Инстанцируем сервис и внедряем зависимости.
        return new TochkaApi($sessionManager, $authParams);
    }
}
