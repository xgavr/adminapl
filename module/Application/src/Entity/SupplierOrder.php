<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Laminas\Json\Decoder;
use Laminas\Json\Json;

/**
 * Description of idoc
 * @ORM\Entity(repositoryClass="\Application\Repository\OrderRepository")
 * @ORM\Table(name="supplier_order")
 * @author Daddy
 */
class SupplierOrder {
    
     // Supplier status constants.
    const STATUS_ORDER_NEW          = 1; // Новый. Не заказано
    const STATUS_ORDER_ORDERED      = 2; // Заказано.
    
    const STATUS_NEW                = 1; // Не получено.
    const STATUS_DOC                = 2; // Получен документ.
    const STATUS_RECEIVED           = 3; // Получен факт.
        
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="status")   
     */
    protected $status;    
    
    /**
     * @ORM\Column(name="status_order")   
     */
    protected $statusOrder;    

    /**
     * @ORM\Column(name="comment")   
     */
    protected $comment;
        
    /**
     * @ORM\Column(name="quantity")   
     */
    protected $quantity;
        
    /** 
     * @ORM\Column(name="date_created")  
     */
    protected $dateCreated;        
       
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Order", inversedBy="supplierOrders") 
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id")
     */
    private $order;    
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Goods", inversedBy="supplierOrders") 
     * @ORM\JoinColumn(name="good_id", referencedColumnName="id")
     */
    private $good;    
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Supplier", inversedBy="supplierOrders") 
     * @ORM\JoinColumn(name="supplier_id", referencedColumnName="id")
     */
    private $supplier;    
        
    public function getId() 
    {
        return $this->id;
    }

    public function setId($id) 
    {
        $this->id = $id;
    }     
    
    public function setComment($comment) 
    {
        $this->comment = $comment;
    }     

    public function getComment() 
    {
        return $this->comment;
    }    

    public function getQuantity() 
    {
        return $this->quantity;
    }

    public function setQuantity($quantity) 
    {
        $this->quantity = $quantuty;
    }     
    
    /**
     * Returns status.
     * @return int     
     */
    public function getStatus() 
    {
        return $this->status;
    }
    
    /**
     * Returns possible statuses as array.
     * @return array
     */
    public static function getStatusList() 
    {
        return [
            self::STATUS_NEW => 'Не получено',
            self::STATUS_DOC => 'Есть документ',
            self::STATUS_RECEIVED => 'Получено',
        ];
    }    
    
    /**
     * Returns user status as string.
     * @return string
     */
    public function getStatusAsString()
    {
        $list = self::getStatusList();
        if (isset($list[$this->status]))
            return $list[$this->status];
        
        return 'Unknown';
    }    
    
    /**
     * Sets status .
     * @param int $status     
     */
    public function setStatus($status) 
    {
        $this->status = $status;
    }   
    
    /**
     * Returns status order.
     * @return int     
     */
    public function getStatusOrder() 
    {
        return $this->statusOrder;
    }

    /**
     * Returns possible statuses order as array.
     * @return array
     */
    public static function getStatusOrderList() 
    {
        return [
            self::STATUS_ORDER_NEW => 'Не заказано',
            self::STATUS_ORDER_ORDERED => 'Заказано',
        ];
    }    
    
    /**
     * Returns user status order as string.
     * @return string
     */
    public function getStatusOrderAsString()
    {
        $list = self::getStatusOrderList();
        if (isset($list[$this->statusOrder]))
            return $list[$this->statusOrder];
        
        return 'Unknown';
    }    
    
    /**
     * Sets status order.
     * @param int $statusOrder     
     */
    public function setStatusOrder($statusOrder) 
    {
        $this->statusOrder = $statusOrder;
    }   
    
    /**
     * Returns the date of doc creation.
     * @return string     
     */
    public function getDateCreated() 
    {
        return $this->dateCreated;
    }
    
    /**
     * Sets the date when this doc was created.
     * @param string $dateCreated     
     */
    public function setDateCreated($dateCreated) 
    {
        $this->dateCreated = $dateCreated;
    }    
    
    /*
     * Возвращает связанный order.
     * @return \Application\Entity\Order
     */    
    public function getOrder() 
    {
        return $this->order;
    }

    /**
     * Задает связанный good.
     * @param \Application\Entity\Good $good
     */    
    public function setGood($good) 
    {
        $this->good = $good;
    }    
        
    /*
     * Возвращает связанный good.
     * @return \Application\Entity\Good
     */    
    public function getGood() 
    {
        return $this->good;
    }

    /**
     * Задает связанный order.
     * @param \Application\Entity\Order $order
     */    
    public function setOrder($order) 
    {
        $this->order = $order;
    }    
        
    /*
     * Возвращает связанный supplier.
     * @return \Application\Entity\Supplier
     */    
    public function getSupplier() 
    {
        return $this->supplier;
    }

    /**
     * Задает связанный supplier.
     * @param \Application\Entity\Supplier $supplier
     */    
    public function setSupplier($supplier) 
    {
        $this->supplier = $supplier;
    }    
}
