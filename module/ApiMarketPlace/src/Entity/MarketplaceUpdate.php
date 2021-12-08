<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ApiMarketPlace\Entity;

use Doctrine\ORM\Mapping as ORM;
use ApiMarketPlace\Entity\Marketplace;
use Application\Entity\Order;

/**
 * Description of Bid
 * @ORM\Entity(repositoryClass="\ApiMarketPlace\Repository\MarketplaceRepository")
 * @ORM\Table(name="marketplace_update")
 * @author Daddy
 */
class MarketplaceUpdate {
    
    const STATUS_ACTIVE       = 1; // Active.
    const STATUS_RETIRED      = 2; // Retired.
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="post_data")   
     */
    protected $postData;

    /**
     * @ORM\Column(name="status")   
     */
    protected $status;

    /** 
     * @ORM\Column(name="date_created")  
     */
    protected $dateCreated;  
                
    /**
     * @ORM\ManyToOne(targetEntity="ApiMarketPlace\Entity\Marketplace", inversedBy="marketplaceUpdates") 
     * @ORM\JoinColumn(name="marketplace_id", referencedColumnName="id")
     */
    private $marketplace;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Order", inversedBy="marketplaceUpdates") 
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id")
     */
    private $order;
        
    
    public function getId() 
    {
        return $this->id;
    }

    public function setId($id) 
    {
        $this->id = $id;
    }     

    public function getPostData() 
    {
        return $this->postData;
    }

    public function setPostData($postData) 
    {
        $this->postData = $postData;
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
        $marketplace->addMarketplaceUpdate($this);
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
     * @param Marketplace $order
     */    
    public function setOrder($order) 
    {
        $this->order = $order;
        $order->addMarketplaceUpdate($this);
    }             
    
}
