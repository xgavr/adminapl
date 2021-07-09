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
use User\Entity\User;
use Application\Entity\Order;
use Application\Entity\Contact;
use Application\Entity\ContactCar;
use Company\Entity\Office;

/**
 * Description of Client
 * @ORM\Entity(repositoryClass="\Application\Repository\RingRepository")
 * @ORM\Table(name="ring")
 * @author Daddy
 */
class Ring {
        
     // Status constants.
    const STATUS_ACTIVE       = 1; // Active user.
    const STATUS_RETIRED      = 2; // Retired user.
   
    const MODE_NEW_ORDER        = 10; // Новый заказ.
    const MODE_CHANGE_ORDER     = 20; // Изменения в заказ. Обработать заказ. Подтвердить заказ
    const MODE_DELIVERY_ORDER   = 30; // Доставка заказа.
    const MODE_RETURN_ORDER     = 40; // Возврат товара.
    const MODE_OTHER_ORDER      = 50; // Прочее по заказу.
    const MODE_OFFICE_LOCATION  = 60; // Как пройти. Как оплатить
    const MODE_CALL_STAFF       = 70; // Переключить на сотрудника.
    const MODE_OTHER            = 80; // Прочие звонки.
    const MODE_UNKNOWN          = 90; // Непонятные звонки.

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
        
    /** 
     * @ORM\Column(name="mode")  
     */
    protected $mode;

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
     * @ORM\Column(name="vin")   
     */
    protected $vin;
    
    /**
     * Запршиваемые артикулы
     * @ORM\Column(name="gds")   
     */
    protected $gds;
    
    /**
     * @ORM\Column(name="info")   
     */
    protected $info;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Order", inversedBy="rings") 
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id")
     */
    private $order;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Contact", inversedBy="rings") 
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="id")
     */
    private $contact;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\ContactCar", inversedBy="rings") 
     * @ORM\JoinColumn(name="contact_car_id", referencedColumnName="id")
     */
    private $contactCar;
    
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
     * @ORM\ManyToOne(targetEntity="Company\Entity\Office", inversedBy="rings") 
     * @ORM\JoinColumn(name="office_id", referencedColumnName="id")
     */
    private $office;
    
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

    public function getVin() 
    {
        return $this->vin;
    }

    public function setVin($vin) 
    {
        $this->vin = $vin;
    }     

    public function getGds() 
    {
        return $this->gds;
    }

    public function setGds($gds) 
    {
        $this->gds = $gds;
    }     

    public function getPhone() 
    {
        return $this->phone;
    }

    public function setPhone($phone) 
    {
        $this->phone = $phone;
    }     

    public function getInfo() 
    {
        return $this->info;
    }

    public function setInfo($info) 
    {
        $this->info = $info;
    }     

    public function getMode() 
    {
        return $this->mode;
    }

    /**
     * Returns possible modes as array.
     * @return array
     */
    public static function getModeList() 
    {
        return [
            self::MODE_NEW_ORDER => 'Новый заказ',
            self::MODE_CHANGE_ORDER => 'Изменить, обработать, подтвердить заказ',
            self::MODE_DELIVERY_ORDER => 'Доставка заказа',
            self::MODE_RETURN_ORDER => 'Возврат товара',
            self::MODE_OTHER_ORDER => 'Прочее по заказу',
            self::MODE_OFFICE_LOCATION => 'Как пройти, забрать, оплатить',
            self::MODE_CALL_STAFF => 'Соеденить с сотрудником',
            self::MODE_OTHER => 'Прочие звонки',
            self::MODE_UNKNOWN => 'Непонятные звонки',
        ];
    }    
    
    /**
     * Returns user mode as string.
     * @return string
     */
    public static function getModeAsString()
    {
        $list = self::getModeList();
        if (isset($list[$this->mode]))
            return $list[$this->mode];
        
        return 'Unknown';
    }    
    

    public function setMode($mode) 
    {
        $this->mode = $mode;
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
     * @return User
     */
    public function getManager() 
    {
        return $this->manager;
    }

    /**
     * Задает связанный manager.
     * @param User $user
     */    
    public function setManager($user) 
    {
        $this->manager = $user;
    }             

    /*
     * Возвращает связанный order.
     * @return Order
     */    
    public function getOrder() 
    {
        return $this->order;
    }

    /**
     * Задает связанный order.
     * @param Order $order
     */    
    public function setOrder($order) 
    {
        $this->order = $order;
    }             
    /*
     * Возвращает связанный order.
     * @return Order
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
    }             
    
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
     * Возвращает связанный user.
     * @return User
     */
    public function getUser() 
    {
        return $this->user;
    }

    /**
     * Задает связанный user.
     * @param User $user
     */    
    public function setUser($user) 
    {
        $this->user = $user;
    }             

    /*
     * Возвращает связанный office.
     * @return Office
     */
    public function getOffice() 
    {
        return $this->office;
    }

    /**
     * Задает связанный office.
     * @param Office $office
     */    
    public function setOffice($office) 
    {
        $this->office = $office;
    }             
}
