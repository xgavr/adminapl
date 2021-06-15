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
 * Description of Make
 * @ORM\Entity(repositoryClass="\Application\Repository\CourierRepository")
 * @ORM\Table(name="courier")
 * @author Daddy
 */

class Courier {
    
     // Make status constants.
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
     * @ORM\Column(name="apl_id")   
     */
    protected $aplId;
    
    /**
     * @ORM\Column(name="comment")  
     */
    protected $comment;    

    /**
     * @ORM\Column(name="status")  
     */
    protected $status;    
       
    /**
     * @ORM\Column(name="site")  
     */
    protected $site;
    
    /**
     * @ORM\Column(name="track")  
     */
    protected $track;

    /**
     * @ORM\Column(name="calculator")  
     */
    protected $calculator;
    
    /**
    * @ORM\OneToMany(targetEntity="Application\Entity\Order", mappedBy="courier")
    * @ORM\JoinColumn(name="id", referencedColumnName="courier_id")
   */
   private $orders;    
    
    public function __construct() {
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

    public function getName() 
    {
        return $this->name;
    }

    public function setName($name) 
    {
        $this->name = $name;
    }     

    public function getComment() 
    {
        return $this->comment;
    }

    public function setComment($comment) 
    {
        $this->comment = $comment;
    }     

    public function getAplId() 
    {
        return $this->aplId;
    }

    public function setAplId($aplId) 
    {
        $this->aplId = $aplId;
    }     
    
    public function getSite() 
    {
        return $this->site;
    }

    public function setSite($site) 
    {
        $this->site = $site;
    }     
    
    public function getTrack() 
    {
        return $this->track;
    }

    public function setTrack($track) 
    {
        $this->track = $track;
    }     
    
    public function getCalculator() 
    {
        return $this->calculator;
    }

    public function setCalculator($calculator) 
    {
        $this->calculator = $calculator;
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
     * Returns make status as string.
     * @return string
     */
    public function getStatusAsString()
    {
        $list = self::getStatusList();
        if (isset($list[$this->status])) {
            return $list[$this->status];
        }

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
}
