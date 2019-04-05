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
 * @ORM\Entity(repositoryClass="\Application\Repository\AttributeRepository")
 * @ORM\Table(name="attribute_value")
 * @author Daddy
 */

class AttributeValue {
            
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
            
    /**
     * @ORM\Column(name="td_id")   
     */
    protected $tdId;
    
    /**
     * @ORM\Column(name="value")   
     */
    protected $value;
    
    /**
    * @ORM\OneToMany(targetEntity="Application\Entity\Attribute", mappedBy="value")
    * @ORM\JoinColumn(name="id", referencedColumnName="value_id")
     */
    protected $attributes;    
    

    public function __construct() {
       $this->attributes = new ArrayCollection();
    }    
    
    public function getId() 
    {
        return $this->id;
    }

    public function setId($id) 
    {
        $this->id = $id;
    }     

    public function getTdId() 
    {
        return $this->tdId;
    }

    public function setTdId($tdId) 
    {
        $this->tdId = $tdId;
    }     

    public function getValue() 
    {
        return $this->value;
    }

    public function setValue($value) 
    {
        $this->value = $value;
    }     
    
    public function getAttributes() 
    {
        return $this->attributes;
    }
    
    public function addAttribute($attribute) 
    {
        $this->attributes[] = $attribute;        
    }     
}
