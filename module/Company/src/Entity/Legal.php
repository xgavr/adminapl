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
 * Description of Legal
 * @ORM\Entity(repositoryClass="\Company\Repository\LegalRepository")
 * @ORM\Table(name="legal")
 * @author Daddy
 */
class Legal {
        
     // Legal status constants.
    const STATUS_ACTIVE       = 1; // Active.
    const STATUS_RETIRED      = 2; // Retired.
   
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="inn")   
     */
    protected $inn;

    /**
     * @ORM\Column(name="knn")   
     */
    protected $knn;

    /**
     * @ORM\Column(name="ogrn")   
     */
    protected $ogrn;

    /**
     * @ORM\Column(name="okpo")   
     */
    protected $okpo;

    /**
     * @ORM\Column(name="head")   
     */
    protected $head;

    /**
     * @ORM\Column(name="chief_account")   
     */
    protected $chiefAccount;

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
     * @ORM\Column(name="date_start")  
     */
    protected $dateStart;
    
    /**
    * @ORM\OneToMany(targetEntity="Company\Entity\BankAccount", mappedBy="legal")
    * @ORM\JoinColumn(name="id", referencedColumnName="legal_id")
     */
    private $bankAccounts;
    
    /**
    * @ORM\OneToMany(targetEntity="Company\Entity\Contract", mappedBy="legal")
    * @ORM\JoinColumn(name="id", referencedColumnName="legal_id")
     */
    private $contracts;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Contact", inversedBy="legal") 
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="id")
     */
    private $contact;

    /**
     * Constructor.
     */
    public function __construct() 
    {
        $this->bankAccounts = new ArrayCollection();
        $this->contracts = new ArrayCollection();
    }
    
    
    public function getId() 
    {
        return $this->id;
    }

    public function setId($id) 
    {
        $this->id = $id;
    }     

    public function getName() 
    {
        return $this->name;
    }

    public function setName($name) 
    {
        $this->name = $name;
    }     

    public function getInn() 
    {
        return $this->inn;
    }

    public function setInn($inn) 
    {
        $this->inn = $inn;
    }     

    public function getKpp() 
    {
        return $this->kpp;
    }

    public function setKpp($kpp) 
    {
        $this->kpp = $kpp;
    }     

    public function getOgrn() 
    {
        return $this->ogrn;
    }

    public function setOgrn($ogrn) 
    {
        $this->ogrn = $ogrn;
    }     
    
    public function getOkpo() 
    {
        return $this->okpo;
    }

    public function setOkpo($okpo) 
    {
        $this->okpo = $okpo;
    }     
    
    public function getHead() 
    {
        return $this->head;
    }

    public function setHead($head) 
    {
        $this->head = $head;
    }     
    
    public function getChiefAccount() 
    {
        return $this->chiefAccount;
    }

    public function setChiefAccount($chiefAccount) 
    {
        $this->chiefAccount = $chiefAccount;
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
     * Returns the date of start.
     * @return string     
     */
    public function getDateStart() 
    {
        return $this->dateStart;
    }
    
    /**
     * Sets the date when start.
     * @param string $dateStart     
     */
    public function setDateStart($dateStart) 
    {
        $this->dateCreated = $dateStart;
    }    
        
    /**
     * @return array
     */
    public function getBankAccounts()
    {
        return $this->bankAccounts;
    }
        
    /**
     * Assigns.
     */
    public function addBankAccount($bankAccount)
    {
        $this->bankAccounts[] = $bankAccount;
    }    

    /**
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
     * Возвращает связанный contact.
     * @return \Application\Entity\Contact
     */
    
    public function getContact() 
    {
        return $this->contact;
    }

    /**
     * Задает связанный contact.
     * @param \Application\Entity\Contact $contact
     */    
    public function setContact($contact) 
    {
        $this->contact = $contact;
        $contact->addLegal($this);
    }     
    
}
