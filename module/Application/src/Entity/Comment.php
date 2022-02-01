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
 * @ORM\Entity(repositoryClass="\Application\Repository\CommentRepository")
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
     * @ORM\Column(name="date_created")   
     */
    protected $dateCreated;


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
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Client", inversedBy="comments") 
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id")
     */
    private $client;
    
    
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

    public function getDateCreated() 
    {
        return $this->dateCreated;
    }

    public function setDateCreated($dateCreated) 
    {
        $this->dateCreated = $dateCreated;
    }     

    /*
     * Возвращает связанный order.
     * @return \Application\Entity\Order
     */    
    public function getOrder() 
    {
        return $this->order;
    }

    public function getOrderLink() 
    {
        if ($this->order){
            return '<a href="/order/view/'.$this->order->getId().'" target="_blank">'.$this->order->getId().'</a>';
        }
        return;
    }

    public function getAplOrderLink() 
    {
        if ($this->order){
            if ($this->order->getAplId()){
                return '<a href="https://autopartslist.ru/admin/orders/view/id/'.$this->order->getAplId().'" target="_blank">'.$this->order->getAplId().'</a>';
            }    
        }
        return;
    }

    /**
     * Задает связанный order.
     * @param \Application\Entity\Order $order
     */    
    public function setOrder($order) 
    {
        $this->order = $order;
        if ($order){
            $order->addComment($this);
        }    
    }     
        
    public function getClient() 
    {
        return $this->client;
    }

    public function getClientLink() 
    {
        if ($this->client){
            return '<a href="/client/view/'.$this->client->getId().'" target="_blank">'.$this->client->getId().'</a>';
        }
        return;
    }

    public function getAplClientLink() 
    {
        if ($this->client){
            if ($this->client->getAplId()){
                return '<a href="https://autopartslist.ru/admin/users/users-view/id/'.$this->client->getAplId().'" target="_blank">'.$this->client->getAplId().'</a>';
            }    
        }
        return;
    }

    /**
     * Задает связанный client.
     * @param \Application\Entity\Client $client
     */    
    public function setClient($client) 
    {
        $this->client = $client;
        if ($client){
            $client->addComment($this);
        }    
    }     
        
    /*
     * Возвращает связанный user.
     * @return \User\Entity\User
     */    
    public function getUser() 
    {
        return $this->user;
    }

    public function getUserName() 
    {
        if ($this->user){
            return $this->user->getFullname();
        }
        return;
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
