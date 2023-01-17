<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Selection
 * @ORM\Entity(repositoryClass="\Application\Repository\OrderRepository")
 * @ORM\Table(name="selection")
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
     * @ORM\Column(name="oe")  
     */
    protected $oe;

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
}
