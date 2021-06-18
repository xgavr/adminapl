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
     * Constructor.
     */
    public function __construct() 
    {
        $this->contacts = new ArrayCollection();
        $this->cart = new ArrayCollection();
    }
    
    public function getId() 
    {
        return $this->id;
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
        return $this->name;
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
    public static function getPricecilList() 
    {
        return [
            self::PRICE_0 => 'Розница',
            self::PRICE_1 => 'VIP',
            self::PRICE_2 => 'Опт2',
            self::PRICE_3 => 'Опт3',
            self::PRICE_4 => 'Опт4',
            self::PRICE_5 => 'Опт5',
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
     * Sets the date when this user was created.
     * @param string $dateCreated     
     */
    public function setDateCreated($dateCreated) 
    {
        $this->dateCreated = $dateCreated;
    }    
            
    /**
     * Returns the array of contacts assigned to this.
     * @return array
     */
    public function getContacts()
    {
        return $this->contacts;
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
        $criteria = Criteria::create()->where(Criteria::expr()->eq("status", Contact::STATUS_LEGAL));
        return $this->getContacts()->matching($criteria);
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
        
}
