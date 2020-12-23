<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Service\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Admin\Service\HelloManager;
use Admin\Service\PostManager;
use Admin\Service\TelegrammManager;
use Admin\Service\AplService;
use Admin\Service\AdminManager;

/**
 * Description of HelloManagerFactory
 *
 * @author Daddy
 */
class HelloManagerFactory  implements FactoryInterface
{
                   
    public function __invoke(ContainerInterface $container, 
                    $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $postManager = $container->get(PostManager::class);
        $telegrammManager = $container->get(TelegrammManager::class);
        $aplService = $container->get(AplService::class);
        $adminManager = $container->get(AdminManager::class);
        // Инстанцируем сервис и внедряем зависимости.
        return new HelloManager($entityManager, $postManager, $telegrammManager, $aplService, $adminManager);
    }
}
