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
 * @ORM\Table(name="good_attribute_value")
 * @author Daddy
 */

class GoodAttributeValue {
            
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
            
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Goods", inversedBy="attributeValues") 
     * @ORM\JoinColumn(name="good_id", referencedColumnName="id")
     * 
     */
    protected $good;    
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Attribute", inversedBy="attributeValues") 
     * @ORM\JoinColumn(name="attribute_id", referencedColumnName="id")
     * 
     */
    protected $attribute;    
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\AttributeValue", inversedBy="attributeValues") 
     * @ORM\JoinColumn(name="attribute_value_id", referencedColumnName="id")
     * 
     */
    protected $attributeValue;    


    public function getId() 
    {
        return $this->id;
    }

    public function setId($id) 
    {
        $this->id = $id;
    }     
    
    // Возвращает товар, связанный с данным атрибутом.
    public function getGood() 
    {
        return $this->good;
    }
    
    /**
     * Устанавливает товар, связанный с этим значением.
     * @param \Application\Entity\Goods $good 
     */
    public function setGood($good) 
    {
        $this->good = $good;
        $good->addAttributeValue($this);        
    }     
    
    public function getAttribute() 
    {
        return $this->attribute;
    }
    
    /**
     * 
     * @param \Application\Entity\Attribute $attribute
     */
    public function addAttribute($attribute) 
    {
        $this->attribute = $attribute;
        $attribute->addAttributeValue($this);
    }     


    public function getAttributeValue() 
    {
        return $this->attributeValue;
    }
    
    /**
     * 
     * @param \Application\Entity\AttributeValue $attributeValue
     */
    public function addAttributeValue($attributeValue) 
    {
        $this->attributeValue = $attributeValue;
        $attributeValue->addAttributeValue($this);
    }     

    
}
