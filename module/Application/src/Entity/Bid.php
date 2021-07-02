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
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Oem", inversedBy="bids") 
     * @ORM\JoinColumn(name="oem_id", referencedColumnName="id")
     */
    private $oem;
    
    
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
    
    public function getOpts() 
    {
        return $this->opts;
    }

    public function setOpts($opts) 
    {
        $this->opts = $opts;
    }     
    
    public function getDisplayName() 
    {
        return $this->displayName;
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
    
    public function getOem() 
    {
        return $this->oem;
    }

    /**
     * Задает связанный oem.
     * @param \Application\Entity\Oem $oem
     */    
    public function setOem($oem) 
    {
        $this->oem = $oem;
    }     
        
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
        ];
    }    
    
}
