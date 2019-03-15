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
    
    const CAR_UPDATED = 2; // машины обновлены
    const CAR_UPDATING = 3; // машины обновляются
    const CAR_FOR_UPDATE = 1; // машины не обновлялись
    
    const IMAGE_UPDATED = 2; // картинки обновлены
    const IMAGE_FOR_UPDATE = 1; // картинки не обновлялись
    
    const DESCRIPTION_UPDATED = 2; // описания обновлены
    const DESCRIPTION_FOR_UPDATE = 1; // описания не обновлялись

    const GROUP_UPDATED = 2; // группы обновлены
    const GROUP_FOR_UPDATE = 1; // группы не обновлялись

    const OEM_UPDATED = 2; // номера обновлены
    const OEM_FOR_UPDATE = 1; // номера не обновлялись

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
     * @ORM\Column(name="code")   
     */
    protected $code;
    
    /**
     * @ORM\Column(name="price")   
     */
    protected $price;
    
    /**
     * @ORM\Column(name="status_car")   
     */
    protected $statusCar;
    
    /**
     * @ORM\Column(name="status_image")   
     */
    protected $statusImage;
    /**
     * @ORM\Column(name="status_group")   
     */
    protected $statusGroup;
    
    /**
     * @ORM\Column(name="status_description")   
     */
    protected $statusDescription;
    
    /**
     * @ORM\Column(name="status_oem")   
     */
    protected $statusOem;

    /**
     * @ORM\Column(name="car_count")   
     */
    protected $carCount;

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
    * @ORM\OneToMany(targetEntity="Application\Entity\Article", mappedBy="good")
    * @ORM\JoinColumn(name="id", referencedColumnName="good_id")
     */
    private $articles;
 
    /**
     * @ORM\OneToMany(targetEntity="Application\Entity\Images", mappedBy="good")
     * @ORM\JoinColumn(name="id", referencedColumnName="good_id")
     */
    protected $images;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\TokenGroup", inversedBy="goods") 
     * @ORM\JoinColumn(name="token_group_id", referencedColumnName="id")
     * 
     */

    protected $tokenGroup;
    
    /**
     * @ORM\ManyToMany(targetEntity="Application\Entity\Car", inversedBy="goods")
     * @ORM\JoinTable(name="good_car",
     *      joinColumns={@ORM\JoinColumn(name="good_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="car_id", referencedColumnName="id")}
     *      )
     */
    protected $cars;
    
    /**
     * Конструктор.
     */
    public function __construct() 
    {
      $this->images = new ArrayCollection();   
      $this->articles = new ArrayCollection();      
      $this->cart = new ArrayCollection(); 
      $this->cars = new ArrayCollection();
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
    public function getArticles()
    {
        return $this->articles;
    }
        
    /**
     * Assigns.
     */
    public function addArticle($article)
    {
        $this->articles[] = $article;
    }
    
    /**
     * Содержит ли наименование товара токен
     * 
     * @param Application\Entity\Token $token
     * @return boolean
     */
    public function hasToken($token)
    {
        foreach ($this->rawprice as $rawprice){
            if ($rawprice->hasToken($token)){
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Возвращает токены из словаря Ru
     * @return array;
     */
    public function getDictRuTokens()
    {
        $result = [];
        foreach($this->rawprice as $rawprice){
            foreach($rawprice->getDictRuTokens() as $token){
                $result[$token->getId()] = $token;
            }
        }
        
        return $result;
    }
            
    /**
     * Возвращает id токенов из словаря Ru
     * @return string;
     */
    public function getDictRuTokenIds()
    {
        $tokens = $this->getDictRuTokens();
        $result = [];
        foreach($tokens as $token){
            $result[] = $token->getId();
        }
        
        $filter = new \Application\Filter\IdsFormat();

        return $filter->filter($result);
    }
            
    /*
     * Возвращает связанный tokenGroup.
     * @return \Application\Entity\TokenGroup
     */    
    public function getTokenGroup() 
    {
        if ($this->tokenGroup){
            if ($this->tokenGroup->getId()){
                return $this->tokenGroup;                
            }
        }
        
        return;
    }
    
    /**
     * Задает связанный tokenGroup.
     * @param \Application\Entity\TokenGroup $tokenGroup
     */    
    public function setTokenGroup($tokenGroup) 
    {
        $this->tokenGroup = $tokenGroup;
        if ($tokenGroup){
            $tokenGroup->addGood($this);
        }    
    }     

    // Возвращает машины для данного товара.
    public function getCars() 
    {
        return $this->cars;
    }      
    
    // Добавляет новую машину к данному товару.
    public function addCar($car) 
    {
        $this->cars[] = $car;        
    }
    
    // Удаляет связь между этим товаром и заданной машиной.
    public function removeCarAssociation($car) 
    {
        $this->cars->removeElement($car);
    }    
    
    public function getStatusCar()
    {
        $this->statusCar;
    }
    
    public function setStatusCar($statusCar)
    {
        $this->statusCar = $statusCar;
    }

    public function getStatusImage()
    {
        $this->statusImage;
    }
    
    public function setStatusImage($statusImage)
    {
        $this->statusImage = $statusImage;
    }
    
    public function getStatusGroup()
    {
        $this->statusGroup;
    }
    
    public function setStatusGroup($statusGroup)
    {
        $this->statusGroup = $statusGroup;
    }
    
    public function getStatusDescription()
    {
        $this->statusDescription;
    }
    
    public function setStatusDescription($statusDescription)
    {
        $this->statusDescription = $statusDescription;
    }    
    
    public function getStatusOem()
    {
        $this->statusOem;
    }
    
    public function setStatusOem($statusOem)
    {
        $this->statusOem = $statusOem;
    }    
    
    public function getCarCount()
    {
        $this->carCount;
    }
    
    public function setCarCount($carCount)
    {
        $this->carCount = $carCount;
    }    
    
}
