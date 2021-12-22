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

/**
 * Description of CashTransaction
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
        $cashDoc->addUserTransaction($this);
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
}
