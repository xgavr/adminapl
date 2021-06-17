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
 * @ORM\Table(name="comment")
 * @author Daddy
 */
class Comment {
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
     * @ORM\Column(name="comment")   
     */
    protected $comment;


    /**
     * @ORM\ManyToOne(targetEntity="User\Entity\User", inversedBy="comments") 
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Order", inversedBy="comments") 
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

    public function getAplId() 
    {
        return $this->aplId;
    }

    public function setAplId($aplId) 
    {
        $this->aplId = $aplId;
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
        $order->addComment($this);
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
        //$oem->addComment($this);
    }     
        
}
