<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Stock\Entity;

use Doctrine\ORM\Mapping as ORM;
use Company\Entity\Legal;
use Company\Entity\Office;
use Application\Entity\Contact;
use User\Entity\User;
use Laminas\Json\Encoder;


/**
 * Description of Comiss
 * @ORM\Entity(repositoryClass="\Stock\Repository\RegisterRepository")
 * @ORM\Table(name="register_variable")
 * @author Daddy
 */
class RegisterVariable {
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /** 
     * @ORM\Column(name="date_var")  
     */
    protected $dateVar;    

    /**
     * @ORM\Column(name="var_type")   
     */
    protected $varType;
    
    /**
     * @ORM\Column(name="var_id")   
     */
    protected $varId;
        
    public function __construct() {
    }
   
    public function getId() 
    {
        return $this->id;
    }

    public function getVarType() 
    {
        return $this->varType;
    }

    public function setVarType($varType) 
    {
        $this->varType = $varType;
    }     

    public function getVarId() 
    {
        return $this->varId;
    }

    public function setVarId($varId) 
    {
        $this->varId = $varId;
    }     

    /**
     * Returns the date of oper.
     * @return string     
     */
    public function getDateVar() 
    {
        return $this->dateVar;
    }
    
    /**
     * Sets the date when oper.
     * @param date $dateVar    
     */
    public function setDateVar($dateVar) 
    {
        $this->dateVar = $dateVar;
    }            
}
