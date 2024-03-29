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
use Company\Entity\Contract;
use Stock\Entity\Pt;
use Stock\Entity\Vt;
use Application\Entity\Order;
use Stock\Entity\Revision;
use ApiMarketPlace\Entity\MarketSaleReport;
use Stock\Entity\Ptu;
use Stock\Entity\Vtp;
use Cash\Entity\CashDoc;

/**
 * Description of Mutual
 * @ORM\Entity(repositoryClass="\Stock\Repository\MutualRepository")
 * @ORM\Table(name="mutual")
 * @author Daddy
 */
class Mutual {
    
    const STATUS_ACTIVE       = 1; // Active.
    const STATUS_RETIRED      = 2; // Retired.
    const STATUS_COMMISSION    = 3; // commission.
    
    const REVISE_OK       = 1; // Сверка ок.
    const REVISE_NOT      = 2; // Сверки нет.

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
     * @ORM\Column(name="doc_stamp")   
     */
    protected $docStamp;

    /** 
     * @ORM\Column(name="date_oper")  
     */
    protected $dateOper;
    
    /**
     * @ORM\Column(name="status")   
     */
    protected $status;

    /**
     * @ORM\Column(name="revise")   
     */
    protected $revise;

    /** 
     * @ORM\Column(name="amount")  
     */
    protected $amount;

    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Legal", inversedBy="mutuals") 
     * @ORM\JoinColumn(name="legal_id", referencedColumnName="id")
     */
    private $legal;
    
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Contract", inversedBy="mutuals") 
     * @ORM\JoinColumn(name="contract_id", referencedColumnName="id")
     */
    private $contract;
    
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Office", inversedBy="mutuals") 
     * @ORM\JoinColumn(name="office_id", referencedColumnName="id")
     */
    private $office;    
    
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Legal", inversedBy="companyMutuals") 
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id")
     */
    private $company;
    
    /**
     * @ORM\ManyToOne(targetEntity="Cash\Entity\CashDoc", inversedBy="mutuals") 
     * @ORM\JoinColumn(name="doc_id", referencedColumnName="id")
     */
    private $cashDoc;    
        
    /**
     * @ORM\ManyToOne(targetEntity="Stock\Entity\Revise", inversedBy="mutuals") 
     * @ORM\JoinColumn(name="doc_id", referencedColumnName="id")
     */
    private $reviseDoc;    
    
    /**
     * @ORM\OneToOne(targetEntity="Stock\Entity\Revision", inversedBy="mutual") 
     * @ORM\JoinColumn(name="revision_id", referencedColumnName="id")
     */
   private $revision;        

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

    public function setDocKey($docKey) 
    {
        $this->docKey = $docKey;
    }     

    public function getDocKey() 
    {
        return $this->docKey;
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
    
    public function getDocStamp() 
    {
        return $this->docStamp;
    }

    public function setDocStamp($docStamp) 
    {
        $this->docStamp = $docStamp;
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
     * Returns status.
     * @return int     
     */
    public function getStatus() 
    {
        return $this->status;
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
     * Returns possible ptu status.
     * @param Ptu $ptu
     * @return integer
     */
    public static function getStatusFromPtu($ptu) 
    {
        switch ($ptu->getStatus()){
            case Ptu::STATUS_RETIRED: return self::STATUS_RETIRED;
            case Ptu::STATUS_COMMISSION: return self::STATUS_COMMISSION;
            default: return self::STATUS_ACTIVE;    
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
            case Vt::STATUS_COMMISSION: 
            case Vt::STATUS_DEFECT: 
            case Vt::STATUS_WAIT: 
                return self::STATUS_COMMISSION;
            default: return self::STATUS_ACTIVE;    
        }
    }    

    /**
     * Returns possible vtp status.
     * @param Vtp $vtp
     * @return integer
     */
    public static function getStatusFromVtp($vtp) 
    {
        if ($vtp->getStatusDoc() == Vtp::STATUS_DOC_NOT_RECD){
            switch ($vtp->getStatus()){
                case Vtp::STATUS_RETIRED: 
                    return self::STATUS_RETIRED;
                default: 
                    return self::STATUS_ACTIVE;    
            }
        }    
        
        return self::STATUS_RETIRED;
    }    

    /**
     * Returns possible report status.
     * @param MarketSaleReport $report
     * @return integer
     */
    public static function getStatusFromReport($report) 
    {
        switch ($report->getStatus()){
            case MarketSaleReport::STATUS_RETIRED: return self::STATUS_RETIRED;
            default: return self::STATUS_ACTIVE;    
        }
    }    

    /**
     * Returns possible cashdoc status.
     * @param CashDoc $cashDoc
     * @return integer
     */
    public static function getStatusFromCashdoc($cashDoc) 
    {
        switch ($cashDoc->getStatus()){
            case CashDoc::STATUS_RETIRED: return self::STATUS_RETIRED;
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
     * Sets status.
     * @param int $status     
     */
    public function setStatus($status) 
    {
        $this->status = $status;
    }   
    
    /**
     * Returns revise.
     * @return int     
     */
    public function getRevise() 
    {
        return $this->revise;
    }

    /**
     * Returns possible revises as array.
     * @return array
     */
    public static function getReviseList() 
    {
        return [
            self::REVISE_OK => 'Проверено',
            self::REVISE_NOT => 'Не проверено'
        ];
    }    
    
    /**
     * Returns revise as string.
     * @return string
     */
    public function getReviseAsString()
    {
        $list = self::getReviseList();
        if (isset($list[$this->revise]))
            return $list[$this->revise];
        
        return 'Unknown';
    }    
    
    /**
     * Sets revise.
     * @param int $revise     
     */
    public function setRevise($revise) 
    {
        $this->revise = $revise;
    }   
    
    /**
     * Returns the legal.
     * @return Legal     
     */
    public function getLegal() 
    {
        return $this->legal;
    }

    /**
     * Returns the contract.
     * @return Contract     
     */
    public function getContract() 
    {
        return $this->contract;
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
     * Returns the company.
     * @return Legal     
     */
    public function getCompany() 
    {
        return $this->company;
    }

    /*
     * @return CashDoc
     */    
    public function getCashDoc() 
    {
        return $this->cashDoc;
    }

    /**
     * @param CashDoc $cashDoc
     */    
    public function setCashDoc($cashDoc) 
    {
        $this->cashDoc = $cashDoc;
    }                         

    /*
     * @return Revise
     */    
    public function getReviseDoc() 
    {
        return $this->reviseDoc;
    }

    /**
     * @param Revise $reviseDoc
     */    
    public function setReviseDoc($reviseDoc) 
    {
        $this->reviseDoc = $reviseDoc;
    }                         
    
    /**
     * 
     * @return Revision
     */
    public function getRevision() {
        return $this->revision;
    }

    /**
     * 
     * @param Revision $revision
     * @return $this
     */
    public function setRevision($revision) {
        $this->revision = $revision;
        return $this;
    }
}
