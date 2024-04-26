<?php
namespace Zp\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use User\Entity\User;
use Company\Entity\Legal;
use Zp\Entity\Accrual;

/**
 * This class represents a position accrual.
 * @ORM\Entity(repositoryClass="\Zp\Repository\ZpRepository")
 * @ORM\Table(name="personal_revise")
 */
class PersonalRevise
{
    const STATUS_ACTIVE       = 1; //.
    const STATUS_RETIRED      = 2; // .
    
    const KIND_OPEN_BALANCE = 1; //начальный остаток
    const KIND_VACATION = 2; //отпуск
    const KIND_FINE = 3; //штраф
    const KIND_BONUS = 4; //премия, разовое начисление
    
    /**
     * @ORM\Id
     * @ORM\Column(name="id")
     * @ORM\GeneratedValue
     */
    protected $id;

    /** 
     * @ORM\Column(name="doc_date")  
     */
    protected $docDate;

    /** 
     * @ORM\Column(name="doc_num")  
     */
    protected $docNum;

    /** 
     * @ORM\Column(name="comment")  
     */
    protected $comment;

    /** 
     * @ORM\Column(name="date_created")  
     */
    protected $dateCreated;

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
     * @ORM\ManyToOne(targetEntity="Company\Entity\Legal", inversedBy="personalRevises") 
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id")
     */
    private $company;

    /**
     * @ORM\ManyToOne(targetEntity="User\Entity\User", inversedBy="personalRevises") 
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="Zp\Entity\Accrual", inversedBy="personalRevises") 
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

    public function getLogKey() 
    {
        return 'zprv:'.$this->id;
    }
    
    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function getDocDate() {
        return $this->docDate;
    }

    public function setDocDate($docDate) {
        $this->docDate = $docDate;
        return $this;
    }

    public function getDocNum() {
        return $this->docNum;
    }

    public function setDocNum($docNum) {
        $this->docNum = $docNum;
        return $this;
    }

    public function getComment() {
        return $this->comment;
    }

    public function setComment($comment) {
        $this->comment = $comment;
        return $this;
    }

    public function getDateCreated() {
        return $this->dateCreated;
    }

    public function setDateCreated($dateCreated) {
        $this->dateCreated = $dateCreated;
        return $this;
    }    
    
    public function getAmount() {
        return $this->amount;
    }

    public function setAmount($amount) {
        $this->amount = $amount;
        return $this;
    }
    
    public function setStatus($status) {
        $this->status = $status;
        return $this;
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
        
    public function getStatus() {
        return $this->status;
    }
    
    public function setKind($kind) {
        $this->kind = $kind;
        return $this;
    }
            
    /**
     * Returns possible kind as array.
     * @return array
     */
    public static function getKindList() 
    {
        return [
            self::KIND_VACATION => 'Отпуск',
            self::KIND_BONUS => 'Премия',
            self::KIND_FINE => 'Штраф',
            self::KIND_OPEN_BALANCE => 'Начальный остаток',
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
        
    public function getKind() {
        return $this->kind;
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



