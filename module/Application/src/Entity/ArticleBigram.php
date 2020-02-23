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
 * @ORM\Entity(repositoryClass="\Application\Repository\BigramRepository")
 * @ORM\Table(name="article_bigram")
 * @author Daddy
 */
class ArticleBigram {
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="bilemma")  
     */
    protected $bilemma;        

    /**
     * @ORM\Column(name="display_bilemma")  
     */
    protected $displayBilemma;        

    /**
     * @ORM\Column(name="status")  
     */
    protected $status;        

    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Bigram", inversedBy="articleBigrams") 
     * @ORM\JoinColumn(name="bigram_id", referencedColumnName="id")
     */
    protected $bigram;    

    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Article", inversedBy="articleBigrams") 
     * @ORM\JoinColumn(name="article_id", referencedColumnName="id")
     */
    protected $article;    
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\ArticleTitle", inversedBy="articleBigrams") 
     * @ORM\JoinColumn(name="title_id", referencedColumnName="id")
     */
    protected $articleTitle;        

    public function getId() 
    {
        return $this->id;
    }

    public function setId($id) 
    {
        $this->id = $id;
    }     

    public function getBilemma()
    {
        return $this->bilemma;
    }
    
    public function setBilemma($bilemma) 
    {
        $this->bilemma = $bilemma;
    }     

    public function getDisplayBilemma()
    {
        return $this->displayBilemma;
    }
    
    public function setDisplayBilemma($displayBilemma) 
    {
        $this->displayBilemma = $displayBilemma;
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
    }           
    
    /**
     * Возвращает связанный articleTitle.
     * @return \Application\Entity\ArticleTitle
     */    
    public function getArticleTitle() 
    {
        return $this->articleTitle;
    }

    /**
     * Задает связанный articleTitle.
     * @param \Application\Entity\ArticleTitle $articleTitle
     */    
    public function setArticleTitle($articleTitle) 
    {
        $this->articleTitle = $articleTitle;
    }               
}
