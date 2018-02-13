<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Company\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Description of Tax
 * @ORM\Entity
 * @ORM\Table(name="region")
 * @author Daddy
 */
class Region {
    
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
     * @ORM\Column(name="full_name")   
     */
    protected $fullName;
    
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

    public function getFullName() 
    {
        return $this->fullName;
    }

    public function setFullName($fullName) 
    {
        $this->fullName = $fullName;
    }     

    /**
    * @ORM\OneToMany(targetEntity="Company\Entity\BankAccount", mappedBy="region")
    * @ORM\JoinColumn(name="id", referencedColumnName="region_id")
     */
    private $offices;
    
    /**
     * Constructor.
     */
    public function __construct() 
    {
        $this->offices = new ArrayCollection();
    }
    
    /**
     * @return array
     */
    public function getOffices()
    {
        return $this->offices;
    }
        
    /**
     * Assigns.
     */
    public function addOffice($office)
    {
        $this->offices[] = $office;
    }        
}
