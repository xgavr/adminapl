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
 * Description of GoodSupplier
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
     * @ORM\Column(name="price")   
     */
    protected $price = 0;
    
    /**
     * Минимальное количество, кратность
     * @ORM\Column(name="lot")   
     */
    protected $lot = 1;
    
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

    public function getPrice()
    {
        return $this->price;
    }

    public function getMarkup($retailPrice)
    {
        return round(($retailPrice - $this->price)/$this->price*100, 2);
    }

    public function getMargin($retailPrice)
    {
        return round(($retailPrice - $this->price)/$retailPrice*100, 2);
    }

    public function setPrice($price)
    {
        $this->price = $price;
    }

    public function getLot()
    {
        return $this->lot;
    }

    public function setLot($lot)
    {
        $this->lot = $lot;
    }

    public function getUpdate()
    {
        return $this->update;
    }

    public function getFormatUpdate()
    {
        if (date('Y-m-d') == date('Y-m-d', strtotime($this->update))){
            return date('H:i', strtotime($this->update));
        }
        return date('H:i d-m-y', strtotime($this->update));        
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
        return $this->good;
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
