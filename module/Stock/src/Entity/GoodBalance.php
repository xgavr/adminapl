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
 * @ORM\Entity(repositoryClass="\Stock\Repository\MovementRepository")
 * @ORM\Table(name="good_balance")
 * @author Daddy
 */
class GoodBalance {
    
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
     * @ORM\Column(name="reserve")   
     */
    protected $reserve;
    
    /**
     * @ORM\Column(name="delivery")   
     */
    protected $delivery;

    /**
     * @ORM\Column(name="vozvrat")   
     */
    protected $vozvrat;

    /** 
     * @ORM\Column(name="price")  
     */
    protected $price;
    
    /** 
     * @ORM\Column(name="status")  
     */
    protected $status;

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
     * @ORM\ManyToOne(targetEntity="Stock\Entity\Register", inversedBy="goodBalances") 
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
        $available = $this->rest - $this->reserve - $this->delivery - $this->vozvrat;
        return ($available > 0) ? $available:0;
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
     * Returns the vozvrat.
     * @return float     
     */
    public function getVozvrat() 
    {
        return $this->vozvrat;
    }
    
    /**
     * Sets vozvrat.
     * @param float $vozvrat     
     */
    public function setVozvrat($vozvrat) 
    {
        $this->vozvrat = $vozvrat;
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
     * Returns the base.
     * @return Register     
     */
    public function getBase() 
    {
        return $this->base;
    }

}
