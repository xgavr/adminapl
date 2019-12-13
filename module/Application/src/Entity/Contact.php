<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Company\Entity\Legal;
use Doctrine\Common\Collections\Criteria;


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
    const STATUS_LEGAL      = 3; // legal record.

    
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
     * @ORM\Column(name="signature")   
     */
    protected $signature;
    
    /**
     * @ORM\Column(name="address")   
     */
    protected $address;
    
    /**
     * @ORM\Column(name="address_sms")   
     */
    protected $addressSms;
    
    /**
     * @ORM\Column(name="status")   
     */
    protected $status;   
    
    /**
     * @ORM\Column(name="icq")   
     */
    protected $icq;   

    /**
     * @ORM\Column(name="telegramm")   
     */
    protected $telegramm;   

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
     * @ORM\ManyToOne(targetEntity="\Company\Entity\Office", inversedBy="contacts") 
     * @ORM\JoinColumn(name="office_id", referencedColumnName="id")
     */
    protected $office;

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
   
    /**
    * @ORM\OneToMany(targetEntity="Application\Entity\Address", mappedBy="contact")
    * @ORM\JoinColumn(name="id", referencedColumnName="contact_id")
   */
   private $addresses;

    /**
    * @ORM\OneToMany(targetEntity="Application\Entity\Messenger", mappedBy="contact")
    * @ORM\JoinColumn(name="id", referencedColumnName="contact_id")
   */
   private $messengers;

    /**
     * @ORM\ManyToMany(targetEntity="\Company\Entity\Legal", inversedBy="contacts")
     * @ORM\JoinTable(name="contact_legal",
     *      joinColumns={@ORM\JoinColumn(name="contact_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="legal_id", referencedColumnName="id")}
     *      )
     */
   private $legals;

   public function __construct() {
      $this->phones = new ArrayCollection();
      $this->emails = new ArrayCollection();
      $this->legals = new ArrayCollection();
      $this->addresses = new ArrayCollection();
      $this->messengers = new ArrayCollection();
      
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

    public function getSignature() 
    {
        return $this->signature;
    }

    public function setSignature($signature) 
    {
        $this->signature = $signature;
    }     

    public function getStatus() 
    {
        return $this->status;
    }
    
    public function getIcq()
    {
        return $this->icq;
    }
    
    public function setIcq($icq)
    {
        $this->icq = $icq;
    }
    
    public function getAddress()
    {
        return $this->address;
    }
    
    public function setAddress($address)
    {
        $this->address = $address;
    }
    
    public function getAddressSms()
    {
        return $this->addressSms;
    }
    
    public function setAddressSms($addressSms)
    {
        $this->addressSms = $addressSms;
    }
    
    public function getTelegramm()
    {
        return $this->telegramm;
    }
    
    public function setTelegramm($telegramm)
    {
        $this->telegramm = $telegramm;
    }
    
    /**
     * Returns possible statuses as array.
     * @return array
     */
    public static function getStatusList() 
    {
        return [
            self::STATUS_ACTIVE => 'Доступен',
            self::STATUS_RETIRED => 'В отставке'
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
    
    /*
     * Возвращает связанный office.
     * @return \Company\Entity\Office
     */    
    public function getOffice() 
    {
        return $this->office;
    }

    /**
     * Задает связанный office.
     * @param \Company\Entity\Office $office
     */    
    public function setOffice($office) 
    {
        $this->office = $office;
        $office->addContact($this);
    }     
    
    /**
     * Возвращает 1 phone для этого contact.
     * @return array
     */   
   public function getPhone() {
      return $this->phones[0];
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
     * Возвращает 1 email для этого contact.
     * @return array
     */   
    public function getEmail() {
      return $this->emails[0];
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
    /**
     * Возвращает email для этого contact.
     * @return array
     */   
    
    /**
     * Возвращает адреса для этого contact.
     * @return array
     */   
    public function getAddresses() {
      return $this->addresses;
   }    
   
    /**
     * Добавляет новый адрес к этому contact.
     * @param $address
     */   
    public function addAddress($address) 
    {
        $this->addresses[] = $address;
    }       
    
    /**
     * Возвращает мессенджер для этого contact.
     * @return array
     */   
    public function getMessengers() {
      return $this->messengers;
   }    
   
    /**
     * Добавляет новый мессенджер к этому contact.
     * @param $address
     */   
    public function addMessenger($messenger) 
    {
        $this->messengers[] = $messenger;
    }       
    
    

    public function getLegals() {
      return $this->legals;
   }    
   
    /**
     * Добавляет новый email к этому contact.
     * @param $email
     */   
    public function addLegal($legal) 
    {
        $this->legals[] = $legal;
    }       
    
    // Удаляет связь между этим контактом и заданным юрлицом.
    public function removeLegalAssociation($legal) 
    {
        $this->legals->removeElement($legal);
    }    
    
    /**
     * Контакт представление
     * @return string
     */
    public function getAsText()
    {
        $phones = [];
        foreach ($this->getPhones() as $phone){
            $phones[] = $phone->getName().' '.$phone->getComment();
        }
        $emails = [];
        foreach ($this->getEmails() as $email){
            $emails[] = $email->getName();
        }
        
        
        $result = $this->name
                . "<br/>"
                . nl2br($this->description)
                . "<br/>"
                . "Телефон: <span>"
                . implode(';', $phones)
                . "</span><br/>"
                . "Почта: <span>"
                . implode(';', $emails)
                . "</span><br/>"
                ;
        
        return $result;        
    }
}
