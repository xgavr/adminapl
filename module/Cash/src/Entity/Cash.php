<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cash\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Company\Entity\Office;

/**
 * Description of Cash
 * @ORM\Entity(repositoryClass="\Cash\Repository\CashRepository")
 * @ORM\Table(name="cash")
 * @author Daddy
 */
class Cash {
    
    const STATUS_ACTIVE       = 1; // Active.
    const STATUS_RETIRED      = 2; // Retired.
    
    const REST_ACTIVE       = 1; // остаток считать.
    const REST_RETIRED      = 2; // остаток не считать.
    
    const TILL_ACTIVE       = 1; // доступно в кассе.
    const TILL_RETIRED      = 2; // не доступно .
    
    const ORDER_ACTIVE       = 1; // доступно в заказе.
    const ORDER_RETIRED      = 2; // не доступно.

    const SUPPLIER_ACTIVE       = 1; // доступно для расчетов.
    const SUPPLIER_RETIRED      = 2; // не доступно.
    
    const REFILL_ACTIVE       = 1; // пополнение доступно.
    const REFILL_RETIRED      = 2; // поплнение не доступно.

    const CHECK_PRINT       = 1; // чек печатать.
    const CHECK_NO_PRINT    = 2; // не печатать.
    const CHECK_IGNORE      = 3; // игнорировать.
    
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
    protected $apl_id;

    /**
     * @ORM\Column(name="commission")   
     */
    protected $commission;

    /**
     * @ORM\Column(name="status")   
     */
    protected $status;

    /**
     * @ORM\Column(name="rest_status")   
     */
    protected $restStatus;

    /**
     * @ORM\Column(name="till_status")   
     */
    protected $tillStatus;

    /**
     * @ORM\Column(name="order_status")   
     */
    protected $orderStatus;

    /**
     * @ORM\Column(name="refill_status")   
     */
    protected $refillStatus;

    /**
     * @ORM\Column(name="supplier_status")   
     */
    protected $supplierStatus;

    /**
     * @ORM\Column(name="check_status")   
     */
    protected $checkStatus;

    /** 
     * @ORM\Column(name="date_created")  
     */
    protected $dateCreated;  
            
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Office", inversedBy="vt") 
     * @ORM\JoinColumn(name="office_id", referencedColumnName="id")
     */
    private $office;
    
    /**
     * Constructor.
     */
    public function __construct() 
    {
        
    }    
    
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
        return $this->apl_id;
    }

    public function setAplId($aplId) 
    {
        $this->apl_id = $aplId;
    }     

    public function getName() 
    {
        return $this->name;
    }

    public function setName($name) 
    {
        $this->name = $name;
    }     

    public function getCommission() 
    {
        return $this->commission;
    }

    public function setComission($comission) 
    {
        $this->commission = $comission;
    }     

    /**
     * Returns the date of cash creation.
     * @return string     
     */
    public function getDateCreated() 
    {
        return $this->dateCreated;
    }
    
    /**
     * Sets the date when this cash was created.
     * @param string $dateCreated     
     */
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
     * Returns cash status as string.
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
     * Returns rest status.
     * @return int     
     */
    public function getRestStatus() 
    {
        return $this->restStatus;
    }

    /**
     * Returns possible rest statuses as array.
     * @return array
     */
    public static function getRestStatusList() 
    {
        return [
            self::REST_ACTIVE => 'Считать',
            self::REST_RETIRED => 'Не считать'
        ];
    }    
    
    /**
     * Returns rest status as string.
     * @return string
     */
    public function getRestStatusAsString()
    {
        $list = self::getRestStatusList();
        if (isset($list[$this->restStatus]))
            return $list[$this->restStatus];
        
        return 'Unknown';
    }    
    
    /**
     * Sets rest status.
     * @param int $restStatus     
     */
    public function setRestStatus($restStatus) 
    {
        $this->restStatus = $restStatus;
    }   
    
    /**
     * Returns till status.
     * @return int     
     */
    public function getTillStatus() 
    {
        return $this->tillStatus;
    }

    /**
     * Returns possible till statuses as array.
     * @return array
     */
    public static function getTillStatusList() 
    {
        return [
            self::TILL_ACTIVE => 'Доступно',
            self::TILL_RETIRED => 'Не доступно'
        ];
    }    
    
    /**
     * Returns till status as string.
     * @return string
     */
    public function getTillStatusAsString()
    {
        $list = self::getTillStatusList();
        if (isset($list[$this->tillStatus]))
            return $list[$this->tillStatus];
        
        return 'Unknown';
    }    
    
    /**
     * Sets till status.
     * @param int $tillStatus     
     */
    public function setTillStatus($tillStatus) 
    {
        $this->tillStatus = $tillStatus;
    }   
    
    /**
     * Returns order status.
     * @return int     
     */
    public function getOrderStatus() 
    {
        return $this->orderStatus;
    }

    /**
     * Returns possible order statuses as array.
     * @return array
     */
    public static function getOrderStatusList() 
    {
        return [
            self::ORDER_ACTIVE => 'Доступно',
            self::ORDER_RETIRED => 'Не доступно'
        ];
    }    
    
    /**
     * Returns order status as string.
     * @return string
     */
    public function getOrderStatusAsString()
    {
        $list = self::getOrderStatusList();
        if (isset($list[$this->orderStatus]))
            return $list[$this->orderStatus];
        
        return 'Unknown';
    }    
    
    /**
     * Sets order status.
     * @param int $orderStatus     
     */
    public function setOrderStatus($orderStatus) 
    {
        $this->orderStatus = $orderStatus;
    }   
    
    /**
     * Returns refill status.
     * @return int     
     */
    public function getRefillStatus() 
    {
        return $this->refillStatus;
    }
    
    /**
     * Returns possible refill statuses as array.
     * @return array
     */
    public static function getRefillStatusList() 
    {
        return [
            self::REFILL_ACTIVE => 'Доступно',
            self::REFILL_RETIRED => 'Не доступно'
        ];
    }    
    
    /**
     * Returns refill status as string.
     * @return string
     */
    public function getRefillStatusAsString()
    {
        $list = self::getRefillStatusList();
        if (isset($list[$this->refillStatus]))
            return $list[$this->refillStatus];
        
        return 'Unknown';
    }    
    
    /**
     * Sets refill status.
     * @param int $refillStatus     
     */
    public function setRefillStatus($refillStatus) 
    {
        $this->refillStatus = $refillStatus;
    }   
    
    /**
     * Returns supplier status.
     * @return int     
     */
    public function getSupplierStatus() 
    {
        return $this->supplierStatus;
    }
    /**
     * Returns possible supplier statuses as array.
     * @return array
     */
    public static function getSupplierStatusList() 
    {
        return [
            self::SUPPLIER_ACTIVE => 'Доступно',
            self::SUPPLIER_RETIRED => 'Не доступно'
        ];
    }    
    
    /**
     * Returns supplier status as string.
     * @return string
     */
    public function getSupplierStatusAsString()
    {
        $list = self::getSupplierStatusList();
        if (isset($list[$this->supplierStatus]))
            return $list[$this->supplierStatus];
        
        return 'Unknown';
    }    
    
    /**
     * Sets supplier status.
     * @param int $supplierStatus     
     */
    public function setSupplierStatus($supplierStatus) 
    {
        $this->supplierStatus = $supplierStatus;
    }   

    /**
     * Returns check status.
     * @return int     
     */
    public function getCheckStatus() 
    {
        return $this->checkStatus;
    }

    /**
     * Returns possible check statuses as array.
     * @return array
     */
    public static function getCheckStatusList() 
    {
        return [
            self::CHECK_PRINT => 'Печатать',
            self::CHECK_NO_PRINT => 'Не печатать',
            self::CHECK_IGNORE => 'Игнорировать',
        ];
    }    
    
    /**
     * Returns check status as string.
     * @return string
     */
    public function getCheckStatusAsString()
    {
        $list = self::getCheckStatusList();
        if (isset($list[$this->checkStatus]))
            return $list[$this->checkStatus];
        
        return 'Unknown';
    }    

    /**
     * Sets check status.
     * @param int $checkStatus     
     */
    public function setCheckStatus($checkStatus) 
    {
        $this->checkStatus = $checkStatus;
    }   
    
    public function getOffice()
    {
        return $this->office;
    }
    
    /**
     * Add office
     * @param Office $office
     */
    public function setOffice($office)
    {
        $this->office = $office;
        $office->addCash($this);
    }

    /**
     * Массив для формы
     * @return array 
     */
    public function toArray()
    {
        $result = [
            'status' => $this->getStatus(),
            'name' => $this->getName(),
            'aplId' => $this->getAplId(),
            'checkStatus' => $this->getCheckStatus(),
            'commission' => $this->getCommission(),
            'orderStatus' => $this->getOrderStatus(),
            'restStatus' => $this->getRestStatus(),
            'tillStatus' => $this->getTillStatus(),
            'refillStatus' => $this->getRefillStatus(),
            'supplierStatus' => $this->getSupplierStatus(),
        ];
        
        return $result;
    }    
}
