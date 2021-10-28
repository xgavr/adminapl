<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Application\Entity\Country;
use Application\Entity\Rate;

/**
 * Description of Producer
 * @ORM\Entity(repositoryClass="\Application\Repository\ProducerRepository")
 * @ORM\Table(name="producer")
 * @author Daddy
 */
class Producer {
    
    const STATUS_ACTIVE       = 1; // Active producer.
    const STATUS_RETIRED      = 2; // Retired producer.   

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="apl_id")   
     */
    protected $aplId = 0;
    
    /**
     * @ORM\Column(name="name")   
     */
    protected $name;
    
    /** 
     * @ORM\Column(name="status")  
     */
    protected $status = self::STATUS_ACTIVE;
    
    /**
     * @ORM\Column(name="good_count")   
     */
    protected $goodCount = 0;

    /**
     * @ORM\Column(name="movement")   
     */
    protected $movement = 0;

    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Country", inversedBy="producer") 
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id")
     */
    protected $country;

   /**
    * @ORM\OneToMany(targetEntity="\Application\Entity\Goods", mappedBy="producer")
    * @ORM\JoinColumn(name="id", referencedColumnName="producer_id")
   */
   private $goods;

   /**
    * @ORM\OneToMany(targetEntity="\Application\Entity\UnknownProducer", mappedBy="producer")
    * @ORM\JoinColumn(name="id", referencedColumnName="producer_id")
   */
   private $unknownProducer;
       
   /**
    * @ORM\OneToMany(targetEntity="Rate", mappedBy="producer")
    * @ORM\JoinColumn(name="id", referencedColumnName="producer_id")
   */
   private $rates;
   
   public function __construct() {
      $this->goods = new ArrayCollection();
      $this->unknownProducer = new ArrayCollection();
      $this->rates = new ArrayCollection();
   }

    public function getId() 
    {
        return $this->id;
    }

    public function setId($id) 
    {
        $this->id = $id;
    }     

    public function getAplId() 
    {
        return $this->aplId;
    }

    public function setAplId($aplId) 
    {
        $this->aplId = $aplId;
    }     

    public function getName() 
    {
        return $this->name;
    }

    public function setName($name) 
    {
        $this->name = trim($name);
    }     
    
    /**
     * Returns status.
     * @return int     
     */
    public function getStatus() 
    {
        return $this->status;
    }

    public function getStatusCheckbox() 
    {
        if ($this->status == self::STATUS_ACTIVE){
            return 'checked';
        }
        return '';
    }

    /**
     * Returns possible statuses as array.
     * @return array
     */
    public static function getStatusList() 
    {
        return [
            self::STATUS_ACTIVE => 'Действующий',
            self::STATUS_RETIRED => 'Отключен'
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
    
    public static function getStatusName($status)
    {
        $list = self::getStatusList();
        if (isset($list[$status]))
            return $list[$status];
        
        return 'Unknown';        
    }
    
    public function setStatus($status)
    {
        $this->status = $status;
    }
    
    public function getGoodCount() 
    {
        return $this->goodCount;
    }

    public function setGoodCount($goodCount) 
    {
        $this->goodCount = $goodCount;
    }      

    public function getMovement() 
    {
        return $this->movement;
    }

    public function setMovement($movement) 
    {
        $this->movement = $movement;
    }      

    /*
     * Возвращает связанный country.
     * @return \Company\Entity\Country
     */    
    public function getCountry() 
    {
        return $this->country;
    }

    /**
     * Задает связанный country.
     * @param \Company\Entity\Country $country
     */    
    public function setCountry($country) 
    {
        $this->country = $country;
        $country->addProducer($this);
    }     

    /**
     * Возвращает goods для этого producer.
     * @return array
     */   
   public function getGoods() {
      return $this->goods;
   }    
   
    /**
     * Добавляет новый goods к этому producer.
     * @param $goods
     */   
    public function addGoods($goods) 
    {
        $this->goods[] = $goods;
    }   

    /**
     * Возвращает unknownProducer для этого producer.
     * @return array
     */   
   public function getUnknownProducer() {
      return $this->unknownProducer;
   }    
   
    /**
     * Добавляет новый unknownProducer к этому producer.
     * @param $unknownProducer
     */   
    public function addUnknownProducer($unknownProducer) 
    {
        $this->unknownProducer[] = $unknownProducer;
    }   
    
    /*
     * Возвращает связанный rates.
     * @return Rate
     */    
    public function getRates() 
    {
        return $this->rates;
    }

    public function addRate($rate) 
    {
        $this->rates[] = $rate;
    }     
            
}
