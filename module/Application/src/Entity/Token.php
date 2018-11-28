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
 * @ORM\Table(name="token")
 * @author Daddy
 */
class Token {
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
     * @ORM\Column(name="status")  
     */
    protected $status;        

    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Article", inversedBy="tokens") 
     * @ORM\JoinColumn(name="article_id", referencedColumnName="id")
     */
    protected $article;    
    
     /**
     * @ORM\ManyToMany(targetEntity="Application\Entity\Rawprice")
     * @ORM\JoinTable(name="rawprice_token",
     *      joinColumns={@ORM\JoinColumn(name="token_id", referencedColumnName="id")},
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

    public function getStatus() 
    {
        return $this->status;
    }

    public function setStatus($status) 
    {
        $this->status = $status;
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
        $article->addToken($this);
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
