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
use Application\Entity\Make;
use Application\Entity\Model;
use Application\Entity\Car;


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
     * @ORM\ManyToOne(targetEntity="Application\Entity\Make", inversedBy="orders") 
     * @ORM\JoinColumn(name="make_id", referencedColumnName="id")
     */
    protected $make;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Model", inversedBy="orders") 
     * @ORM\JoinColumn(name="model_id", referencedColumnName="id")
     */
    protected $model;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Car", inversedBy="orders") 
     * @ORM\JoinColumn(name="car_id", referencedColumnName="id")
     */
    protected $car;
    
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
    * @ORM\OneToMany(targetEntity="Application\Entity\Bid", mappedBy="orders")
    * @ORM\JoinColumn(name="id", referencedColumnName="order_id")
     */
    private $bid;
    
    /**
     * Constructor.
     */
    public function __construct() 
    {
        $this->bid = new ArrayCollection();
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
     * Возвращает связанный make.
     * @return Make
     */
    
    public function getMake() 
    {
        return $this->make;
    }

    /**
     * Задает связанный make.
     * @param Make $make
     */    
    public function setMake($make) 
    {
        $this->make = $make;
    }         
 
    /*
     * Возвращает связанный model.
     * @return Model
     */    
    
    public function getModel() 
    {
        return $this->model;
    }

    /**
     * Задает связанный model.
     * @param Model $model
     */    
    public function setModel($model) 
    {
        $this->model = $model;
    }         
 
    /*
     * Возвращает связанный car.
     * @return Car
     */    
    
    public function getCar() 
    {
        return $this->car;
    }

    /**
     * Задает связанный car.
     * @param Car $car
     */    
    public function setCar($car) 
    {
        $this->car = $car;
    }         
 
    /**
     * Returns the array of bid assigned to this.
     * @return array
     */
    public function getBid()
    {
        return $this->bid;
    }
        
    /**
     * Assigns.
     * @param Application\Entity\Bid $bid
     */
    public function addBid($bid)
    {
        $this->bid[] = $bid;
    }
            
}
