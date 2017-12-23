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
 * Description of Contact
 * @ORM\Entity(repositoryClass="\Application\Repository\ContactRepository")
 * @ORM\Table(name="contact")
 * @author Daddy
 */
class Contact {
    
    // Константы доступности.
    const STATUS_ACTIVE       = 1; // Active user.
    const STATUS_RETIRED      = 2; // Retired user.
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="name")   
     */
    protected $name;
    
    /**
     * @ORM\Column(name="description")   
     */
    protected $description;
    
    /**
     * @ORM\Column(name="status")   
     */
    protected $status;   
    
    /** 
     * @ORM\Column(name="date_created")  
     */
    protected $dateCreated;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Supplier", inversedBy="contact") 
     * @ORM\JoinColumn(name="supplier_id", referencedColumnName="id")
     */
    protected $supplier;

    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Client", inversedBy="contact") 
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id")
     */
    protected $client;

    /**
     * @ORM\ManyToOne(targetEntity="User\Entity\User", inversedBy="user") 
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

   /**
    * @ORM\OneToMany(targetEntity="Application\Entity\Phone", mappedBy="contact")
    * @ORM\JoinColumn(name="id", referencedColumnName="contact_id")
   */
   private $phones;

   /**
    * @ORM\OneToMany(targetEntity="Application\Entity\Email", mappedBy="contact")
    * @ORM\JoinColumn(name="id", referencedColumnName="contact_id")
   */
   private $emails;

   public function __construct() {
      $this->phones = new ArrayCollection();
      $this->emails = new ArrayCollection();
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

    public function getDescription() 
    {
        return $this->description;
    }

    public function setDescription($description) 
    {
        $this->description = $description;
    }     

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
     * Возвращает связанный supplier.
     * @return \Application\Entity\Supplier
     */    
    public function getSupplier() 
    {
        return $this->supplier;
    }

    /**
     * Задает связанный supplier.
     * @param \Application\Entity\Supplier $supplier
     */    
    public function setSupplier($supplier) 
    {
        $this->supplier = $supplier;
        $supplier->addContact($this);
    }     
    
    /*
     * Возвращает связанный client.
     * @return \Application\Entity\Client
     */    
    public function getClient() 
    {
        return $this->client;
    }

    /**
     * Задает связанный client.
     * @param \Application\Entity\Client $client
     */    
    public function setClient($client) 
    {
        $this->client = $client;
        $client->addContact($this);
    }     
    
    /*
     * Возвращает связанный user.
     * @return \User\Entity\User
     */    
    public function getUser() 
    {
        return $this->user;
    }

    /**
     * Задает связанный user.
     * @param \Application\Entity\User $user
     */    
    public function setUser($user) 
    {
        $this->user = $user;
        $user->addContact($this);
    }     
    
    /**
     * Возвращает phone для этого contact.
     * @return array
     */   
   public function getPhones() {
      return $this->phones;
   }    
   
       /**
     * Returns the string of assigned phones.
     */
    public function getPhonesAsString()
    {
        $phoneList = '';
        
        $count = count($this->phones);
        $i = 0;
        foreach ($this->phones as $phone) {
            $phoneList .= $phone->getName();
            if ($i<$count-1)
                $phoneList .= ', ';
            $i++;
        }
        
        return $phoneList;
    }
   
    /**
     * Добавляет новый phone к этому contact.
     * @param $phone
     */   
    public function addPhone($phone) 
    {
        $this->phones[] = $phone;
    }       
    
    /**
     * Возвращает email для этого contact.
     * @return array
     */   
    public function getEmails() {
      return $this->emails;
   }    
   
    /**
     * Добавляет новый email к этому contact.
     * @param $email
     */   
    public function addEmail($email) 
    {
        $this->emails[] = $email;
    }       
}
