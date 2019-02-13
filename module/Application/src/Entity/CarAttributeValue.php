<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Phone
 * @ORM\Entity(repositoryClass="\Application\Repository\CarRepository")
 * @ORM\Table(name="car_attribute_value")
 * @author Daddy
 */
class CarAttributeValue {
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="value")   
     */
    protected $value;
        
    /**
    * @ORM\ManyToOne(targetEntity="Application\Entity\CarAttributeType", inversedBy="carAttributeValues")
    * @ORM\JoinColumn(name="car_attribute_type_id", referencedColumnName="id")
     */
    protected $carAttributeType;

    /**
    * @ORM\ManyToOne(targetEntity="Application\Entity\Car", inversedBy="carAttributeValues")
    * @ORM\JoinColumn(name="car_id", referencedColumnName="id")
     */
    protected $car;
    
    public function getId() 
    {
        return $this->id;
    }

    public function setId($id) 
    {
        $this->id = $id;
    }     

    public function getValue() 
    {
        return $this->value;
    }

    public function setValue($value) 
    {
        $this->value = $value;
    }     

    /*
     * Возвращает type.
     * @return array
     */    
    public function getCarAtributeType() 
    {
        return $this->carAttributeType;
    }
    
    public function setCarAttributeType($carAttributeType)
    {
        $this->carAttributeType = $carAttributeType;
        $carAttributeType->addCarAttributeValue($this);
    }

    /*
     * Возвращает car.
     * @return array
     */    
    public function getCar() 
    {
        return $this->car;
    }
    
    public function setCar($car)
    {
        $this->car = $car;
        $car->addCarAttributeValue($this);
    }

}
