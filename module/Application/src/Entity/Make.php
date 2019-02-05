<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Producer
 * @ORM\Entity(repositoryClass="\Application\Repository\MakeRepository")
 * @ORM\Table(name="make")
 * @author Daddy
 */
class Make {
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="td_id")   
     */
    protected $tdId;    
    
    /**
     * @ORM\Column(name="apl_id")   
     */
    protected $aplId;    
    
    /**
     * @ORM\Column(name="name")   
     */
    protected $name;    
    
    /**
     * @ORM\Column(name="fullname")   
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

    public function getTdId() 
    {
        return $this->tdId;
    }

    public function setTdId($tdId) 
    {
        $this->tdId = $tdId;
    }     

    public function getAplId() 
    {
        return $this->aplId;
    }

    public function setAplId($aplId) 
    {
        $this->aplId = $aplId;
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

}
