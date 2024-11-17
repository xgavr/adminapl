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
use Application\Entity\Phone;

/**
 * Description of OrderPhone
 * @ORM\Entity(repositoryClass="\Application\Repository\OrderRepository")
 * @ORM\Table(name="order_phone")
 * @author Daddy
 */
class OrderPhone {
    
    const KIND_MAIN = 1; //основной телефон
    const KIND_OTHER = 2; //дополнительный
    
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
     * @ORM\Column(name="kind")   
     */
    protected $kind;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Order", inversedBy="phones") 
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id")
     */
    protected $order;

    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Phone", inversedBy="orders") 
     * @ORM\JoinColumn(name="phone_id", referencedColumnName="id")
     */
    protected $phone;

    
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

    
    public function getKind() {
        return $this->kind;
    }

    /**
     * Returns possible kind as array.
     * @return array
     */
    public static function getKindList() 
    {
        return [
            self::KIND_MAIN => 'Основной',
            self::KIND_OTHER => 'Дополнительный',
        ];
    }    
    
    /**
     * Returns kind as string.
     * @return string
     */
    public function getKindAsString()
    {
        $list = self::getЛштвList();
        if (isset($list[$this->kind]))
            return $list[$this->kind];
        
        return 'Unknown';
    }    
    
    
    public function setKind($kind) {
        $this->kind = $kind;
        return $this;
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
        $order->addPhone($this);
        return $this;
    }

    /**
     * 
     * @return Phone
     */
    public function getPhone() {
        return $this->phone;
    }

    /**
     * 
     * @param Phone $phone
     * @return $this
     */
    public function setPhone($phone) {
        $this->phone = $phone;
        $phone->addOrder($this);
        return $this;
    }
}
