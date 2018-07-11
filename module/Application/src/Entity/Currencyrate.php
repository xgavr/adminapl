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
 * Description of Currency
 * @ORM\Entity(repositoryClass="\Application\Repository\CurrencyRepository")
 * @ORM\Table(name="currency_rate")
 * @author Daddy
 */
class Currencyrate {
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="date_rate")   
     */
    protected $dateRate;

    /**
     * @ORM\Column(name="rate")   
     */
    protected $rate;

    public function getId() 
    {
        return $this->id;
    }

    public function setId($id) 
    {
        $this->id = $id;
    }     

    public function getDateRate() 
    {
        return $this->dateRate;
    }

    public function setDateRate($dateRate) 
    {
        $this->dateRate = $dateRate;
    }     

    public function getRate() 
    {
        return $this->rate;
    }

    public function setRate($rate) 
    {
        $this->rate = $rate;
    }     
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Currency", inversedBy="currency_rate") 
     * @ORM\JoinColumn(name="currency_id", referencedColumnName="id")
     */
    protected $currency;    
    
    /*
     * Возвращает связанный currency.
     * @return \Application\Entity\Currency
     */    
    public function getCurrency() 
    {
        return $this->currency;
    }

    /**
     * Задает связанный currency.
     * @param \Application\Entity\Currency $currncy
     */    
    public function setCurrency($currency) 
    {
        $this->currency = $currency;
        $currency->addRate($this);
    }     

}
