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
 * @ORM\Table(name="good_attribute")
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
     * @ORM\Column(name="assembly_group")  
     */
    protected $assemblyGroup;    

    /**
     * @ORM\Column(name="master_name")   
     */
    protected $masterName;
    /**
     * @ORM\Column(name="usage_name")   
     */
    protected $usageName;

    /**
     * @ORM\Column(name="status")  
     */
    protected $status;    
           
    /**
     * @ORM\Column(name="good_count")  
     */
    protected $goodCount;    

    /**
    * @ORM\OneToMany(targetEntity="Application\Entity\Goods", mappedBy="att")
    * @ORM\JoinColumn(name="id", referencedColumnName="generic_group_id")
     */
    protected $goods;    
    

    public function __construct() {
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

    public function getAssemblyGroup() 
    {
        return $this->assemblyGroup;
    }

    public function setAssemblyGroup($assemblyGroup) 
    {
        $this->assemblyGroup = $assemblyGroup;
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
    
    public function getMasterName() 
    {
        return $this->masterName;
    }

    public function setMasterName($masterName) 
    {
        $this->masterName = $masterName;
    }     
    
    public function getUsageName() 
    {
        return $this->usageName;
    }

    public function setUsageName($usageName) 
    {
        $this->usageName = $usageName;
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
    
    
    public function getGoodCount() 
    {
        return $this->goodCount;
    }

    public function setGoodCount($goodCount) 
    {
        $this->goodCount = $goodCount;
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
