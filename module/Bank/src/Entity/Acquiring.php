<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Bank\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

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
     * @ORM\ManyToMany(targetEntity="Application\Entity\AplPayment", inversedBy="acquirings")
     * @ORM\JoinTable(name="acquiring_good_car",
     *      joinColumns={@ORM\JoinColumn(name="acquiring_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="apl_payment_id", referencedColumnName="id")}
     *      )
     */
    protected $aplPayments;
    
    public function __construct() 
    {
      $this->aplPayments = new ArrayCollection();
    }
    
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
     * ИНН
     * @return string
     */
    public function getInn() 
    {
        return $this->inn;
    }
    
    /**
     * 
     * @param string $inn
     */
    public function setInn($inn) 
    {
        $this->inn = $inn;
    }     

    /**
     * НОМЕР_ТОЧКИ
     * @return string
     */
    public function getPoint() 
    {
        return $this->point;
    }

    /**
     * НОМЕР_ТОЧКИ
     * @param string $point
     */
    public function setPoint($point) 
    {
        $this->point = $point;
    }     

    /**
     * НОМЕР_КАРТЫ
     * @return string
     */
    public function getCart() 
    {
        return $this->cart;
    }

    /**
     * НОМЕР_КАРТЫ
     * @param string $cart
     */
    public function setCart($cart) 
    {
        $this->cart = $cart;
    }     

    /**
     * КОД_АВТОРИЗАЦИИ
     * @return string
     */
    public function getAcode() 
    {
        return $this->acode;
    }

    /**
     * КОД_АВТОРИЗАЦИИ
     * @param string $acode
     */
    public function setAcode($acode) 
    {
        $this->acode = $acode;
    }     


    /**
     * ТИП_КАРТЫ
     * @return string
     */
    public function getСartType() 
    {
        return $this->cartType;
    }

    /**
     * ТИП_КАРТЫ
     * @param string $cartType
     */             
    public function setCartType($cartType) 
    {
        $this->cartType = $cartType;
    }     

    /**
     * СУММА_ТРАНЗАКЦИИ
     * @return string
     */
    public function geAmount() 
    {
        return $this->amount;
    }

    /**
     * СУММА_ТРАНЗАКЦИИ
     * @param string $amount
     */
    public function setAmount($amount) 
    {
        $this->amount = $amount;
    }     

    /**
     * КОМИССИЯ
     * @return string
     */
    public function getСomiss() 
    {
        return $this->comiss;
    }

    /**
     * КОМИССИЯ
     * @param string $comiss
     */
    public function setComiss($comiss) 
    {
        $this->comiss = $comiss;
    }     

    /**
     * СУММА_ВОЗМЕЩЕНИЯ
     * @return string
     */
    public function getOutput() 
    {
        return $this->output;
    }

    /**
     * СУММА_ВОЗМЕЩЕНИЯ
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
     * ДАТА_ОБРАБОТКИ
     * @return date
     */
    public function getOperDate() 
    {
        return $this->operDate;
    }

    /**
     * ДАТА_ОБРАБОТКИ
     * @param date $operDate
     */
    public function setOperDate($operDate) 
    {
        $this->operDate = date('Y-m-d', strtotime($operDate));
    }     
    
    /**
     * ДАТА_ТРАНЗАКЦИИ
     * @return date
     */
    public function getTransDate() 
    {
        return $this->transDate;
    }

    /**
     * ДАТА_ТРАНЗАКЦИИ
     * @param date $transDate
     */
    public function setTransDate($transDate) 
    {
        $this->transDate = date('Y-m-d H:i:s', strtotime($transDate));
    }     
    
    /**
     * RRN
     * @return string
     */
    public function getRrn() 
    {
        return $this->rrn;
    }

    /**
     * RRN
     * @param string $rrn
     */
    public function setRrn($rrn) 
    {
        $this->rrn = $rrn;
    }     
    
    /**
     * ИДЕНТИФИКАТОР_ОПЕРАЦИИ
     * @return string
     */
    public function getIdent() 
    {
        return $this->ident;
    }

    /**
     * ИДЕНТИФИКАТОР_ОПЕРАЦИИ
     * @param string $ident
     */
    public function setIdent($ident) 
    {
        $this->ident = $ident;
    }     

    public function getAplPayments() 
    {
        return $this->aplPayments;
    }      
    
    public function addAplPayment($aplPayment) 
    {
        $this->aplPayments[] = $aplPayment;        
    }
    
    public function removeAplPaymentAssociation($aplPayment) 
    {
        $this->aplPayments->removeElement($aplPayment);
    }    
        
}
