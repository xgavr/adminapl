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
 * @ORM\Table(name="attribute")
 * @author Daddy
 */

class Attribute {
    
     // Make status constants.
    const STATUS_ACTIVE       = 1; // Active.
    const STATUS_RETIRED      = 2; // Retired.
        
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
     * @ORM\Column(name="block_no")   
     */
    protected $blockNo;
    
    /**
     * @ORM\Column(name="is_conditional")  
     */
    protected $isConditional;    

    /**
     * @ORM\Column(name="is_interval")   
     */
    protected $isInterval;
    
    /**
     * @ORM\Column(name="is_linked")   
     */
    protected $isLinked;

    /**
     * @ORM\Column(name="name")   
     */
    protected $name;

    /**
     * @ORM\Column(name="short_name")   
     */
    protected $shortName;


    /**
     * @ORM\Column(name="status")  
     */
    protected $status;   
    
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\GoodAttributeValue", inversedBy="attribute") 
     * @ORM\JoinColumn(name="value_id", referencedColumnName="id")
     * 
     */
    protected $attributeValues;    
           
    
    /**
     * Конструктор.
     */
    public function __construct() 
    {
      $this->attributesValues = new ArrayCollection();
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

    public function getBloockNo() 
    {
        return $this->blockNo;
    }

    public function setBlockNo($blockNo) 
    {
        $this->blockNo = $blockNo;
    }     

    public function getIsConditional() 
    {
        return $this->isConditional;
    }

    public function setIsConditional($isConditional) 
    {
        $this->isConditional = $isConditional;
    }     

    public function getIsInterval() 
    {
        return $this->isInterval;
    }

    public function setIsInterval($isInterval) 
    {
        $this->isInterval = $isInterval;
    }     

    public function getIsLinked() 
    {
        return $this->isLinked;
    }

    public function setIsLinked($isLinked) 
    {
        $this->isLinked = $isLinked;
    }     
    
    public function getName() 
    {
        return $this->name;
    }

    public function setName($name) 
    {
        $this->name = $name;
    }     

    public function getShortName() 
    {
        return $this->shortName;
    }

    public function setShortName($shortName) 
    {
        $this->shortName = $shortName;
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
