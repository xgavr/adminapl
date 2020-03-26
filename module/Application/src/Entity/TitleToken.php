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
 * @ORM\Entity(repositoryClass="\Application\Repository\TitleRepository")
 * @ORM\Table(name="title_token")
 * @author Daddy
 */
class TitleToken {
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="display_lemma")  
     */
    protected $displayLemma;        

    /**
     * @ORM\Column(name="title_md5")  
     */
    protected $titleMd5;        

    /**
     * @ORM\Column(name="frequency")  
     */
    protected $frequency;            
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\TokenGroup", inversedBy="titleTokens") 
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id")
     */
    protected $tokenGroup;    
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Token", inversedBy="titleTokens") 
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

    public function getDisplayLemma()
    {
        return $this->displayLemma;
    }
    
    public function setDisplayLemma($displayLemma) 
    {
        $this->displayLemma = $displayLemma;
    }     

    public function getTitleMd5() 
    {
        return $this->titleMd5;
    }

    public function setTitleMd5($titleMd5) 
    {
        $this->titleMd5 = $titleMd5;
    }     

    public function setFrequency($frequency)
    {
        $this->frequency = $frequency;
    }
    
    public function getFrequency()
    {
        return $this->frequency;
    }
    
    
    /**
     * Возвращает связанный tokenGroup.
     * @return \Application\Entity\TokenGroup
     */    
    public function getTokenGroup() 
    {
        return $this->tokenGroup;
    }

    /**
     * Задает связанный tokenGroup.
     * @param \Application\Entity\TokenGroup $tokenGroup
     */    
    public function setTokenGroup($tokenGroup) 
    {
        $this->tokenGroup = $tokenGroup;
    }           
    
    /**
     * Возвращает связанный token.
     * @return \Application\Entity\Token
     */    
    public function getToken() 
    {
        return $this->token;
    }

    /**
     * Задает связанный token.
     * @param \Application\Entity\Token $token
     */    
    public function setToken($token) 
    {
        $this->token = $token;
    }               
}
