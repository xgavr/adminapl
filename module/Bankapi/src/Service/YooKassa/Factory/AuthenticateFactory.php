<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Bankapi\Service\YooKassa\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * Description of AuthenticateFactory
 *
 * @author Daddy
 */
class AuthenticateFactory  implements FactoryInterface
{
                   
    public function __invoke(ContainerInterface $container, 
                    $requestedName, array $options = null)
    {
        
        $config = $container->get('config');
        $authParams = $config['bankapi']['sber'];
        
        // Инстанцируем сервис и внедряем зависимости.
        return new Authenticate($authParams);
    }
}
