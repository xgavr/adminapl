<?php

namespace Company\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Country
 * @ORM\Entity(repositoryClass="\Company\Repository\CountryRepository")
 * @ORM\Table(name="country")
 *
 * @author Daddy
 */
class Country {

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
     * @ORM\Column(name="fullname")   
     */
    protected $fullname;

    /**
     * @ORM\Column(name="code")   
     */
    protected $code;
    
    /**
     * @ORM\Column(name="alpha2")   
     */
    protected $alpha2;
    
    /**
     * @ORM\Column(name="alpha3")   
     */
    protected $alpha3;
    
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
        $this->name = trim($name);
    }     

    public function getFullName() 
    {
        return $this->fullname;
    }

    public function setFullName($fullname) 
    {
        $this->fullname = trim($fullname);
    }     

    public function getCode() 
    {
        return $this->code;
    }

    public function setCode($code) 
    {
        $this->code = $code;
    }     

    public function getAlpha2() 
    {
        return $this->alpha2;
    }

    public function setAlpha2($alpha2) 
    {
        $this->alpha2 = $alpha2;
    }     

    public function getAlpha3() 
    {
        return $this->alpha3;
    }

    public function setAlpha3($alpha3) 
    {
        $this->alpha3 = $alpha3;
    }     
    
   /**
    * @ORM\OneToMany(targetEntity="\Application\Entity\Producer", mappedBy="country")
    * @ORM\JoinColumn(name="id", referencedColumnName="country_id")
   */
   private $producer;

   public function __construct() {
      $this->producer = new ArrayCollection();
   }

    /**
     * Возвращает producer для этого country.
     * @return array
     */   
   public function getProducer() {
      return $this->producer;
   }    
   
    /**
     * Добавляет новый producer к этому country.
     * @param $producer
     */   
    public function addProducer($producer) 
    {
        $this->producer[] = $producer;
    }   
}
