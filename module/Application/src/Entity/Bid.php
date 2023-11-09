<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Bid
 * @ORM\Entity(repositoryClass="\Application\Repository\OrderRepository")
 * @ORM\Table(name="bid")
 * @author Daddy
 */
class Bid {
    
    const TAKE_OK  = 1;// учтено 
    const TAKE_NO  = 2;// не учтено 
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="row_no")   
     */
    protected $rowNo;

    /**
     * @ORM\Column(name="price")   
     */
    protected $price;

    /**
     * @ORM\Column(name="num")   
     */
    protected $num;

    /**
     * @ORM\Column(name="display_name")   
     */
    protected $displayName;

    /**
     * @ORM\Column(name="opts")   
     */
    protected $opts;

    /**
     * @ORM\Column(name="take")   
     */
    protected $take;

    /** 
     * @ORM\Column(name="base_key")  
     */
    protected $baseKey;
    
    /** 
     * @ORM\Column(name="oe")  
     */
    protected $oe;

    /**
     * @ORM\Column(name="date_created")  
     */
    protected $dateCreated;    

    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Goods", inversedBy="bids") 
     * @ORM\JoinColumn(name="good_id", referencedColumnName="id")
     */
    private $good;

    /**
     * @ORM\ManyToOne(targetEntity="User\Entity\User", inversedBy="bids") 
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Order", inversedBy="bids") 
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id")
     */
    private $order;
        
    public function getId() 
    {
        return $this->id;
    }

    public function getRowKey() 
    {
        return 'bid:'.$this->id;
    }    
    
    public function setId($id) 
    {
        $this->id = $id;
    }     

    public function getRowNo() 
    {
        return $this->rowNo;
    }

    public function setRowNo($rowNo) 
    {
        $this->rowNo = $rowNo;
    }     

    public function getPrice() 
    {
        return $this->price;
    }

    public function setPrice($price) 
    {
        $this->price = $price;
    }     

    public function getNum() 
    {
        return $this->num;
    }

    public function setNum($num) 
    {
        $this->num = $num;
    }     
    
    public function getTotal() 
    {
        return $this->num*$this->price;
    }

    public function getOpts() 
    {
        return $this->opts;
    }

    public function setOpts($opts) 
    {
        $this->opts = $opts;
    }     
    
    /**
     * Returns take.
     * @return int     
     */
    public function getTake() 
    {
        return $this->take;
    }

    /**
     * Sets base key.
     * @param string $baseKey     
     */
    public function setBaseKey($baseKey) 
    {
        $this->baseKey = $baseKey;
    }    
    
    /**
     * Returns the base key.
     * @return string     
     */
    public function getBaseKey() 
    {
        return $this->baseKey;
    }
    
    /**
     * Sets oe.
     * @param string $oe     
     */
    public function setOe($oe) 
    {
        $this->oe = $oe;
    }    
    
    /**
     * Returns the oe.
     * @return string     
     */
    public function getOe() 
    {
        return $this->oe;
    }

    /**
     * Returns possible take as array.
     * @return array
     */
    public static function getTakeList() 
    {
        return [
            self::TAKE_OK => 'Проведено',
            self::TAKE_NO => 'Не проведено',
        ];
    }    
    
    /**
     * Returns take as string.
     * @return string
     */
    public function getTakeAsString()
    {
        $list = self::getTakeList();
        if (isset($list[$this->take]))
            return $list[$this->take];
        
        return 'Unknown';
    }    
        
    /**
     * Sets take.
     * @param int $take     
     */
    public function setTake($take) 
    {
        $this->take = $take;
    }   
    
    public function getDisplayName() 
    {
        if ($this->displayName){
            return $this->displayName;
        }
        
        return$this->getGood()->getNameShort();
    }

    public function getDisplayNameProducer() 
    {
        $producerName = $this->getGood()->getProducer()->getName();
        return $producerName.' '.$this->getDisplayName();
    }

    public function getDisplayNameProducerCode() 
    {
        $producerName = $this->getGood()->getProducer()->getName();
        $code = $this->getGood()->getCode();
        return $code.' '.$producerName.' '.$this->getDisplayName();
    }

    public function setDisplayName($displayName) 
    {
        $this->displayName = $displayName;
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
     * Возвращает связанный good.
     * @return \Application\Entity\Goods
     */
    
    public function getGood() 
    {
        return $this->good;
    }

    /**
     * Задает связанный good.
     * @param \Application\Entity\Goods $good
     */    
    public function setGood($good) 
    {
        $this->good = $good;
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
     * Возвращает связанный order.
     * @return \Application\Entity\Order
     */
    
    public function getOrder() 
    {
        return $this->order;
    }

    /**
     * Задает связанный order.
     * @param \Application\Entity\Order $order
     */    
    public function setOrder($order) 
    {
        $this->order = $order;
        $order->addBid($this);
    }     
        
    /*
     * Возвращает связанный oem.
     * @return \Application\Entity\Oem
     */
    
    /**
     * Лог
     * @return array
     */
    public function toLog()
    {
        return [
            'price' => $this->getPrice(),
            'good' => $this->getGood()->getId(),
            'num' => $this->getNum(),
            'rowNo' => $this->getRowNo(),
            'displayName' => $this->getDisplayName(),
            'oem' => $this->getOe(),
        ];
    }    

    /**
     * Для апи
     * 
     * @return array
     */
    public function toArray()
    {
        return [
            'price' => $this->getPrice(),
            'good' => $this->getGood()->toArray(),
            'num' => $this->getNum(),
            'rowNo' => $this->getRowNo(),
        ];
    }    
    
}
