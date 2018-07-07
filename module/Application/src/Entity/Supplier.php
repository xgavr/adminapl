<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;


use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Application\Entity\Contact;
use Doctrine\Common\Collections\Criteria;

/**
 * Description of Customer
 * @ORM\Entity(repositoryClass="\Application\Repository\SupplierRepository")
 * @ORM\Table(name="supplier")
 * @author Daddy
 */
class Supplier {
        
     // Supplier status constants.
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
     * @ORM\Column(name="name")   
     */
    protected $name;

    /**
     * @ORM\Column(name="info")   
     */
    protected $info;

    /** 
     * @ORM\Column(name="status")  
     */
    protected $status;

    /**
     * @ORM\Column(name="address")   
     */
    protected $address;

    /** 
     * @ORM\Column(name="date_created")  
     */
    protected $dateCreated;
    
    /**
    * @ORM\OneToMany(targetEntity="Application\Entity\Contact", mappedBy="supplier")
    * @ORM\JoinColumn(name="id", referencedColumnName="supplier_id")
     */
    private $contacts;
    
    /**
    * @ORM\OneToMany(targetEntity="Application\Entity\Raw", mappedBy="supplier")
    * @ORM\JoinColumn(name="id", referencedColumnName="supplier_id")
     */
    private $raw;

    /**
    * @ORM\OneToMany(targetEntity="Application\Entity\PriceDescription", mappedBy="supplier")
    * @ORM\JoinColumn(name="id", referencedColumnName="supplier_id")
     */
    private $priceDescriptions;    
    
    /**
    * @ORM\OneToMany(targetEntity="Application\Entity\PriceGetting", mappedBy="supplier")
    * @ORM\JoinColumn(name="id", referencedColumnName="supplier_id")
     */
    private $priceGettings;    
    
    /**
    * @ORM\OneToMany(targetEntity="Application\Entity\BillGetting", mappedBy="supplier")
    * @ORM\JoinColumn(name="id", referencedColumnName="supplier_id")
     */
    private $billGettings;    
    
    /**
    * @ORM\OneToMany(targetEntity="Application\Entity\RequestSetting", mappedBy="supplier")
    * @ORM\JoinColumn(name="id", referencedColumnName="supplier_id")
     */
    private $requestSettings;    
    
    /**
    * @ORM\OneToMany(targetEntity="Application\Entity\SupplySetting", mappedBy="supplier")
    * @ORM\JoinColumn(name="id", referencedColumnName="supplier_id")
     */
    private $supplySettings;    
    
    /**
     * Constructor.
     */
    public function __construct() 
    {
        $this->contacts = new ArrayCollection();
        $this->raw = new ArrayCollection();
        $this->priceDescriptions = new ArrayCollection();
        $this->priceGettings = new ArrayCollection();
        $this->billGettings = new ArrayCollection();
        $this->requestSettings = new ArrayCollection();
        $this->supplySettings = new ArrayCollection();
    }
    
    
    public function getId() 
    {
        return $this->id;
    }

    public function setId($id) 
    {
        $this->id = $id;
    }     

    public function getAplId() 
    {
        return $this->aplId;
    }

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

    public function getInfo() 
    {
        return $this->info;
    }

    public function setInfo($info) 
    {
        $this->info = $info;
    }     

    public function getAddress() 
    {
        return $this->address;
    }

    public function setAddress($address) 
    {
        $this->address = $address;
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
            self::STATUS_ACTIVE => 'Действующий',
            self::STATUS_RETIRED => 'Отключен'
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
    
    public function getStatusName($status)
    {
        $list = self::getStatusList();
        if (isset($list[$status]))
            return $list[$status];
        
        return 'Unknown';        
    }
            
    public function getStatusActive()
    {
        return self::STATUS_ACTIVE;
    }        
    
    public function getStatusRetired()
    {
        return self::STATUS_RETIRED;
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
     * Returns the array of contacts assigned to this.
     * @return array
     */
    public function getRaw()
    {
        return $this->raw;
    }
        
    /**
     * Assigns.
     */
    public function addRaw($raw)
    {
        $this->raw[] = $raw;
    }
    
    /**
     * Returns the array of contacts assigned to this.
     * @return array
     */
    public function getPriceDescriptions()
    {
        return $this->priceDescriptions;
    }
        
    /**
     * Assigns.
     */
    public function addPriceDescription($priceDescription)
    {
        $this->priceDescriptions[] = $priceDescription;
    }
    
    /**
     * Returns the array of prices assigned to this.
     * @return array
     */
    public function getPriceGettings()
    {
        return $this->priceGettings;
    }
        
    /**
     * Assigns.
     */
    public function addPriceGettings($priceGetting)
    {
        $this->priceGettings[] = $priceGetting;
    }
    
    /**
     * Returns the array of bills assigned to this.
     * @return array
     */
    public function getBillGettings()
    {
        return $this->billGettings;
    }
        
    /**
     * Assigns.
     */
    public function addBillGettings($billGetting)
    {
        $this->billGettings[] = $billGetting;
    }
    
    /**
     * Returns the array of request assigned to this.
     * @return array
     */
    public function getRequestSettings()
    {
        return $this->requestSettings;
    }
        
    /**
     * Assigns.
     */
    public function addRequestSetting($requestSetting)
    {
        $this->requestSettings[] = $requestSetting;
    }
    
    /**
     * Returns the array of supply assigned to this.
     * @return array
     */
    public function getSupplySettings()
    {
        return $this->supplySettings;
    }
        
    /**
     * Assigns.
     */
    public function addSupplySetting($supplySetting)
    {
        $this->supplySettings[] = $supplySetting;
    }
    
}
