<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Contact
 * @ORM\Entity(repositoryClass="\Application\Repository\ExternalRepository")
 * @ORM\Table(name="address")
 * @author Daddy
 */
class LaximoBrand {
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="laximo_id")   
     */
    protected $laximoId;
    
    /**
     * @ORM\Column(name="name")   
     */
    protected $name;
    
    /**
     * @ORM\Column(name="is_original")   
     */
    protected $isOriginal;
    

   public function __construct() {
   }
   
    public function getId() 
    {
        return $this->id;
    }
    
    public function getLaximoId() {
        return $this->laximoId;
    }

    public function setLaximoId($laximoId) {
        $this->laximoId = $laximoId;
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
    
    public function getIsOriginal() {
        return (bool) $this->isOriginal;
    }

    public function setIsOriginal($isOriginal) {
        $this->isOriginal = $isOriginal;
    }

   
    
}
