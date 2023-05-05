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
use Application\Entity\Contact;
use ApiMarketPlace\Entity\MarketSaleReport;


/**
 * Description of Comiss
 * @ORM\Entity(repositoryClass="\Stock\Repository\ComitentRepository")
 * @ORM\Table(name="comitent")
 * @author Daddy
 */
class Comitent {
    
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
     * @ORM\Column(name="doc_type")   
     */
    protected $docType;
    
    /**
     * @ORM\Column(name="doc_id")   
     */
    protected $docId;

    /**
     * @ORM\Column(name="base_key")   
     */
    protected $baseKey;
    
    /**
     * @ORM\Column(name="base_type")   
     */
    protected $baseType;
    
    /**
     * @ORM\Column(name="base_id")   
     */
    protected $baseId;

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
     * @ORM\Column(name="doc_stamp")   
     */
    protected $docStamp;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Goods", inversedBy="comitent") 
     * @ORM\JoinColumn(name="good_id", referencedColumnName="id")
     */
    private $good;
                
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Legal", inversedBy="comitent") 
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id")
     */
    private $company;    
    
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Legal", inversedBy="comitent") 
     * @ORM\JoinColumn(name="legal_id", referencedColumnName="id")
     */
    private $legal;    

    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Contract", inversedBy="comitent") 
     * @ORM\JoinColumn(name="contract_id", referencedColumnName="id")
     */
    private $contract;    
    
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

    public function getDocType() 
    {
        return $this->docType;
    }

    public function setDocType($docType) 
    {
        $this->docType = $docType;
    }     

    public function getDocId() 
    {
        return $this->docId;
    }

    public function setDocId($docId) 
    {
        $this->docId = $docId;
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
        
    public function getDocStamp() 
    {
        return $this->docStamp;
    }

    public function setDocStamp($docStamp) 
    {
        $this->docStamp = $docStamp;
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
     * Returns possible st status.
     * @param MarketSaleReport $marketSaleReport
     * @return integer
     */
    public static function getStatusFromMarketSaleReport($marketSaleReport) 
    {
        switch ($marketSaleReport->getStatus()){
            case MarketSaleReport::STATUS_RETIRED: return self::STATUS_RETIRED;
            default: return self::STATUS_ACTIVE;    
        }
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
    
    public function getBaseKey() {
        return $this->baseKey;
    }

    public function getBaseType() {
        return $this->baseType;
    }

    public function getBaseId() {
        return $this->baseId;
    }

    public function setBaseKey($baseKey): void {
        $this->baseKey = $baseKey;
    }

    public function setBaseType($baseType): void {
        $this->baseType = $baseType;
    }

    public function setBaseId($baseId): void {
        $this->baseId = $baseId;
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

    /*
     * @return Legal
     */    
    public function getLegal() 
    {
        return $this->legal;
    }

    /**
     * @param Legal $legal
     */    
    public function setLegal($legal) 
    {
        $this->legal = $legal;
    }                         

    /*
     * @return Contact
     */    
    public function getContract() 
    {
        return $this->contact;
    }

    /**
     * @param Contact $contract
     */    
    public function setContract($contract) 
    {
        $this->contract = $contract;
    }                         
}
