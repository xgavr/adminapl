<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Service\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Application\Service\BillManager;
use Admin\Service\PostManager;
use Stock\Service\PtuManager;
use Application\Service\AssemblyManager;

/**
 * Description of BillManagerFactory
 *
 * @author Daddy
 */
class BillManagerFactory  implements FactoryInterface
{
                   
    public function __invoke(ContainerInterface $container, 
                    $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $postManager = $container->get(PostManager::class);
        $ptuManager = $container->get(PtuManager::class);
        $assemblyManager = $container->get(AssemblyManager::class);
        
        // Инстанцируем сервис и внедряем зависимости.
        return new BillManager($entityManager, $postManager, $ptuManager, $assemblyManager);
    }
}
