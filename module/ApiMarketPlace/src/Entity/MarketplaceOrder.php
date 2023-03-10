<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ApiMarketPlace\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use ApiMarketPlace\Entity\Marketplace;
use Application\Entity\Order;
use ApiMarketPlace\Entity\MarketplaceUpdate;

/**
 * Description of Marketplace update
 * @ORM\Entity(repositoryClass="\ApiMarketPlace\Repository\MarketplaceRepository")
 * @ORM\Table(name="marketplace_order")
 * @author Daddy
 */
class MarketplaceOrder {
    
    const STATUS_ACTIVE       = 1; // Active.
    const STATUS_RETIRED      = 2; // Retired.
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * Ip торговой площадки
     * @ORM\Column(name="marketplace_order_id")   
     */
    protected $orderId;    
    
    /**
     * @ORM\Column(name="marketplace_order_number")   
     */
    protected $orderNumber;

    /**
     * @ORM\Column(name="marketplace_posting_number")   
     */
    protected $postingNumber;


    /** 
     * @ORM\Column(name="date_created")  
     */
    protected $dateCreated;  
                
    /**
     * @ORM\Column(name="status")   
     */
    protected $status;

    /**
     * @ORM\ManyToOne(targetEntity="ApiMarketPlace\Entity\Marketplace", inversedBy="marketplaceUpdates") 
     * @ORM\JoinColumn(name="marketplace_id", referencedColumnName="id")
     */
    private $marketplace;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Order", inversedBy="marketplaceOrders") 
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id")
     */
    private $order;
        
    /**
    * @ORM\OneToMany(targetEntity="ApiMarketPlace\Entity\MarketplaceUpdate", mappedBy="marketplaceOrder")
    * @ORM\JoinColumn(name="id", referencedColumnName="marketplace_order_id")
     */
    private $marketplaceUpdates;    
    
    public function __construct() 
    {
        $this->marketplaceUpdates = new ArrayCollection();
    }        
    
    public function getId() 
    {
        return $this->id;
    }

    public function setId($id) 
    {
        $this->id = $id;
    }     
    
    public function getOrderId() 
    {
        return $this->orderId;
    }

    public function setOrderId($orderId) 
    {
        $this->orderId = $orderId;
    }         

    public function getOrderNumber() 
    {
        return $this->orderNumber;
    }

    public function setOrderNumber($orderNumber) 
    {
        $this->orderNumber = $orderNumber;
    }         

    public function getPostingNumber() 
    {
        return $this->postingNumber;
    }

    public function setPostingNumber($postingNumber) 
    {
        $this->postingNumber = $postingNumber;
    }         

    /**
     * Returns the date of marketplace_update creation.
     * @return string     
     */
    public function getDateCreated() 
    {
        return $this->dateCreated;
    }
    
    /**
     * Sets the date when this marketplace_update was created.
     * @param string $dateCreated     
     */
    public function setDateCreated($dateCreated) 
    {
        $this->dateCreated = $dateCreated;
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
            self::STATUS_ACTIVE => 'Используется',
            self::STATUS_RETIRED => 'Не используется'
        ];
    }    
    
    /**
     * Returns marketplace status as string.
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
     * Sets status.
     * @param int $status     
     */
    public function setStatus($status) 
    {
        $this->status = $status;
    }   
    
    /*
     * Возвращает связанный marketplace.
     * @return Marketplace
     */
    
    public function getMarketplace() 
    {
        return $this->marketplace;
    }

    /**
     * Задает связанный marketplace.
     * @param Marketplace $marketplace
     */    
    public function setMarketplace($marketplace) 
    {
        $this->marketplace = $marketplace;
        $marketplace->addMarketplaceOrder($this);
    }             
    
    /*
     * Возвращает связанный order.
     * @return Order
     */
    
    public function getOrder() 
    {
        return $this->order;
    }

    /**
     * Задает связанный order.
     * @param Order $order
     */    
    public function setOrder($order) 
    {
        $this->order = $order;
        if ($order){
            $order->addMarketplaceOrder($this);
        }    
    }             
    
    /**
     * Returns the array of marketplaceUpdates assigned to this.
     * @return array
     */
    public function getMarketplaceUpdates()
    {
        return $this->marketplaceUpdates;
    }
        
    /**
     * Assigns.
     * @param MarketplaceUpdate $marketplaceUpdate
     */
    public function addMarketplaceUpdate($marketplaceUpdate)
    {
        $this->marketplaceUpdates[] = $marketplaceUpdate;
    }    
    
    /**
     * Массив для формы
     * @return array 
     */
    public function toLog()
    {
        $result = [
            'id' => $this->getId(),
            'status' => $this->getStatus(),
            'orderId' => $this->getOrder(),
            'orderNumber' => $this->getOrderNumber(),
            'postingNumber' => $this->getPostingNumber(),
            'marketplaceId' => $this->getMarketplace()->getId(),
            'marketplaceName' => $this->getMarketplace()->getName(),
            'order' => ($this->getOrder()) ? $this->getOrder()->getId():null,
        ];
        
        return $result;
    }    
    
}
