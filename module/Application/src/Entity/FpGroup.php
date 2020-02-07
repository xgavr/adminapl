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
 * Description of Producer
 * @ORM\Entity(repositoryClass="\Application\Repository\FpTreeRepository")
 * @ORM\Table(name="fp_group")
 * @author Daddy
 */
class FpGroup {
    
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
     * @ORM\Column(name="frequency")  
     */
    protected $frequency;        

    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Token", inversedBy="fpGroups") 
     * @ORM\JoinColumn(name="token_id", referencedColumnName="id")
     */
    protected $token;        
    

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
        
    public function setFrequency($frequency)
    {
        $this->frequency = $frequency;
    }
    
    public function getFrequency()
    {
        return $this->frequency;
    }
        
    public function getFpTree() 
    {
        return $this->fpTree;
    }        
}
