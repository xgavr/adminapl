<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Company\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Description of Client
 * @ORM\Entity(repositoryClass="\Application\Repository\LegalRepository")
 * @ORM\Table(name="office")
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
     * @ORM\Id
     * @ORM\GeneratedValue
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
    * @ORM\OneToMany(targetEntity="Application\Entity\Contact", mappedBy="office")
    * @ORM\JoinColumn(name="id", referencedColumnName="office_id")
     */
    private $contacts;
        
    /**
    * @ORM\OneToMany(targetEntity="Company\Entity\Legal", mappedBy="office")
    * @ORM\JoinColumn(name="id", referencedColumnName="legal_id")
     */
    private $legals;
        
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Region", inversedBy="office") 
     * @ORM\JoinColumn(name="region_id", referencedColumnName="id")
     */
    private $region;
    
    /**
    * @ORM\OneToMany(targetEntity="User\Entity\User", mappedBy="office")
    * @ORM\JoinColumn(name="id", referencedColumnName="office_id")
     */
    private $staffs;
        
    /**
     * Constructor.
     */
    public function __construct() 
    {
        $this->contacts = new ArrayCollection();
        $this->staffs = new ArrayCollection();
        $this->legals = new ArrayCollection();
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
    public function getStaffs()
    {
        return $this->staffs;
    }
        
    /**
     * Assigns.
     */
    public function addStaff($staff)
    {
        $this->staffs[] = $staff;
    }
        
    /**
     * Returns the array of order assigned to this.
     * @return array
     */
    public function getLegals()
    {
        return $this->legals;
    }
        
    /**
     * Assigns.
     */
    public function addLegal($legal)
    {
        $this->legals[] = $legal;
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
        $user->addRegion($region);
    }     
        
}
