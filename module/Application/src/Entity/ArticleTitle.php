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
 * @ORM\Entity(repositoryClass="\Application\Repository\TokenRepository")
 * @ORM\Table(name="article_title")
 * @author Daddy
 */
class ArticleTitle {
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="title")  
     */
    protected $title;

    /**
     * @ORM\Column(name="title_md5")  
     */
    protected $titleMd5;
    
    /**
     * @ORM\Column(name="token_group_title")  
     */
    protected $tokenGroupTitle;

    /**
     * @ORM\Column(name="token_group_title_md5")  
     */
    protected $tokenGroupTitleMd5;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Article", inversedBy="articleTitles") 
     * @ORM\JoinColumn(name="article_id", referencedColumnName="id")
     */
    protected $article;        
    
    /**
    * @ORM\OneToMany(targetEntity="Application\Entity\ArticleToken", mappedBy="articleTitle")
    * @ORM\JoinColumn(name="id", referencedColumnName="title_id")
   */
    private $articleTokens;        

    /**
    * @ORM\OneToMany(targetEntity="Application\Entity\ArticleBigram", mappedBy="articleTitle")
    * @ORM\JoinColumn(name="id", referencedColumnName="title_id")
   */
    private $articleBigrams;        

    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\FpTree", inversedBy="articleTitles") 
     * @ORM\JoinColumn(name="fp_tree_id", referencedColumnName="id")
     */
    protected $fpTree;   
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\TokenGroup", inversedBy="articleTitles") 
     * @ORM\JoinColumn(name="token_group_id", referencedColumnName="id")
     */
    protected $tokenGroup;   

    public function getId() 
    {
        return $this->id;
    }

    public function setId($id) 
    {
        $this->id = $id;
    }     

    public function setTitle($title)
    {
        $this->title = mb_strtoupper(trim($title), 'UTF-8');
        $this->titleMd5 = md5($this->title);
    }
    
    public function getTitle()
    {
        return $this->title;
    }

    public function getTitleMd5()
    {
        return $this->titleMd5;
    }
    
    public function setTokenGroupTitle($tokenGroupTitle)
    {
        $this->tokenGroupTitle = mb_strtoupper(trim($tokenGroupTitle), 'UTF-8');
        $this->tokenGroupTitleMd5 = md5($this->tokenGroupTitle);
    }
    
    public function getTokenGroupTitle()
    {
        return $this->tokenGroupTitle;
    }

    public function getTokenGroupTitleMd5()
    {
        return $this->tokenGroupTitleMd5;
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
    
    /*
     * Возвращает связанный articleTokens.
     * @return array
     */    
    public function getArticleTokens() 
    {
        return $this->articleTokens;
    }
    
    /*
     * Возвращает связанный articleBigrams.
     * @return array
     */    
    public function getArticleBigrams() 
    {
        return $this->articleBigrams;
    }
    
    /**
     * Возвращает связанный fpTree.
     * @return \Application\Entity\FpTree
     */    
    public function getFpTree() 
    {
        return $this->fpTree;
    }

    /**
     * Задает связанный fpTree.
     * @param \Application\Entity\FpTree $fpTree
     */    
    public function setFpTree($fpTree) 
    {
        $this->fpTree = $fpTree;
    }           
    
    /**
     * Возвращает связанный tokenGroup.
     * @return \Application\Entity\tokenGroup
     */    
    public function getTokenGroup() 
    {
        return $this->tokenGroup;
    }

    /**
     * Задает связанный tokenGroup.
     * @param \Application\Entity\tokenGroup $tokenGroup
     */    
    public function setTokenGroup($tokenGroup) 
    {
        $this->tokenGroup = $tokenGroup;
    }           
    
}
