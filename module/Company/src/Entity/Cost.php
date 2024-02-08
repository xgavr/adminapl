<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Company\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Description of Cost
 * @ORM\Entity(repositoryClass="\Company\Repository\CostRepository")
 * @ORM\Table(name="cost")
 * @author Daddy
 */
class Cost {
    
    const STATUS_ACTIVE       = 1; // Active.
    const STATUS_RETIRED      = 2; // Retired.
    
    const KIND_EXP       = 1; // текущие расходы
    const KIND_FIX       = 2; // постоянные расходы
//    const KIND_OTH       = 3; // прочие расходы
    const KIND_MP       = 4; // расходы ТП
        
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
     * @ORM\Column(name="apl_id")   
     */
    protected $aplId;
    
    /** 
     * @ORM\Column(name="status")  
     */
    protected $status;
    
    /** 
     * @ORM\Column(name="kind")  
     */
    protected $kind;
    
    
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
            self::STATUS_ACTIVE => 'Активный',
            self::STATUS_RETIRED => 'Не доступно',
        ];
    }    
    
    /**
     * Returns user status as string.
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
        
    public function getKind() {
        return $this->kind;
    }

    /**
     * Returns possible kinds as array.
     * @return array
     */
    public static function getKindList() 
    {
        return [
            self::KIND_EXP => 'Текущие',
            self::KIND_FIX => 'Постоянные',
            self::KIND_MP => 'Расходы ТП',
//            self::KIND_OTH => 'Прочие',
        ];
    }    

    /**
     * Returns kind as string.
     * @return string
     */
    public function getKindAsString()
    {
        $list = self::getKindList();
        if (isset($list[$this->kind]))
            return $list[$this->kind];
        
        return 'Unknown';
    }    
    
    public function setKind($kind) {
        $this->kind = $kind;
        return $this;
    }    
        
    /**
     * Массив для формы
     * @return array 
     */
    public function toArray()
    {
        $result = [
            'aplId' => $this->getAplId(),
            'id' => $this->getId(),
            'name' => $this->getName(),
            'status' => $this->getStatus(),
        ];
        
        return $result;
    }                    
}
