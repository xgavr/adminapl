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


/**
 * Description of Movement
 * @ORM\Entity(repositoryClass="\Stock\Repository\MovementRepository")
 * @ORM\Table(name="movement")
 * @author Daddy
 */
class Movement {
    
    const STATUS_ACTIVE       = 1; // Active.
    const STATUS_RETIRED      = 2; // Retired.
    
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
            self::STATUS_RETIRED => 'Удален'
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
        
}
