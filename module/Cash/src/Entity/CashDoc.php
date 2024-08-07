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
use Bank\Entity\Statement;
use Bank\Entity\QrCodePayment;
use Bank\Entity\AplPayment;

/**
 * Description of CashOut
 * @ORM\Entity(repositoryClass="\Cash\Repository\CashRepository")
 * @ORM\Table(name="cash_doc")
 * @author Daddy
 */
class CashDoc {
    
    const STATUS_ACTIVE       = 1; // Active.
    const STATUS_RETIRED      = 2; // Retired.
    const STATUS_CORRECT      = 3; // Корректировка.
    
    const CHECK_ACTIVE     = 1; // чек печатать.
    const CHECK_RETIRED    = 2; // не печатать.
    
     // Status doc constants.
    const STATUS_EX_NEW  = 1; // Не отправлено.
    const STATUS_EX_RECD  = 2; // Получено из АПЛ.
    const STATUS_EX_APL  = 3; // Отправлено в АПЛ.    
    
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
    
    const STATUS_ACCOUNT_OK  = 1;// обновлено 
    const STATUS_ACCOUNT_NO  = 2;// не обновлено
    const STATUS_TAKE_NO  = 3;// не проведено    
    
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
     * @ORM\Column(name="status_account")  
     */
    protected $statusAccount = self::STATUS_ACCOUNT_NO;
    
    /**
     * @ORM\Column(name="check_status")   
     */
    protected $checkStatus;

    /** 
     * @ORM\Column(name="status_ex")  
     */
    protected $statusEx;
    
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
     * @ORM\ManyToOne(targetEntity="Company\Entity\Legal", inversedBy="companyCashDocs") 
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
    * @ORM\OneToOne(targetEntity="Bank\Entity\Statement", mappedBy="cashDoc")
    * @ORM\JoinColumn(name="statement_id", referencedColumnName="id")
   */
   private $statement;    

   /**
    * @ORM\OneToOne(targetEntity="Bank\Entity\QrCodePayment", mappedBy="cashDoc")
   */
   private $qrcodePayment;    

   /**
    * @ORM\OneToOne(targetEntity="Bank\Entity\AplPayment", mappedBy="cashDoc")
   */
   private $aplPayment;    
   
   /**
    * @ORM\OneToMany(targetEntity="Stock\Entity\Mutual", mappedBy="cashDoc")
    * @ORM\JoinColumn(name="id", referencedColumnName="doc_id")
   */
   private $mutuals;           

   /**
    * @ORM\OneToMany(targetEntity="Company\Entity\CostMutual", mappedBy="cashDoc")
    * @ORM\JoinColumn(name="id", referencedColumnName="doc_id")
   */
   private $costMutuals;           

   /**
     * Constructor.
     */
    public function __construct() 
    {
        $this->cashTransactions = new ArrayCollection();
        $this->userTransactions = new ArrayCollection();
        $this->mutuals = new ArrayCollection();
        $this->costMutuals = new ArrayCollection();
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
        return ($this->comment) ? $this->comment:'';
    }

    public function setComment($comment) 
    {
        $this->comment = $comment;
    }     

    public function getInfo() 
    {
        return ($this->info) ? $this->info:'';
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

    public function getFinServiceAmount() 
    {
        switch ($this->kind){
            case $this::KIND_OUT_RETURN_CLIENT:
                return -$this->amount;
            default:    
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
    
    public function getDocDateAtomFormat() {
        $datetime = new \DateTime($this->dateOper);
        return $datetime->format(\DateTime::ATOM);
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
            self::STATUS_RETIRED => 'Не используется',
            self::STATUS_CORRECT => 'Коректировка'
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
     * Returns statusAccount.
     * @return int     
     */
    public function getStatusAccount() 
    {
        return $this->statusAccount;
    }

    /**
     * Returns possible statusAccount as array.
     * @return array
     */
    public static function getStatusAccountList() 
    {
        return [
            self::STATUS_ACCOUNT_OK => 'Обновлено',
            self::STATUS_ACCOUNT_NO=> 'Не обновлено',
            self::STATUS_TAKE_NO=> 'Не проведено',
        ];
    }    
    
    /**
     * Returns statusAccount as string.
     * @return string
     */
    public function getStatusAccountAsString()
    {
        $list = self::getStatusAccountList();
        if (isset($list[$this->statusAccount]))
            return $list[$this->statusAccount];
        
        return 'Unknown';
    }    
        
    /**
     * Sets statusAccount.
     * @param int $statusAccount     
     */
    public function setStatusAccount($statusAccount) 
    {
        $this->statusAccount = $statusAccount;
    }   
    
    /**
     * Returns possible apl statuses as array.
     * @return array
     */
    public static function getAplStatusList() 
    {
        return [
            self::STATUS_ACTIVE => 1,
            self::STATUS_RETIRED => 0
        ];
    }    
    
    /**
     * Returns cash status as apl.
     * @return string
     */
    public function getStatusAsApl()
    {
        $list = self::getAplStatusList();
        if (isset($list[$this->status]))
            return $list[$this->status];
        
        return 'Unknown';
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
     * Returns possible Apl check statuses as array.
     * @return array
     */
    public static function getAplCheckStatusList() 
    {
        return [
            self::CHECK_ACTIVE => 1,
            self::CHECK_RETIRED => 0,
        ];
    }    
    
    /**
     * Returns check status as Apl.
     * @return string
     */
    public function getCheckStatusAsApl()
    {
        $list = self::getAplCheckStatusList();
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
     * Returns status.
     * @return int     
     */
    public function getStatusEx() 
    {
        return $this->statusEx;
    }

    /**
     * Returns possible statuses as array.
     * @return array
     */
    public static function getStatusExList() 
    {
        return [
            self::STATUS_EX_NEW => 'Новый',
            self::STATUS_EX_APL => 'Отправлен в АПЛ',
            self::STATUS_EX_RECD => 'Получен из АПЛ',
        ];
    }    
    
    /**
     * Returns user status as string.
     * @return string
     */
    public function getStatusExAsString()
    {
        $list = self::getStatusList();
        if (isset($list[$this->statusEx]))
            return $list[$this->statusEx];
        
        return 'Unknown';
    }    
    
    /**
     * Sets status.
     * @param int $statusEx     
     */
    public function setStatusEx($statusEx) 
    {
        $this->statusEx = $statusEx;
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
    
    public function getAplType()
    {
        switch ($this->kind){
            case $this::KIND_IN_PAYMENT_CLIENT:
            case $this::KIND_IN_RETURN_USER:
            case $this::KIND_OUT_RETURN_CLIENT:
            case $this::KIND_OUT_USER:
                return 'Users';
            case $this::KIND_OUT_SALARY:
            case $this::KIND_OUT_COURIER:
                return 'Staffs';
            case $this::KIND_IN_RETURN_SUPPLIER:
            case $this::KIND_OUT_SUPPLIER:                
                return 'Suppliers';
            case $this::KIND_OUT_REFILL:
            case $this::KIND_IN_REFILL:
                return 'Tills';    
            case $this::KIND_OUT_COST:
                return 'Costs';    
            default : return '';    
        }
    }    

    public function getAplParent()
    {
        switch ($this->kind){
            case $this::KIND_IN_PAYMENT_CLIENT:
            case $this::KIND_OUT_RETURN_CLIENT:
                return ($this->contact->getClient()) ? $this->contact->getClient()->getAplId():null;
            case $this::KIND_IN_RETURN_SUPPLIER:
            case $this::KIND_OUT_SUPPLIER:                
                return ($this->legal->getSupplier()) ? $this->legal->getSupplier()->getAplId():null;
            case $this::KIND_IN_RETURN_USER:
            case $this::KIND_OUT_SALARY:
            case $this::KIND_OUT_USER:
                return $this->getUserRefill()->getAplId();
            case $this::KIND_OUT_REFILL:
            case $this::KIND_IN_REFILL:
                return $this->getCashRefill()->getAplId();
            case $this::KIND_OUT_COST:
                return $this->getCost()->getAplId();    
            case $this::KIND_OUT_COURIER:
                if ($this->contact){
                    return ($this->contact->getClient()) ? $this->contact->getClient()->getAplId():null;
                }    
//                return $this->getOrder()->getClientAplId();    
            default : return '';    
        }
    }    
    
    public function getAplParentParent()
    {
        if ($this->getCash()){
            return $this->getCash()->getOffice()->getAplId();
        }
        if ($this->getUser()){
            return $this->getUser()->getOffice()->getAplId();
        }
        return;
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
     * Платежный сервис?
     * @return boolean
     */
    public function isFinService()
    {
        switch ($this->kind){
            case $this::KIND_IN_PAYMENT_CLIENT:
            case $this::KIND_OUT_RETURN_CLIENT:
                if ($this->getCash()){
                    if ($this->getCash()->getPayment() == Cash::PAYMENT_PAY_SERVICE){
                        return !empty($this->getCash()->getBankInn());
                    }
                }
            default : return false;    
        }
    }
    
    /**
     * Нал
     * @return bool
     */
    public function contractPayCash() 
    {
        if ($this->getCash()){
            if ($this->getCash()->getBankAccounts()->count()){
                return Contract::PAY_CASHLESS;
            }
        }    
        
        return Contract::PAY_CASH;
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
            self::KIND_OUT_USER => 'Выдача в подотчет',
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
    public static function getAplKindList() 
    {
        return [
            self::KIND_OUT_USER => 'out1',
            self::KIND_OUT_SUPPLIER => 'out2',
            self::KIND_OUT_COURIER => 'out8',
            self::KIND_OUT_REFILL => 'out3',
            self::KIND_OUT_RETURN_CLIENT => 'out4',
            self::KIND_OUT_SALARY => 'out6',
            self::KIND_OUT_COST => 'out5',
            self::KIND_IN_PAYMENT_CLIENT => 'in1',
            self::KIND_IN_REFILL => 'in3',
            self::KIND_IN_RETURN_USER => 'in2',
            self::KIND_IN_RETURN_SUPPLIER => 'in4',
        ];
    }    

    /**
     * Returns kind as string.
     * @return string
     */
    public function getKindAsApl()
    {
        $list = self::getAplKindList();
        if (isset($list[$this->kind]))
            return $list[$this->kind];
        
        return 'Unknown';
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
    
    /**
     * 
     * @return Cash
     */
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

    /**
     * 
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
    
    public function getAplBo()
    {
        if ($this->user && !$this->cash){
            return 1;
        }
        return 0;
    }

    public function getAplSf()
    {
        if ($this->user){
            return $this->user->getAplId();
        }
        if ($this->cash){
            return $this->cash->getAplId();
        }
        return 0;
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

    /**
     * 
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    public function getOrderAplId()
    {
        if ($this->order){
            return $this->order->getAplId();
        }
        
        return;
    }
    
    public function getOrderUserId()
    {
        if ($this->order){
            return $this->order->getUserId();
        }
        
        return;
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

    /**
     * 
     * @return Legal
     */
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

    public function getDefaultSupplierAplId()
    {
        $supplier = $this->getDefaultSupplier();
        if ($supplier){
            return $supplier->getAplId();
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
     * 
     * @return Statement
     */
    public function getStatement()
    {
        return $this->statement;
    }
    
    /**
     * 
     * @param Statement $statement
     * @return $this
     */
    public function setStatement($statement) {
        $this->statement = $statement;
        return $this;
    }
    
    /**
     * 
     * @return QrCodePayment
     */
    public function getQrCodePayment()
    {
        return $this->qrcodePayment;
    }
    
    /**
     * 
     * @return AplPayment
     */
    public function getAplPayment() {
        return $this->aplPayment;
    }
    
    public function getMutuals() {
        return $this->mutuals;
    }
    
    public function getCostMutuals() {
        return $this->costMutuals;
    }
    
    /**
     * Массив для формы
     * @return array 
     */
    public function toArray()
    {
        $result = [
            'id' => $this->getId(),
            'amount' => $this->amount,
            'aplId' => $this->aplId,
            'cash' => ($this->cash) ? $this->cash->getId():null,
            'cashRefill' => ($this->cashRefill) ? $this->cashRefill->getId():NULL,
            'comment' => $this->comment,
            'company' => $this->company->getId(),
            'contact' => ($this->contact) ? $this->contact->getId():NULL,            
            'phone' => ($this->contact) ? ($this->contact->getPhone()) ? $this->contact->getPhone()->getName():NULL:NULL,            
            'cost' => ($this->cost) ? $this->cost->getId():NULL,
            'dateOper' => date('Y-m-d', strtotime($this->dateOper)),
            'info' => $this->info,
            'kind' => $this->kind,
            'legal' => ($this->legal) ? $this->legal->getId():null,
            'legalData' => ($this->legal) ? $this->getLegal()->toArray():null,
            'supplier' => $this->getDefaultSupplierId(),
            'order' => ($this->order) ? $this->order->getId():null,
            'orderApl' => ($this->order) ? $this->order->getAplId():null,
            'status' => $this->status,
            'user' => ($this->user) ? $this->user->getId():null,
            'userRefill' => ($this->userRefill) ? $this->userRefill->getId():null,
            'vt' => ($this->vt) ? $this->vt->getId():null,
        ];
        
        return $result;
    }    
        
    /**
     * Массив для export
     * @return array 
     */
    public function toExport()
    {
        $result = [
            'id' => $this->getId(),
            'amount' => $this->getKindAmount(),
            'aplId' => $this->aplId,
            'cash' => ($this->cash) ? $this->cash->toArray():null,
            'cashRefill' => ($this->cashRefill) ? $this->cashRefill->toArray():NULL,
            'comment' => $this->comment,
            'company' => $this->company->toArray(),
            'cost' => ($this->cost) ? $this->cost->toArray():NULL,
//            'dateOper' => $this->getDocDateAtomFormat(),
            'dateOper' => date('Ymd', strtotime($this->dateOper)),
            'info' => $this->info,
            'kind' => $this->kind,
            'legal' => ($this->legal) ? $this->legal->toArray():null,
            'supplierAplId' => $this->getDefaultSupplierAplId(),
//            'order' => ($this->order) ? $this->order->getAplId():null,
            'order' => ($this->order) ? $this->order->toArray():null,
            'status' => $this->status,
            'user' => ($this->user) ? $this->user->toArray():null,
            'userRefill' => ($this->userRefill) ? $this->userRefill->toArray():null,
            'vt' => ($this->vt) ? $this->vt->toArray():null,
            'creator' => ($this->userCreator) ? $this->userCreator->toArray():null,
            'statement' => ($this->statement) ? $this->statement->toArray():null,
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
            'cashInfo' => ($this->cash) ? $this->cash->toArray():null,
            'cashRefull' => ($this->cashRefill) ? $this->cashRefill->getId():NULL,
            'cashRefullInfo' => ($this->cashRefill) ? $this->cashRefill->toArray():NULL,
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
            'userInfo' => ($this->user) ? $this->user->toArray():null,
            'userRefill' => ($this->userRefill) ? $this->userRefill->getId():null,
            'userRefillInfo' => ($this->userRefill) ? $this->userRefill->toArray():null,
            'vt' => ($this->vt) ? $this->vt->getId():null,
        ];
    }    
}
