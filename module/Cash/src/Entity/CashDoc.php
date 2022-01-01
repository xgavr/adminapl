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
use Company\Entity\Legal;
use Application\Entity\Order;
use Application\Entity\Contact;
use Stock\Entity\Vt;
use Company\Entity\Cost;
use Cash\Entity\CashTransaction;
use User\Entity\User;
use Company\Entity\Contract;

/**
 * Description of CashOut
 * @ORM\Entity(repositoryClass="\Cash\Repository\CashRepository")
 * @ORM\Table(name="cash_doc")
 * @author Daddy
 */
class CashDoc {
    
    const STATUS_ACTIVE       = 1; // Active.
    const STATUS_RETIRED      = 2; // Retired.
    
    const CHECK_ACTIVE     = 1; // чек печатать.
    const CHECK_RETIRED    = 2; // не печатать.
    
    const KIND_IN_PAYMENT_CLIENT    = 1; // оплата от покупателя.
    const KIND_IN_REFILL            = 2; // пополнение.
    const KIND_IN_RETURN_USER       = 3; // возврат от сотрудника.
    const KIND_IN_RETURN_SUPPLIER   = 4; // возврат от поставщика

    const KIND_OUT_USER             = 11; // выдача в подотчет.
    const KIND_OUT_SUPPLIER         = 12; // оплата поставщику.
    const KIND_OUT_COURIER          = 13; // оплата курьеру .
    const KIND_OUT_REFILL           = 14; // пополнение
    const KIND_OUT_RETURN_CLIENT    = 15; // вовзарат покупателю.
    const KIND_OUT_SALARY           = 16; // выдача зп.
    const KIND_OUT_COST             = 17; // расходы.
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
        
    /**
     * @ORM\Column(name="comment")   
     */
    protected $comment;

    /**
     * @ORM\Column(name="info")   
     */
    protected $info;

    /**
     * @ORM\Column(name="apl_id")   
     */
    protected $aplId;

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
     * @ORM\Column(name="check_status")   
     */
    protected $checkStatus;

    /**
     * @ORM\Column(name="kind")   
     */
    protected $kind;

    /**
     * @ORM\ManyToOne(targetEntity="Cash\Entity\Cash", inversedBy="cashDocs") 
     * @ORM\JoinColumn(name="cash_id", referencedColumnName="id")
     */
    private $cash;
    
    /**
     * @ORM\ManyToOne(targetEntity="Cash\Entity\Cash", inversedBy="cashRefillDocs") 
     * @ORM\JoinColumn(name="cash_refill_id", referencedColumnName="id")
     */
    private $cashRefill;
    
    /**
     * @ORM\ManyToOne(targetEntity="User\Entity\User", inversedBy="cashDocs") 
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;
    
    /**
     * @ORM\ManyToOne(targetEntity="User\Entity\User", inversedBy="cashRefillDocs") 
     * @ORM\JoinColumn(name="user_refill_id", referencedColumnName="id")
     */
    private $userRefill;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Contact", inversedBy="cashDocs") 
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="id")
     */
    private $contact;

    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Order", inversedBy="cashDocs") 
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id")
     */
    private $order;

    /**
     * @ORM\ManyToOne(targetEntity="Stock\Entity\Vt", inversedBy="cashDocs") 
     * @ORM\JoinColumn(name="vt_id", referencedColumnName="id")
     */
    private $vt;

    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Cost", inversedBy="cashDocs") 
     * @ORM\JoinColumn(name="cost_id", referencedColumnName="id")
     */
    private $cost;

    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Legal", inversedBy="cashDocs") 
     * @ORM\JoinColumn(name="legal_id", referencedColumnName="id")
     */
    private $legal;

    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Legal", inversedBy="cashDocs") 
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id")
     */
    private $company;    
    
    /**
     * @ORM\ManyToOne(targetEntity="User\Entity\User", inversedBy="cashCreatorDocs") 
     * @ORM\JoinColumn(name="user_creator_id", referencedColumnName="id")
     */
    private $userCreator;    
    
   /**
    * @ORM\OneToMany(targetEntity="Cash\Entity\CashTransaction", mappedBy="cashDoc")
    * @ORM\JoinColumn(name="id", referencedColumnName="cash_doc_id")
   */
   private $cashTransactions;    
    
   /**
    * @ORM\OneToMany(targetEntity="Cash\Entity\UserTransaction", mappedBy="cashDoc")
    * @ORM\JoinColumn(name="id", referencedColumnName="cash_doc_id")
   */
   private $userTransactions;    
    
    /**
     * Constructor.
     */
    public function __construct() 
    {
        $this->cashTransactions = new ArrayCollection();
        $this->userTransactions = new ArrayCollection();
    }    
    
    public function getId() 
    {
        return $this->id;
    }

    public function getLogKey() 
    {
        return 'cash:'.$this->id;
    }
    
    public function setId($id) 
    {
        $this->id = $id;
    }     

    public function getComment() 
    {
        return $this->comment;
    }

    public function setComment($comment) 
    {
        $this->comment = $comment;
    }     

    public function getInfo() 
    {
        return $this->info;
    }

    public function setInfo($info) 
    {
        $this->info = $info;
    }     

    public function getAplId() 
    {
        return $this->aplId;
    }

    public function setAplId($aplId) 
    {
        $this->aplId = $aplId;
    }     

    public function getAmount() 
    {
        return $this->amount;
    }

    public function getKindAmount() 
    {
        switch ($this->kind){
            case $this::KIND_IN_PAYMENT_CLIENT:
            case $this::KIND_IN_REFILL:
            case $this::KIND_IN_RETURN_SUPPLIER:
            case $this::KIND_IN_RETURN_USER:
                return $this->amount;
            default : 
                return -$this->amount;
        }
    }

    public function getMutualAmount() 
    {
        switch ($this->kind){
            case $this::KIND_IN_RETURN_SUPPLIER:
            case $this::KIND_IN_PAYMENT_CLIENT:
                return -$this->amount;
            case $this::KIND_OUT_RETURN_CLIENT:
            case $this::KIND_OUT_SUPPLIER:
                return $this->amount;
        }
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
            self::CHECK_ACTIVE => 'Напечатан',
            self::CHECK_RETIRED => 'Не напечатан',
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
    
    /**
     * Returns kind.
     * @return int     
     */
    public function getKind() 
    {
        return $this->kind;
    }
    
    public function getContractKind()
    {
        switch ($this->kind){
            case $this::KIND_IN_PAYMENT_CLIENT:
            case $this::KIND_OUT_RETURN_CLIENT:
                return Contract::KIND_CUSTOMER;
            case $this::KIND_IN_RETURN_SUPPLIER:
            case $this::KIND_OUT_SUPPLIER:                
                return Contract::KIND_SUPPLIER;
            default : return Contract::KIND_OTHER;    
        }
    }

    public function isMutual()
    {
        switch ($this->kind){
            case $this::KIND_IN_PAYMENT_CLIENT:
            case $this::KIND_OUT_RETURN_CLIENT:
            case $this::KIND_IN_RETURN_SUPPLIER:
            case $this::KIND_OUT_SUPPLIER:
                if ($this->legal && $this->company){
                    return true;
                }    
            default : return false;    
        }
    }

    public function isRetail()
    {
        switch ($this->kind){
            case $this::KIND_IN_PAYMENT_CLIENT:
            case $this::KIND_OUT_RETURN_CLIENT:
                return true;
            default : return false;    
        }
    }

    /**
     * Returns possible kinds as array.
     * @return array
     */
    public static function getKindInList() 
    {
        return [
            self::KIND_IN_PAYMENT_CLIENT => 'Оплата от покупателя',
            self::KIND_IN_REFILL => 'Пополнение',
            self::KIND_IN_RETURN_USER => 'Возврат с подотчета',
            self::KIND_IN_RETURN_SUPPLIER => 'Возврат от поставщика',
        ];
    }    
    
    
    /**
     * Returns possible kinds as array.
     * @return array
     */
    public static function getKindOutList() 
    {
        return [
            self::KIND_OUT_USER => 'Подотчет сотруднику',
            self::KIND_OUT_SUPPLIER => 'Оплата поставщику',
            self::KIND_OUT_COURIER => 'Оплата курьеру',
            self::KIND_OUT_REFILL => 'Пополнение',
            self::KIND_OUT_RETURN_CLIENT => 'Возврат покупателю',
            self::KIND_OUT_SALARY => 'Выдача З/П',
            self::KIND_OUT_COST => 'Расходы',
        ];
    }    
    
    /**
     * Returns possible kinds as array.
     * @return array
     */
    public static function getKindList() 
    {
        return self::getKindInList() + self::getKindOutList();
    }    
    
    /**
     * Returns possible kinds as array.
     * @return array
     */
    public static function getOrderInList() 
    {
        return [
            self::KIND_IN_PAYMENT_CLIENT => 'Оплата от покупателя',
        ];
    }    
    
    
    /**
     * Returns possible kinds as array.
     * @return array
     */
    public static function getOrderOutList() 
    {
        return [
            self::KIND_OUT_RETURN_CLIENT => 'Возврат покупателю',
        ];
    }    
    
    /**
     * Returns possible kinds as array.
     * @return array
     */
    public static function getOrderList() 
    {
        return self::getOrderInList() + self::getOrderOutList();
    }    

    public static function getKindListAsJavascriptArray() 
    {
        $result = [];
        foreach (self::getKindList() as $key=>$value){
            $result[] = "$key: '$value'";
        }
        return '{'.implode(', ', $result).'}';
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

    /**
     * Sets kind.
     * @param int $kind     
     */
    public function setKind($kind) 
    {
        $this->kind = $kind;
    }   
    
    public function getCash()
    {
        return $this->cash;
    }
    
    /**
     * Add cash
     * @param Cash $cash
     */
    public function setCash($cash)
    {
        $this->cash = $cash;
    }

    public function getCashRefill()
    {
        return $this->cashRefill;
    }
    
    /**
     * Add cashRefill
     * @param Cash $cashRefill
     */
    public function setCashRefill($cashRefill)
    {
        $this->cashRefill = $cashRefill;
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

    public function getUserRefill()
    {
        return $this->userRefill;
    }
    
    /**
     * Add userRefill
     * @param User $userRefill
     */
    public function setUserRefill($userRefill)
    {
        $this->userRefill = $userRefill;
    }

    public function getUserCreator()
    {
        return $this->userCreator;
    }
    
    /**
     * Add userCreator
     * @param User $userCreator
     */
    public function setUserCreator($userCreator)
    {
        $this->userCreator = $userCreator;
    }

    public function getContact()
    {
        return $this->contact;
    }
    
    /**
     * Add contact
     * @param Contact $contact
     */
    public function setContact($contact)
    {
        $this->contact = $contact;
    }

    public function getOrder()
    {
        return $this->order;
    }
    
    /**
     * Add order
     * @param Order $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    public function getVt()
    {
        return $this->vt;
    }
    
    /**
     * Add vt
     * @param Vt $vt
     */
    public function setVt($vt)
    {
        $this->vt = $vt;
    }

    public function getCost()
    {
        return $this->cost;
    }
    
    /**
     * Add cost
     * @param Cost $cost
     */
    public function setCost($cost)
    {
        $this->cost = $cost;
    }

    public function getLegal()
    {
        return $this->legal;
    }
    
    public function getDefaultSupplier()
    {
        if ($this->legal){
            $contacts = $this->legal->getContacts();
            foreach ($contacts as $contact){
                if ($contact->getSupplier()){
                    return $contact->getSupplier();
                }
            }
        }
        
        return;
    }    
    
    public function getDefaultSupplierId()
    {
        $supplier = $this->getDefaultSupplier();
        if ($supplier){
            return $supplier->getId();
        }
        return;
    }
    
    /**
     * Add legal
     * @param Legal $legal
     */
    public function setLegal($legal)
    {
        $this->legal = $legal;
    }

    public function getCompany()
    {
        return $this->company;
    }
    
    /**
     * Add company
     * @param Legal $company
     */
    public function setCompany($company)
    {
        $this->company = $company;
    }

    public function getCashTransactions()
    {
        return $this->cashTransactions;
    }
    
    /**
     * Добавить запись
     * @param CashTransaction $cashTransaction
     */
    public function addCashTransaction($cashTransaction)
    {
        $this->cashTransactions[] = $cashTransaction;
    }
    
    public function getUserTransactions()
    {
        return $this->userTransactions;
    }
    
    /**
     * Добавить запись
     * @param UserTransaction $userTransaction
     */
    public function addUserTransaction($userTransaction)
    {
        $this->userTransactions[] = $userTransaction;
    }
    
    /**
     * Массив для формы
     * @return array 
     */
    public function toArray()
    {
        $result = [
            'amount' => $this->amount,
            'aplId' => $this->aplId,
            'cash' => ($this->cash) ? $this->cash->getId():null,
            'cashRefill' => ($this->cashRefill) ? $this->cashRefill->getId():NULL,
            'comment' => $this->comment,
            'company' => $this->company->getId(),
            'contact' => ($this->contact) ? $this->contact->getId():NULL,            
            'phone' => ($this->contact) ? $this->contact->getPhone()->getName():NULL,            
            'cost' => ($this->cost) ? $this->cost->getId():NULL,
            'dateOper' => date('Y-m-d', strtotime($this->dateOper)),
            'info' => $this->info,
            'kind' => $this->kind,
            'legal' => ($this->legal) ? $this->legal->getId():null,
            'supplier' => $this->getDefaultSupplierId(),
            'order' => ($this->order) ? $this->order->getAplId():null,
            'status' => $this->status,
            'user' => ($this->user) ? $this->user->getId():null,
            'userRefill' => ($this->userRefill) ? $this->userRefill->getId():null,
            'vt' => ($this->vt) ? $this->vt->getId():null,
        ];
        
        return $result;
    }    
        
    /**
     * Лог
     * @return array
     */
    public function toLog()
    {
        return [
            'amount' => $this->amount,
            'aplId' => $this->aplId,
            'cash' => ($this->cash) ? $this->cash->getId():null,
            'cashRefull' => ($this->cashRefill) ? $this->cashRefill->getId():NULL,
            'comment' => $this->comment,
            'company' => $this->company->getId(),
            'contact' => ($this->contact) ? $this->contact->getId():NULL,            
            'cost' => ($this->cost) ? $this->cost->getId():NULL,
            'dateOper' => $this->dateOper,
            'info' => $this->info,
            'kind' => $this->kind,
            'legal' => ($this->legal) ? $this->legal->getId():null,
            'supplier' => $this->getDefaultSupplierId(),
            'order' => ($this->order) ? $this->order->getAplId():null,
            'status' => $this->status,
            'user' => ($this->user) ? $this->user->getId():null,
            'userRefill' => ($this->userRefill) ? $this->userRefill->getId():null,
            'order' => ($this->vt) ? $this->vt->getId():null,
        ];
    }    
}
