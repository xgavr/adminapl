<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Stock\Entity;

use Doctrine\ORM\Mapping as ORM;
use Company\Entity\Contact;
use Application\Entity\Goods;
use Stock\Entity\Register;


/**
 * Description of ComissBalance
 * @ORM\Entity(repositoryClass="\Stock\Repository\ComissRepository")
 * @ORM\Table(name="comiss_balance")
 * @author Daddy
 */
class ComissBalance {
    
    const STATUS_ACTIVE       = 1; // Active.
    const STATUS_RETIRED      = 2; // Retired.
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="rest")   
     */
    protected $rest;
    
    /** 
     * @ORM\Column(name="price")  
     */
    protected $price;
    
    /** 
     * @ORM\Column(name="status")  
     */
    protected $status;

    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Goods", inversedBy="comissBalances") 
     * @ORM\JoinColumn(name="good_id", referencedColumnName="id")
     */
    private $good;
            
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Contact", inversedBy="comissBalances") 
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="id")
     */
    private $contact;
    
    /**
     * @ORM\ManyToOne(targetEntity="Stock\Entity\Register", inversedBy="comitentBalances") 
     * @ORM\JoinColumn(name="base_stamp", referencedColumnName="id")
     */
    private $base;

    public function __construct() {
    }
   
    public function getId() 
    {
        return $this->id;
    }

    public function setId($id) 
    {
        $this->id = $id;
    }     

    public function getRest() 
    {
        return $this->rest;
    }

    public function getAvailable() 
    {
        $available = $this->rest;
        return ($available > 0) ? $available:0;
    }

    public function setRest($rest) 
    {
        $this->rest = $rest;
    }     

    /**
     * Sets price.
     * @param float $price     
     */
    public function setPrice($price) 
    {
        $this->price = $price;
    }    
    
    /**
     * Returns the price.
     * @return float     
     */
    public function getPrice() 
    {
        return $this->price;
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
            self::STATUS_ACTIVE => 'Активный',
            self::STATUS_RETIRED => 'Оставить',
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
     * Sets status.
     * @param int $status     
     */
    public function setStatus($status) 
    {
        $this->status = $status;
    }   
    
    /**
     * Returns the good.
     * @return Goods     
     */
    public function getGood() 
    {
        return $this->good;
    }

    /**
     * Returns the contact.
     * @return Contact     
     */
    public function getContact() 
    {
        return $this->contact;
    }

    public function setContact($contact) {
        $this->contact = $contact;
        return $this;
    }

    /**
     * Returns the base.
     * @return Register     
     */
    public function getBase() 
    {
        return $this->base;
    }

}
