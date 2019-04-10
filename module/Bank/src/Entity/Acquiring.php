<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Bank\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Bank
 * @ORM\Entity(repositoryClass="\Bank\Repository\BankRepository")
 * @ORM\Table(name="acquiring")
 * @author Daddy
 */
class Acquiring {
    

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="inn")   
     */
    protected $inn;
   
    /**
     * @ORM\Column(name="point")   
     */
    protected $point;
   
    /**
     * @ORM\Column(name="cart")   
     */
    protected $cart;
   
    /**
     * @ORM\Column(name="acode")   
     */
    protected $acode;
   
    /**
     * @ORM\Column(name="cart_type")   
     */
    protected $cartType;
   
    /**
     * @ORM\Column(name="amount")   
     */
    protected $amount;
   
    /**
     * @ORM\Column(name="comiss")   
     */
    protected $comiss;
   
    /**
     * @ORM\Column(name="output")   
     */
    protected $output;
   
    /**
     * @ORM\Column(name="oper_type")   
     */
    protected $operType;
   
    /**
     * @ORM\Column(name="oper_date")   
     */
    protected $operDate;
   
    /** 
     * @ORM\Column(name="trans_date")  
     */
    protected $transDate;
    
    /** 
     * @ORM\Column(name="rrn")  
     */
    protected $rrn;
    
    /** 
     * @ORM\Column(name="ident")  
     */
    protected $ident;
    
    /**
     * Возвращает Id
     * @return int
     */    
    public function getId() 
    {
        return $this->id;
    }

    /**
     * Устанавливает Id
     * @param int $id
     */
    public function setId($id) 
    {
        $this->id = $id;
    }     

    /**
     * @return string
     */
    public function getInn() 
    {
        return $this->inn;
    }
    
    /**
     * @param string $inn
     */
    public function setInn($inn) 
    {
        $this->inn = $inn;
    }     

    /**
     * @return string
     */
    public function getPoint() 
    {
        return $this->point;
    }

    /**
     * @param string $point
     */
    public function setPoint($point) 
    {
        $this->point = $point;
    }     

    /**
     * @return string
     */
    public function getCart() 
    {
        return $this->cart;
    }

    /**
     * @param string $cart
     */
    public function setCart($cart) 
    {
        $this->cart = $cart;
    }     

    /**
     * @return string
     */
    public function getAcode() 
    {
        return $this->acode;
    }

    /**
     * @param string $acode
     */
    public function setAcode($acode) 
    {
        $this->acode = $acode;
    }     


    /**
     * @return string
     */
    public function getСartType() 
    {
        return $this->cartType;
    }

    /**
     * @param string $cartType
     */             
    public function setCartType($cartType) 
    {
        $this->cartType = $cartType;
    }     

    /**
     * @return string
     */
    public function geAmount() 
    {
        return $this->amount;
    }

    /**
     * @param string $amount
     */
    public function setAmount($amount) 
    {
        $this->amount = $amount;
    }     

    /**
     * @return string
     */
    public function getСomiss() 
    {
        return $this->comiss;
    }

    /**
     * @param string $comiss
     */
    public function setComiss($comiss) 
    {
        $this->comiss = $comiss;
    }     

    /**
     * @return string
     */
    public function getOutput() 
    {
        return $this->output;
    }

    /**
     * @param string $output
     */
    public function setOutput($output) 
    {
        $this->output = $output;
    }     

    /**
     * Возвращает тип операции.
     * @return string
     */
    public function getОperType() 
    {
        return $this->operType;
    }

    /**
     * Устанавливает тип операции
     * @param string $operType
     */
    public function setOperType($operType) 
    {
        $this->operType = $operType;
    }     
    
    /**
     * @return date
     */
    public function getOperDate() 
    {
        return $this->operDate;
    }

    /**
     * @param date $operDate
     */
    public function setOperDate($operDate) 
    {
        $this->operDate = date('Y-m-d', strtotime($operDate));
    }     
    
    /**
     * @return date
     */
    public function getTransDate() 
    {
        return $this->transDate;
    }

    /**
     * @param date $transDate
     */
    public function setTransDate($transDate) 
    {
        $this->transDate = date('Y-m-d H:i:s', strtotime($transDate));
    }     
    
    /**
     * @return string
     */
    public function getRrn() 
    {
        return $this->rrn;
    }

    /**
     * @param string $rrn
     */
    public function setRrn($rrn) 
    {
        $this->rrn = $rrn;
    }     
    
    /**
     * @return string
     */
    public function getIdent() 
    {
        return $this->ident;
    }

    /**
     * @param string $ident
     */
    public function setIdent($ident) 
    {
        $this->ident = $ident;
    }     
        
}
