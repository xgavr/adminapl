<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Application\Entity\Contact;
use User\Filter\PhoneFilter;
use User\Entity\User;
use Application\Entity\Client;
use Application\Entity\Supplier;
use Company\Entity\Office;
use Application\Entity\Order;
use Application\Entity\Email;

/**
 * Description of OrderEmail
 * @ORM\Entity(repositoryClass="\Application\Repository\OrderRepository")
 * @ORM\Table(name="order_email")
 * @author Daddy
 */
class OrderEmail {
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
     * @ORM\ManyToOne(targetEntity="Application\Entity\Order", inversedBy="emails") 
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id")
     */
    protected $order;

    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Email", inversedBy="orders") 
     * @ORM\JoinColumn(name="email_id", referencedColumnName="id")
     */
    protected $email;

    
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
     * 
     * @return Order
     */
    public function getOrder() {
        return $this->order;
    }

    /**
     * 
     * @param Order $order
     * @return $this
     */
    public function setOrder($order) {
        $this->order = $order;
        $order->addEmail($this);
        return $this;
    }

    /**
     * 
     * @return Email
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * 
     * @param Email $email
     * @return $this
     */
    public function setEmail($email) {
        $this->email = $email;
        $email->addOrder($this);
        return $this;
    }
}
