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
class Selection {
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="comment")   
     */
    protected $comment;


    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Oem", inversedBy="selections") 
     * @ORM\JoinColumn(name="oem_id", referencedColumnName="id")
     */
    private $oem;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Order", inversedBy="selections") 
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id")
     */
    private $order;
    
    
    public function getId() 
    {
        return $this->id;
    }

    public function setId($id) 
    {
        $this->id = $id;
    }     

    public function getComment() 
    {
        return $this->comment;
    }

    public function setComment($comment) 
    {
        $this->comment = $comment;
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
        $order->addSelection($this);
    }     
        
    /*
     * Возвращает связанный order.
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
        $oem->addSelection($this);
    }     
        
}
