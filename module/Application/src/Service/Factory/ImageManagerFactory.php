<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Service\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Application\Service\ImageManager;
use Admin\Service\PostManager;
use Admin\Service\FtpManager;
use Admin\Service\AdminManager;
/**
 * Description of ImageManagerFactory
 *
 * @author Daddy
 */
class ImageManagerFactory  implements FactoryInterface
{
                   
    public function __invoke(ContainerInterface $container, 
                    $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $postManager = $container->get(PostManager::class);
        $ftpManager = $container->get(FtpManager::class);
        $adminManager = $container->get(AdminManager::class);
        
        // Инстанцируем сервис и внедряем зависимости.
        return new ImageManager($entityManager, $postManager, $ftpManager, $adminManager);
    }
}
