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
 * Description of Article
 * @ORM\Entity(repositoryClass="\Application\Repository\ArticleRepository")
 * @ORM\Table(name="article")
 * @author Daddy
 */
class Article {
    
    const TOKEN_UPDATE_FLAG = 5; // установить любое число (1-9), для запуска обновления токенов артикулов
    
    const LONG_CODE_NAME = 'moreThan24'; //наименование для длинных артикулов
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="code")   
     */
    protected $code;
    
    /**
     * @ORM\Column(name="fullcode")  
     */
    protected $fullcode;        

    /**
     * @ORM\Column(name="token_update_flag")  
     */
    protected $tokenUpdateFlag = self::TOKEN_UPDATE_FLAG;     
    
    /**
     * @ORM\Column(name="mean_price")  
     */
    protected $meanPrice = 0.0;        

    /**
     * @ORM\Column(name="standart_deviation")  
     */
    protected $standartDeviation = 0.0;        

    /**
     * @ORM\Column(name="total_rest")  
     */
    protected $totalRest = 0.0;        

    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Goods", inversedBy="good") 
     * @ORM\JoinColumn(name="good_id", referencedColumnName="id")
     */
    protected $good;    

    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\UnknownProducer", inversedBy="code") 
     * @ORM\JoinColumn(name="unknown_producer_id", referencedColumnName="id")
     */
    protected $unknownProducer;    
        
    /**
    * @ORM\OneToMany(targetEntity="Application\Entity\Rawprice", mappedBy="code")
    * @ORM\JoinColumn(name="id", referencedColumnName="article_id")
     */
    private $rawprice;
        
    /**
    * @ORM\OneToMany(targetEntity="Application\Entity\CrossList", mappedBy="article")
    * @ORM\JoinColumn(name="id", referencedColumnName="article_id")
     */
    private $crossList;
        
    /**
    * @ORM\OneToMany(targetEntity="Application\Entity\OemRaw", mappedBy="article")
    * @ORM\JoinColumn(name="id", referencedColumnName="article_id")
     */
    private $oemRaw;
        
     /**
    * @ORM\OneToMany(targetEntity="Application\Entity\ArticleToken", mappedBy="article")
    * @ORM\JoinColumn(name="id", referencedColumnName="article_id")
     */
    private $articleTokens;

     /**
    * @ORM\OneToMany(targetEntity="Application\Entity\ArticleBigram", mappedBy="article")
    * @ORM\JoinColumn(name="id", referencedColumnName="article_id")
     */
    private $articleBigrams;

    /**
    * @ORM\OneToMany(targetEntity="\Application\Entity\ArticleTitle", mappedBy="article")
    * @ORM\JoinColumn(name="id", referencedColumnName="article_id")
     */
    private $articleTitles;

    /**
     * Constructor.
     */
    public function __construct() 
    {
        $this->rawprice = new ArrayCollection();
        $this->crossList = new ArrayCollection();
    }
    

    public function getId() 
    {
        return $this->id;
    }

    public function setId($id) 
    {
        $this->id = $id;
    }     

    public function getCode() 
    {
        return $this->code;
    }

    public function setCode($code) 
    {
        $this->code = trim($code);
    }     

    public function getFullCode() 
    {
        return trim($this->fullcode, " '`");
    }

    public function setFullCode($fullcode) 
    {
        $this->fullcode = trim($fullcode);
    }     

    public function getTokenUpdateFlag() 
    {
        return $this->tokenUpdateFlag;
    }

    public function setTokenUpdateFlag($tokenUpdateFlag) 
    {
        $this->tokenUpdateFlag = $tokenUpdateFlag;
    }     

    public function getMeanPrice() 
    {
        return $this->meanPrice;
    }

    public function setMeanPrice($meanPrice) 
    {
        $this->meanPrice = $meanPrice;
    }     

    public function getStandartDeviation() 
    {
        return $this->standartDeviation;
    }

    public function setStandartDeviation($standartDeviation) 
    {
        $this->standartDeviation = $standartDeviation;
    }     

    public function getTotalRest() 
    {
        return $this->totalRest;
    }

    public function setTotalRest($totalRest) 
    {
        $this->totalRest = $totalRest;
    }     

    /**
     * Возвращает связанный good.
     * @return \Application\Entity\Goods
     */    
    public function getGood() 
    {
        return $this->good;
    }

    /**
     * Задает связанный good.
     * @param \Application\Entity\Goods $good
     */    
    public function setGood($good) 
    {
        $this->good = $good;
        if ($good){
            $good->addArticle($this);
        }    
    }     
    
    /**
     * Returns the array of contacts assigned to this.
     * @return array
     */
    public function getRawprice()
    {
        return $this->rawprice;
    }
        
    /**
     * Assigns.
     */
    public function addRawprice($rawprice)
    {
        $this->rawprice[] = $rawprice;
    }
      
    /**
     * Returns the array of crosslist assigned to this.
     * @return array
     */
    public function getCrossList()
    {
        return $this->crossList;
    }
        
    /**
     * Assigns.
     */
    public function addCrossList($crossList)
    {
        $this->crossList[] = $crossList;
    }
      
    /**
     * Returns the array of contacts assigned to this.
     * @return array
     */
    public function getOemRaw()
    {
        return $this->oemRaw;
    }
        
    /**
     * Assigns.
     */
    public function addOemRaw($oemRaw)
    {
        $this->oemRaw[] = $oemRaw;
    }
      
    /**
     * Возвращает связанный unknownProducer.
     * @return \Application\Entity\UnknownProducer
     */    
    public function getUnknownProducer() 
    {
        return $this->unknownProducer;
    }

    /**
     * Задает связанный unknownProducer.
     * @param \Application\Entity\UnknownProducer $unknownProducer
     */    
    public function setUnknownProducer($unknownProducer) 
    {
        $this->unknownProducer = $unknownProducer;
        $unknownProducer->addCode($this);
    }    
    
    /**
     * Returns the array of article tokens assigned to this token.
     * @return array
     */
    public function getArticleTokens()
    {
        return $this->articleTokens;
    }        
    
    /**
     * Returns the array of article bigrams assigned to this article.
     * @return array
     */
    public function getArticleBigrams()
    {
        return $this->articleBigrams;
    }        

    /**
     * Returns the array of article titles assigned to this token.
     * @return array
     */
    public function getArticleTitles()
    {
        return $this->articleTitles;
    }        
    
}
