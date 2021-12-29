<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Admin\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Admin\Controller\AplController;
use Admin\Service\AplService;
use Admin\Service\AplBankService;
use Admin\Service\AplDocService;
use Admin\Service\AplOrderService;
use Admin\Service\AplCashService;


/**
 * Description of ClientControllerFactory
 *
 * @author Daddy
 */
class AplControllerFactory implements FactoryInterface {
    
    public function __invoke(ContainerInterface $container, 
                     $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $aplService = $container->get(AplService::class);
        $aplBankService = $container->get(AplBankService::class);
        $aplDocService = $container->get(AplDocService::class);
        $aplOrderService = $container->get(AplOrderService::class);
        $aplCashService = $container->get(AplCashService::class);
        
        // Инстанцируем контроллер и внедряем зависимости.
        return new AplController($entityManager, $aplService, $aplBankService, 
                $aplDocService, $aplOrderService, $aplCashService);
    }
}
