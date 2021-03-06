<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Application\Entity\ScaleTreshold;
use Application\Entity\Rate;

/**
 * Description of Phone
 * @ORM\Entity(repositoryClass="\Application\Repository\RateRepository")
 * @ORM\Table(name="scale")
 * @author Daddy
 */
class Scale {
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="name")   
     */
    protected $name;

    /** 
     * @ORM\Column(name="min_price")  
     */
    protected $minPrice;

    /** 
     * @ORM\Column(name="max_price")  
     */
    protected $maxPrice;

   /**
    * @ORM\OneToMany(targetEntity="Application\Entity\ScaleTreshold", mappedBy="scale")
    * @ORM\JoinColumn(name="id", referencedColumnName="scale_id")
   */
   private $tresholds;

   /**
    * @ORM\OneToMany(targetEntity="Application\Entity\Rate", mappedBy="scale")
    * @ORM\JoinColumn(name="id", referencedColumnName="scale_id")
   */
   private $rates;

   public function __construct() {
      $this->tresholds = new ArrayCollection();      
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

    public function getName() 
    {
        return $this->name;
    }

    public function setName($name) 
    {
        $this->name = $name;
    }     

    public function getMinPrice() 
    {
        return $this->minPrice;
    }

    public function setMinPrice($minPrice) 
    {
        $this->minPrice = $minPrice;
    }     

    public function getMaxPrice() 
    {
        return $this->maxPrice;
    }

    public function setMaxPrice($maxPrice) 
    {
        $this->maxPrice = $maxPrice;
    }     
        
    /*
     * Возвращает связанный tresholds.
     * @return ScaleTreshold
     */    
    public function getTresholds() 
    {
        return $this->tresholds;
    }

    public function addTreshold($treshold) 
    {
        $this->tresholds[] = $treshold;
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
        
    /**
     * Лог
     * @return array
     */
    public function toLog()
    {
        return [
            'name' => $this->getName(),
            'minPrice' => $this->getMinPrice(),
            'maxPrice' => $this->getMaxPrice(),
            'tresholds' => [],
        ];
    }    
    
}
