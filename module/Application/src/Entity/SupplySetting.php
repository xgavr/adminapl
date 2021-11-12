<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * Description of Pricelist
 * @ORM\Entity(repositoryClass="\Application\Repository\SupplierRepository")
 * @ORM\Table(name="supply_setting")
 * @author Daddy
 */
class SupplySetting {
    
     // Supplier status constants.
    const STATUS_ACTIVE       = 1; // Active.
    const STATUS_RETIRED      = 2; // Retired.
    
    const SUPPLY_SAT_POSSIBLE       = 1; // Подвоз в субботу возможен.
    const SUPPLY_SAT_NOT_POSSIBLE    = 2; // Подвоз в субботу не возможен.
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="order_before")   
     */
    protected $orderBefore;
        
    /**
     * @ORM\Column(name="supply_time")   
     */
    protected $supplyTime;
    
    /**
     * @ORM\Column(name="date_created")  
     */
    protected $dateCreated;    

    /**
     * @ORM\Column(name="status")  
     */
    protected $status;    
       
    /**
     * @ORM\Column(name="supply_sat")  
     */
    protected $supplySat;    
       
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Supplier", inversedBy="requestSettings") 
     * @ORM\JoinColumn(name="supplier_id", referencedColumnName="id")
     */
    private $supplier;    
    
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Office", inversedBy="requestSettings") 
     * @ORM\JoinColumn(name="office_id", referencedColumnName="id")
     */
    private $office;    

    public function getId() 
    {
        return $this->id;
    }

    public function setId($id) 
    {
        $this->id = $id;
    }     

    public function getOrderBefore() 
    {
        return $this->orderBefore;
    }

    public function getOrderBeforeHi() 
    {
        return date('H:i', strtotime($this->orderBefore));
    }

    public function getOrderBeforeHMax12() 
    {
        if ($this->supplyTime < 24){
            return 12;
        }
        return max(12, date('H', strtotime($this->orderBefore)));
    }

    public function setOrderBefore($orderBefore) 
    {
        $this->orderBefore = $orderBefore;
    }     

    public function getSupplyTime() 
    {
        return $this->supplyTime;
    }

    public function getSupplyTimeAsDay() 
    {
        if ($this->supplyTime){
            return round($this->supplyTime / 24, 0);
        }
        return 3;
    }

    public function getSupplyTimeAsDayWithSat() 
    {
        $supplyTime = $this->supplyTime;
        if (!$supplyTime){
            $supplyTime = 72;
        }
        if ($this->supplySat == self::SUPPLY_SAT_NOT_POSSIBLE && date('w') === 4 && $supplyTime >= 12){
            $supplyTime += 24;
        }
        return round($supplyTime / 24, 0);
    }

    public function getSupplyTimeColor() 
    {
        $color = '#1C1C1C';
        if ($this->supplyTime <= 6){
            $color = '#66FF00';
        } elseif ($this->supplyTime <= 12){
            $color = '#00FF7F';
        } elseif ($this->supplyTime <= 18){
            $color = '#00FFFF';
        } elseif ($this->supplyTime <= 24){
            $color = '#0000FF';
        } elseif ($this->supplyTime <= 36){
            $color = '#FC0FC0';
        } elseif ($this->supplyTime <= 48){
            $color = '#FF9218';
        }
        
        return $color;
    }
    
    public function getSupplyTimeSpan()
    {
        $span = '<span style="color:#E6E6E6; background-color:';
        $span .= $this->getSupplyTimeColor();
        $span .= '">';
        $span .= $this->getSupplier()->getAplId();
        $span .= '</span>';
        
        return $span;
    }
    
    public function setSupplyTime($supplyTime) 
    {
        $this->supplyTime = $supplyTime;
    }     

    public function getDateCreated() 
    {
        return $this->dateCreated;
    }

    public function setDateCreated($dateCreated) 
    {
        $this->dateCreated = $dateCreated;
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
    
    /**
     * Returns supplySat.
     * @return int     
     */
    public function getSupplySat() 
    {
        return $this->supplySat;
    }
    
    /**
     * Returns possible supplySat as array.
     * @return array
     */
    public static function getSupplySatList() 
    {
        return [
            self::SUPPLY_SAT_POSSIBLE => 'Возможен',
            self::SUPPLY_SAT_NOT_POSSIBLE => 'Не возможен'
        ];
    }    
    
    /**
     * Returns user mode as string.
     * @return string
     */
    public function getSupplySatAsString()
    {
        $list = self::getSupplySatList();
        if (isset($list[$this->supplySat]))
            return $list[$this->supplySat];
        
        return 'Unknown';
    }    
    
    /**
     * Sets supplySat.
     * @param int $supplySat     
     */
    public function setSupplySat($supplySat) 
    {
        $this->supplySat = $supplySat;
    }   
    
    /*
     * Возвращает связанный supplier.
     * @return \Application\Entity\Supplier
     */    
    public function getSupplier() 
    {
        return $this->supplier;
    }

    /**
     * Задает связанный supplier.
     * @param \Application\Entity\Supplier $supplier
     */    
    public function setSupplier($supplier) 
    {
        $this->supplier = $supplier;
        $supplier->addSupplySetting($this);
    }    
        
    /*
     * Возвращает связанный office.
     * @return \Company\Entity\Office
     */    
    public function getOffice() 
    {
        return $this->office;
    }

    /**
     * Задает связанный office.
     * @param \Company\Entity\Office $office
     */    
    public function setOffice($office) 
    {
        $this->office = $office;
//        $office->addSupplySetting($this);
    }    
        
}
