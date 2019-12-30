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
        return $this->rate;
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
