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
use Application\Entity\Rate;
use Doctrine\Common\Collections\Criteria;
use Company\Entity\Region;

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
    
    const PREPAY_ON           = 1;//Брать предоплату
    const PREPAY_OFF          = 2;//Не брать предоплату
   
    const PRICE_LIST_ON       = 1;//Выгружать в прайсы
    const PRICE_LIST_OFF      = 2;//Не выгружать в прайсы
   
    const PRICE_FOLDER       = './data/prices'; // папка с прайсами
    const PRICE_FOLDER_ARX   = './data/prices/arx'; // папка с архивами прайсов
    
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
     * @ORM\Column(name="amount")  
     */
    protected $amount;

    /** 
     * @ORM\Column(name="quantity")  
     */
    protected $quantity;

    /** 
     * @ORM\Column(name="prepay")  
     */
    protected $prepayStatus;

    /** 
     * @ORM\Column(name="price_list")  
     */
    protected $priceListStatus;

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
    * @ORM\OneToMany(targetEntity="Application\Entity\Cross", mappedBy="supplier")
    * @ORM\JoinColumn(name="id", referencedColumnName="supplier_id")
     */
    private $crosses;

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
    * @ORM\OneToMany(targetEntity="Application\Entity\BillSetting", mappedBy="supplier")
    * @ORM\JoinColumn(name="id", referencedColumnName="supplier_id")
     */
    private $billSettings;    
    
    /**
    * @ORM\OneToMany(targetEntity="Application\Entity\Idoc", mappedBy="supplier")
    * @ORM\JoinColumn(name="id", referencedColumnName="supplier_id")
     */
    private $idocs;    
    
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
    * @ORM\OneToMany(targetEntity="Application\Entity\SupplierApiSetting", mappedBy="supplier")
    * @ORM\JoinColumn(name="id", referencedColumnName="supplier_id")
     */
    private $supplierApiSettings;    
    
   /**
    * @ORM\OneToMany(targetEntity="Rate", mappedBy="supplier")
    * @ORM\JoinColumn(name="id", referencedColumnName="supplier_id")
   */
   private $rates;

    /**
     * @ORM\ManyToMany(targetEntity="Application\Entity\Region")
     * @ORM\JoinTable(name="supplier_region",
     *      joinColumns={@ORM\JoinColumn(name="supplier_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="region_id", referencedColumnName="id")}
     *      )
     */
    private $regions;
   
   /**
     * Constructor.
     */
    public function __construct() 
    {
        $this->contacts = new ArrayCollection();
        $this->raw = new ArrayCollection();
        $this->crosses = new ArrayCollection();
        $this->priceDescriptions = new ArrayCollection();
        $this->priceGettings = new ArrayCollection();
        $this->billGettings = new ArrayCollection();
        $this->billSettings = new ArrayCollection();
        $this->idocs = new ArrayCollection();
        $this->requestSettings = new ArrayCollection();
        $this->supplySettings = new ArrayCollection();
        $this->supplierApiSettings = new ArrayCollection();
        $this->rates = new ArrayCollection();
        $this->regions = new ArrayCollection();        
    }
    
    
    public function getId() 
    {
        return $this->id;
    }

    public function setId($id) 
    {
        $this->id = $id;
    }     
    
    public function getPriceFolder()
    {
        return self::PRICE_FOLDER.'/'.$this->getId();
    }

    public function getArxPriceFolder()
    {
        return self::PRICE_FOLDER_ARX.'/'.$this->getId();
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

    public function getAmount() 
    {
        return round($this->amount);
    }

    public function setAmount($amount) 
    {
        $this->amount = $amount;
    }     

    public function getQuantity() 
    {
        return $this->quantity;
    }

    public function setQuantity($quantity) 
    {
        $this->quantity = $quantity;
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
     * Returns apl status.
     * @return int     
     */
    public function getAplStatus() 
    {
        switch ($this->status){
            case self::STATUS_ACTIVE: return 1;
            default: return 0;    
        }
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
    
    public static function getStatusName($status)
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
     * Returns prepay status.
     * @return int     
     */
    public function getPrepayStatus() 
    {
        return $this->prepayStatus;
    }

    public function getAplPrepayStatus() 
    {
        switch ($this->prepayStatus){
            case self::PREPAY_ON: return 1;
            default: return 0;    
        }
    }

    /**
     * Returns possible prepay status as array.
     * @return array
     */
    public static function getPrepayStatusList() 
    {
        return [
            self::PREPAY_ON => 'Брать предоплату',
            self::PREPAY_OFF => 'Не брать предоплату'
        ];
    }    
    
    /**
     * Returns user prepay as string.
     * @return string
     */
    public function getPrepayStatusAsString()
    {
        $list = self::getPrepayStatusList();
        if (isset($list[$this->prepayStatus]))
            return $list[$this->prepayStatus];
        
        return 'Unknown';
    }    
    
    public function getPrepayStatusName($prepayStatus)
    {
        $list = self::getPrepayStatusList();
        if (isset($list[$prepayStatus]))
            return $list[$prepayStatus];
        
        return 'Unknown';        
    }

    /**
     * Sets prepay.
     * @param int $prepayStatus     
     */
    public function setPrepayStatus($prepayStatus) 
    {
        $this->prepayStatus = $prepayStatus;
    }   

    /**
     * Returns priceListStatus.
     * @return int     
     */
    public function getPriceListStatus() 
    {
        return $this->priceListStatus;
    }

    public function getAplPriceListStatus() 
    {
        switch ($this->priceListStatus){
            case self::PRICE_LIST_ON: return 1;
            default: return 0;    
        }
    }

    /**
     * Returns possible price list status as array.
     * @return array
     */
    public static function getPriceListStatusList() 
    {
        return [
            self::PRICE_LIST_ON => 'Выгружать в прайс листы',
            self::PRICE_LIST_OFF => 'Не выгружать в прайс листы'
        ];
    }    
    
    /**
     * Returns user price list status as string.
     * @return string
     */
    public function getPriceListStatusAsString()
    {
        $list = self::getPriceListStatusList();
        if (isset($list[$this->priceListStatus]))
            return $list[$this->priceListStatus];
        
        return 'Unknown';
    }    
    
    public function getPriceListStatusName($priceListStatus)
    {
        $list = self::getPriceListStatusList();
        if (isset($list[$priceListStatus]))
            return $list[$priceListStatus];
        
        return 'Unknown';        
    }

    /**
     * Sets priceListStatus.
     * @param int $priceListStatus     
     */
    public function setPriceListStatus($priceListStatus) 
    {
        $this->priceListStatus = $priceListStatus;
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
     * Returns the array of crosses assigned to this.
     * @return array
     */
    public function getCrosses()
    {
        return $this->crosses;
    }
        
    /**
     * Assigns.
     */
    public function addCross($cross)
    {
        $this->crosses[] = $cross;
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
     * Returns the array of bills assigned to this.
     * @return array
     */
    public function getBillSettings()
    {
        return $this->billSettings;
    }
        
    /**
     * Assigns.
     */
    public function addBillSettings($billSetting)
    {
        $this->billSettings[] = $billSetting;
    }
    
    /**
     * Returns the array of idoc to this.
     * @return array
     */
    public function getIdocs()
    {
        return $this->idocs;
    }
        
    /**
     * Assigns.
     */
    public function addIdoc($idoc)
    {
        $this->idocs[] = $idoc;
    }
    
    /**
     * Returns the array of request assigned to this.
     * @return array
     */
    public function getRequestSettings()
    {
        return $this->requestSettings;
    }
    
    public function getActiveManualRequestSetting()
    {
        $criteria = Criteria::create()
                ->andWhere(Criteria::expr()->eq('status', RequestSetting::STATUS_ACTIVE))
                ->andWhere(Criteria::expr()->eq('mode', RequestSetting::MODE_MANUALLY))
                ->orderBy(['id' => Criteria::ASC])
                ;
        
        return $this->requestSettings->matching($criteria);                
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
    
    /**
     * Returns the array of supplierApiSetting assigned to this.
     * @return array
     */
    public function getSupplierApiSettings()
    {
        return $this->supplierApiSettings;
    }
        
    /**
     * Assigns.
     */
    public function addSupplierApiSetting($supplierApiSetting)
    {
        $this->supplierApiSettings[] = $supplierApiSetting;
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
        
    public function getRegions()
    {
        return $this->regions;
    }
    
    /**
     * Returns the string of assigned regions names.
     */
    public function getRegionssAsString()
    {
        $regionList = '';
        
        $count = count($this->regions);
        if (!$count) return 'Все';
        $i = 0;
        foreach ($this->regions as $region) {
            $regionList .= $region->getName();
            if ($i<$count-1)
                $regionList .= ', ';
            $i++;
        }
        
        return $regionList;
    }
    
    public function getRatesAsArray()
    {
        $regionList = [];
        
        foreach ($this->regions as $region) {
            $regionList[] = $region->getId();
        }
        
        return $rateList;
    }

    /**
     * Assigns a region to supplier.
     * @param Region $region
     */
    public function addRegion($region)
    {
        $this->regions->add($region);
    }        
}
