<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ApiMarketPlace\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use ApiMarketPlace\Entity\MarketplaceUpdate;
use ApiMarketPlace\Entity\MarketplaceOrder;

/**
 * Description of Marketplace
 * @ORM\Entity(repositoryClass="\ApiMarketPlace\Repository\MarketplaceRepository")
 * @ORM\Table(name="marketplace")
 * @author Daddy
 */
class Marketplace {
    
    const STATUS_ACTIVE       = 1; // Active.
    const STATUS_RETIRED      = 2; // Retired.
    
    const TYPE_OZON = 1; // 
    const TYPE_UNKNOWN = 99; // 
    
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
     * @ORM\Column(name="site")   
     */
    protected $site;

    /**
     * @ORM\Column(name="login")   
     */
    protected $login;

    /**
     * @ORM\Column(name="password")   
     */
    protected $password;

    /**
     * @ORM\Column(name="comment")   
     */
    protected $comment;

    /**
     * Номер в магазина в торговой площадке
     * @ORM\Column(name="merchantId")   
     */
    protected $merchantId;

    /**
     * Api token в торговой площадке
     * @ORM\Column(name="api_token")   
     */
    protected $apiToken;

    /**
     * Ip торговой площадки
     * @ORM\Column(name="remote_addr")   
     */
    protected $remoteAddr;

    /**
     * @ORM\Column(name="status")   
     */
    protected $status;

    /**
     * @ORM\Column(name="market_type")   
     */
    protected $marketType;

    /** 
     * @ORM\Column(name="date_created")  
     */
    protected $dateCreated;  
            
    /**
    * @ORM\OneToMany(targetEntity="ApiMarketPlace\Entity\MarketplaceOrder", mappedBy="marketplace")
    * @ORM\JoinColumn(name="id", referencedColumnName="marketplace_id")
     */
    private $marketplaceOrders;
    
    /**
    * @ORM\OneToMany(targetEntity="ApiMarketPlace\Entity\MarketplaceUpdate", mappedBy="marketplace")
    * @ORM\JoinColumn(name="id", referencedColumnName="marketplace_id")
     */
    private $marketplaceUpdates;

    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Contact", inversedBy="marketplaces") 
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="id")
     */
    private $contact;

    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Contract", inversedBy="marketplaces") 
     * @ORM\JoinColumn(name="contract_id", referencedColumnName="id")
     */
    protected $contract;

    /**
     * Constructor.
     */
    public function __construct() 
    {
        $this->marketplaceOrders = new ArrayCollection();
        $this->marketplaceUpdates = new ArrayCollection();
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

    public function getSite() 
    {
        return $this->site;
    }

    public function getTagName() 
    {
        $result = '<nobr>';
        if ($this->site){
            $result .= '<a href="'.$this->site.'" class="btn btn-link btn-xs" title="Перейти на сайт '.$this->name.'" target="_blank"><span class="glyphicon glyphicon-link"></span></a> ';
        }
        $result .= '<a href="/market-place/view/'.$this->id.'" target=_blank>'.$this->name.'</a></nobr>';

        return $result;
    }

    public function setSite($site) 
    {
        $this->site = $site;
    }     

    public function getLogin() 
    {
        return $this->login;
    }

    public function setLogin($login) 
    {
        $this->login = $login;
    }     

    public function getPassword() 
    {
        return $this->password;
    }

    public function setPassword($password) 
    {
        $this->password = $password;
    }     

    public function getComment() 
    {
        return $this->comment;
    }

    public function setComment($comment) 
    {
        $this->comment = $comment;
    }     

    public function getMerchantId() 
    {
        return $this->merchantId;
    }

    public function setMerchantId($merchantId) 
    {
        $this->merchantId = $merchantId;
    }     

    public function getApiToken() 
    {
        return $this->apiToken;
    }

    public function setApiToken($apiToken) 
    {
        $this->apiToken = $apiToken;
    }     

    public function getRemoteAddr() 
    {
        return $this->remoteAddr;
    }

    public function setRemoteAddr($remoteAddr) 
    {
        $this->remoteAddr = $remoteAddr;
    }     

    /**
     * Returns the date of marketplace creation.
     * @return string     
     */
    public function getDateCreated() 
    {
        return $this->dateCreated;
    }
    
    /**
     * Sets the date when this marketplace was created.
     * @param string $dateCreated     
     */
    public function setDateCreated($dateCreated) 
    {
        $this->dateCreated = $dateCreated;
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
            self::STATUS_ACTIVE => 'Используется',
            self::STATUS_RETIRED => 'Не используется'
        ];
    }    
    
    /**
     * Returns marketplace status as string.
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
    
    public function getMarketType() {
        return $this->marketType;
    }

    /**
     * Returns possible market type as array.
     * @return array
     */
    public static function getMarketTypeList() 
    {
        return [
            self::TYPE_UNKNOWN => 'Неизвестно',
            self::TYPE_OZON => 'Озон'
        ];
    }    
    
    /**
     * Returns marketplace market type as string.
     * @return string
     */
    public function getMarketTypeAsString()
    {
        $list = self::getMarketTypeList();
        if (isset($list[$this->marketType]))
            return $list[$this->marketType];
        
        return 'Unknown';
    }    
    
    public function setMarketType($marketType): void {
        $this->marketType = $marketType;
    }

       
    /**
     * Returns the array of marketplaceUpdates assigned to this.
     * @return array
     */
    public function getMarketplaceOrders()
    {
        return $this->marketplaceOrders;
    }
        
    /**
     * Assigns.
     * @param MarketplaceOrder $marketplaceOrder
     */
    public function addMarketplaceOrder($marketplaceOrder)
    {
        $this->marketplaceOrders[] = $marketplaceOrder;
    }    
    
    /**
     * Returns the array of marketplaceUpdates assigned to this.
     * @return array
     */
    public function getMarketplaceUpdates()
    {
        return $this->marketplaceUpdates;
    }
        
    /**
     * Assigns.
     * @param MarketplaceUpdate $marketplaceUpdate
     */
    public function addMarketplaceUpdate($marketplaceUpdate)
    {
        $this->marketplaceUpdates[] = $marketplaceUpdate;
    }    

    public function getContact() {
        return $this->contact;
    }

    public function getContactPresent() {
        if ($this->contact){
            return $this->contact->getParetnLink();
        }
        
        return;
    }

    public function getContract() {
        return $this->contract;
    }

    public function getContractPresent() {
        if ($this->contract){
            return $this->contract->getContractPresent();
        }
        
        return;
    }

    public function setContact($contact): void {
        $this->contact = $contact;
    }

    public function setContract($contract): void {
        $this->contract = $contract;
    }

        /**
     * Массив для формы
     * @return array 
     */
    public function toArray()
    {
        $result = [
            'status' => $this->getStatus(),
            'name' => $this->getName(),
            'comment' => $this->getComment(),
            'apiToken' => $this->getApiToken(),
            'login' => $this->getLogin(),
            'merchantId' => $this->getMerchantId(),
            'password' => $this->getPassword(),
            'site' => $this->getSite(),
            'contact' => ($this->getContact()) ? $this->getContact()->getId():null,
            'phone' => ($this->getContact()) ? $this->getContact()->getPhoneAsString():null,
            'contract' => ($this->getContract()) ? $this->getContract()->getId():null,
            'marketType' => $this->getMarketType(),
        ];
        
        return $result;
    }    
}
