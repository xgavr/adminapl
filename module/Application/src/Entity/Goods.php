<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Application\Entity\Images;
use Application\Entity\Producer;
use Company\Entity\Tax;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Goods
 * @ORM\Entity(repositoryClass="\Application\Repository\GoodsRepository")
 * @ORM\Table(name="goods")
 * @author Daddy
 */
class Goods {
    
    // Константы доступности товар.
    const AVAILABLE_TRUE    = 1; // Доступен.
    const AVAILABLE_FALSE   = 0; // Недоступен.
    
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
     * @ORM\Column(name="code")   
     */
    protected $code;
    
    /**
     * @ORM\Column(name="price")   
     */
    protected $price;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Producer", inversedBy="goods") 
     * @ORM\JoinColumn(name="producer_id", referencedColumnName="id")
     * 
     */
    protected $producer;
    
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Tax", inversedBy="goods") 
     * @ORM\JoinColumn(name="tax_id", referencedColumnName="id")
     */
    protected $tax;
    
    /**
     * @ORM\Column(name="available")   
     */
    protected $available;
    
    /**
     * @ORM\Column(name="description")   
     */
    protected $description;
    
    /**
    * @ORM\OneToMany(targetEntity="Application\Entity\Rawprice", mappedBy="goods")
    * @ORM\JoinColumn(name="id", referencedColumnName="good_id")
     */
    private $rawprice;
 
    /**
     * @ORM\OneToMany(targetEntity="\Application\Entity\Images", mappedBy="goods")
     * @ORM\JoinColumn(name="id", referencedColumnName="good_id")
     */
    protected $images;
    
    /**
     * Конструктор.
     */
    public function __construct() 
    {
      $this->images = new ArrayCollection();   
      $this->rawprice = new ArrayCollection();      
      $this->cart = new ArrayCollection();      
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

    public function getCode() 
    {
        return $this->code;
    }

    public function setCode($code) 
    {
        $this->code = $code;
    }     

    public function getPrice() 
    {
        return $this->price;
    }

    public function setPrice($price) 
    {
        $this->price = $price;
    }     

    /*
     * Возвращает связанный producer.
     * @return \Application\Entity\Producer
     */    
    public function getProducer() 
    {
        return $this->producer;
    }
    
    /**
     * Задает связанный producer.
     * @param \Application\Entity\Producer $producer
     */    
    public function setProducer($producer) 
    {
        $this->producer = $producer;
        $producer->addGoods($this);
    }     

    /*
     * Возвращает связанный tax.
     * @return \Company\Entity\Tax
     */    
    public function getTax() 
    {
        return $this->tax;
    }

    /**
     * Задает связанный tax.
     * @param \Company\Entity\Tax $tax
     */    
    public function setTax($tax) 
    {
        $this->tax = $tax;
    }     

    public function getAvailable() 
    {
        return $this->available;
    }

    public function setAvailable($available) 
    {
        $this->available = $available;
    }     
    
    public function getDescription() 
    {
        return $this->description;
    }

    public function setDescription($description) 
    {
        $this->description = $description;
    }     

    /**
     * Возвращает картинки для этого товара.
     * @return array
     */
    public function getImages() 
    {
        return $this->images;
    }
    
    /**
     * Добавляет новою картинку к этому товару.
     * @param $image
     */
    public function addImage($image) 
    {
        $this->images[] = $image;
    }
    
    /**
     * Returns the array of contacts assigned to this.
     * @return array
     */
    public function getRawprice()
    {
        return $this->rawprice;
    }
        
    /**
     * Assigns.
     */
    public function addRawprice($rawprice)
    {
        $this->rawprice[] = $rawprice;
    }
            
}
