<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Zp\Service;

use Zp\Entity\Accrual;
use Company\Entity\Legal;
use Zp\Entity\Position;
use Zp\Entity\Personal;
use Zp\Entity\PersonalAccrual;
use User\Entity\User;
use Zp\Entity\OrderCalculator;
use Zp\Entity\DocCalculator;
use Application\Entity\Order;
use Stock\Entity\Movement;
use Stock\Entity\Vt;

/**
 * Description of ZpCalculator
 * 
 * @author Daddy
 */
class ZpCalculator {
    
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    private $adminManager;

    public function __construct($entityManager, $adminManager)
    {
        $this->entityManager = $entityManager;
        $this->adminManager = $adminManager;
    }
    
    /**
     * Добавить расчет заказа
     * @param Order $order
     * @return OrderCalculator
     */
    public function addOrderCalculator($order)
    {
        $orderCalculator = $this->entityManager->getRepository(OrderCalculator::class)
                ->findOneBy(['docType' => Movement::DOC_ORDER, 'docId' => $order->getId()]);
        
        if ($orderCalculator){
            $orderCalculator->setStatus(OrderCalculator::STATUS_RETIRED);
            $this->entityManager->persist($orderCalculator);
        }    
        
        if ($order->getStatus() == Order::STATUS_SHIPPED){
            
            $base = $this->entityManager->getRepository(Movement::class)
                    ->findBaseAmount(Movement::DOC_ORDER, $order->getId());
            
            if (!$orderCalculator){
                $orderCalculator = new OrderCalculator();
                $orderCalculator->setDateCreated(date('Y-m-d H:i:s'));
            }    
            
            $orderCalculator->setAmount($base['amount']);
            $orderCalculator->setBaseAmount(abs($base['baseAmount']));
            $orderCalculator->setCompany($order->getCompany());
            $orderCalculator->setCourier($order->getSkiper());
            $orderCalculator->setDateOper($order->getDateOper());
            $orderCalculator->setDocType(Movement::DOC_ORDER);
            $orderCalculator->setDocId($order->getId());
            $orderCalculator->setDeliveryAmount($order->getShipmentTotal());
            $orderCalculator->setOffice($order->getOffice());
            $orderCalculator->setOrder($order);
            $orderCalculator->setPayAmount(0);
            $orderCalculator->setShipping($order->getShipping());
            $orderCalculator->setStatus(OrderCalculator::STATUS_ACTIVE);
            $orderCalculator->setUser($order->getUser());

            $this->entityManager->persist($orderCalculator);            
        }
        
        $this->entityManager->flush();
        
        return $orderCalculator;
    }
    
    /**
     * Добавить расчет возврата
     * @param Vt $vt
     * @return OrderCalculator
     */
    public function addVtCalculator($vt)
    {
        $orderCalculator = $this->entityManager->getRepository(OrderCalculator::class)
                ->findOneBy(['docType' => Movement::DOC_VT, 'docId' => $vt->getId()]);
        
        if ($orderCalculator){
            $orderCalculator->setStatus(OrderCalculator::STATUS_RETIRED);
            $this->entityManager->persist($orderCalculator);
        }    
        
        if ($vt->getStatus() == Vt::STATUS_ACTIVE){
            
            $base = $this->entityManager->getRepository(Movement::class)
                    ->findBaseAmount(Movement::DOC_VT, $vt->getId());

            if (!$orderCalculator){
                $orderCalculator = new OrderCalculator();
                $orderCalculator->setDateCreated(date('Y-m-d H:i:s'));
            }    
            
            $orderCalculator->setAmount(-$base['amount']);
            $orderCalculator->setBaseAmount(-abs($base['baseAmount']));
            $orderCalculator->setCompany($vt->getOrder()->getCompany());
            $orderCalculator->setCourier(null);
            $orderCalculator->setDateOper($vt->getDocDate());
            $orderCalculator->setDocType(Movement::DOC_VT);
            $orderCalculator->setDocId($vt->getId());
            $orderCalculator->setDeliveryAmount(0);
            $orderCalculator->setOffice($vt->getOffice());
            $orderCalculator->setOrder($vt->getOrder());
            $orderCalculator->setPayAmount(0);
            $orderCalculator->setShipping(null);
            $orderCalculator->setStatus(OrderCalculator::STATUS_ACTIVE);
            $orderCalculator->setUser($vt->getOrder()->getUser());

            $this->entityManager->persist($orderCalculator);            
        }
        
        $this->entityManager->flush();
        
        return $orderCalculator;
    }
    
    /**
     * Удалить расчет
     * @param Order $order
     * @return null
     */
    public function removeOrderCalculator($order)
    {
        $orderCalculators = $this->entityManager->getRepository(OrderCalculator::class)
                ->findBy(['order' => $order->getId()]);
        foreach ($orderCalculators as $orderCalculator){
            $this->entityManager->remove($orderCalculator);
        }
        
        $this->entityManager->flush();
        
        return;
    }
    
    /**
     * Рассчитать за день
     * @param PersonalAccrual $personalAccrual
     * @param date $calcDate
     */
    public function dateCalculation($personalAccrual, $calcDate)
    {
        
    }
}
