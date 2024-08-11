<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cash\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Cash\Entity\Cash;
use Cash\Entity\CashDoc;
use Application\Entity\Order;
use Stock\Entity\Vt;
use Company\Entity\Legal;

/**
 * Description of UserTransaction
 * @ORM\Entity(repositoryClass="\Cash\Repository\CashRepository")
 * @ORM\Table(name="user_transaction")
 * @author Daddy
 */
class UserTransaction {
    
    const STATUS_ACTIVE       = 1; // Active.
    const STATUS_RETIRED      = 2; // Retired.
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="amount")   
     */
    protected $amount;

    /** 
     * @ORM\Column(name="date_oper")  
     */
    protected $dateOper;

    /** 
     * @ORM\Column(name="date_created")  
     */
    protected $dateCreated;  
            
    /** 
     * @ORM\Column(name="doc_stamp")  
     */
    protected $docStamp;  

    /** 
     * @ORM\Column(name="doc_id")  
     */
    protected $docId;  

    /** 
     * @ORM\Column(name="doc_type")  
     */
    protected $docType;  

    /**
     * @ORM\Column(name="status")   
     */
    protected $status;

    /**
     * @ORM\ManyToOne(targetEntity="Cash\Entity\CashDoc", inversedBy="userTransactions") 
     * @ORM\JoinColumn(name="cash_doc_id", referencedColumnName="id")
     */
    private $cashDoc;
        
    /**
     * @ORM\ManyToOne(targetEntity="User\Entity\User", inversedBy="userTransactions") 
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;
    
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Legal", inversedBy="userTransactions") 
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id")
     */
    private $company;    
    
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

    public function getAmount() 
    {
        return $this->amount;
    }

    public function setAmount($amount) 
    {
        $this->amount = $amount;
    }     

    /**
     * Returns the date oper.
     * @return string     
     */
    public function getDateOper() 
    {
        return $this->dateOper;
    }
    
    /**
     * Sets the date when this cash oper.
     * @param string $dateOper     
     */
    public function setDateOper($dateOper) 
    {
        $this->dateOper = $dateOper;
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
    
    public function getDocStamp() {
        return $this->docStamp;
    }

    public function setDocStamp($docStamp) {
        $this->docStamp = $docStamp;
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
     * Returns possible vt status.
     * @param Vt $vt
     * @return integer
     */
    public static function getStatusFromVt($vt) 
    {
        switch ($vt->getStatus()){
            case Vt::STATUS_RETIRED: return self::STATUS_RETIRED;
            case Vt::STATUS_COMMISSION: return self::STATUS_COMMISSION;
            default: return self::STATUS_ACTIVE;    
        }
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
    
    public function getCashDoc()
    {
        return $this->cashDoc;
    }
    
    /**
     * Add cash doc
     * @param CashDoc $cashDoc
     */
    public function setCashDoc($cashDoc)
    {
        $this->cashDoc = $cashDoc;
        if ($cashDoc){
            $cashDoc->addUserTransaction($this);
        }    
    }
        
    public function getUser()
    {
        return $this->user;
    }
    
    /**
     * Add user
     * @param User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
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
    
}
