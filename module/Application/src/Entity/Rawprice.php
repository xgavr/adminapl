<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;


use Doctrine\ORM\Mapping as ORM;
use Zend\Json\Json;

/**
 * Description of Customer
 * @ORM\Entity(repositoryClass="\Application\Repository\RawRepository")
 * @ORM\Table(name="rawprice")
 * @author Daddy
 */
class Rawprice {
           
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
     * @ORM\Column(name="date_created")  
     */
    protected $dateCreated;
    
    /**
     * @ORM\Column(name="article")   
     */
    protected $article;

    /**
     * @ORM\Column(name="producer")   
     */
    protected $producer;

    /**
     * @ORM\Column(name="goodname")   
     */
    protected $goodname;

    /**
     * @ORM\Column(name="price")   
     */
    protected $price;

    /**
     * @ORM\Column(name="rest")   
     */
    protected $rest;

    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Raw", inversedBy="rawprice") 
     * @ORM\JoinColumn(name="raw_id", referencedColumnName="id")
     */
    private $raw;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\UnknownProducer", inversedBy="rawprice") 
     * @ORM\JoinColumn(name="unknown_producer_id", referencedColumnName="id")
     */
    private $unknownProducer;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Goods", inversedBy="rawprice") 
     * @ORM\JoinColumn(name="good_id", referencedColumnName="id")
     */
    private $good;
    
    public function getId() 
    {
        return $this->id;
    }

    public function setId($id) 
    {
        $this->id = $id;
    }     
    
    public function getArticle() 
    {
        return $this->article;
    }

    public function setArticle($article) 
    {
        $this->article = $article;
    }     
    
    public function getProducer() 
    {
        return $this->producer;
    }

    public function setProducer($producer) 
    {
        $this->producer = $producer;
    }     
    
    public function getGoodname() 
    {
        return $this->goodname;
    }

    public function setGoodname($goodname) 
    {
        $this->goodname = $goodname;
    }     
    
    public function getPrice() 
    {
        return $this->price;
    }

    public function setPrice($price) 
    {
        $this->price = $price;
    }     
    
    public function getRest() 
    {
        return $this->rest;
    }

    public function setRest($rest) 
    {
        $this->rest = $rest;
    }     

    public function getRawdata() 
    {
        return $this->rawdata;
    }

    public function getRawdataAsArray() 
    {
        return Json::decode($this->rawdata);
    }

    public function setRawdata($rawdata) 
    {
        $this->rawdata = $rawdata;
    }   
    
    /**
     * Returns the date of user creation.
     * @return string     
     */
    public function getDateCreated() 
    {
        return $this->dateCreated;
    }
    
    /**
     * Sets the date when this user was created.
     * @param string $dateCreated     
     */
    public function setDateCreated($dateCreated) 
    {
        $this->dateCreated = $dateCreated;
    }    
        
    /*
     * Возвращает связанный raw.
     * @return \Application\Entity\Raw
     */    
    public function getRaw() 
    {
        return $this->raw;
    }

    /**
     * Задает связанный raw.
     * @param \Application\Entity\Raw $raw
     */    
    public function setRaw($raw) 
    {
        $this->raw = $raw;
        $raw->addRawprice($this);
    }     
    
    /*
     * Возвращает связанный raw.
     * @return \Application\Entity\UnknownProducer
     */
    
    public function getUnknownProducer() 
    {
        return $this->unknownProducer;
    }

    /**
     * Задает связанный raw.
     * @param \Application\Entity\UnknownProducer $unknownProducer
     */    
    public function setUnknownProducer($unknownProducer) 
    {
        $this->unknownProducer = $unknownProducer;
        $unknownProducer->addRawprice($this);
    }
    
    /*
     * Возвращает связанный raw.
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
        $good->addRawprice($this);
    }     
}
