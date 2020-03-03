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
 * @ORM\Table(name="token_group_bigram")
 * @author Daddy
 */
class TokenGroupBigram {
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="frequency")  
     */
    protected $frequency = 0;        

    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\TokenGroup", inversedBy="tokenGroupTokens") 
     * @ORM\JoinColumn(name="token_group_id", referencedColumnName="id")
     */
    protected $tokenGroup;    
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Bigram", inversedBy="tokenGroupBigrams") 
     * @ORM\JoinColumn(name="bigram_id", referencedColumnName="id")
     */
    protected $bigram;    
    
    
    public function getId() 
    {
        return $this->id;
    }

    public function setId($id) 
    {
        $this->id = $id;
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
     * Возвращает связанный bigram.
     * @return \Application\Entity\Bigram
     */    
    public function getBigram() 
    {
        return $this->bigram;
    }

    /**
     * Задает связанный bigram.
     * @param \Application\Entity\Bigram $bigram
     */    
    public function setBigram($bigram) 
    {
        $this->bigram = $bigram;
    }               
}
