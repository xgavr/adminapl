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
 * @ORM\Table(name="car_attribute_group")
 * @author Daddy
 */
class CarAttributeGroup {
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
    * @ORM\OneToMany(targetEntity="Application\Entity\CarAttributeType", mappedBy="carAttributeGroup")
    * @ORM\JoinColumn(name="id", referencedColumnName="car_attribute_group_id")
     */
    protected $carAttributeTypes;

   public function __construct() {
      $this->carAttributeTypes = new ArrayCollection();      
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

    /*
     * Возвращает types.
     * @return array
     */    
    public function getCarAtributeTypes() 
    {
        return $this->carAttributeTypes;
    }
    
    public function addCarAttributeType($carAttributeType)
    {
        $this->carAttributeTypes[] = $carAttributeType;
    }

}
