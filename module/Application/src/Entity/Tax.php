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
 * Description of Tax
 * @ORM\Entity
 * @ORM\Table(name="tax")
 * @author Daddy
 */
class Tax {
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
     * @ORM\Column(name="amount")   
     */
    protected $amount;

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

    public function getAmount() 
    {
        return $this->amount;
    }

    public function setAmount($amount) 
    {
        $this->amount = $amount;
    }     
    
   /**
    * @ORM\OneToMany(targetEntity="\Application\Entity\Goods", mappedBy="tax")
    * @ORM\JoinColumn(name="id", referencedColumnName="tax_id")
   */
   private $goods;

   public function __construct() {
      $this->goods = new ArrayCollection();
   }

    /**
     * Возвращает goods для этого tax.
     * @return array
     */   
   public function getGoods() {
      return $this->goods;
   }    
   
    /**
     * Добавляет новый goods к этому tax.
     * @param $goods
     */   
    public function addGoods($goods) 
    {
        $this->goods[] = $goods;
    }   
    
}
