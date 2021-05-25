<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Company\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Application\Entity\Contact;
use Application\Entity\Rate;
use Doctrine\Common\Collections\Criteria;
use Stock\Entity\Ptu;

/**
 * Description of Office
 * @ORM\Entity(repositoryClass="\Company\Repository\OfficeRepository")
 * @ORM\Table(name="office")
 * @author Daddy
 */
class Office {
        
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
     * @ORM\Column(name="full_name")   
     */
    protected $fullName;

    
    /**
    * @ORM\OneToMany(targetEntity="\Application\Entity\Contact", mappedBy="office")
    * @ORM\JoinColumn(name="id", referencedColumnName="office_id")
     */
    private $contacts;
                
    /**
     * @ORM\ManyToOne(targetEntity="\Company\Entity\Region", inversedBy="offices") 
     * @ORM\JoinColumn(name="region_id", referencedColumnName="id")
     */
    private $region;
    
    /**
    * @ORM\OneToMany(targetEntity="Company\Entity\Contract", mappedBy="office")
    * @ORM\JoinColumn(name="id", referencedColumnName="office_id")
     */
    private $contracts;    
    
   /**
    * @ORM\OneToMany(targetEntity="\Application\Entity\Rate", mappedBy="office")
    * @ORM\JoinColumn(name="id", referencedColumnName="office_id")
   */
   private $rates;    
    
   /**
    * @ORM\OneToMany(targetEntity="Stock\Entity\Ptu", mappedBy="office")
    * @ORM\JoinColumn(name="id", referencedColumnName="office_id")
   */
   private $ptu;    
    
   /**
    * @ORM\OneToMany(targetEntity="Stock\Entity\Ot", mappedBy="office")
    * @ORM\JoinColumn(name="id", referencedColumnName="office_id")
   */
   private $ot;    

   /**
     * Constructor.
     */
    public function __construct() 
    {
        $this->contacts = new ArrayCollection();
        $this->contracts = new ArrayCollection();
        $this->rates = new ArrayCollection();      
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

    public function getFullName() 
    {
        return $this->fullName;
    }

    public function setFullName($fullName) 
    {
        $this->fullName = $fullName;
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
            self::STATUS_RETIRED => 'Закрыт'
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
    
    /*
     * Возвращает связанный region.
     * @return \Company\Entity\Region
     */
    
    public function getRegion() 
    {
        return $this->region;
    }

    /**
     * Задает связанный region.
     * @param \Company\Entity\Region $region
     */    
    public function setRegion($region) 
    {
        $this->region = $region;
        $region->addOffice($this);
    }     
        
    /**
     * Returns the array of contracts assigned to this.
     * @return array
     */
    public function getContracts()
    {
        return $this->contracts;
    }
        
    /**
     * Assigns.
     */
    public function addContract($contract)
    {
        $this->contracts[] = $contract;
    }
        
    /*
     * Возвращает связанный rates.
     * @return Rate
     */    
    public function getRates() 
    {
        return $this->rates;
    }

    public function addRate($rate) 
    {
        $this->rates[] = $rate;
    }     
            
    /*
     * Возвращает связанный ptu.
     * @return array
     */    
    public function getPtu() 
    {
        return $this->ptu;
    }

    /*
     * Возвращает связанный ot.
     * @return array
     */    
    public function getOt() 
    {
        return $this->ot;
    }
}
