<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Application\Entity\Scale;

/**
 * Description of Phone
 * @ORM\Entity(repositoryClass="\Application\Repository\RateRepository")
 * @ORM\Table(name="scale_treshold")
 * @author Daddy
 */
class ScaleTreshold 
{
    const DEFAULT_ROUNDING = -1; //окруление по умолчанию
    
    const MIN_RATE = 7;        //минимальная наценка
    const PRICE_COL_COUNT = 5; //количество колонок цен
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="treshold")   
     */
    protected $treshold;

    /**
     * @ORM\Column(name="rate")   
     */
    protected $rate;
    
    /** 
     * @ORM\Column(name="rounding")  
     */
    protected $rounding;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Scale", inversedBy="tresholds") 
     * @ORM\JoinColumn(name="scale_id", referencedColumnName="id")
     */
    protected $scale;

    
    public function getId() 
    {
        return $this->id;
    }

    public function setId($id) 
    {
        $this->id = $id;
    }     

    public function getTreshold() 
    {
        return $this->treshold;
    }

    public function setTreshold($treshold) 
    {
        $this->treshold = $treshold;
    }     

    public function getRate() 
    {   
        if ($this->rate > self::MIN_RATE){
            return $this->rate;
        } else {
            return self::MIN_RATE;
        }    
    }

    public function getFormatRate() 
    {
        return number_format($this->rate, 4, '.', '');
    }

    public function setRate($rate) 
    {
        $this->rate = $rate;
    }     

    public function getRounding() 
    {
        return $this->rounding;
    }
    
    public function setRounding($rounding) 
    {
        $this->rounding = $rounding;
    } 
    
    
    /**
     * Розничная цена
     * 
     * @param float $price Закупка
     * @return float
     */
    public function retail($price)
    {
        var_dump(round($price + $price*$this->getRate()/100, $this->rounding));
        return round($price + $price*$this->getRate()/100, $this->rounding);
    }
    
    
    /**
     * Розничная цена
     * 
     * @return float
     */
    public function getRetail()
    {
        return $this->retail($this->treshold);
    }
    
    /**
     * Получить колонки цен
     * 
     * @param float $price Закупка
     * @return array
     */
    public function priceCols($price)            
    {
        $retailPrice = $this->retail($price);
        
        $result = [];
        $col = 0;
        $priceTreshold = ($retailPrice-$price)/self::PRICE_COL_COUNT;
        $minRetailPrice = ceil($price + $price*self::MIN_RATE/100);
        while ($col <= self::PRICE_COL_COUNT){
            $result[$col]['price'] = round($retailPrice - $col*$priceTreshold, self::DEFAULT_ROUNDING);
            if ($result[$col]['price'] < $minRetailPrice){
                $result[$col]['price'] = $minRetailPrice;
            }
            $result[$col]['percent'] = ($result[$col]['price'] - $price)*100/$price;
            $col++;
        }
        
        return $result;
    }
    
    public function getPriceCols()
    {
        return $this->priceCols($this->treshold);
    }

    /*
     * Возвращает связанный scale.
     * @return Scale
     */    
    public function getScale() 
    {
        return $this->scale;
    }

    /**
     * Задает связанный scale.
     * @param Scale $scale
     */    
    public function setScale($scale) 
    {
        $this->scale = $scale;
        $scale->addTreshold($this);
    }     
        
}
