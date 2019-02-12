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
 * Description of Phone
 * @ORM\Entity(repositoryClass="\Application\Repository\CarRepository")
 * @ORM\Table(name="car_attribute_type")
 * @author Daddy
 */
class CarAttributeType {
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
     * @ORM\Column(name="title")   
     */
    protected $title;
    
    /**
    * @ORM\ManyToOne(targetEntity="Application\Entity\CarAttributeGroup", mappedBy="carAttributeTypes")
    * @ORM\JoinColumn(name="car_attribute_group_id", referencedColumnName="id")
     */
    protected $carAttributeGroup;
    
    /**
    * @ORM\OneToMany(targetEntity="Application\Entity\CarAttributeValue", mappedBy="carAttributeType")
    * @ORM\JoinColumn(name="id", referencedColumnName="car_attribute_type_id")
     */
    protected $carAttributeValues;    

    public function __construct() {
       $this->carAttributeValues = new ArrayCollection();      
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

    public function getTitle() 
    {
        return $this->title;
    }

    public function setTitle($title) 
    {
        $this->title = $title;
    }     

    /*
     * Возвращает group.
     * @return array
     */    
    public function getCarAtributeGroup() 
    {
        return $this->carAttributeGroup;
    }
    
    public function setCarAttributeGroup($carAttributeGroup)
    {
        $this->carAttributeGroup = $carAttributeGroup;
        $carAttributeGroup->addCarAttributeType($this);
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


}
