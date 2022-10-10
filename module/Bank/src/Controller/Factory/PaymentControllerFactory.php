<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Bank\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Bank\Controller\PaymentController;
use Bank\Service\PaymentManager;


/**
 * Description of PaymentControllerFactory
 *
 * @author Daddy
 */
class PaymentControllerFactory implements FactoryInterface {
    
    public function __invoke(ContainerInterface $container, 
                     $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $paymentManager = $container->get(PaymentManager::class);
        
        // Инстанцируем контроллер и внедряем зависимости.
        return new PaymentController($entityManager, $paymentManager);
    }
}
