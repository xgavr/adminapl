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
use Application\Entity\Shipping;
use Doctrine\Common\Collections\Criteria;
use Stock\Entity\Ptu;
use ApiMarketPlace\Entity\Cash;
use User\Entity\User;
use Company\Entity\Commission;
use User\Filter\PhoneFilter;
use Application\Entity\Messenger;
use Stock\Entity\PtSheduler;
use Application\Entity\Supplier;

/**
 * Description of Office
 * @ORM\Entity(repositoryClass="\Company\Repository\OfficeRepository")
 * @ORM\Table(name="office")
 * @author Daddy
 */
class Office {
        
     // Status constants.
    const STATUS_ACTIVE       = 1; // Active office.
    const STATUS_RETIRED      = 2; // Retired office.
   
    const DEFAULT_SHIPPING_LIMIT_1   = 3000; // По умолчанию граница 1 стоимости заказа для изменения цены доставки.
    const DEFAULT_SHIPPING_LIMIT_2   = 12000; // По умолчанию граница 2 стоимости заказа для изменения цены доставки.
    
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
     * @ORM\Column(name="address")   
     */
    protected $address;

    /**
     * @ORM\Column(name="address_sms")   
     */
    protected $addressSms;

    /**
     * Граница стоимости заказа 1
     * @ORM\Column(name="shipping_limit_1")   
     */
    protected $shippingLimit1;

    /**
     * Граница стоимости заказа 2
     * @ORM\Column(name="shipping_limit_2")   
     */
    protected $shippingLimit2;
    
    /**
     * Карта сб
     * @ORM\Column(name="sb_card")   
     */
    protected $sbCard;
    
    /**
     * Держатель карты сб
     * @ORM\Column(name="sb_owner")   
     */
    protected $sbOwner;

    /**
     * Идентификатор ТСП в СПБ
     * @ORM\Column(name="sbp_merchant_id")   
     */
    protected $sbpMerchantId;

    /**
     * Ссылка на отзыв
     * @ORM\Column(name="link_review")   
     */
    protected $linkReview;
    
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
    * @ORM\OneToMany(targetEntity="\Application\Entity\Shipping", mappedBy="office")
    * @ORM\JoinColumn(name="id", referencedColumnName="office_id")
   */
   private $shippings;    
    
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
    * @ORM\OneToMany(targetEntity="Cash\Entity\Cash", mappedBy="office")
    * @ORM\JoinColumn(name="id", referencedColumnName="office_id")
   */
   private $cashes;    

   /**
    * @ORM\OneToMany(targetEntity="User\Entity\User", mappedBy="office")
    * @ORM\JoinColumn(name="id", referencedColumnName="office_id")
   */
   private $users;    

   /**
    * @ORM\OneToMany(targetEntity="Company\Entity\Commission", mappedBy="office")
    * @ORM\JoinColumn(name="id", referencedColumnName="office_id")
   */
   private $commission;    

   /**
    * @ORM\OneToMany(targetEntity="Stock\Entity\PtSheduler", mappedBy="office")
    * @ORM\JoinColumn(name="id", referencedColumnName="office_id")
   */
   private $ptShedulers;    

   /**
    * @ORM\OneToMany(targetEntity="Stock\Entity\PtSheduler", mappedBy="office2")
    * @ORM\JoinColumn(name="id", referencedColumnName="office2_id")
   */
   private $ptShedulers2;    
   
   /**
    * @ORM\OneToMany(targetEntity="Application\Entity\Supplier", mappedBy="office")
    * @ORM\JoinColumn(name="id", referencedColumnName="office_id")
   */
   private $suppliers;    
   
    /**
     * One Office has Many Offices.
     * @ORM\OneToMany(targetEntity="Company\Entity\Office", mappedBy="parent")
     */
    private $children;

   /**
     * Many Offices have One Office.
     * @ORM\ManyToOne(targetEntity="Company\Entity\Office", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true)
     */
    private $parent;    
   
   /**
     * Constructor.
     */
    public function __construct() 
    {
        $this->contacts = new ArrayCollection();
        $this->contracts = new ArrayCollection();
        $this->rates = new ArrayCollection();      
        $this->shippings = new ArrayCollection();      
        $this->cashes = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->commission = new ArrayCollection();
        $this->ptShedulers = new ArrayCollection();
        $this->ptShedulers2 = new ArrayCollection();
        $this->suppliers = new ArrayCollection();
        $this->children = new ArrayCollection();        
    }
    
    public function getId() 
    {
        return $this->id;
    }

    public function getLink()
    {
        return "<a href='/offices/view/{$this->id}' target='_blank'>{$this->name}</a>";                
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

    public function getAddress() {
        return $this->address;
    }

    public function setAddress($address) {
        $this->address = $address;
        return $this;
    }

    public function getAddressSms() {
        return $this->addressSms;
    }

    public function setAddressSms($addressSms) {
        $this->addressSms = $addressSms;
        return $this;
    }
    
    public function getShippingLimit1() 
    {
        return $this->shippingLimit1;
    }

    public function setShippingLimit1($shippingLimit1) 
    {
        $this->shippingLimit1 = $shippingLimit1;
    }     

    public function getShippingLimit2() 
    {
        return $this->shippingLimit2;
    }

    public function setShippingLimit2($shippingLimit2) 
    {
        $this->shippingLimit2 = $shippingLimit2;
    }     

    public function getSbCard() 
    {
        return $this->sbCard;
    }

    public function setSbCard($sbCard) 
    {
        $this->sbCard = $sbCard;
    }     

    public function getSbOwner() 
    {
        return $this->sbOwner;
    }

    public function setSbOwner($sbOwner) 
    {
        $this->sbOwner = $sbOwner;
    }     

    public function getSbpMerchantId() {
        return $this->sbpMerchantId;
    }

    public function setSbpMerchantId($sbpMerchantId): void {
        $this->sbpMerchantId = $sbpMerchantId;
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
    
    public function getLegalContactPhone()
    {
        $contact = $this->getLegalContact();
        if ($contact){
            if ($contact->getPhone()){
                $filter = new PhoneFilter(['format' => PhoneFilter::PHONE_FORMAT_RU]);
                return $filter->filter($contact->getPhone()->getName());
            }
        }
        
        if ($this->getParent()){
            return $this->getParent()->getLegalContactPhone();
        }
        
        return;
    }
        
    public function getLegalContactPhones()
    {
        $result = [];
        $contact = $this->getLegalContact();
        if ($contact){
            if ($contact->getPhones()){
                foreach ($contact->getPhones() as $phone){
                    $filter = new PhoneFilter(['format' => PhoneFilter::PHONE_FORMAT_RU]);
                    $result[] = $filter->filter($phone->getName());
                }    
            }
        }

        if ($this->getParent()){
            return $this->getParent()->getLegalContactPhone();
        }
        
        return implode(', ', $result);
    }
        
    public function getLegalContactSmsAddress()
    {
        $contact = $this->getLegalContact();
        if ($contact){
            if ($contact->getAddress()){
                return $contact->getAddress()->getAddressSms();
            }    
        }
        return;
    }
        
    public function getLegalContactWhatsapp()
    {
        $contact = $this->getLegalContact();
        if ($contact){
            if ($contact->getMessengers()){
                foreach ($contact->getMessengers() as $messenger){
                    if ($messenger->getType() == Messenger::TYPE_WHATSAPP){
                        return $messenger->getIdent();
                    }    
                }    
            }    
        }
        if ($this->getParent()){
            return $this->getParent()->getLegalContactWhatsapp();
        }
        return;
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
     * Возвращает связанный shippngs.
     * @return Shippng
     */    
    public function getShippings() 
    {
        return $this->shippings;
    }

    /**
     * Add shipping
     * @param Shipping $shipping
     */
    public function addShippng($shipping) 
    {
        $this->shippings[] = $shipping;
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
    
    /*
     * Возвращает связанный cash.
     * @return array
     */    
    public function getCashes() 
    {
        return $this->cashes;
    }

    /**
     * Add cash
     * @param Cash $cash
     */
    public function addCash($cash) 
    {
        $this->cashes[] = $cash;
    }         
    
    /*
     * Возвращает связанный user.
     * @return array
     */    
    public function getUsers() 
    {
        return $this->users;
    }

    /**
     * Add user
     * @param User $user
     */
    public function addUser($user) 
    {
        $this->users[] = $user;
    }         
    
    /*
     * Возвращает связанный commission.
     * @return array
     */    
    public function getCommission() 
    {
        return $this->commission;
    }

    /**
     * Add commisar
     * @param Commission $commisar
     */
    public function addCommisar($commisar) 
    {
        $this->commission[] = $commisar;
    }         

    /*
     * Возвращает связанный ptSheduler.
     * @return array
     */    
    public function getPtShedulers() 
    {
        return $this->ptShedulers;
    }

    /**
     * Add ptSheduler
     * @param PtSheduler $ptSheduler
     */
    public function addPtSheduler($ptSheduler) 
    {
        $this->ptShedulers[] = $ptSheduler;
    }         
    
    /*
     * Возвращает связанный ptSheduler2.
     * @return array
     */    
    public function getPtShedulers2() 
    {
        return $this->ptShedulers2;
    }

    /**
     * Add ptSheduler
     * @param PtSheduler $ptSheduler2
     */
    public function addPtSheduler2($ptSheduler2) 
    {
        $this->ptShedulers2[] = $ptSheduler2;
    }                 

    /*
     * Возвращает связанный suppliers.
     * @return array
     */    
    public function getSuppliers() 
    {
        return $this->suppliers;
    }

    /**
     * Add Supplier
     * @param Supplier $supplier
     */
    public function addSupplier($supplier) 
    {
        $this->suppliers[] = $supplier;
    }         
    
   /**
     * Add children
     * @param Office $office
     */
    public function addChildren($office)
    {
        $this->children[] = $office;

    }

    /**
     * Remove children
     *
     * @param Office $office
     */
    public function removeChildren($office)
    {
        $this->children->removeElement($office);
    }

    /**
     * Get children
     * @return array
     */
    public function getChildren()
    {
        return $this->children;
    }
    
    /**
     * Set parent
     * @param Office $office
     * @return MenuItem
     */
    public function setParent($office)
    {
        $this->parent = $office;
        if ($office){
            $office->addChildren($this);
        }    
    }

    /**
     * Get parent
     * @return Supplier
     */
    public function getParent()
    {
        return $this->parent;
    }    

    /**
     * Get parent Id
     * @return integer
     */
    public function getParentId()
    {
        if ($this->parent){
            return $this->parent->getId();
        }    
        
        return;
    }    

    public function getLinkReview() {
        return $this->linkReview;
    }

    public function setLinkReview($linkReview) {
        $this->linkReview = $linkReview;
        return $this;
    }
    
    /**
     * Массив для формы
     * @return array 
     */
    public function toArray()
    {
        $result = [
            'aplId' => $this->getAplId(),
            'fullName' => $this->getFullName(),
            'id' => $this->getId(),
            'name' => $this->getName(),
            'status' => $this->getStatus(),
        ];
        
        return $result;
    }                
}
