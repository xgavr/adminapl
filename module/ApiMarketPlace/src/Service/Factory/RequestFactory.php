<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ApiMarketPlace\Service\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use ApiMarketPlace\Service\Update;

/**
 * Description of RequestFactory
 *
 * @author Daddy
 */
class RequestFactory  implements FactoryInterface
{
                   
    public function __invoke(ContainerInterface $container, 
                    $requestedName, array $options = null)
    {
        $updateManager = $container->get(Update::class);
        
        // Инстанцируем сервис и внедряем зависимости.
        return new Request($updateManager);
    }
}
