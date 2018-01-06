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

/**
 * Description of Producer
 * @ORM\Entity(repositoryClass="\Application\Repository\ProducerRepository")
 * @ORM\Table(name="producer")
 * @author Daddy
 */
class Producer {
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
     * @ORM\ManyToOne(targetEntity="Application\Entity\Country", inversedBy="producer") 
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id")
     */
    protected $country;

    
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
        $this->name = trim($name);
    }     

    /*
     * Возвращает связанный country.
     * @return \Application\Entity\Country
     */    
    public function getCountry() 
    {
        return $this->country;
    }

    /**
     * Задает связанный country.
     * @param \Application\Entity\Country $country
     */    
    public function setCountry($country) 
    {
        $this->country = $country;
        $country->addProducer($this);
    }     

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

   public function __construct() {
      $this->goods = new ArrayCollection();
      $this->unknownProducer = new ArrayCollection();
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
    
}
