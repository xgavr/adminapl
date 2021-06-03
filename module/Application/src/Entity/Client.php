<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

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
