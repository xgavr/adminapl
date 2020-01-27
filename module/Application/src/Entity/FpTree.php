<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use Zend\Config\Config;

/**
 * Description of Producer
 * @ORM\Entity(repositoryClass="\Application\Repository\FpTreeRepository")
 * @ORM\Table(name="fp_tree")
 * @author Daddy
 */
class FpTree {
    
    const MIN_FREQUENCY = 5; // минимальная частота

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="root_token_id")   
     */
    protected $rootTree;

    /**
     * @ORM\Column(name="root_token_id")   
     */
    protected $rootToken;
    
    /**
     * @ORM\Column(name="frequency")  
     */
    protected $frequency;        

    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Token", inversedBy="fpTree") 
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

    public function getRootTree() 
    {
        return $this->rootTree;
    }
    
    public function setRootTree($rootTree) 
    {
        $this->rootTree = $rootTree;
    }     
    
    public function getRootToken() 
    {
        return $this->rootToken;
    }
    
    public function setRootToken($rootToken) 
    {
        $this->rootToken = $rootToken;
    }     
    
    
    public function setFrequency($frequency)
    {
        $this->frequency = $frequency;
    }
    
    public function getFrequency()
    {
        return $this->frequency;
    }
    
    public function getToken() 
    {
        return $this->token;
    }    
}
