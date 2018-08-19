<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Bankapi\Service\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
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
        
        $sessionContainer = $container->get('ContainerNamespace');
        
        $config = $container->get('config');
        $authParams = $config['bankapi']['tochka'];
        
        // Инстанцируем сервис и внедряем зависимости.
        return new TochkaApi($sessionContainer, $authParams);
    }
}
