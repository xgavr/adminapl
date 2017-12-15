<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of App
 * @ORM\Entity(repositoryClass="\Application\Repository\OrderRepository")
 * @ORM\Table(name="orders")
 * @author Daddy
 */
class Order {
    
    // Константы доступности.
    const AVAILABLE_TRUE    = 1; // Доступен.
    const AVAILABLE_FALSE   = 0; // Недоступен.
    
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
     * @ORM\Column(name="client_id")   
     */
    protected $client;
    
    /**
     * @ORM\Column(name="user_id")   
     */
    protected $user;
    
    
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
    
    public function getStatus() 
    {
        return $this->status;
    }

    public function setStatus($status) 
    {
        $this->status = $status;
    }     
    
}
