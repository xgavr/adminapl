<?php
namespace Company\Entity;

use Doctrine\ORM\Mapping as ORM;
//use Doctrine\Common\Collections\ArrayCollection;
use Company\Entity\Legal;
use Cash\Entity\CashDoc;
use Stock\Entity\St;
use Company\Entity\Tax;
use Stock\Entity\Ptu;
use Zp\Entity\DocCalculator;
use Zp\Entity\PersonalRevise;

/**
 * This class represents a position accrual.
 * @ORM\Entity(repositoryClass="\Company\Repository\TaxRepository")
 * @ORM\Table(name="tax_mutual")
 */
class TaxMutual
{
    const STATUS_ACTIVE       = 1; //.
    const STATUS_RETIRED      = 2; // .
    
    /**
     * @ORM\Id
     * @ORM\Column(name="id")
     * @ORM\GeneratedValue
     */
    protected $id;

    /** 
     * @ORM\Column(name="doc_id")  
     */
    protected $docId;
    
    /** 
     * @ORM\Column(name="doc_type")  
     */
    protected $docType;
    
    /** 
     * @ORM\Column(name="doc_key")  
     */
    protected $docKey;

    /** 
     * @ORM\Column(name="doc_stamp")  
     */
    protected $docStamp;

    /** 
     * @ORM\Column(name="date_oper")  
     */
    protected $dateOper;

    /** 
     * @ORM\Column(name="amount")  
     */
    protected $amount;
    
    /** 
     * @ORM\Column(name="status")  
     */
    protected $status;

    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Legal", inversedBy="costMutuals") 
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id")
     */
    private $company;

    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Tax", inversedBy="taxMutuals") 
     * @ORM\JoinColumn(name="tax_id", referencedColumnName="id")
     */
    private $tax;

    /**
     * Constructor.
     */
    public function __construct() 
    {
    }
    
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function getDocId() {
        return $this->docId;
    }

    public function setDocId($docId) {
        $this->docId = $docId;
        return $this;
    }

    public function getDocType() {
        return $this->docType;
    }

    public function setDocType($docType) {
        $this->docType = $docType;
        return $this;
    }

    public function getDocKey() {
        return $this->docKey;
    }

    public function setDocKey($docKey) {
        $this->docKey = $docKey;
        return $this;
    }

    public function getDocStamp() {
        return $this->docStamp;
    }

    public function setDocStamp($docStamp) {
        $this->docStamp = $docStamp;
        return $this;
    }

    public function getDateOper() {
        return $this->dateOper;
    }

    public function setDateOper($dateOper) {
        $this->dateOper = $dateOper;
        return $this;
    }

    public function getAmount() {
        return $this->amount;
    }

    public function setAmount($amount) {
        $this->amount = $amount;
        return $this;
    }
        
    public function getStatus() {
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
        ];
    }    

    /**
     * Returns status as string.
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
     * Returns possible cashDoc status.
     * @param CashDoc $cashDoc
     * @return integer
     */
    public static function getStatusFromCashDoc($cashDoc) 
    {
        switch ($cashDoc->getStatus()){
            case CashDoc::STATUS_ACTIVE: return self::STATUS_ACTIVE;
            default: return self::STATUS_RETIRED;    
        }
    }    

    /**
     * Returns possible PersonalRevise status.
     * @param PersonalRevise $personalRevise
     * @return integer
     */
    public static function getStatusFromPersonalRevise($personalRevise) 
    {
        switch ($personalRevise->getStatus()){
            case PersonalRevise::STATUS_ACTIVE: return self::STATUS_ACTIVE;
            default: return self::STATUS_RETIRED;    
        }
    }    

    /**
     * Returns possible st status.
     * @param DocCalculator $docCalculator
     * @return integer
     */
    public static function getStatusFromDocCalculator($docCalculator) 
    {
        switch ($docCalculator->getStatus()){
            case DocCalculator::STATUS_ACTIVE: return self::STATUS_ACTIVE;
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
            case Ptu::STATUS_ACTIVE: return self::STATUS_ACTIVE;
            default: return self::STATUS_RETIRED;    
        }
    }    

    public function setStatus($status) {
        $this->status = $status;
        return $this;
    }

    /**
     * 
     * @return Legal
     */
    public function getCompany() {
        return $this->company;
    }

    /**
     * 
     * @param Legal $company
     * @return $this
     */
    public function setCompany($company) {
        $this->company = $company;
        return $this;
    }

    /**
     * 
     * @return Tax
     */
    public function getTax() {
        return $this->tax;
    }

    /**
     * 
     * @param Tax $tax
     * @return $this
     */
    public function setTax($tax) {
        $this->tax = $tax;
        return $this;
    }    
}



