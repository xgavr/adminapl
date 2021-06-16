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
use Application\Entity\ContactCar;
use Application\Entity\Courier;
use Application\Entity\Shipping;


/**
 * Description of App
 * @ORM\Entity(repositoryClass="\Application\Repository\OrderRepository")
 * @ORM\Table(name="orders")
 * @author Daddy
 */
class Order {
    
    // Константы.
    const STATUS_NEW    = 10; // Новый.
    const STATUS_PROCESSED   = 20; // Обработан.
    const STATUS_CONFIRMED   = 30; // Подтвержден.
    const STATUS_DELIVERY   = 40; // Доставка.
    const STATUS_SHIPPED   = 50; // Отгружен.
    const STATUS_CANCELED  = -10; // Отменен.
        
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
     * Дата заказа
     * @ORM\Column(name="date_oper")  
     */
    protected $dateOper;    

    /**
     * Дата доставки/отгрузки
     * @ORM\Column(name="date_shipment")  
     */
    protected $dateShipment;    

    /**
     * Дата модификации
     * @ORM\Column(name="date_mod")  
     */
    protected $dateMod;    
    
    /**
     * Дата создания
     * @ORM\Column(name="date_created")  
     */
    protected $dateCreated;    
    
    /**
     * @ORM\Column(name="total")  
     */
    protected $total;    
    
    /**
     * @ORM\Column(name="comment")  
     */
    protected $comment;    
    
    /**
     * @ORM\Column(name="status")  
     */
    protected $status;    

    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\ContactCar", inversedBy="orders") 
     * @ORM\JoinColumn(name="contact_car_id", referencedColumnName="id")
     */
    protected $contactCar;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Courier", inversedBy="orders") 
     * @ORM\JoinColumn(name="courier_id", referencedColumnName="id")
     */
    protected $courier;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Shipping", inversedBy="orders") 
     * @ORM\JoinColumn(name="shipping_id", referencedColumnName="id")
     */
    protected $shipping;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Contact", inversedBy="orders") 
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="id")
     */
    protected $contact;
    
    /**
     * @ORM\ManyToOne(targetEntity="User\Entity\User", inversedBy="orders") 
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;
        
    /**
    * @ORM\OneToMany(targetEntity="Application\Entity\Bid", mappedBy="order")
    * @ORM\JoinColumn(name="id", referencedColumnName="order_id")
     */
    private $bids;
    
    /**
    * @ORM\OneToMany(targetEntity="Application\Entity\Selection", mappedBy="order")
    * @ORM\JoinColumn(name="id", referencedColumnName="order_id")
     */
    private $selections;
    
    /**
    * @ORM\OneToMany(targetEntity="Application\Entity\Comment", mappedBy="order")
    * @ORM\JoinColumn(name="id", referencedColumnName="order_id")
     */
    private $comments;
    
    /**
     * Constructor.
     */
    public function __construct() 
    {
        $this->bids = new ArrayCollection();
        $this->selections = new ArrayCollection();
        $this->comments = new ArrayCollection();
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
    
    public function getDateOper() 
    {
        return $this->dateOper;
    }

    public function setDateOper($dateOper) 
    {
        $this->dateOper = $dateOper;
    }     

    public function getDateShipment() 
    {
        return $this->dateShipment;
    }

    public function setDateShipment($dateShipment) 
    {
        $this->dateShipment = $dateShipment;
    }     

    public function getDateMod() 
    {
        return $this->dateMod;
    }

    public function setDateMod($dateMod) 
    {
        $this->dateMod = $dateMod;
    }     

    public function getDateCreated() 
    {
        return $this->dateCreated;
    }

    public function setDateCreated($dateCreated) 
    {
        $this->dateCreated = $dateCreated;
    }     

    public function getTotal() 
    {
        return $this->total;
    }

    public function setTotal($total) 
    {
        $this->total = $total;
    }     
    
    public function getComment() 
    {
        return $this->comment;
    }

    public function setComment($comment) 
    {
        $this->comment = $comment;
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
            self::STATUS_NEW => 'Новый',
            self::STATUS_PROCESSED => 'Обработан',
            self::STATUS_CONFIRMED => 'Подтвержден',
            self::STATUS_DELIVERY => 'Доставка',
            self::STATUS_SHIPPED => 'Отгружен',
            self::STATUS_CANCELED => 'Отменен',
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
        
    /*
     * Возвращает связанный contact.
     * @return Contact
     */
    
    public function getContact() 
    {
        return $this->contact;
    }

    /**
     * Задает связанный contact.
     * @param Contact $contact
     */    
    public function setContact($contact) 
    {
        $this->contact = $contact;
        $contact->addOrder($this);
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
     * @param \User\Entity\User $user
     */    
    public function setUser($user) 
    {
        $this->user = $user;
    }         
 
    /*
     * Возвращает связанный contactCar.
     * @return ContactCar
     */
    
    public function getContactCar() 
    {
        return $this->contactCar;
    }

    /**
     * Задает связанный contactCar.
     * @param ContactCar $contactCar
     */    
    public function setContactCar($contactCar) 
    {
        $this->contactCar = $contactCar;
    }         
 
    /*
     * Возвращает связанный courier.
     * @return Courier
     */    
    
    public function getCounrier() 
    {
        return $this->courier;
    }

    /**
     * Задает связанный counrier.
     * @param Courier $courier
     */    
    public function setCourier($courier) 
    {
        $this->courier = $courier;
    }         
 
    /*
     * Возвращает связанный shipping.
     * @return Shipping
     */    
    
    public function getShipping() 
    {
        return $this->shipping;
    }

    /**
     * Задает связанный shipping.
     * @param Shipping $shipping
     */    
    public function setShipping($shipping) 
    {
        $this->shipping = $shipping;
    }         
 
    /**
     * Returns the array of bid assigned to this.
     * @return array
     */
    public function getBids()
    {
        return $this->bids;
    }
        
    /**
     * Assigns.
     * @param Application\Entity\Bid $bid
     */
    public function addBid($bid)
    {
        $this->bids[] = $bid;
    }
            
    /**
     * Returns the array of selection assigned to this.
     * @return array
     */
    public function getSelections()
    {
        return $this->selections;
    }
        
    /**
     * Assigns.
     * @param \Application\Entity\Selection $selection
     */
    public function addSelection($selection)
    {
        $this->selections[] = $selection;
    }
            
    /**
     * Returns the array of comments assigned to this.
     * @return array
     */
    public function getComments()
    {
        return $this->comments;
    }
        
    /**
     * Assigns.
     * @param \Application\Entity\Comment $comment
     */
    public function addComment($comment)
    {
        $this->comments[] = $comment;
    }
}
