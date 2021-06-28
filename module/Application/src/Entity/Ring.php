<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

/**
 * Description of Client
 * @ORM\Entity(repositoryClass="\Application\Repository\RingRepository")
 * @ORM\Table(name="client")
 * @author Daddy
 */
class Ring {
        
     // Status constants.
    const STATUS_ACTIVE       = 1; // Active user.
    const STATUS_RETIRED      = 2; // Retired user.
   
    const PRICE_0   = 0; // Розница
    const PRICE_1   = 1; // ВИП
    const PRICE_2   = 2; // опт2
    const PRICE_3   = 3; // опт3
    const PRICE_4   = 4; // опт4
    const PRICE_5   = 5; // опт5
    
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
     * @ORM\Column(name="phone")   
     */
    protected $phone;
    
    /**
     * @ORM\Column(name="info")   
     */
    protected $info;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Orders", inversedBy="rings") 
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id")
     */
    private $order;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Contact", inversedBy="rings") 
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="id")
     */
    private $contact;
    
    /**
     * @ORM\ManyToOne(targetEntity="User\Entity\User", inversedBy="rings") 
     * @ORM\JoinColumn(name="manager_id", referencedColumnName="id")
     */
    private $manager;
    
    /**
     * @ORM\ManyToOne(targetEntity="User\Entity\User", inversedBy="rings") 
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;
    
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

    public function getName() 
    {
        return $this->name;
    }

    public function setName($name) 
    {
        $this->name = $name;
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
     * Returns pricecol.
     * @return int     
     */
    public function getPricecol() 
    {
        return $this->pricecol;
    }

    /**
     * Returns possible pricecols as array.
     * @return array
     */
    public static function getPricecilList() 
    {
        return [
            self::PRICE_0 => 'Розница',
            self::PRICE_1 => 'VIP',
            self::PRICE_2 => 'Опт2',
            self::PRICE_3 => 'Опт3',
            self::PRICE_4 => 'Опт4',
            self::PRICE_5 => 'Опт5',
        ];
    }    
    
    /**
     * Returns pricecol as string.
     * @return string
     */
    public function getPriceColAsString()
    {
        $list = self::getPricecolList();
        if (isset($list[$this->pricecol]))
            return $list[$this->pricecol];
        
        return 'Unknown';
    }    
    
    /**
     * Sets pricecol.
     * @param int $pricecol 
     */
    public function setPricecol($pricecol) 
    {
        $this->pricecol = $pricecol;
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
     * Возвращает связанный manager.
     * @return \User\Entity\User
     */
    
    public function getManager() 
    {
        return $this->manager;
    }

    /**
     * Задает связанный manager.
     * @param \User\Entity\User $user
     */    
    public function setManager($user) 
    {
        $this->manager = $user;
        $user->addClient($this);
    }             
}
