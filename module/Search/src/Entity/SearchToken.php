<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Search\Entity;

use Doctrine\ORM\Mapping as ORM;
use Search\Entity\SearchTitle;

/**
 * Description of SearchToken
 * @ORM\Entity(repositoryClass="\Search\Repository\SearchRepository")
 * @ORM\Table(name="search_token")
 * @author Daddy
 */
class SearchToken {
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="lemma")  
     */
    protected $lemma;        
    
    /**
     * @ORM\ManyToOne(targetEntity="Search\Entity\SearchTitle", inversedBy="searchTokens") 
     * @ORM\JoinColumn(name="search_title_id", referencedColumnName="id")
     */
    protected $searchTitle;    
    
    public function getId() 
    {
        return $this->id;
    }

    public function setId($id) 
    {
        $this->id = $id;
    }     

    public function getLemma()
    {
        return $this->lemma;
    }
    
    public function setLemma($lemma) 
    {
        $this->lemma = mb_strcut(trim($lemma), 0, 64, 'UTF-8');
    }     

    public function isIntersectLemma()
    {
        if (is_numeric($this->lemma)){
            return false;
        }

        if (mb_strlen($this->lemma, 'utf-8') < 4){
            return false;
        }        
        
        return true;
    }

    /**
     * Возвращает связанный searchTitle.
     * @return SearchTitle
     */    
    public function getSearchTitle() 
    {
        return $this->searchTitle;
    }

    /**
     * Задает связанный searchTitle.
     * @param SearchTitle $searchTitle
     */    
    public function setSearchTitle($searchTitle) 
    {
        $this->searchTitle = $searchTitle;
    }           

}
