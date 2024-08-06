<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Bankapi\Service\YooKassa\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Bankapi\Service\YooKassa\Authenticate;
use Bankapi\Service\YooKassa\SbpManager;

/**
 * Description of SbpManagerFactory
 *
 * @author Daddy
 */
class SbpManagerFactory  implements FactoryInterface
{
                   
    public function __invoke(ContainerInterface $container, 
                    $requestedName, array $options = null)
    {
        
        $auth = $container->get(Authenticate::class);
        
        // Инстанцируем сервис и внедряем зависимости.
        return new SbpManager($auth);
    }
}
