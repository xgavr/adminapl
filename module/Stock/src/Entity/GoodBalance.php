<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Stock\Entity;

use Doctrine\ORM\Mapping as ORM;
use Company\Entity\Legal;
use Company\Entity\Office;
use Application\Entity\Goods;
use User\Entity\User;
use Stock\Entity\Register;


/**
 * Description of Mutual
 * @ORM\Entity(repositoryClass="\Stock\Repository\RegisterRepository")
 * @ORM\Table(name="good_balance")
 * @author Daddy
 */
class Mutual {
    
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
     * @ORM\Column(name="reserve")   
     */
    protected $reserve;
    
    /**
     * @ORM\Column(name="delivery")   
     */
    protected $delivery;

    /** 
     * @ORM\Column(name="price")  
     */
    protected $price;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Goods", inversedBy="goodBalances") 
     * @ORM\JoinColumn(name="good_id", referencedColumnName="id")
     */
    private $good;
            
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Office", inversedBy="goodBalances") 
     * @ORM\JoinColumn(name="office_id", referencedColumnName="id")
     */
    private $office;    
    
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Legal", inversedBy="goodBalances") 
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id")
     */
    private $company;
    
    /**
     * @ORM\ManyToOne(targetEntity="User\Entity\User", inversedBy="goodBalances") 
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;
    
    /**
     * @ORM\ManyToOne(targetEntity="Stock\Entity\Register", inversedBy="goodBalances") 
     * @ORM\JoinColumn(name="base_id", referencedColumnName="id")
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

    public function setRest($rest) 
    {
        $this->rest = $rest;
    }     

    public function getReserve() 
    {
        return $this->reserve;
    }

    public function setReserve($reserve) 
    {
        $this->reserve = $reserve;
    }     
    
    /**
     * Returns the delivery.
     * @return float     
     */
    public function getDelivery() 
    {
        return $this->delivery;
    }
    
    /**
     * Sets delivery.
     * @param float $delivery     
     */
    public function setDelivery($delivery) 
    {
        $this->delivery = $delivery;
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
     * Returns the good.
     * @return Goods     
     */
    public function getGood() 
    {
        return $this->good;
    }

    /**
     * Returns the office.
     * @return Office     
     */
    public function getOffice() 
    {
        return $this->office;
    }

    /**
     * Returns the company.
     * @return Legal     
     */
    public function getCompany() 
    {
        return $this->company;
    }

    /**
     * Returns the user.
     * @return User     
     */
    public function getUser() 
    {
        return $this->user;
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
