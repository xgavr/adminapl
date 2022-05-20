<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Stock\Entity;

use Doctrine\ORM\Mapping as ORM;
use Company\Entity\Legal;
use Company\Entity\Office;
use Application\Entity\Goods;
use Stock\Entity\Pt;
use Stock\Entity\Vt;
use Stock\Entity\St;
use Stock\Entity\Ptu;
use Stock\Entity\Ot;
use Application\Entity\Order;


/**
 * Description of Movement
 * @ORM\Entity(repositoryClass="\Stock\Repository\MovementRepository")
 * @ORM\Table(name="movement")
 * @author Daddy
 */
class Movement {
    
    const STATUS_ACTIVE       = 1; // Active.
    const STATUS_RETIRED      = 2; // Retired.
    const STATUS_COMMISSION    = 3; // commission.
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="doc_key")   
     */
    protected $docKey;
    
    /**
     * @ORM\Column(name="doc_row_key")   
     */
    protected $docRowKey;
    
    /**
     * @ORM\Column(name="doc_row_no")   
     */
    protected $docRowNo;

    /** 
     * @ORM\Column(name="date_oper")  
     */
    protected $dateOper;
    
    /**
     * @ORM\Column(name="status")   
     */
    protected $status;

    /** 
     * @ORM\Column(name="quantity")  
     */
    protected $quantity;

    /** 
     * @ORM\Column(name="amount")  
     */
    protected $amount;

    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Goods", inversedBy="movements") 
     * @ORM\JoinColumn(name="good_id", referencedColumnName="id")
     */
    private $good;
            
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Office", inversedBy="movements") 
     * @ORM\JoinColumn(name="office_id", referencedColumnName="id")
     */
    private $office;    
    
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Legal", inversedBy="movements") 
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id")
     */
    private $company;    
    
    public function __construct() {
    }
   
    public function getId() 
    {
        return $this->id;
    }

    public function setId($id) 
    {
        $this->id = $id;
    }     

    public function getDocKey() 
    {
        return $this->docKey;
    }

    public function setDocKey($docKey) 
    {
        $this->docKey = $docKey;
    }     

    public function getDocRowKey() 
    {
        return $this->docRowKey;
    }

    public function setDocRowKey($docRowKey) 
    {
        $this->docRowKey = $docRowKey;
    }     

    public function getDocRowNo() 
    {
        return $this->docRowNo;
    }

    public function setDocRowNo($docRowNo) 
    {
        $this->docRowNo = $docRowNo;
    }     

    /**
     * Returns the date of operation.
     * @return string     
     */
    public function getDateOper() 
    {
        return $this->dateOper;
    }
    
    /**
     * Sets the date when this oper was created.
     * @param string $dateOper     
     */
    public function setDateOper($dateOper) 
    {
        $this->dateOper = $dateOper;
    }    
                
    /**
     * Sets  amount.
     * @param float $amount     
     */
    public function setAmount($amount) 
    {
        $this->amount = $amount;
    }    
    
    /**
     * Returns the amount of doc.
     * @return float     
     */
    public function getAmount() 
    {
        return $this->amount;
    }
    
    /**
     * Sets  quantity.
     * @param float $quantity     
     */
    public function setQuantity($quantity) 
    {
        $this->quantity = $quantity;
    }    
    
    /**
     * Returns the quantity of doc.
     * @return float     
     */
    public function getQuantity() 
    {
        return $this->quantity;
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
            self::STATUS_RETIRED => 'Удален',
            self::STATUS_COMMISSION => 'На комиссии',
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
     * Returns possible order status.
     * @param Order $order
     * @return integer
     */
    public static function getStatusFromOrder($order) 
    {
        switch ($order->getStatus()){
            case Order::STATUS_SHIPPED: return self::STATUS_ACTIVE;
            default: return self::STATUS_RETIRED;    
        }
    }    

    /**
     * Returns possible pt status.
     * @param Pt $pt
     * @return integer
     */
    public static function getStatusFromPt($pt) 
    {
        switch ($pt->getStatus()){
            case Pt::STATUS_RETIRED: return self::STATUS_RETIRED;
            default: return self::STATUS_ACTIVE;    
        }
    }    

    /**
     * Returns possible vt status.
     * @param Vt $vt
     * @return integer
     */
    public static function getStatusFromVt($vt) 
    {
        switch ($vt->getStatus()){
            case Vt::STATUS_RETIRED: return self::STATUS_RETIRED;
            default: return self::STATUS_ACTIVE;    
        }
    }    

    /**
     * Returns possible st status.
     * @param St $st
     * @return integer
     */
    public static function getStatusFromSt($st) 
    {
        switch ($st->getStatus()){
            case St::STATUS_RETIRED: return self::STATUS_RETIRED;
            default: return self::STATUS_ACTIVE;    
        }
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
     * Returns the office.
     * @return Office     
     */
    public function getOffice() 
    {
        return $this->office;
    }

    /**
     * Returns the good.
     * @return Goods     
     */
    public function getGood() 
    {
        return $this->good;
    }
    
    /*
     * @return Legal
     */    
    public function getCompany() 
    {
        return $this->company;
    }

    /**
     * @param Legal $company
     */    
    public function setCompany($company) 
    {
        $this->company = $company;
    }                         
}
