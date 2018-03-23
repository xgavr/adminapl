<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Service\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Admin\Service\AutoruManager;
use Admin\Service\PostManager;

/**
 * Description of AutoruManagerFactory
 *
 * @author Daddy
 */
class AutoruManagerFactory  implements FactoryInterface
{
                   
    public function __invoke(ContainerInterface $container, 
                    $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $postManager = $container->get(PostManager::class);
        // Инстанцируем сервис и внедряем зависимости.
        return new AutoruManager($entityManager, $postManager);
    }
}
