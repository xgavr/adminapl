<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Service\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Application\Service\PriceManager;
use Admin\Service\PostManager;
use Admin\Service\FtpManager;
/**
 * Description of PbManagerFactory
 *
 * @author Daddy
 */
class PriceManagerFactory  implements FactoryInterface
{
                   
    public function __invoke(ContainerInterface $container, 
                    $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $postManager = $container->get(PostManager::class);
        $ftpManager = $container->get(FtpManager::class);
        
        // Инстанцируем сервис и внедряем зависимости.
        return new PriceManager($entityManager, $postManager, $ftpManager);
    }
}
