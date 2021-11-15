<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Application\Entity\Goods;
use Application\Entity\Supplier;

/**
 * Description of Producer
 * @ORM\Entity(repositoryClass="\Application\Repository\SupplierRepository")
 * @ORM\Table(name="good_supplier")
 * @author Daddy
 */
class GoodSupplier {
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="rest")   
     */
    protected $rest = 0;
    
    /**
     * @ORM\Column(name="up_date")   
     */
    protected $update;

    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Goods", inversedBy="suppliers") 
     * @ORM\JoinColumn(name="good_id", referencedColumnName="id")
     */
    protected $good;        
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Supplier", inversedBy="goods") 
     * @ORM\JoinColumn(name="supplier_id", referencedColumnName="id")
     */
    protected $supplier;       
    

    public function getId() 
    {
        return $this->id;
    }

    public function setId($id) 
    {
        $this->id = $id;
    }     

    public function getRest()
    {
        return $this->rest;
    }

    public function setRest($rest)
    {
        $this->rest = $rest;
    }

    public function getUpdate()
    {
        return $this->update;
    }

    public function setUpdate($update)
    {
        $this->update = $update;
    }

    /**
     * Возвращает связанный good.
     * @return Goods
     */    
    public function getGood() 
    {
        return $this->goods;
    }

    /**
     * Задает связанный good.
     * @param Goods $good
     */    
    public function setGood($good) 
    {
        $this->good = $good;
    }           
    
    /**
     * Возвращает связанный supplier.
     * @return Supplier
     */    
    public function getSupplier() 
    {
        return $this->supplier;
    }

    /**
     * Задает связанный supplier.
     * @param Supplier $supplier
     */    
    public function setSupplier($supplier) 
    {
        $this->supplier = $supplier;
    }           
    
}
