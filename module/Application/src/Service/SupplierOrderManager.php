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
        $supplierOrder->setComment(!empty($data['comment']) ? $data['comment'] : null);
        $supplierOrder->setDateCreated(date('Y-m-d H:i:s'));
        $supplierOrder->setGood($data['good_id']);
        $supplierOrder->setOrder($data['order_id']);
        $supplierOrder->setQuantity(!empty($data['quantity']) ? $data['quantity'] : 0);
        $supplierOrder->setStatus(!empty($data['status']) ? $data['status'] :SupplierOrder::STATUS_NEW);
        $supplierOrder->setStatusOrder(!empty($data['statusOrder']) ? $data['statusOrder'] : SupplierOrder::STATUS_ORDER_NEW);
        $supplierOrder->setSupplier($data['supplier_id']);
        
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
        $supplierOrder->setComment(!empty($data['comment']) ? $data['comment'] : null);
        $supplierOrder->setGood($data['good_id']);
        $supplierOrder->setOrder($data['order_id']);
        $supplierOrder->setQuantity(!empty($data['quantity']) ? $data['quantity'] : 0);
        $supplierOrder->setStatus(!empty($data['status']) ? $data['status'] :SupplierOrder::STATUS_NEW);
        $supplierOrder->setStatusOrder(!empty($data['statusOrder']) ? $data['statusOrder'] : SupplierOrder::STATUS_ORDER_NEW);
        $supplierOrder->setSupplier($data['supplier_id']);
        
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
        
}
