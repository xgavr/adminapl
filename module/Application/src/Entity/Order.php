<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * Description of App
 * @ORM\Entity(repositoryClass="\Application\Repository\OrderRepository")
 * @ORM\Table(name="orders")
 * @author Daddy
 */
class Order {
    
    // Константы.
    const STATUS_NEW    = 10; // Новый.
    const STATUS_CONFIRMED   = 20; // Подтвержден.
    const STATUS_PAID   = 30; // Оплачен.
    const STATUS_SHIPPED   = 40; // Отгружен.
    const STATUS_CANCELED  = -10; // Отменен.
        
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
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
     * @ORM\ManyToOne(targetEntity="Application\Entity\Client", inversedBy="orders") 
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id")
     */
    protected $client;
    
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
            self::STATUS_CANCELED => 'Отменен',
            self::STATUS_CONFIRMED => 'Подтвержден',
            self::STATUS_PAID => 'Оплачен',
            self::STATUS_SHIPPED => 'Отгружен',
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
     * Возвращает связанный client.
     * @return \Application\Entity\Client
     */
    
    public function getClient() 
    {
        return $this->client;
    }

    /**
     * Задает связанный client.
     * @param \Application\Entity\Client $client
     */    
    public function setClient($client) 
    {
        $this->client = $client;
        $client->addOrder($this);
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
