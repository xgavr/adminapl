<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service;

use Laminas\ServiceManager\ServiceManager;
use Company\Entity\Office;
use Application\Entity\Order;
use Application\Entity\SupplierOrder;

/**
 * Description of SupplierOrderManager
 *
 * @author Daddy
 */
class SupplierOrderManager
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
     * Добавить заказ поставщику
     * @param array $data
     * @return SupplierOrder
     */
    public function addSupplierOrder($data)
    {
        $supplierOrder = new SupplierOrder();
        $supplierOrder->setAplId(!empty($data['aplId']) ? $data['aplId'] : null);
        $supplierOrder->setComment(!empty($data['comment']) ? $data['comment'] : null);
        $supplierOrder->setDateCreated(date('Y-m-d H:i:s'));
        $supplierOrder->setGood($data['good']);
        $supplierOrder->setOrder($data['order']);
        $supplierOrder->setQuantity(!empty($data['quantity']) ? $data['quantity'] : 0);
        $supplierOrder->setStatus(!empty($data['status']) ? $data['status'] :SupplierOrder::STATUS_NEW);
        $supplierOrder->setStatusOrder(!empty($data['statusOrder']) ? $data['statusOrder'] : SupplierOrder::STATUS_ORDER_NEW);
        $supplierOrder->setSupplier($data['supplier']);
        
        $this->entityManager->persist($supplierOrder);
        $this->entityManager->flush($supplierOrder);
        
        return $supplierOrder;
    }
    
    /**
     * Обновить заказ поставщику
     * @param SupplierOrder $supplierOrder
     * @param array $data
     * @return Courier
     */
    public function updateSupplierOrder($supplierOrder, $data)
    {
        $supplierOrder->setAplId(!empty($data['aplId']) ? $data['aplId'] : null);
        $supplierOrder->setComment(!empty($data['comment']) ? $data['comment'] : null);
        $supplierOrder->setGood($data['good']);
        $supplierOrder->setOrder($data['order']);
        $supplierOrder->setQuantity(!empty($data['quantity']) ? $data['quantity'] : 0);
        $supplierOrder->setStatus(!empty($data['status']) ? $data['status'] :SupplierOrder::STATUS_NEW);
        $supplierOrder->setStatusOrder(!empty($data['statusOrder']) ? $data['statusOrder'] : SupplierOrder::STATUS_ORDER_NEW);
        $supplierOrder->setSupplier($data['supplier']);
        
        $this->entityManager->persist($supplierOrder);
        $this->entityManager->flush($supplierOrder);
        
        return $supplierOrder;
    }
    
    /**
     * Удалить заказпоставщику
     * @param SupplierOrder $supplierOrder
     */
    public function removeSupplierOrder($supplierOrder)
    {
        $this->entityManager->remove($supplierOrder);
        $this->entityManager->flush();
        return true;
    }
    
    /**
     * Удалить все заказы поставщикам по заказу
     * 
     * @param Order $order
     */
    public function removeByOrder($order)
    {
        $supplierOrders = $this->entityManager->getRepository(SupplierOrder::class)
                ->findBy(['order' => $order->getId()]);
        foreach ($supplierOrders as $supplierOrder){
            $this->removeSupplierOrder($supplierOrder);
        }
        
        return;
    }
}
