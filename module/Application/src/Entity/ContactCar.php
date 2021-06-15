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
use Application\Entity\Order;


/**
 * Description of App
 * @ORM\Entity(repositoryClass="\Application\Repository\OrderRepository")
 * @ORM\Table(name="contact_car")
 * @author Daddy
 */
class ContactCar {
    
    // Константы.
    const STATUS_ACTIVE       = 1; // Active user.
    const STATUS_RETIRED      = 2; // Retired user.
    
    const WHEEL_LEFT = 1; //руль слева
    const WHEEL_RIGHT = 2; //руль справа
    
    const TM_AUTO = 1; //коробка автомат
    const TM_MECH = 2; //коробка механика
    const TM_UNKNOWN = 3; // неизвестно
    
    const AC_YES = 1; // кондиционер есть
    const AC_NO = 2; // кондиционер нет
    const AC_UNKNOWN = 3; // неизвестно
    
        
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
     * Дата создания
     * @ORM\Column(name="date_created")  
     */
    protected $dateCreated;    
    
    /**
     * @ORM\Column(name="comment")  
     */
    protected $comment;    
    
    /**
     * @ORM\Column(name="vin")  
     */
    protected $vin;    

    /**
     * @ORM\Column(name="vin2")  
     */
    protected $vin2;    

    /**
     * @ORM\Column(name="status")  
     */
    protected $status;    

    /**
     * Год выпуска
     * @ORM\Column(name="yocm")  
     */
    protected $yocm;    

    /**
     * Руль
     * @ORM\Column(name="wheel")  
     */
    protected $wheel;

    
    /**
     * Коробка передач
     * @ORM\Column(name="tm")  
     */
    protected $tm;

    /**
     * Кондиционер
     * @ORM\Column(name="ac")  
     */
    protected $ac;
    
    /**
     * Модель двигателя
     * @ORM\Column(name="md")  
     */
    protected $md;

    /**
     * Рабочий объем двигателя
     * @ORM\Column(name="ed")  
     */
    protected $ed;

    /**
     * Мощность двигателя
     * @ORM\Column(name="ep")  
     */
    protected $ep;


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
     * @ORM\ManyToOne(targetEntity="Application\Entity\Contact", inversedBy="contactCars") 
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="id")
     */
    protected $contact;
    
    /**
    * @ORM\OneToMany(targetEntity="Application\Entity\Order", mappedBy="contactCar")
    * @ORM\JoinColumn(name="id", referencedColumnName="contact_car_id")
   */
   private $orders;
    
    /**
     * Constructor.
     */
    public function __construct() 
    {
        $this->orders = new ArrayCollection();
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
    
    public function getDateCreated() 
    {
        return $this->dateCreated;
    }

    public function setDateCreated($dateCreated) 
    {
        $this->dateCreated = $dateCreated;
    }     

    public function getComment() 
    {
        return $this->comment;
    }

    public function setComment($comment) 
    {
        $this->comment = $comment;
    }     
    
    public function getVin() 
    {
        return $this->vin;
    }

    public function setVin($vin) 
    {
        $this->vin = $vin;
    }     
    
    public function getVin2() 
    {
        return $this->vin2;
    }

    public function setVin2($vin2) 
    {
        $this->vin2 = $vin2;
    }     
    
    public function getYocm() 
    {
        return $this->yocm;
    }

    public function setYocm($yocm) 
    {
        $this->yocm = $yocm;
    }     
    
    public function getMd() 
    {
        return $this->md;
    }

    public function setMd($md) 
    {
        $this->md = $md;
    }     
    
    public function getEd() 
    {
        return $this->ed;
    }

    public function setEd($ed) 
    {
        $this->ed = $ed;
    }     
    
    public function getEp() 
    {
        return $this->ep;
    }

    public function setEp($ep) 
    {
        $this->ep = $ep;
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
            self::STATUS_ACTIVE => 'Активный',
            self::STATUS_RETIRED => 'Не использовать',
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
     * Returns wheel.
     * @return int     
     */
    public function getWheel() 
    {
        return $this->wheel;
    }

    /**
     * Returns possible wheels as array.
     * @return array
     */
    public static function getWheelList() 
    {
        return [
            self::WHEEL_LEFT => 'Слева',
            self::WHEEL_RIGHT => 'Справа',
        ];
    }    
    
    /**
     * Returns user wheel as string.
     * @return string
     */
    public function getWheelAsString()
    {
        $list = self::getWheelList();
        if (isset($list[$this->wheel]))
            return $list[$this->wheel];
        
        return 'Unknown';
    }    
        
    /**
     * Sets wheel.
     * @param int $wheel     
     */
    public function setWheel($wheel) 
    {
        $this->wheel = $wheel;
    }   
        
    /**
     * Returns tm.
     * @return int     
     */
    public function getTm() 
    {
        return $this->tm;
    }

    /**
     * Returns possible tms as array.
     * @return array
     */
    public static function getTmList() 
    {
        return [
            self::TM_AUTO => 'Автомат',
            self::TM_MECH => 'Механика',
            self::TM_UNKNOWN => 'Неизвестно',
        ];
    }    
    
    /**
     * Returns tm as string.
     * @return string
     */
    public function getTmAsString()
    {
        $list = self::getTmList();
        if (isset($list[$this->tm]))
            return $list[$this->tm];
        
        return 'Unknown';
    }    
        
    /**
     * Sets tm.
     * @param int $tm     
     */
    public function setTm($tm) 
    {
        $this->tm = $tm;
    }   
        
    /**
     * Returns ac.
     * @return int     
     */
    public function getAc() 
    {
        return $this->ac;
    }

    /**
     * Returns possible acs as array.
     * @return array
     */
    public static function getAcList() 
    {
        return [
            self::AC_YES => 'С кондиционером',
            self::AC_NO => 'Без кондиционера',
            self::AC_UNKNOWN => 'Неизвестно',
        ];
    }    
    
    /**
     * Returns ac as string.
     * @return string
     */
    public function getAcAsString()
    {
        $list = self::getAcList();
        if (isset($list[$this->ac]))
            return $list[$this->ac];
        
        return 'Unknown';
    }    
        
    /**
     * Sets ac.
     * @param int $ac     
     */
    public function setAc($ac) 
    {
        $this->ac = $ac;
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
        $contact->addContactCar($this);
    }     
    
    /*
     * Возвращает связанный order.
     * @return Order
     */    
    public function getOrders() 
    {
        return $this->orders;
    }

    /**
     * Assigns.
     */
    public function addOrder($order)
    {
        $this->orders[] = $order;
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
    
    public function formArray()
    {
        
        return [
            'id'      => $this->getId(),
            'aplId'   => $this->getAplId(),
            'comment' => $this->getComment(),
            'vin'     => $this->getVin(),
            'vin2'    => $this->getVin2(),
            'status'  => $this->getStatus(),
            'yocm'    => $this->getYocm(),
            'wheel'   => $this->getWheel(),
            'tm'      => $this->getTm(),
            'ac'      => $this->getAc(),
            'md'      => $this->getMd(),
            'ed'      => $this->getEd(),
            'ep'      => $this->getEp(),
            'make'    => $this->getMake(),
            'model'   => $this->getModel(),
            'car'     => $this->getCar(),
        ];    
    }    

    public function exchangeArray(array $data)
    {
        $this->id      = !empty($data['id']) ? $data['id'] : null;
        $this->aplId   = !empty($data['aplId']) ? $data['aplId'] : null;
        $this->comment = !empty($data['comment']) ? $data['comment'] : null;
        $this->vin     = !empty($data['vin']) ? $data['vin'] : null;
        $this->vin2    = !empty($data['vin2']) ? $data['vin2'] : null;
        $this->status  = !empty($data['status']) ? $data['status'] : $this::STATUS_ACTIVE;
        $this->yocm    = !empty($data['yocm']) ? $data['yocm'] : null;
        $this->wheel   = !empty($data['wheel']) ? $data['wheel'] : $this::WHEEL_LEFT;
        $this->tm      = !empty($data['tm']) ? $data['tm'] : $this::TM_UNKNOWN;
        $this->ac      = !empty($data['ac']) ? $data['ac'] : $this::AC_UNKNOWN;
        $this->md      = !empty($data['md']) ? $data['md'] : null;
        $this->ed      = !empty($data['ed']) ? $data['ed'] : null;
        $this->ep      = !empty($data['ep']) ? $data['ep'] : null;
        $this->make    = !empty($data['make']) ? $data['make'] : null;
        $this->model   = !empty($data['model']) ? $data['model'] : null;
        $this->car     = !empty($data['car']) ? $data['car'] : null;
    }    
}
