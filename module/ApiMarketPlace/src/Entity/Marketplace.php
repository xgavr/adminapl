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

/**
 * Description of Bid
 * @ORM\Entity(repositoryClass="\ApiMarketPlace\Repository\MarketplaceRepository")
 * @ORM\Table(name="marketplace")
 * @author Daddy
 */
class Marketplace {
    
    const STATUS_ACTIVE       = 1; // Active.
    const STATUS_RETIRED      = 2; // Retired.
    
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
     * @ORM\Column(name="date_created")  
     */
    protected $dateCreated;  
            
    /**
    * @ORM\OneToMany(targetEntity="ApiMarketPlace\Entity\MarketplaceUpdate", mappedBy="marketplace")
    * @ORM\JoinColumn(name="id", referencedColumnName="marketplace_id")
     */
    private $marketplaceUpdates;
    
    /**
     * Constructor.
     */
    public function __construct() 
    {
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
        return '<a href="'.$this->site.'" target=_blank>'.$this->name.'</a>';
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
     * @param MarketplaceUpdate $marketplaceupdate
     */
    public function addMarketplaceUpdate($marketplaceUpdate)
    {
        $this->marketplaceUpdates[] = $marketplaceUpdate;
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
        ];
        
        return $result;
    }    
}
