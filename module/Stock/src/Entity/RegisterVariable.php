<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Stock\Entity;

use Doctrine\ORM\Mapping as ORM;
use Stock\Entity\Movement;
use Stock\Entity\Register;


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
        
    /**
     * @ORM\Column(name="var_stamp")   
     */
    protected $varStamp;
        
    /** 
     * @ORM\Column(name="allow_date")  
     */
    protected $allowDate;    

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

    public function getVarStamp() 
    {
        return $this->varStamp;
    }

    public function setVarStamp($varStamp) 
    {
        $this->varStamp = $varStamp;
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
    
    /**
     * Returns the date of allow.
     * @return date     
     */
    public function getAllowDate() 
    {
        return $this->allowDate;
    }
    
    /**
     * Sets the date when aloow.
     * @param date $allowDate    
     */
    public function setAllowDate($allowDate) 
    {
        $this->allowDate = $allowDate;
    }  

    /**
     * Представление документа
     * @return string
     */
    public function getDoc()
    {
        return Register::getDocLink($this->varType, $this->varId);
    }
}
