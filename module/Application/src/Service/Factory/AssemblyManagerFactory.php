<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Service\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Application\Service\AssemblyManager;
use Application\Service\ArticleManager;
use Application\Service\MlManager;
use Application\Service\ProducerManager;
/**
 * Description of AssemblyManagerFactory
 *
 * @author Daddy
 */
class AssemblyManagerFactory  implements FactoryInterface
{
                   
    public function __invoke(ContainerInterface $container, 
                    $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $articleManager = $container->get(ArticleManager::class);
        $mlManager = $container->get(MlManager::class);
        $producerManager = $container->get(ProducerManager::class);
        
        // Инстанцируем сервис и внедряем зависимости.
        return new AssemblyManager($entityManager, $articleManager, $mlManager, $producerManager);
    }
}
