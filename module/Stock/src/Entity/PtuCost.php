<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Stock\Entity;


use Doctrine\ORM\Mapping as ORM;
use Laminas\Json\Decoder;
use Laminas\Json\Encoder;
use Company\Entity\Cost;

/**
 * Description of PtuCost
 * @ORM\Entity(repositoryClass="\Stock\Repository\PtuRepository")
 * @ORM\Table(name="ptu_cost")
 * @author Daddy
 */
class PtuCost {
        
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="row_no")   
     */
    protected $rowNo;

    /**
     * @ORM\Column(name="comment")   
     */
    protected $comment;

    /**
     * @ORM\Column(name="info")   
     */
    protected $info;

    /** 
     * Количество
     * @ORM\Column(name="quantity")  
     */
    protected $quantity;

    /** 
     * Сумма итого по стоке
     * @ORM\Column(name="amount")  
     */
    protected $amount;

    /**
     * @ORM\ManyToOne(targetEntity="Stock\Entity\Ptu", inversedBy="ptuCosts") 
     * @ORM\JoinColumn(name="ptu_id", referencedColumnName="id")
     */
    private $ptu;
        
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Cost", inversedBy="ptuCosts") 
     * @ORM\JoinColumn(name="cost_id", referencedColumnName="id")
     */
    private $cost;
        
    /**
     * Constructor.
     */
    public function __construct() 
    {
    }
    
    
    public function getId() 
    {
        return $this->id;
    }
    
    public function getDocRowKey() 
    {
        return 'ptu_cost:'.$this->id;
    }    

    public function setId($id) 
    {
        $this->id = $id;
    }     

    public function getRowNo() 
    {
        return $this->rowNo;
    }

    public function setRowNo($rowNo) 
    {
        $this->rowNo = $rowNo;
    }     

    public function getComment() 
    {
        return $this->comment;
    }

    public function setComment($comment) 
    {
        $this->comment = $comment;
    }     

    public function getInfo() 
    {
        return $this->info;
    }

    public static function getInfoAsArray()
    {
        try{
            return Decoder::decode($this->info, \Laminas\Json\Json::TYPE_ARRAY);            
        } catch (Exception $ex) {
            return [];
        }
    }

    public static function setJsonInfo($info)
    {
        return Encoder::encode($info);
    }
    
    public function setInfo($info) 
    {
        $this->info = $this->setJsonInfo($info);
    }     
    
    /**
     * Sets  quantity.
     * @param float $quantity     
     */
    public function setQuantity($quantity) 
    {
        $this->quantity = $quantity;
    }    
    
    /**
     * Returns the quantity of doc.
     * @return float     
     */
    public function getQuantity() 
    {
        return $this->quantity;
    }
    
    /**
     * Sets  amount.
     * @param float $amount     
     */
    public function setAmount($amount) 
    {
        $this->amount = $amount;
    }    
    
    /**
     * Returns the amount of doc.
     * @return float     
     */
    public function getAmount() 
    {
        return $this->amount;
    }
    
    /**
     * Returns the amount of doc.
     * @return float     
     */
    public function getPrice() 
    {
        if ($this->quantity){
            return $this->amount/$this->quantity;
        }    
        
        return 0;
    }
    
    /**
     * Returns the country.
     * @return Country     
     */
    public function getCountry() 
    {
        return $this->country;
    }
    
    /**
     * Returns the cost.
     * @return Cost     
     */
    public function getCost() 
    {
        return $this->cost;
    }
    
    /**
     * Установить услугу
     * @param Cost $cost
     */
    public function setGood($cost)
    {
        $this->cost = $cost;
    }

    /**
     * Returns the ptu.
     * @return Ptu     
     */
    public function getPtu() 
    {
        return $this->ptu;
    }
    
    /**
     * Лог
     * @return array
     */
    public function toLog()
    {
        return [
            'amount' => $this->getAmount(),
            'comment' => $this->getComment(),
            'cost' => $this->getCost()->getId(),
            'info' => $this->getInfo(),
            'quantity' => $this->getQuantity(),
            'rowNo' => $this->getRowNo(),
        ];
    }    
}
