<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Bank\Service\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Bank\Service\BankManager;
use Bankapi\Service\Tochka\Statement;
use Admin\Service\AdminManager;
use Admin\Service\PostManager;
use Company\Service\CostManager;
use Ai\Service\GigaManager;


/**
 * Description of BankManagerFactory
 *
 * @author Daddy
 */
class BankManagerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, 
                    $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $tochkaStatement = $container->get(Statement::class);
        $adminManager = $container->get(AdminManager::class);
        $postManager = $container->get(PostManager::class);
        $costManager = $container->get(CostManager::class);
        $gigaManager = $container->get(GigaManager::class);
        
        // Инстанцируем сервис и внедряем зависимости.
        return new BankManager($entityManager, $tochkaStatement, $adminManager,
                $postManager, $costManager, $gigaManager);
    }
}
