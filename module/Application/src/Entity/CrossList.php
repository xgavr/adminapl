<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;


use Doctrine\ORM\Mapping as ORM;
use Application\Filter\ProducerName;

/**
 * Description of Customer
 * @ORM\Entity(repositoryClass="\Application\Repository\CrossRepository")
 * @ORM\Table(name="cross_list")
 * @author Daddy
 */
class CrossList {
           
    const STATUS_NEW       = 1; // только что загрузили
    const STATUS_PARSED    = 2; // прошел разборку.
    const STATUS_RETIRED   = 3; //строка не актуальна
    const STATUS_BLACK_LIST = 4; //наименовние содержит слово из черного списка
    const STATUS_BIND = 5;//привязан к артикулу

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="rawdata")   
     */
    protected $rawdata;

    /**
     * @ORM\Column(name="producer_name")   
     */
    protected $producerName;

    /**
     * @ORM\Column(name="producer_article")   
     */
    protected $producerArticle;

    /**
     * @ORM\Column(name="producer_article_name")   
     */
    protected $producerArticleName;

    /**
     * @ORM\Column(name="brand_name")   
     */
    protected $brandName;

    /**
     * @ORM\Column(name="brand_article")   
     */
    protected $brandArticle;

    /**
     * @ORM\Column(name="brand_article_name")   
     */
    protected $brandArticleName;

    /**
     * @ORM\Column(name="oe")   
     */
    protected $oe;

    /**
     * @ORM\Column(name="oe_brand")   
     */
    protected $oeBrand;

    /**
     * @ORM\Column(name="code_id")   
     */
    protected $codeId;

    /**
     * @ORM\Column(name="status")   
     */
    protected $status;    

    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Cross", inversedBy="lines") 
     * @ORM\JoinColumn(name="cross_id", referencedColumnName="id")
     */
    private $cross;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Article", inversedBy="crossList") 
     * @ORM\JoinColumn(name="article_id", referencedColumnName="id")
     */
    private $article;
    
    /**
     * Constructor.
     */
    public function __construct() 
    {
    }
   
    /**
     * Returns status.
     * @return int     
     */
    public function getStatus() 
    {
        return $this->status;
    }

    /**
     * Returns possible statuses as array.
     * @return array
     */
    public static function getStatusList() 
    {
        return [
            self::STATUS_NEW => 'Новый',
            self::STATUS_PARSED => 'Разобран',
            self::STATUS_RETIRED => 'Устарело',
            self::STATUS_BLACK_LIST => 'Черный список'
        ];
    }    
    
    /**
     * Returns user status as string.
     * @return string
     */
    public function getStatusAsString()
    {
        $list = self::getStatusList();
        if (isset($list[$this->status]))
            return $list[$this->status];
        
        return 'Unknown';
    }  
    
    /**
     * Returns user status as string.
     * @return string
     */
    public function getStatusAsAplPublish()
    {
        if ($this->status == self::STATUS_PARSED){
            return 1;
        }
        return 0;
    }  
    
    public function getStatusName($status)
    {
        $list = self::getStatusList();
        if (isset($list[$status]))
            return $list[$status];
        
        return 'Unknown';        
    }
    
    /**
     * Sets status.
     * @param int $status     
     */
    public function setStatus($status) 
    {
        $this->status = $status;
    }   
    
    public function getId() 
    {
        return $this->id;
    }

    public function setId($id) 
    {
        $this->id = $id;
    }     
    
    public function getProducerName() 
    {
        return $this->producerName;
    }

    public function setProducerName($producerName) 
    {
        $producerNameFilter = new ProducerName();
        $this->producerName = $producerNameFilter->filter($producerName);
    }     
    
    public function getProducerArticle() 
    {
        return $this->producerArticle;
    }

    public function setProducerArticle($producerArticle) 
    {
        $this->producerArticle = $producerArticle;
    }     
    
    public function getProducerArticleName() 
    {
        return $this->producerArticleName;
    }

    public function setProducerArticleName($producerArticleName) 
    {
        $this->producerArticleName = $producerArticleName;
    }     
    
    
    public function getBrandName() 
    {
        return $this->brandName;
    }

    public function setBrandName($brandName) 
    {
        $producerNameFilter = new ProducerName();
        $this->brandName = $producerNameFilter->filter($brandName);
    }     
    
    public function getBrandArticle() 
    {
        return $this->brandArticle;
    }

    public function setBrandArticle($brandArticle) 
    {
        $this->brandArticle = $brandArticle;
    }     
    
    public function getBrandArticleName() 
    {
        return $this->brandArticleName;
    }

    public function setBrandArticleName($brandArticleName) 
    {
        $this->brandArticleName = $brandArticleName;
    }     
    
    public function getRawdata() 
    {
        return $this->rawdata;
    }

    public function getRawdataAsArray() 
    {        
        return array_filter(explode(';', $this->rawdata));
    }

    public function setRawdata($rawdata) 
    {
        $this->rawdata = $rawdata;
    }   
    
    public function getOe() 
    {
        return $this->oe;
    }

    public function setOe($oe) 
    {
        $this->oe = $oe;
    }     
    
    public function getOeBrand() 
    {
        return $this->oeBrand;
    }

    public function setOeBrand($oeBrand) 
    {
        $this->oeBrand = $oeBrand;
    }     
    
    public function getCodeId() 
    {
        return $this->codeId;
    }

    public function setCodeId($codeId) 
    {
        $this->codeId = $codeId;
    }     
    
    /*
     * Возвращает связанный raw.
     * @return \Application\Entity\Cross
     */    
    public function getCross() 
    {
        return $this->cross;
    }

    /**
     * Задает связанный cross.
     * @param \Application\Entity\Cross $cross
     */    
    public function setCross($cross) 
    {
        $this->cross = $cross;
        $cross->addLine($this);
    }     
    
    /*
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
        if ($article){
            $article->addCrossList($this);
        }    
    }
    
}
