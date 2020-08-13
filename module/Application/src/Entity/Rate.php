<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Application\Entity\Scale;
use Company\Entity\Office;
use Application\Entity\Supplier;
use Application\Entity\Producer;
use Application\Entity\GenericGroup;
use Application\Entity\TokenGroup;

/**
 * Description of Phone
 * @ORM\Entity(repositoryClass="\Application\Repository\RateRepository")
 * @ORM\Table(name="rate")
 * @author Daddy
 */
class Rate 
{
    const STATUS_ACTIVE       = 1; // Active user.
    const STATUS_RETIRED      = 2; // Retired user.   
    
    const MODE_MARKUP       = 1; // наценка.
    const MODE_DISCOUNT     = 2; // скидка.   

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
     * @ORM\Column(name="status")   
     */
    protected $status;
    
    /** 
     * @ORM\Column(name="mode")  
     */
    protected $mode;
    
    /** 
     * @ORM\Column(name="min_price")  
     */
    protected $minPrice;

    /** 
     * @ORM\Column(name="max_price")  
     */
    protected $maxPrice;

    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Scale", inversedBy="rates") 
     * @ORM\JoinColumn(name="scale_id", referencedColumnName="id")
     */
    protected $scale;

    /**
     * @ORM\ManyToOne(targetEntity="\Company\Entity\Office", inversedBy="rates") 
     * @ORM\JoinColumn(name="office_id", referencedColumnName="id")
     */
    protected $office;

    /**
     * @ORM\ManyToOne(targetEntity="Supplier", inversedBy="rates") 
     * @ORM\JoinColumn(name="supplier_id", referencedColumnName="id")
     */
    protected $supplier;

    /**
     * @ORM\ManyToOne(targetEntity="Producer", inversedBy="rates") 
     * @ORM\JoinColumn(name="producer_id", referencedColumnName="id")
     */
    protected $producer;

    /**
     * @ORM\ManyToOne(targetEntity="GenericGroup", inversedBy="rates") 
     * @ORM\JoinColumn(name="generic_group_id", referencedColumnName="id")
     */
    protected $genericGroup;

    /**
     * @ORM\ManyToOne(targetEntity="TokenGroup", inversedBy="rates") 
     * @ORM\JoinColumn(name="token_group_id", referencedColumnName="id")
     */
    protected $tokenGroup;

    
    public function getId() 
    {
        return $this->id;
    }

    public function getLogKey() 
    {
        return 'rate:'.$this->id;
    }

    public function getRateModelFileName() 
    {
        return "rate_scale_{$this->id}.dat";
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

    public function getMinPrice() 
    {
        return $this->minPrice;
    }

    public function setMinPrice($minPrice) 
    {
        $this->minPrice = $minPrice;
    }     

    public function getMaxPrice() 
    {
        return $this->maxPrice;
    }

    public function setMaxPrice($maxPrice) 
    {
        $this->maxPrice = $maxPrice;
    }     

    /**
     * Returns status.
     * @return int     
     */
    public function getStatus() 
    {
        return $this->status;
    }

    public function getStatusCheckbox() 
    {
        if ($this->status == self::STATUS_ACTIVE){
            return 'checked';
        }
        return '';
    }

    /**
     * Returns possible statuses as array.
     * @return array
     */
    public static function getStatusList() 
    {
        return [
            self::STATUS_ACTIVE => 'Действующий',
            self::STATUS_RETIRED => 'Отключен'
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
    
    public function getStatusName($status)
    {
        $list = self::getStatusList();
        if (isset($list[$status]))
            return $list[$status];
        
        return 'Unknown';        
    }
    
    public function setStatus($status)
    {
        $this->status = $status;
    }
    
    /**
     * Returns mode.
     * @return int     
     */
    public function getMode() 
    {
        return $this->mode;
    }

    /**
     * Returns possible modes as array.
     * @return array
     */
    public static function getModeList() 
    {
        return [
            self::MODE_MARKUP => 'Наценка',
            self::MODE_DISCOUNT => 'Скидка'
        ];
    }    
    
    /**
     * Returns user mode as string.
     * @return string
     */
    public function getModeAsString()
    {
        $list = self::getModeList();
        if (isset($list[$this->mode]))
            return $list[$this->mode];
        
        return 'Unknown';
    }    
    
    public function getModeName($mode)
    {
        $list = self::getModeList();
        if (isset($list[$mode]))
            return $list[$mode];
        
        return 'Unknown';        
    }
    
    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    public function getRate() 
    {
        return $this->rate;
    }

    public function setRate($rate) 
    {
        $this->rate = $rate;
    }     

    public function getRounding() 
    {
        return $this->rounding;
    }
    
    public function setRounding($rounding) 
    {
        $this->rounding = $rounding;
    } 

    /*
     * Возвращает связанный scale.
     * @return Scale
     */    
    public function getScale() 
    {
        return $this->scale;
    }

    /**
     * Задает связанный scale.
     * @param Scale $scale
     */    
    public function setScale($scale) 
    {
        $this->scale = $scale;
        $scale->addRate($this);
    }     
        
    /**
     * Задает связанный office.
     * @param Office $office
     */    
    public function setOffice($office) 
    {
        $this->office = $office;
        $office->addRate($this);
    }     
        
    /*
     * Возвращает связанный office.
     * @return Office
     */    
    public function getOffice() 
    {
        return $this->office;
    }

    /**
     * Задает связанный supplier.
     * @param supplier $supplier
     */    
    public function setSupplier($supplier) 
    {
        $this->supplier = $supplier;
        $supplier->addRate($this);
    }     
        
    /*
     * Возвращает связанный supplier.
     * @return Supplier
     */    
    public function getSupplier() 
    {
        return $this->supplier;
    }

    /**
     * Задает связанный producer.
     * @param Producer $producer
     */    
    public function setProducer($producer) 
    {
        $this->producer = $producer;
        $producer->addRate($this);
    }     
        
    /*
     * Возвращает связанный producer.
     * @return Producer
     */    
    public function getProducer() 
    {
        return $this->producer;
    }

    /**
     * Задает связанный genericGroup.
     * @param GenericGroup $genericGroup
     */    
    public function setGenericGroup($genericGroup) 
    {
        $this->genericGroup = $genericGroup;
        $genericGroup->addRate($this);
    }     
        
    /*
     * Возвращает связанный genericGroup.
     * @return GenericGroup
     */    
    public function getGenericGroup() 
    {
        return $this->genericGroup;
    }

    /**
     * Задает связанный tokenGroup.
     * @param TokenGroup $tokenGroup
     */    
    public function setTokenGroup($tokenGroup) 
    {
        $this->tokenGroup = $tokenGroup;
        $tokenGroup->addRate($this);
    }     
        
    /*
     * Возвращает связанный tokenGroup.
     * @return TokenGroup
     */    
    public function getTokenGroup() 
    {
        return $this->tokenGroup;
    }

    /**
     * Лог
     * @return array
     */
    public function toLog()
    {
        return [
            'name' => $this->getName(),
            'status' => $this->getStatus(),
            'mode' => $this->getMode(),
            'minPrice' => $this->getMinPrice(),
            'maxPrice' => $this->getMaxPrice(),
            'scale' => $this->getScale()->getId(),
            'office' => $this->getOffice()->getId(),
            'producer' => ($this->getProducer()) ? $this->getProducer()->getId():null,
            'genericGroup' => ($this->getGenericGroup()) ? $this->getGenericGroup()->getId():null,
            'tokenGroup' => ($this->getTokenGroup()) ? $this->getTokenGroup()->getId():null,
            'tresholds' => [],
        ];
    }    
}
