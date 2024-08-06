<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Bankapi\Service\Sber\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Bankapi\Service\Tochka\Authenticate;
use Bankapi\Service\Tochka\Webhook;

/**
 * Description of WebhookFactory
 *
 * @author Daddy
 */
class WebhookFactory  implements FactoryInterface
{
                   
    public function __invoke(ContainerInterface $container, 
                    $requestedName, array $options = null)
    {
        
        $auth = $container->get(Authenticate::class);
        
        // Инстанцируем сервис и внедряем зависимости.
        return new Webhook($auth);
    }
}
