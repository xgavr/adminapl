<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;


use Doctrine\ORM\Mapping as ORM;
use Application\Filter\OemDetectDelimiterFilter;

/**
 * Description of Customer
 * @ORM\Entity(repositoryClass="\Application\Repository\RawRepository")
 * @ORM\Table(name="rawprice")
 * @author Daddy
 */
class Rawprice {
           
    const STATUS_NEW       = 1; // только что загрузили
    const STATUS_PARSED    = 2; // прошел разборку.
    const STATUS_RETIRED   = 3; //строка не актуальна


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
     * @ORM\Column(name="iid")   
     */
    protected $iid;
    
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
     * @ORM\Column(name="oem")   
     */
    protected $oem;

    /**
     * @ORM\Column(name="oem_brand")   
     */
    protected $brand;

    /**
     * @ORM\Column(name="vendor")   
     */
    protected $vendor;

    /**
     * @ORM\Column(name="lot")   
     */
    protected $lot;

    /**
     * @ORM\Column(name="unit")   
     */
    protected $unit;

    /**
     * @ORM\Column(name="pack")   
     */
    protected $pack;

    /**
     * @ORM\Column(name="car")   
     */
    protected $car;

    /**
     * @ORM\Column(name="bar")   
     */
    protected $bar;

    /**
     * @ORM\Column(name="currency")   
     */
    protected $currency;

    /**
     * @ORM\Column(name="comment")   
     */
    protected $comment;

    /**
     * @ORM\Column(name="weight")   
     */
    protected $weight;

    /**
     * @ORM\Column(name="country")   
     */
    protected $country;

    /**
     * @ORM\Column(name="markdown")   
     */
    protected $markdown;

    /**
     * @ORM\Column(name="sale")   
     */
    protected $sale;

    /**
     * @ORM\Column(name="image")   
     */
    protected $image;    

    /**
     * @ORM\Column(name="status")   
     */
    protected $status;    

    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Raw", inversedBy="rawprice") 
     * @ORM\JoinColumn(name="raw_id", referencedColumnName="id")
     */
    private $raw;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\PriceDescription", inversedBy="rawprice") 
     * @ORM\JoinColumn(name="price_description_id", referencedColumnName="id")
     */
    private $priceDescription;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\UnknownProducer", inversedBy="rawprice") 
     * @ORM\JoinColumn(name="unknown_producer_id", referencedColumnName="id")
     */
    private $unknownProducer;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Article", inversedBy="rawprice") 
     * @ORM\JoinColumn(name="article_id", referencedColumnName="id")
     */
    private $code;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Goods", inversedBy="rawprice") 
     * @ORM\JoinColumn(name="good_id", referencedColumnName="id")
     */
    private $good;
    
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
    
    public function getArticle() 
    {
        return $this->article;
    }

    public function setArticle($article) 
    {
        $this->article = (string) $article;
    }     
    
    public function getIid() 
    {
        return $this->iid;
    }

    public function setIid($iid) 
    {
        $this->iid = (string) $iid;
    }     

    public function getProducer() 
    {
        return $this->producer;
    }

    public function setProducer($producer) 
    {
        $this->producer = (string) $producer;
    }     
    
    public function getGoodname() 
    {
        return $this->goodname;
    }

    public function setGoodname($goodname) 
    {
        $this->goodname = mb_substr($title, 0, 200);
    }     
    
    public function getTitle() 
    {
        return $this->goodname;
    }

    public function setTitle($title) 
    {
        $this->goodname = mb_substr($title, 0, 200);
    }     
    
    public function getPrice() 
    {
        return $this->price;
    }

    public function setPrice($price) 
    {
        $this->price = (string) $price;
    }     
    
    public function getRest() 
    {
        return $this->rest;
    }

    public function setRest($rest) 
    {
        $this->rest = (string) $rest;
    }     
    
    public function setOem($oem) 
    {
        $this->oem = $oem;
    }     

    public function getOem() 
    {
        return $this->oem;
    }

    public function getOemAsArray() 
    {
        $filter = new OemDetectDelimiterFilter();
        $oemDelimeter = $filter->filter($this->oem);
        $oem = explode($oemDelimeter, $this->oem);
        $vendorDelimeter = $filter->filter($this->vendor);
        $vendor = explode($vendorDelimeter, $this->vendor);
        return array_filter(array_unique(array_merge($oem, $vendor)));
    }

    public function getBrand() 
    {
        return $this->brand;
    }

    public function setBrand($brand) 
    {
        $this->brand = $brand;
    }     

    public function getVendor() 
    {
        return $this->vendor;
    }

    public function setVendor($vendor) 
    {
        $this->vendor = $vendor;
    }     

    public function getUnit() 
    {
        return $this->unit;
    }

    public function setUnit($unit) 
    {
        $this->unit = $unit;
    }     

    public function getPack() 
    {
        return $this->pack;
    }

    public function setPack($pack) 
    {
        $this->pack = $pack;
    }     

    public function getCar() 
    {
        return $this->car;
    }

    public function setCar($car) 
    {
        $this->car = $car;
    }     

    public function getLot() 
    {
        return $this->lot;
    }

    public function setLot($lot) 
    {
        $this->lot = (string) $lot;
    }     

    public function getBar() 
    {
        return $this->bar;
    }

    public function setBar($bar) 
    {
        $this->bar = $bar;
    }     

    public function getCurrency() 
    {
        return $this->currency;
    }

    public function setCurrency($currency) 
    {
        $this->currency = $currency;
    }     

    public function getComment() 
    {
        return $this->comment;
    }

    public function setComment($comment) 
    {
        $this->comment = $comment;
    }     

    public function getWeight() 
    {
        return $this->weight;
    }

    public function setWeight($weight) 
    {
        $this->weight = (string) $weight;
    }     

    public function getCountry() 
    {
        return $this->country;
    }

    public function setCountry($country) 
    {
        $this->country = $country;
    }     

    public function getMarkdown() 
    {
        return $this->markdown;
    }

    public function setMarkdown($markdown) 
    {
        $this->markdown = (string) $markdown;
    }     

    public function getSale() 
    {
        return $this->sale;
    }

    public function setSale($sale) 
    {
        $this->sale = (string) $sale;
    }     

    public function getImage() 
    {
        return $this->image;
    }

    public function setImage($image) 
    {
        $this->image = $image;
    }         

    public function getRawdata() 
    {
        return $this->rawdata;
    }

    public function getRawdataAsArray() 
    {        
        return explode(';', $this->rawdata);
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
     * Возвращает связанный priceDescription.
     * @return \Application\Entity\PriceDescription
     */    
    public function getPriceDescription() 
    {
        return $this->priceDescription;
    }

    /**
     * Задает связанный priceDescription.
     * @param \Application\Entity\PriceDescription $priceDescription
     */    
    public function setPriceDescription($priceDescription) 
    {
        $this->priceDescription = $priceDescription;
//        $priceDescription->addRawprice($this);
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
     * Возвращает связанный code.
     * @return \Application\Entity\Article
     */
    
    public function getCode() 
    {
        return $this->code;
    }

    /**
     * Задает связанный code.
     * @param \Application\Entity\Code $code
     */    
    public function setCode($code) 
    {
        $this->code = $code;
        $code->addRawprice($this);
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
    
    /*
     * Получить поля со значениями и заголовками
     */
    public function getFieldValues()
    {
        $result = [];
        
        $form = new \Application\Form\PriceDescriptionForm();
        $elements = $form->getElements();
        foreach ($elements as $element){
            if(in_array($element->getName(), ['name', 'status', 'type'])) continue;
            $func = 'get'.ucfirst($element->getName());
            if (method_exists($this, $func)){
                if($this->$func()){
                    $result[$element->getLabel()] = $this->$func();
                }
            }
        }
        return $result;
    }
    
    
}
