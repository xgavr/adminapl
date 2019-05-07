<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Service\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Admin\Service\ThreadManager;
use Admin\Service\TelegrammManager;

/**
 * Description of ThreadManagerFactory
 *
 * @author Daddy
 */
class ThreadManagerFactory  implements FactoryInterface
{
                   
    public function __invoke(ContainerInterface $container, 
                    $requestedName, array $options = null)
    {
        $telegramManager = $container->get(TelegrammManager::class);
        
        // Инстанцируем сервис и внедряем зависимости.
        return new ThreadManager($telegramManager);
    }
}
