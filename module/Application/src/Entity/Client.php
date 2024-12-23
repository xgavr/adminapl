<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
//use Application\Entity\Contact;
use Stock\Entity\Movement;

/**
 * Description of Client
 * @ORM\Entity(repositoryClass="\Application\Repository\ClientRepository")
 * @ORM\Table(name="client")
 * @author Daddy
 */
class Client {
        
     // Status constants.
    const STATUS_ACTIVE       = 1; // Active user.
    const STATUS_RETIRED      = 2; // Retired user.
   
    const PRICE_0   = 0; // Розница
    const PRICE_1   = 1; // ВИП
    const PRICE_2   = 2; // опт2
    const PRICE_3   = 3; // опт3
    const PRICE_4   = 4; // опт4
    const PRICE_5   = 5; // опт5
    
    const RETAIL_ID = 'retail'; //код розницы
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /** 
     * @ORM\Column(name="apl_id")  
     */
    protected $aplId;
    
    /** 
     * @ORM\Column(name="status")  
     */
    protected $status;

    /** 
     * @ORM\Column(name="date_created")  
     */
    protected $dateCreated;

    /** 
     * @ORM\Column(name="date_registration")  
     */
    protected $dateRegistration;

    /** 
     * @ORM\Column(name="date_order")  
     */
    protected $dateOrder;
    
    /**
     * @ORM\Column(name="name")   
     */
    protected $name;

    /**
     * @ORM\Column(name="sales_total")   
     */
    protected $salesTotal = 0;

    /**
     * @ORM\Column(name="sales_order")   
     */
    protected $salesOrder = 0;

    /**
     * @ORM\Column(name="sales_good")   
     */
    protected $salesGood = 0;

    /**
     * @ORM\Column(name="pricecol")   
     */
    protected $pricecol = self::PRICE_0;
    
    /**
     * @ORM\Column(name="balance")   
     */
    protected $balance = 0;

    /**
     * Дата последней операции
     * @ORM\Column(name="balance_date")   
     */
    protected $balanceDate = null;

    /**
    * @ORM\OneToMany(targetEntity="Application\Entity\Contact", mappedBy="client")
    * @ORM\JoinColumn(name="id", referencedColumnName="client_id")
     */
    private $contacts;
    
    /**
    * @ORM\OneToMany(targetEntity="Application\Entity\Cart", mappedBy="client")
    * @ORM\JoinColumn(name="id", referencedColumnName="client_id")
     */
    private $cart;
    
    /**
     * @ORM\ManyToOne(targetEntity="User\Entity\User", inversedBy="client") 
     * @ORM\JoinColumn(name="manager_id", referencedColumnName="id")
     */
    private $manager;
    
    /**
    * @ORM\OneToMany(targetEntity="Application\Entity\Comment", mappedBy="client")
    * @ORM\JoinColumn(name="id", referencedColumnName="client_id")
     */
    private $comments;
    
    /**
    * @ORM\OneToMany(targetEntity="Stock\Entity\Movement", mappedBy="client")
    * @ORM\JoinColumn(name="id", referencedColumnName="client_id")
     */
    private $movements;
    
    /**
     * Constructor.
     */
    public function __construct() 
    {
        $this->contacts = new ArrayCollection();
        $this->cart = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->movements = new ArrayCollection();
    }
    
    public function getId() 
    {
        return $this->id;
    }
    
    public function getLink()
    {
        return "<a href='/client/view/{$this->id}' target='_blank'>{$this->getNameOrNan()}</a>";                
    }    

    public function setId($id) 
    {
        $this->id = $id;
    }     

    /**
     * Returns client apl ID.
     * @return integer
     */
    public function getAplId() 
    {
        return $this->aplId;
    }

    /**
     * Sets client apl ID. 
     * @param int $aplId    
     */
    public function setAplId($aplId) 
    {
        $this->aplId = $aplId;
    }

    public function getName() 
    {
        return ($this->name) ? $this->name:'Nan';
    }

    public function getContactName() 
    {
        return $this->getContact()->getName();
    }

    public function getBestName() 
    {
        return max($this->getContactName(), $this->getName());
    }

    public function getNameOrNan() 
    {
        return ($this->name) ? $this->name:'Nan';
    }

    public function setName($name) 
    {
        $this->name = $name;
    }     

    public function getSalesTotal() 
    {
        return $this->salesTotal;
    }

    public function setSalesTotal($salesTotal) 
    {
        $this->salesTotal = $salesTotal;
    }     

    public function getSalesOrder() 
    {
        return $this->salesOrder;
    }

    public function setSalesOrder($salesOrder) 
    {
        $this->salesOrder = $salesOrder;
    }     

    public function getSalesGood() 
    {
        return $this->salesGood;
    }

    public function setSalesGood($salesGood) 
    {
        $this->salesGood = $salesGood;
    }     
    
    public function getBalance() {
        return $this->balance;
    }

    public function setBalance($balance) {
        $this->balance = $balance;
        return $this;
    }
    
    public function getBalanceDate() {
        return $this->balanceDate;
    }

    public function getFormatedBalanceDate() {
        if ($this->balanceDate){
            return date('d.m.Y', strtotime($this->balanceDate));
        }
        
        return;
    }
    
    public function getBalanceDateTimeOrNow() {
        if ($this->balanceDate){
            return date('Y-m-d 23:59:59', strtotime($this->balanceDate));
        }
        
        return date('Y-m-d 23:59:59');
    }

    public function setBalanceDate($balanceDate) {
        $this->balanceDate = $balanceDate;
        return $this;
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
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_RETIRED => 'Retired'
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
     * Returns pricecol.
     * @return int     
     */
    public function getPricecol() 
    {
        return $this->pricecol;
    }

    /**
     * Returns possible pricecols as array.
     * @return array
     */
    public static function getPricecolList() 
    {
        return [
            self::PRICE_0 => '0-Розница',
            self::PRICE_1 => '1-VIP',
            self::PRICE_2 => '2-Опт2',
            self::PRICE_3 => '3-Опт3',
            self::PRICE_4 => '4-Опт4',
            self::PRICE_5 => '5-Опт5',
        ];
    }    
    
    /**
     * Returns pricecol as string.
     * @return string
     */
    public function getPriceColAsString()
    {
        $list = self::getPricecolList();
        if (isset($list[$this->pricecol]))
            return $list[$this->pricecol];
        
        return 'Unknown';
    }    
    
    /**
     * Sets pricecol.
     * @param int $pricecol 
     */
    public function setPricecol($pricecol) 
    {
        $this->pricecol = $pricecol;
    }   
    
    /**
     * Returns the date of user creation.
     * @return string     
     */
    public function getDateCreated() 
    {
        return $this->dateCreated;
    }
    
    /**
     * 
     * @return string
     */
    public function getFormatedDateCreated() {
        return date('d.m.Y', strtotime($this->dateCreated));
    }
    
    /**
     * Sets the date when this user was created.
     * @param string $dateCreated     
     */
    public function setDateCreated($dateCreated) 
    {
        $this->dateCreated = $dateCreated;
    }    
         
    public function getDateRegistration() {
        return $this->dateRegistration;
    }

    /**
     * 
     * @return string
     */
    public function getFormatedDateRegistration() {
        return date('d.m.Y', strtotime($this->dateRegistration));
    }
    
    public function setDateRegistration($dateRegistration) {
        $this->dateRegistration = $dateRegistration;
        return $this;
    }

    public function getDateOrder() {
        return $this->dateOrder;
    }

    public function setDateOrder($dateOrder) {
        $this->dateOrder = $dateOrder;
        return $this;
    }
    
    /**
     * Returns the array of contacts assigned to this.
     * @return array
     */
    public function getContacts()
    {
        return $this->contacts;
    }
    
    public function getContactsCount()
    {
        return $this->contacts->count();
    }
        
    public function getContact()
    {
        return $this->contacts[0];
    }

    public function getContactPhone()
    {
        foreach ($this->contacts as $contact){
            if ($contact->getPhone()){
                return $contact->getPhone()->getName();
            }
        }
        return;
    }

    public function getContactEmail()
    {
        foreach ($this->contacts as $contact){
            if ($contact->getEmail()){
                return $contact->getEmail()->getName();
            }
        }
        return;
    }

    /**
     * Assigns.
     */
    public function addContact($contact)
    {
        $this->contacts[] = $contact;
    }
            
    /**
     * Returns the array of for legal contacts assigned to this.
     * @return array
     */
    public function getLegalContacts()
    {
        $result = new ArrayCollection();
        
        foreach ($this->getContacts() as $contact){
            if ($contact->getStatus() == Contact::STATUS_LEGAL){
                $result[] = $contact;
            }
        }
        return $result;
        
//        $criteria = Criteria::create()->where(Criteria::expr()->eq("status", Contact::STATUS_LEGAL));
//        return $this->getContacts()->matching($criteria);
    }
        
    public function getLegalContactsCount()
    {
        return $this->getLegalContacts()->count();
    }
        
    
    /**
     * Returns the array of for first legal contact assigned to this.
     * @return array
     */
    public function getLegalContact()
    {
        $contacts = $this->getLegalContacts();
        return $contacts[0];
    }
        
    /**
     * Returns the array of for other contacts assigned to this.
     * @return array
     */
    public function getOtherContacts()
    {
        $criteria = Criteria::create()->where(Criteria::expr()->neq("status", Contact::STATUS_LEGAL));
        return $this->getContacts()->matching($criteria);
    }
    
    /**
     * Returns the array of cart assigned to this.
     * @return array
     */
    public function getCart()
    {
        return $this->cart;
    }
        
    /**
     * Assigns.
     */
    public function addCart($cart)
    {
        $this->cart[] = $cart;
    }
        
    /**
     * Returns the array of comment assigned to this.
     * @return array
     */
    public function getComments()
    {
        return $this->comments;
    }
        
    /**
     * Assigns.
     */
    public function addComment($comment)
    {
        $this->comments[] = $comment;
    }
        
    
    public function getMovements() {
        return $this->movements;
    }

    /**
     * 
     * @param Movement $movement
     * @return $this
     */
    public function addMovement($movement) {
        if ($movement){
            $this->movements[] = $movement;
        }    
        return $this;
    }
    
    /*
     * Возвращает связанный manager.
     * @return \User\Entity\User
     */
    
    public function getManager() 
    {
        return $this->manager;
    }

    /**
     * Задает связанный manager.
     * @param \User\Entity\User $user
     */    
    public function setManager($user) 
    {
        $this->manager = $user;
        $user->addClient($this);
    }     
        
    /**
     * Массив для формы
     * @return array 
     */
    public function toArray()
    {
        $result = [
            'id' => $this->getId(),
            'aplId' => $this->getAplId(),
            'name' => $this->getNameOrNan(),
            'balance' => $this->getBalance(),
            'phone' => $this->getContactPhone(),
            'pricecol' => $this->getPricecol(),
            'pricecolName' => $this->getPriceColAsString(),
            'status' => $this->getStatus(),
            'statusName' => $this->getStatusAsString(),
            'orders' => [],
        ];
        
        return $result;
    }   
    
    /**
     * Массив для реактивации
     * @return array 
     */
    public function toReactor()
    {
        $result = [
            'client_id' => $this->getId(),
            'name' => $this->getNameOrNan(),
            'phone' => $this->getContactPhone(),
            'orders' => [],
        ];
        
        return $result;
    }        
}
