<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
    use Doctrine\Common\Collections\ArrayCollection;

/**
 * Description of Make
 * @ORM\Entity(repositoryClass="\Application\Repository\CarRepository")
 * @ORM\Table(name="car")
 * @author Daddy
 */

class Car {
    
     // Make status constants.
    const STATUS_ACTIVE       = 1; // Active.
    const STATUS_RETIRED      = 2; // Retired.
    
    const PASSENGER_YES       = 1; //
    const PASSENGER_NO       = 2; //    
    
    const COMMERC_YES       = 1; //
    const COMMERC_NO       = 2; //
    
    const MOTO_YES       = 1; //
    const MOTO_NO       = 2; //
    
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
     * @ORM\Column(name="td_id")   
     */
    protected $tdId;
    
    /**
     * @ORM\Column(name="apl_id")   
     */
    protected $aplId;
    
    /**
     * @ORM\Column(name="fullname")  
     */
    protected $fullName;    

    /**
     * @ORM\Column(name="status")  
     */
    protected $status;    
       
    /**
     * @ORM\Column(name="passenger")  
     */
    protected $passenger;
    
    /**
     * @ORM\Column(name="commerc")  
     */
    protected $commerc;

    /**
     * @ORM\Column(name="moto")  
     */
    protected $moto;
        
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Model", inversedBy="model") 
     * @ORM\JoinColumn(name="model_id", referencedColumnName="id")
     * 
     */
    protected $model;
    
    /**
    * @ORM\OneToMany(targetEntity="Application\Entity\CarAttributeValue", mappedBy="car")
    * @ORM\JoinColumn(name="id", referencedColumnName="car_id")
     */
    protected $carAttributeValues;    
    
    /**
     * @ORM\ManyToMany(targetEntity="\Application\Entity\Goods", mappedBy="cars")
     */
    protected $goods;    

    public function __construct() {
       $this->carAttributeValues = new ArrayCollection();  
       $this->goods = new ArrayCollection();
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

    public function getFullName() 
    {
        return $this->fullName;
    }

    public function setFullName($fullName) 
    {
        $this->fullName = $fullName;
    }     

    public function getTdId() 
    {
        return $this->tdId;
    }

    public function setTdId($tdId) 
    {
        $this->tdId = $tdId;
    }     

    public function getAplId() 
    {
        return $this->aplId;
    }

    public function setAplId($aplId) 
    {
        $this->aplId = $aplId;
    }     
    
    public function getPasenger() 
    {
        return $this->passenger;
    }

    public function setPassenger($passenger) 
    {
        $this->passenger = $passenger;
    }     
    
    public function getCommerc() 
    {
        return $this->commerc;
    }

    public function setCommerc($commerc) 
    {
        $this->commerc = $commerc;
    }     
    
    public function getMoto() 
    {
        return $this->moto;
    }

    public function setMoto($moto) 
    {
        $this->moto = $moto;
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
     * Returns make status as string.
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
     * Возвращает связанный model.
     * @return \Application\Entity\Model
     */    
    public function getModel() 
    {
        return $this->model;
    }
    
    /**
     * Задает связанный model.
     * @param \Application\Entity\Model $model
     */    
    public function setModel($model) 
    {
        $this->model = $model;
        $model->addCar($this);
    }         

    /*
     * Возвращает values.
     * @return array
     */    
    public function getCarAtributeValues() 
    {
        return $this->carAttributeValues;
    }
    
    public function addCarAttributeValue($carAttributeValue)
    {
        $this->carAttributeValues[] = $carAttributeValue;
    }
    
    // Возвращает товары, связанные с данной машиной.
    public function getGoods() 
    {
        return $this->goods;
    }
    
    // Добавляет товар в коллекцию товаров, связанных с этой машиной.
    public function addGood($good) 
    {
        $this->goods[] = $good;        
    }     
}
