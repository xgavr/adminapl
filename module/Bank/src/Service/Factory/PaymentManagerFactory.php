<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Bank\Service\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Bank\Service\PaymentManager;
use Bankapi\Service\Tochka\Payment;
use Admin\Service\LogManager;


/**
 * Description of PaymentManagerFactory
 *
 * @author Daddy
 */
class PaymentManagerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, 
                    $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $tochkaPayment = $container->get(Payment::class);
        $logManager = $container->get(LogManager::class);
        
        // Инстанцируем сервис и внедряем зависимости.
        return new PaymentManager($entityManager, $tochkaPayment, $logManager);
    }
}
