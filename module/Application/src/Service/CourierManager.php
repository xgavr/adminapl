<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service;

use Laminas\ServiceManager\ServiceManager;
use Application\Entity\Courier;
use Application\Entity\Shipping;
use Company\Entity\Office;
use Application\Entity\Order;

/**
 * Description of CourierService
 *
 * @author Daddy
 */
class CourierManager
{
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
  
    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    /**
     * Добавить перевозчика
     * @param array $data
     * @return Courier
     */
    public function addCourier($data)
    {
        $courier = new Courier();
        $courier->setAplId(!empty($data['aplId']) ? $data['aplId'] : null);
        $courier->setName(!empty($data['name']) ? $data['name'] : null);
        $courier->setComment(!empty($data['comment']) ? $data['comment'] : null);
        $courier->setSite(!empty($data['site']) ? $data['site'] : null);
        $courier->setTrack(!empty($data['track']) ? $data['track'] : null);
        $courier->setCalculator(!empty($data['calculator']) ? $data['calculator'] : null);
        $courier->setStatus(!empty($data['status']) ? $data['status'] : Courier::STATUS_ACTIVE);
        
        $this->entityManager->persist($courier);
        $this->entityManager->flush();
        
        return $courier;
    }
    
    /**
     * Обновить перевозчика
     * @param Courier $courier
     * @param array $data
     * @return Courier
     */
    public function updateCourier($courier, $data)
    {
        $courier->setAplId(!empty($data['aplId']) ? $data['aplId'] : null);
        $courier->setName(!empty($data['name']) ? $data['name'] : null);
        $courier->setComment(!empty($data['comment']) ? $data['comment'] : null);
        $courier->setSite(!empty($data['site']) ? $data['site'] : null);
        $courier->setTrack(!empty($data['track']) ? $data['track'] : null);
        $courier->setCalculator(!empty($data['calculator']) ? $data['calculator'] : null);
        $courier->setStatus(!empty($data['status']) ? $data['status'] : Courier::STATUS_ACTIVE);
        
        $this->entityManager->persist($courier);
        $this->entityManager->flush($courier);
        
        return $courier;
    }
    
    /**
     * Удалить перевзчика
     * @param Courier $courier
     */
    public function removeCourier($courier)
    {
        $orders = $this->entityManager->getRepository(Order::class)
                ->count(['courier' => $courier->getId()]);
        if ($orders){
            return FALSE;
        }
        $this->entityManager->remove($courier);
        $this->entityManager->flush();
        return true;
    }
    
    /**
     * Добавить доставку
     * @param Office $office 
     * @param array $data
     * @return Shipping
     */
    public function addShipping($office, $data)
    {
        $shipping = new Shipping();
        $shipping->setAplId(!empty($data['aplId']) ? $data['aplId'] : null);
        $shipping->setName(!empty($data['name']) ? $data['name'] : null);
        $shipping->setComment(!empty($data['comment']) ? $data['comment'] : null);
        $shipping->setRateDistance(!empty($data['rateDistance']) ? $data['rateDistance'] : null);
        $shipping->setRateTrip(!empty($data['rateTrip']) ? $data['rateTrip'] : null);
        $shipping->setRateTrip(!empty($data['rateTrip1']) ? $data['rateTrip1'] : null);
        $shipping->setRateTrip(!empty($data['rateTrip2']) ? $data['rateTrip2'] : null);
        $shipping->setSorting(!empty($data['sorting']) ? $data['sorting'] : 0);
        $shipping->setStatus(!empty($data['status']) ? $data['status'] : Shipping::STATUS_ACTIVE);
        $shipping->setRate(!empty($data['rate']) ? $data['rate'] : Shipping::RATE_TRIP);
        
        $shipping->setOffice($office);
        
        $this->entityManager->persist($shipping);
        $this->entityManager->flush();
        
        return $shipping;
    }
    
    /**
     * Обновить доставку
     * @param Shipping $shipping
     * @param array $data
     * @return Shipping
     */
    public function updateShipping($shipping, $data)
    {
        $shipping->setAplId(!empty($data['aplId']) ? $data['aplId'] : null);
        $shipping->setName(!empty($data['name']) ? $data['name'] : null);
        $shipping->setComment(!empty($data['comment']) ? $data['comment'] : null);
        $shipping->setRateDistance(!empty($data['rateDistance']) ? $data['rateDistance'] : null);
        $shipping->setRateTrip(!empty($data['rateTrip']) ? $data['rateTrip'] : null);
        $shipping->setRateTrip(!empty($data['rateTrip1']) ? $data['rateTrip1'] : null);
        $shipping->setRateTrip(!empty($data['rateTrip2']) ? $data['rateTrip2'] : null);
        $shipping->setSorting(!empty($data['sorting']) ? $data['sorting'] : null);
        $shipping->setStatus(!empty($data['status']) ? $data['status'] : Shipping::STATUS_ACTIVE);
        $shipping->setRate(!empty($data['rate']) ? $data['rate'] : Shipping::RATE_TRIP);
        
        $this->entityManager->persist($shipping);
        $this->entityManager->flush($shipping);
        
        return $shipping;
    }
    
    /**
     * Изменить сортировку
     * 
     * @param Shipping $shipping
     * @param integer $delta
     */
    public function changeSorting($shipping, $delta)
    {
        $newSort = $shipping->getSorting() + $delta;
        $shipping->setSorting($newSort);
        $this->entityManager->persist($shipping);

        $shpSort = $this->entityManager->getRepository(Shipping::class)
                ->findBy(['office' => $shipping->getOffice()->getId(), 'sorting' => $shipping->getSorting()]);
        if ($shpSort){
            $shpSort->setSorting($shipping->getSorting());
            $this->entityManager->persist($shpSort);
        }
        $this->entityManager->flush();
        return;
    }
    
    /**
     * Удалить доставку
     * @param Shipping $shipping
     */
    public function removeShipping($shipping)
    {
        $orders = $this->entityManager->getRepository(Order::class)
                ->count(['shipping' => $shipping->getId()]);
        if ($orders){
            return FALSE;
        }
        $this->entityManager->remove($shipping);
        $this->entityManager->flush();
        return true;
    }
    
}
