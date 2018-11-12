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
 * @ORM\Entity(repositoryClass="\Application\Repository\NameRepository")
 * @ORM\Table(name="name_raw")
 * @author Daddy
 */
class NameRaw {
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
     * @ORM\Column(name="pos")  
     */
    protected $position;        

    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Article", inversedBy="nameRaw") 
     * @ORM\JoinColumn(name="article_id", referencedColumnName="id")
     */
    protected $article;    
    
     /**
     * @ORM\ManyToMany(targetEntity="Application\Entity\Rawprice")
     * @ORM\JoinTable(name="rawprice_oem_raw",
     *      joinColumns={@ORM\JoinColumn(name="name_raw_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="rawprice_id", referencedColumnName="id")}
     *      )
     */
    private $rawprice;  
    

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
        $this->lemma = mb_strcut(trim($lemma), 0, 24, 'UTF-8');
    }     

    public function getPosition() 
    {
        return $this->position;
    }

    public function setPosition($position) 
    {
        $this->position = $position;
    }     

    /**
     * Возвращает связанный article.
     * @return \Application\Entity\Article
     */    
    public function getArticle() 
    {
        return $this->article;
    }

    /**
     * Задает связанный article.
     * @param \Application\Entity\Article $article
     */    
    public function setArticle($article) 
    {
        $this->article = $article;
        $article->addNameRaw($this);
    }           
    
    /**
     * Returns the array of rawprice assigned to this oemRaw.
     * @return array
     */
    public function getRawprice()
    {
        return $this->rawprice;
    }        
}
