<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Bankapi\Service\Tochka\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Bankapi\Service\Tochka\Authenticate;

/**
 * Description of ShopManagerFactory
 *
 * @author Daddy
 */
class AuthenticateFactory  implements FactoryInterface
{
                   
    public function __invoke(ContainerInterface $container, 
                    $requestedName, array $options = null)
    {
        
        $config = $container->get('config');
        $authParams = $config['bankapi']['tochka'];
        
        // Инстанцируем сервис и внедряем зависимости.
        return new Authenticate($authParams);
    }
}
