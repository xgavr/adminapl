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
            
    const EX_NEW            = 1; // не передано
    const EX_TO_TRANSFER    = 3; // нужно передать
    const EX_TRANSFERRED    = 2; // передано.

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
     * @ORM\Column(name="apl_id")   
     */
    protected $aplId = 0;

    /**
     * @ORM\Column(name="value")   
     */
    protected $value;
    
    /**
     * @ORM\Column(name="status_ex")   
     */
    protected $statusEx = self::EX_TO_TRANSFER;    

    /**
     * @ORM\OneToMany(targetEntity="Application\Entity\GoodAttributeValue", mappedBy="attributeValue")
     * @ORM\JoinColumn(name="id", referencedColumnName="value_id")
     * 
     */
    protected $attributeValues;        
    

    public function __construct() {
       $this->attributeValues = new ArrayCollection();
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

    public function getAplId() 
    {
        return $this->aplId;
    }

    public function setAplId($aplId) 
    {
        $this->aplId = $aplId;
    }     
    
    public function getValue() 
    {
        return $this->value;
    }

    public function setValue($value) 
    {
        $this->value = $value;
    }     
    
    /**
     * Returns statusEx.
     * @return int     
     */
    public function getStatusEx() 
    {
        return $this->statusEx;
    }
    
    /**
     * Returns possible statuses as array.
     * @return array
     */
    public static function getStatusExList() 
    {
        return [
            self::EX_NEW => 'Не передано',
            self::EX_TO_TRANSFER => 'Надо передать',
            self::EX_TRENSFERRED => 'Передано',
        ];
    }    
    
    /**
     * Returns user statusEx as string.
     * @return string
     */
    public function getStatusExAsString()
    {
        $list = self::getStatusExList();
        if (isset($list[$this->statusEx])) {
            return $list[$this->statusEx];
        }

        return 'Unknown';
    }  
    
    public function getStatusExName($statusEx)
    {
        $list = self::getStatusExList();
        if (isset($list[$statusEx])) {
            return $list[$statusEx];
        }

        return 'Unknown';        
    }
        
    // Возвращает значения аттрибутов для данного атрибута.
    public function getAttributeValues() 
    {
        return $this->attributeValues;
    }      
    
    // Добавляет новое значение аттрибута к данному атрибуту.
    public function addAttributeValue($attributeValue) 
    {
        $this->attributeValues[] = $attributeValue;        
    }
    
    // Удаляет связь между этим атрибутом и значением аттрибута.
    public function removeAttributeValueAssociation($attributeValue) 
    {
        $this->attributeValues->removeElement($attributeValue);
    }    
}
