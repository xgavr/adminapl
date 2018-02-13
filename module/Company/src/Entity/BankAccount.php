<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Company\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of BankAccount
 * @ORM\Entity(repositoryClass="\Application\Repository\LegalRepository")
 * @ORM\Table(name="bank_account")
 * @author Daddy
 */
class BankAccount {
        
     // Status constants.
    const STATUS_ACTIVE       = 1; // Active.
    const STATUS_RETIRED      = 2; // Retired.
   
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
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
     * @ORM\Column(name="city")   
     */
    protected $city;
    
    /**
     * @ORM\Column(name="bik")   
     */
    protected $bik;

    /**
     * @ORM\Column(name="ks")   
     */
    protected $ks;

    /**
     * @ORM\Column(name="rs")   
     */
    protected $rs;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Legal", inversedBy="bank_account") 
     * @ORM\JoinColumn(name="legal_id", referencedColumnName="id")
     */
    private $legal;
    
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

    public function getBik() 
    {
        return $this->bik;
    }

    public function setBik($bik) 
    {
        $this->bik = $bik;
    }     

    public function getName() 
    {
        return $this->name;
    }

    public function setName($name) 
    {
        $this->name = $name;
    }     

    public function getCity() 
    {
        return $this->city;
    }

    public function setCity($city) 
    {
        $this->city = $city;
    }     

    public function getKs() 
    {
        return $this->ks;
    }

    public function setKs($ks) 
    {
        $this->ks = $ks;
    }     

    public function getRs() 
    {
        return $this->rs;
    }

    public function setRs($rs) 
    {
        $this->rs = $rs;
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
            
        
    /*
     * @return \Application\Entity\Legal
     */
    
    public function getLegal() 
    {
        return $this->legal;
    }

    /**
     * @param \Application\Entity\Legal $legal
     */    
    public function setLegal($legal) 
    {
        $this->legal = $legal;
        $legal->addBankAccount($this);
    }     
        
}
