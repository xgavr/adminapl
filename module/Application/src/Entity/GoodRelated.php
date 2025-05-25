<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Application\Entity\Order;
use Application\Entity\Goods;

/**
 * Description of Producer
 * @ORM\Entity(repositoryClass="\Application\Repository\TokenRepository")
 * @ORM\Table(name="good_title")
 * @author Daddy
 */
class GoodRelated {
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Order", inversedBy="goodRelations") 
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id")
     */
    protected $order;        
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Goods", inversedBy="goodRelations") 
     * @ORM\JoinColumn(name="good_id", referencedColumnName="id")
     */
    protected $good;        
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Goods", inversedBy="goodRelatedRelations") 
     * @ORM\JoinColumn(name="good_related_id", referencedColumnName="id")
     */
    protected $goodRelated;        
    

    public function getId() 
    {
        return $this->id;
    }

    public function setId($id) 
    {
        $this->id = $id;
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
     */
    public function setOrder($order) {
        $this->order = $order;
    }

    /**
     * 
     * @return Goods
     */
    public function getGood() {
        return $this->good;
    }

    /**
     * 
     * @param Goods $good
     */
    public function setGood($good) {
        $this->good = $good;
    }

    /**
     * 
     * @return Goods
     */
    public function getGoodRelated() {
        return $this->goodRelated;
    }

    /**
     * 
     * @param Goods $goodRelated
     */
    public function setGoodRelated($goodRelated) {
        $this->goodRelated = $goodRelated;
    }

}
