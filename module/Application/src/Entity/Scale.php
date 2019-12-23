<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Application\Entity\ScaleTreshold;

/**
 * Description of Phone
 * @ORM\Entity(repositoryClass="\Application\Repository\RateRepository")
 * @ORM\Table(name="scale")
 * @author Daddy
 */
class Scale {
    
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
    * @ORM\OneToMany(targetEntity="ScaleTreshold", mappedBy="scale")
    * @ORM\JoinColumn(name="id", referencedColumnName="scale_id")
   */
   private $tresholds;

   public function __construct() {
      $this->tresholds = new ArrayCollection();      
   }
    
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

    /*
     * Возвращает связанный tresholds.
     * @return ScaleTreshold
     */    
    public function getTresholds() 
    {
        return $this->tresholds;
    }

    public function addTreshold($treshold) 
    {
        $this->tresholds[] = $treshold;
    }     
        
}
