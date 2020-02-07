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
     * @ORM\Column(name="root_tree_id")   
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
     * @ORM\Column(name="parent_tree_id")  
     */
    protected $parentTree;        

    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Token", inversedBy="fpTree") 
     * @ORM\JoinColumn(name="token_id", referencedColumnName="id")
     */
    protected $token;        
    
    /**
    * @ORM\OneToMany(targetEntity="\Application\Entity\ArticleTitle", mappedBy="fpTree")
    * @ORM\JoinColumn(name="id", referencedColumnName="fp_tree_id")
     */
    private $articleTitles;
    
    /**
    * @ORM\OneToMany(targetEntity="\Application\Entity\ArticleToken", mappedBy="fpTree")
    * @ORM\JoinColumn(name="id", referencedColumnName="fp_tree_id")
     */
    private $articleTokens;

    /**
    * @ORM\OneToMany(targetEntity="\Application\Entity\FpGroup", mappedBy="fpTree")
    * @ORM\JoinColumn(name="id", referencedColumnName="fp_tree_id")
     */
    private $fpGroups;

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
    
    public function setParentTree($parentTreeId)
    {
        $this->parentTree = $parentTreeId;
    }
    
    public function getParentTree()
    {
        return $this->parentTree;
    }
    
    public function getToken() 
    {
        return $this->token;
    }    
    
    /**
     * Returns the array of article titles assigned to this fpTree.
     * @return array
     */
    public function getArticleTitles()
    {
        return $this->articleTitles;
    }            

    /**
     * Returns the array of article tokens assigned to this fpTree.
     * @return array
     */
    public function getArticleTokens()
    {
        return $this->articleTokens;
    }            
    
    /**
     * Returns the array of fpGroups assigned to this fpTree.
     * @return array
     */
    public function getFpGroups()
    {
        return $this->fpGroups;
    }            
}
