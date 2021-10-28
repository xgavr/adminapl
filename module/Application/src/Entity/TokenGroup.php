<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Application\Filter\IdsFormat;
use Application\Entity\Rate;

/**
 * Description of NameGroup
 * @ORM\Entity(repositoryClass="\Application\Repository\TokenRepository")
 * @ORM\Table(name="token_group")
 * @author Daddy
 */
class TokenGroup {
    
    const FREQUENCY_MIN   = 5000; // минимальная чатота токена
    const MIN_GOODCOUNT = 10; // минимальное количество товаров в группе

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="name")   
     */
    protected $name;

    /**
     * @ORM\Column(name="lemms")   
     */
    protected $lemms;

    /**
     * @ORM\Column(name="ids")   
     */
    protected $ids;

    /**
     * @ORM\Column(name="good_count")   
     */
    protected $goodCount = 0;

    /**
     * @ORM\Column(name="movement")   
     */
    protected $movement = 0;
    
   /**
    * @ORM\OneToMany(targetEntity="\Application\Entity\Goods", mappedBy="tokenGroup")
    * @ORM\JoinColumn(name="id", referencedColumnName="token_group_id")
   */
   private $goods;

     /**
     * @ORM\ManyToMany(targetEntity="\Application\Entity\Token")
     * @ORM\JoinTable(name="token_group_token",
     *      joinColumns={@ORM\JoinColumn(name="token_group_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="token_id", referencedColumnName="id")}
     *      )
     */
    private $tokens;
    
     /**
     * @ORM\ManyToMany(targetEntity="\Application\Entity\Bigram")
     * @ORM\JoinTable(name="token_group_bigram",
     *      joinColumns={@ORM\JoinColumn(name="token_group_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="bigram_id", referencedColumnName="id")}
     *      )
     */
    private $bigrams;

    /**
    * @ORM\OneToMany(targetEntity="Rate", mappedBy="tokenGroup")
    * @ORM\JoinColumn(name="id", referencedColumnName="token_group_id")
   */
   private $rates;        

   /**
    * @ORM\OneToMany(targetEntity="Application\Entity\ArticleTitle", mappedBy="tokenGroup")
    * @ORM\JoinColumn(name="id", referencedColumnName="token_group_id")
   */
   private $articleTitles;        

   /**
    * @ORM\OneToMany(targetEntity="Application\Entity\ArticleToken", mappedBy="tokenGroup")
    * @ORM\JoinColumn(name="id", referencedColumnName="token_group_id")
   */
   private $articleTokens;        

   /**
    * @ORM\OneToMany(targetEntity="Application\Entity\ArticleBigram", mappedBy="tokenGroup")
    * @ORM\JoinColumn(name="id", referencedColumnName="token_group_id")
   */
   private $articleBigrams;        

   /**
    * @ORM\OneToMany(targetEntity="Application\Entity\TitleToken", mappedBy="tokenGroup")
    * @ORM\JoinColumn(name="id", referencedColumnName="group_id")
   */
   private $titleTokens;        

   /**
    * @ORM\OneToMany(targetEntity="Application\Entity\TitleBigram", mappedBy="tokenGroup")
    * @ORM\JoinColumn(name="id", referencedColumnName="group_id")
   */
   private $titleBigrams;        


    public function __construct() {
        $this->goods = new ArrayCollection();
        $this->tokens = new ArrayCollection();
        $this->bigrams = new ArrayCollection();
        $this->rates = new ArrayCollection();
        $this->titleTokens = new ArrayCollection();
        $this->titleBigrams = new ArrayCollection();
    }

    public function getId() 
    {
        return $this->id;
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
        $fGn = mb_strtoupper(mb_substr(trim($name), 0, 1));
        $Gn = $fGn.mb_substr(trim($name), 1);
        $this->name = $Gn;
    }  
    
    public function getLemms() 
    {
        return $this->lemms;
    }

    public function setLemms($lemms) 
    {
        $filter = new IdsFormat(['separator' => ' ']);
        $this->lemms = $filter->filter($lemms);
    }  
    
    public function getGoodCount() 
    {
        return $this->goodCount;
    }

    public function setGoodCount($goodCount) 
    {
        $this->goodCount = $goodCount;
    }  
    
    public function getMovement() 
    {
        return $this->movement;
    }

    public function setMovement($movement) 
    {
        $this->movement = $movement;
    }      
    
    public function getIds() 
    {
        return $this->ids;
    }

    /**
     * 
     * @param array $ids
     */
    public function setIds($ids) 
    {        
        $filter = new IdsFormat();
        $this->ids = md5($filter->filter($ids));
    }  
    
    /**
     * Возвращает goods для этого tokenGroup.
     * @return array
     */   
   public function getGoods() {
      return $this->goods;
   }    
   
    /**
     * Добавляет новый goods к этому tokenGroup.
     * @param Application\Entity\Goods $good
     */   
    public function addGood($good) 
    {
        $this->goods[] = $good;
    }   

    public function getTokens() {
       return $this->tokens;
    }    
   
    /**
     * Содержет ли строка токен?
     * 
     * @param Application\Entity\Token $token
     * @return bool
     */
    public function hasToken($token)
    {
        return $this->tokens->contains($token);
    }

    /**
     * 
     * @param Application\Entity\Token $token
     */
    public function addToken($token)
    {
        //if (!$this->hasToken($token)){
            $this->tokens->add($token);
        //}    
    }

    
    public function getTokenView()
    {
        $result = [];
        
        foreach ($this->tokens as $token){
            $result[] = $token->getLemma();
        }
        
        if (count($result)){
            return implode(' ', $result);
        }
        
        return 'NaN';
    }
    
    public function getBigrams() {
       return $this->bigrams;
    }    
   
    /**
     * Содержет ли строка bigram?
     * 
     * @param \Application\Entity\Bigram $bigram
     * @return bool
     */
    public function hasBigram($bigram)
    {
        return $this->bigrams->contains($bigram);
    }

    /**
     * 
     * @param \Application\Entity\Bigram $bigram
     */
    public function addBigram($bigram)
    {
        $this->bigrams->add($bigram);
    }

    
    public function getBigramView()
    {
        $result = [];
        
        foreach ($this->bigrams as $bigram){
            $result[] = $bigram->getBilemma();
        }
        
        if (count($result)){
            return implode(' ', $result);
        }
        
        return 'NaN';
    }
    /*
     * Возвращает связанный rates.
     * @return Rate
     */    
    public function getRates() 
    {
        return $this->rates;
    }

    public function addRate($rate) 
    {
        $this->rates[] = $rate;
    }             

    /*
     * Возвращает связанный articleTitles.
     * @return array
     */    
    public function getArticleTitles() 
    {
        return $this->articleTitles;
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

    /*
     * Возвращает связанный titleTokens.
     * @return array
     */    
    public function getTitleTokens() 
    {
        return $this->titleTokens;
    }

    public function addTitleToken($titleToken) 
    {
        $this->titleTokens[] = $titleToken;
    }             

}
