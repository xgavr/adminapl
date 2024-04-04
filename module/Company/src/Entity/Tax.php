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
 * Description of Tax
 * @ORM\Entity(repositoryClass="\Company\Repository\TaxRepository")
 * @ORM\Table(name="tax")
 * @author Daddy
 */
class Tax {
    
    const STATUS_ACTIVE       = 1; // Active.
    const STATUS_RETIRED      = 2; // Retired.
    
    const KIND_NDS       = 1; // НДС
    const KIND_PROFIT       = 2; // прибыль
    const KIND_PROGIT_MIN   = 3; // прибыль минимальный
    const KIND_ESN       = 4; // ЕСН
    const KIND_NDFL    = 5; // подоходный
    
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
     * @ORM\Column(name="kind")   
     */
    protected $kind;

    /**
     * @ORM\Column(name="status")   
     */
    protected $status;

    /**
     * @ORM\Column(name="date_start")   
     */
    protected $dateStart;

    /**
     * @ORM\Column(name="amount")   
     */
    protected $amount;
    
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

    public function getAmount() 
    {
        return $this->amount;
    }

    public function setAmount($amount) 
    {
        $this->amount = $amount;
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
            self::KIND_NDFL => 'Подоходный',
            self::KIND_ESN => 'ЕСН',
            self::KIND_PROFIT => 'Прибыль',
            self::KIND_PROGIT_MIN => 'Прибыль минимальный',
            self::KIND_NDS => 'НДС',
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
    
    public function getDateStart() {
        return $this->dateStart;
    }

    public function setDateStart($dateStart) {
        $this->dateStart = $dateStart;
        return $this;
    }
    
}
