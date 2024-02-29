<?php
namespace Zp\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use User\Entity\User;
use Company\Entity\Legal;
use Zp\Entity\DocCalculator;
use Cash\Entity\CashDoc;
use Stock\Entity\St;
use Zp\Entity\Accrual;
use Zp\Entity\Position;
use Zp\Entity\PersonalRevise;

/**
 * This class represents a position accrual.
 * @ORM\Entity(repositoryClass="\Zp\Repository\ZpRepository")
 * @ORM\Table(name="personal_mutual")
 */
class PersonalMutual
{
    const STATUS_ACTIVE       = 1; //.
    const STATUS_RETIRED      = 2; // .
    
    const KIND_ACCRUAL_ADM       = 1; // начисление adm
    const KIND_ACCRUAL_RETAIL       = 2; // начисление retail
    const KIND_ACCRUAL_TP       = 3; // начисление tp
    const KIND_DEDUCTION     = 10; // удержание
    const KIND_PAYMENT       = 20; // выплата
    
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
     * @ORM\Column(name="kind")  
     */
    protected $kind;

    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Legal", inversedBy="personalMutuals") 
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id")
     */
    private $company;

    /**
     * @ORM\ManyToOne(targetEntity="User\Entity\User", inversedBy="personalMutuals") 
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="Zp\Entity\Accrual", inversedBy="personalMutuals") 
     * @ORM\JoinColumn(name="accrual_id", referencedColumnName="id")
     */
    private $accrual;

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
     * Returns possible docCalculator status.
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
     * Returns possible personal revise status.
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
     * Returns possible st status.
     * @param St $st
     * @return integer
     */
    public static function getStatusFromSt($st) 
    {
        switch ($st->getStatus()){
            case St::STATUS_ACTIVE: return self::STATUS_ACTIVE;
            default: return self::STATUS_RETIRED;    
        }
    }    

    public function setStatus($status) {
        $this->status = $status;
        return $this;
    }

    public function getKind() {
        return $this->kind;
    }

    /**
     * Returns possible docCalculator kind.
     * @param DocCalculator $docCalculator
     * @return integer
     */
    public static function getKindFromDocCalculator($docCalculator) 
    {
        switch ($docCalculator->getPosition()->getKind()){
            case Position::KIND_ADM: return self::KIND_ACCRUAL_ADM;
            case Position::KIND_RETAIL: return self::KIND_ACCRUAL_RETAIL;
            case Position::KIND_TP: return self::KIND_ACCRUAL_TP;
            default: return self::KIND_ACCRUAL_ADM;    
        }
    }    
    
    /**
     * Returns possible kinds as array.
     * @return array
     */
    public static function getKindList() 
    {
        return [
            self::KIND_ACCRUAL_ADM => 'Начисление Администрация',
            self::KIND_ACCRUAL_Retail => 'Начисление Розница',
            self::KIND_ACCRUAL_TP => 'Начисление ТП',
            self::KIND_DEDUCTION => 'Удержание',
            self::KIND_PAYMENT => 'Выплата',
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
     * @return User
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * 
     * @param User $user
     * @return $this
     */
    public function setUser($user) {
        $this->user = $user;
        return $this;
    }
    
    /**
     * 
     * @return Accrual
     */
    public function getAccrual() {
        return $this->accrual;
    }

    /**
     * 
     * @param Accrual $accrual
     * @return $this
     */
    public function setAccrual($accrual) {
        $this->accrual = $accrual;
        return $this;
    }
   
}



