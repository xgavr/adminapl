<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Company\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Company\Entity\Currencyrate;

/**
 * Description of Currency
 * @ORM\Entity(repositoryClass="\Company\Repository\CurrencyRepository")
 * @ORM\Table(name="currency")
 * @author Daddy
 */
class Currency {
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
     * @ORM\Column(name="description")   
     */
    protected $description;

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

    public function getDescription() 
    {
        return $this->description;
    }

    public function setDescription($description) 
    {
        $this->description = $description;
    }     
    
   /**
    * @ORM\OneToMany(targetEntity="\Application\Entity\Currencyrate", mappedBy="currency")
    * @ORM\JoinColumn(name="id", referencedColumnName="currency_id")
   */
   private $rate;

   public function __construct() {
      $this->rate = new ArrayCollection();
   }

    /**
     * Возвращает rate для этого currency.
     * @return array
     */   
   public function getRate() {
      return $this->rate;
   }    
   
    /**
     * Добавляет новый rate к этому currency.
     * @param $rate
     */   
    public function addRate($rate) 
    {
        $this->rate[] = $rate;
    }   

}
