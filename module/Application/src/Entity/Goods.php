<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Application\Entity\ScaleTreshold;
use Laminas\Json\Decoder;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Goods
 * @ORM\Entity(repositoryClass="\Application\Repository\GoodsRepository")
 * @ORM\Table(name="goods")
 * @author Daddy
 */
class Goods {
    
    // Константы доступности товар.
    const AVAILABLE_TRUE    = 1; // Доступен.
    const AVAILABLE_FALSE   = 0; // Недоступен.
    
    const CAR_UPDATED = 2; // машины обновлены
    const CAR_UPDATING = 3; // машины обновляются
    const CAR_FOR_UPDATE = 1; // машины не обновлялись
    
    const IMAGE_UPDATED = 2; // картинки обновлены
    const IMAGE_FOR_UPDATE = 1; // картинки не обновлялись
    
    const DESCRIPTION_UPDATED = 2; // описания обновлены
    const DESCRIPTION_FOR_UPDATE = 1; // описания не обновлялись

    const GROUP_UPDATED = 2; // группы обновлены
    const GROUP_FOR_UPDATE = 1; // группы не обновлялись

    const OEM_UPDATED = 2; // номера обновлены
    const OEM_FOR_UPDATE = 1; // номера не обновлялись

    const RAWPRICE_EX_NEW            = 1; // не передано
    const RAWPRICE_EX_TO_TRANSFER    = 3; //нужно передать
    const RAWPRICE_EX_TRANSFERRED    = 2; // передано.
    
    const OEM_EX_NEW            = 1; // не передано
    const OEM_EX_TRANSFERRED    = 2; // передано.
    
    const ATTR_EX_NEW            = 1; // не передано
    const ATTR_EX_TRANSFERRED    = 2; // передано.
    
    const CAR_EX_NEW            = 1; // не передано
    const CAR_EX_TRANSFERRED    = 2; // передано.
    
    const IMG_EX_NEW            = 1; // не передано
    const IMG_EX_TRANSFERRED    = 2; // передано.
    
    const PRICE_EX_NEW            = 1; // не передано
    const PRICE_EX_TRANSFERRED    = 2; // передано.
    
    const GROUP_EX_NEW            = 1; // не передано
    const GROUP_EX_TRANSFERRED    = 2; // передано.
    
    const NAME_EX_NEW            = 1; // не передано
    const NAME_EX_TRANSFERRED    = 2; // передано.
    
    const DEFAULT_GROUP_APL_ID    = -1; //группа апл по умолчнию 
    
    const TD_DIRECT = 1; //точно совпадает с товаровм в ТД
    const TD_NO_DIRECT = 2; //не совпадает с товаровм в ТД
    
    const GROUP_TOKEN_UPDATE_FLAG = 2; // установить любое число (2-9), для запуска обновления групп токенов
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="apl_id")   
     */
    protected $aplId = 0;
    
    /**
     * @ORM\Column(name="name")   
     */
    protected $name;

    /**
     * @ORM\Column(name="code")   
     */
    protected $code;
    
    /**
     * @ORM\Column(name="price")   
     */
    protected $price;
    
    /**
     * @ORM\Column(name="status_car")   
     */
    protected $statusCar;
    
    /**
     * @ORM\Column(name="status_image")   
     */
    protected $statusImage;
    /**
     * @ORM\Column(name="status_group")   
     */
    protected $statusGroup;
    
    /**
     * @ORM\Column(name="status_description")   
     */
    protected $statusDescription;
    
    /**
     * @ORM\Column(name="status_oem")   
     */
    protected $statusOem;

    /**
     * @ORM\Column(name="status_rawprice_ex")   
     */
    protected $statusRawpriceEx = self::RAWPRICE_EX_NEW;

    /**
     * @ORM\Column(name="status_oem_ex")   
     */
    protected $statusOemEx = self::OEM_EX_NEW;

    /**
     * @ORM\Column(name="status_attr_ex")   
     */
    protected $statusAttrEx = self::ATTR_EX_NEW;

    /**
     * @ORM\Column(name="status_car_ex")   
     */
    protected $statusCarEx = self::CAR_EX_NEW;

    /**
     * @ORM\Column(name="status_img_ex")   
     */
    protected $statusImgEx = self::IMG_EX_NEW;

    /**
     * @ORM\Column(name="status_price_ex")   
     */
    protected $statusPriceEx = self::PRICE_EX_NEW;

    /**
     * @ORM\Column(name="status_group_ex")   
     */
    protected $statusGroupEx = self::GROUP_EX_NEW;

    /**
     * @ORM\Column(name="status_name_ex")   
     */
    protected $statusNameEx = self::NAME_EX_NEW;

    /**
     * @ORM\Column(name="td_direct")   
     */
    protected $tdDirect = self::TD_NO_DIRECT;

    /**
     * @ORM\Column(name="car_count")   
     */
    protected $carCount;

    /**
     * @ORM\Column(name="min_price")   
     */
    protected $minPrice = 0.0;

    /**
     * @ORM\Column(name="mean_price")   
     */
    protected $meanPrice = 0.0;

    /**
     * @ORM\Column(name="fix_price")   
     */
    protected $fixPrice = 0.0;

    /**
     * @ORM\Column(name="markup")   
     */
    protected $markup = 0.0;

    /**
     * @ORM\Column(name="group_apl")   
     */
    protected $groupApl = self::DEFAULT_GROUP_APL_ID;

    /**
     * @ORM\Column(name="group_token_update_flag")  
     */
    protected $groupTokenUpdateFlag = self::GROUP_TOKEN_UPDATE_FLAG;     

    /**
     * @ORM\Column(name="available")   
     */
    protected $available;
    
    /**
     * @ORM\Column(name="description")   
     */
    protected $description;
    
    /** 
     * @ORM\Column(name="date_ex")  
     */
    protected $dateEx;
    
    /**
     * @ORM\ManyToOne(targetEntity="\Application\Entity\Producer", inversedBy="goods") 
     * @ORM\JoinColumn(name="producer_id", referencedColumnName="id")
     * 
     */
    protected $producer;
    
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Tax", inversedBy="goods") 
     * @ORM\JoinColumn(name="tax_id", referencedColumnName="id")
     */
    protected $tax;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\GenericGroup", inversedBy="goods") 
     * @ORM\JoinColumn(name="generic_group_id", referencedColumnName="id")
     */
    protected $genericGroup;
    
    /**
    * @ORM\OneToMany(targetEntity="Application\Entity\Article", mappedBy="good")
    * @ORM\JoinColumn(name="id", referencedColumnName="good_id")
     */
    private $articles;
 
    /**
     * @ORM\OneToMany(targetEntity="Application\Entity\Images", mappedBy="good")
     * @ORM\JoinColumn(name="id", referencedColumnName="good_id")
     */
    protected $images;
    
    /**
     * @ORM\OneToMany(targetEntity="Application\Entity\Oem", mappedBy="good")
     * @ORM\JoinColumn(name="id", referencedColumnName="good_id")
     */
    protected $oems;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\TokenGroup", inversedBy="goods") 
     * @ORM\JoinColumn(name="token_group_id", referencedColumnName="id")
     * 
     */

    protected $tokenGroup;
    
    /**
     * @ORM\ManyToMany(targetEntity="Application\Entity\Car", inversedBy="goods")
     * @ORM\JoinTable(name="good_car",
     *      joinColumns={@ORM\JoinColumn(name="good_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="car_id", referencedColumnName="id")}
     *      )
     */
    protected $cars;
    
    /**
     * @ORM\OneToMany(targetEntity="Application\Entity\GoodAttributeValue", mappedBy="good")
     * @ORM\JoinColumn(name="id", referencedColumnName="good_id")
     */
    protected $attributeValues;

     /**
    * @ORM\OneToMany(targetEntity="Application\Entity\GoodToken", mappedBy="good")
    * @ORM\JoinColumn(name="id", referencedColumnName="good_id")
     */
    private $goodTokens;
    
     /**
    * @ORM\OneToMany(targetEntity="\Application\Entity\GoodTitle", mappedBy="good")
    * @ORM\JoinColumn(name="id", referencedColumnName="good_id")
     */
    private $goodTitles;    

    /**
     * Конструктор.
     */
    public function __construct() 
    {
      $this->images = new ArrayCollection();   
      $this->articles = new ArrayCollection();      
      $this->cart = new ArrayCollection(); 
      $this->cars = new ArrayCollection();
      $this->attributeValues = new ArrayCollection();
      $this->oems = new ArrayCollection();
    }
    
  
    public function getId() 
    {
        return $this->id;
    }

    public function setId($id) 
    {
        $this->id = $id;
    }     

    public function getAplId() 
    {
        return $this->aplId;
    }

    public function setAplId($aplId) 
    {
        $this->aplId = $aplId;
    }     

    public function getName() 
    {
        return $this->name;
    }

    public function setName($name) 
    {
        $this->name = $name;
    }     

    public function getCode() 
    {
        return $this->code;
    }

    public function setCode($code) 
    {
        $this->code = $code;
    }     

    public function getPrice() 
    {
        return $this->price;
    }
    
    public function getOpts()
    {
        $result = [];
        $priceCols = ScaleTreshold::retailPriceCols($this->getPrice(), $this->getMeanPrice());
        if (is_array($priceCols)){
            foreach ($priceCols as $priceCol){
                if (isset($priceCol['price'])){
                    $result[] = $priceCol['price'];
                }    
            }
        }    
        return $result;
    }

    public function setPrice($price) 
    {
        $this->price = $price;
    }     
    
    public function getMinPrice()
    {
        return $this->minPrice;
    }
    
    public function getFormatMinPrice()
    {
        return round($this->minPrice, 2);
    }
    
    public function setMinPrice($minPrice)
    {
        $this->minPrice = $minPrice;
    }

    public function getMeanPrice()
    {
        return $this->meanPrice;
    }
    
    public function getFormatMeanPrice()
    {
        return round($this->meanPrice, 2);
    }
    
    public function setMeanPrice($meanPrice)
    {
        $this->meanPrice = $meanPrice;
    }

    public function getFixPrice()
    {
        return $this->fixPrice;
    }
    
    public function setFixPrice($fixPrice)
    {
        $this->fixPrice = $fixPrice;
    }

    public function getMarkup()
    {
        return $this->markup;
    }
    
    public function setMarkup($markup)
    {
        $this->markup = $markup;
    }

    public function getGroupApl()
    {
        return $this->groupApl;
    }
    
    public function getTransferGroupApl()
    {
        if ($this->groupApl >= 0){
            return $this->groupApl;
        }
        return;
    }
    
    public function getGroupAplAsString()
    {
        if ($this->groupApl == self::DEFAULT_GROUP_APL_ID){
            return;
        }
        
        return $this->groupApl;
    }
    
    public function setGroupApl($groupApl)
    {
        $this->groupApl = $groupApl;
    }

    /*
     * Возвращает связанный producer.
     * @return \Application\Entity\Producer
     */    
    public function getProducer() 
    {
        return $this->producer;
    }
    
    /**
     * Задает связанный producer.
     * @param \Application\Entity\Producer $producer
     */    
    public function setProducer($producer) 
    {
        $this->producer = $producer;
//        $producer->addGoods($this);
    }     

    /*
     * Возвращает связанный tax.
     * @return \Company\Entity\Tax
     */    
    public function getTax() 
    {
        return $this->tax;
    }

    /**
     * Задает связанный tax.
     * @param \Company\Entity\Tax $tax
     */    
    public function setTax($tax) 
    {
        $this->tax = $tax;
    }     

    /*
     * Возвращает связанный genericGroup.
     * @return \Application\Entity\GenericGroup
     */    
    public function getGenericGroup() 
    {
        return $this->genericGroup;
    }

    /**
     * Задает связанный genericGroup.
     * @param \Application\Entity\GenericGroup $genericGroup
     */    
    public function setGenericGroup($genericGroup) 
    {
        $this->genericGroup = $genericGroup;
    }     

    public function getAvailable() 
    {
        return $this->available;
    }

    public function setAvailable($available) 
    {
        $this->available = $available;
    }     
    
    public function getDescription() 
    {
        return $this->description;
    }
    
//    public function getDescriptionAsArray() 
//    {
//        if ($this->description){
//            try{
//                return Decoder::decode($this->description, \Laminas\Json\Json::TYPE_ARRAY);
//            } catch (Exception $e){
//                return;
//            }    
//        }
//        
//        return;
//    }    

    public function setDescription($description) 
    {
        $this->description = trim($description);
    }     

    public function getDateEx() 
    {
        return $this->dateEx;
    }

    public function setDateEx($dateEx) 
    {
        $this->dateEx = $dateEx;
    }     

    /**
     * Возвращает картинки для этого товара.
     * @return array
     */
    public function getImages() 
    {
        return $this->images;
    }
    
    /**
     * Добавляет новою картинку к этому товару.
     * @param $image
     */
    public function addImage($image) 
    {
        $this->images[] = $image;
    }
    
    /**
     * Возвращает номера для этого товара.
     * @return array
     */
    public function getOems() 
    {
        return $this->oems;
    }
    
    /**
     * Добавляет новый номер к этому товару.
     * @param $oem
     */
    public function addOem($oem) 
    {
        $this->oems[] = $oem;
    }
    
    /**
     * Returns the array of contacts assigned to this.
     * @return array
     */
    public function getArticles()
    {
        return $this->articles;
    }
        
    /**
     * Assigns.
     */
    public function addArticle($article)
    {
        $this->articles[] = $article;
    }
    
    /**
     * Содержит ли наименование товара токен
     * 
     * @param Application\Entity\Token $token
     * @return boolean
     */
    public function hasToken($token)
    {
        foreach ($this->rawprice as $rawprice){
            if ($rawprice->hasToken($token)){
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Возвращает токены из словаря Ru
     * @return array;
     */
    public function getDictRuTokens()
    {
        $result = [];
        foreach($this->rawprice as $rawprice){
            foreach($rawprice->getDictRuTokens() as $token){
                $result[$token->getId()] = $token;
            }
        }
        
        return $result;
    }
            
    /**
     * Возвращает id токенов из словаря Ru
     * @return string;
     */
    public function getDictRuTokenIds()
    {
        $tokens = $this->getDictRuTokens();
        $result = [];
        foreach($tokens as $token){
            $result[] = $token->getId();
        }
        
        $filter = new \Application\Filter\IdsFormat();

        return $filter->filter($result);
    }
            
    /*
     * Возвращает связанный tokenGroup.
     * @return \Application\Entity\TokenGroup
     */    
    public function getTokenGroup() 
    {
        if ($this->tokenGroup){
            if ($this->tokenGroup->getId()){
                return $this->tokenGroup;                
            }
        }
        
        return;
    }
    
    /**
     * Задает связанный tokenGroup.
     * @param \Application\Entity\TokenGroup $tokenGroup
     */    
    public function setTokenGroup($tokenGroup) 
    {
        $this->tokenGroup = $tokenGroup;
        if ($tokenGroup){
            $tokenGroup->addGood($this);
        }    
    }     

    // Возвращает машины для данного товара.
    public function getCars() 
    {
        return $this->cars;
    }      
    
    // Добавляет новую машину к данному товару.
    public function addCar($car) 
    {
        $this->cars[] = $car;        
    }
    
    // Удаляет связь между этим товаром и заданной машиной.
    public function removeCarAssociation($car) 
    {
        $this->cars->removeElement($car);
    }    
    
    // Возвращает аттрибуты для данного товара.
    public function getAttributeValues() 
    {
        return $this->attributeValues;
    }      
    
    // Добавляет новую аттрибут к данному товару.
    public function addAttributeValue($attributeValue) 
    {
        $this->attributeValues[] = $attributeValue;        
    }
    
    // Удаляет связь между этим товаром и заданным аттрибутом.
    public function removeAttributeValueAssociation($attributeValue) 
    {
        $this->attributeValues->removeElement($attributeValue);
    }    
    
    public function getStatusCar()
    {
        $this->statusCar;
    }
    
    public function setStatusCar($statusCar)
    {
        $this->statusCar = $statusCar;
    }

    public function getStatusImage()
    {
        $this->statusImage;
    }
    
    public function setStatusImage($statusImage)
    {
        $this->statusImage = $statusImage;
    }
    
    public function getStatusGroup()
    {
        $this->statusGroup;
    }
    
    public function setStatusGroup($statusGroup)
    {
        $this->statusGroup = $statusGroup;
    }
    
    public function getStatusDescription()
    {
        $this->statusDescription;
    }
    
    public function setStatusDescription($statusDescription)
    {
        $this->statusDescription = $statusDescription;
    }    
    
    public function getStatusOem()
    {
        $this->statusOem;
    }
    
    public function getStyleListStatusOem()
    {
        return [
            self::OEM_FOR_UPDATE => '',
            self::OEM_UPDATED => 'alert-success',
        ];
    }
    
    public function getStyleStatusOem()
    {
        $list = self::getStyleListStatusOem();
        if (isset($list[$this->statusOem])) {
            return $list[$this->statusOem];
        }

        return '';
    }
    
    public function setStatusOem($statusOem)
    {
        $this->statusOem = $statusOem;
    }    
    
    public function getCarCount()
    {
        $this->carCount;
    }
    
    public function setCarCount($carCount)
    {
        $this->carCount = $carCount;
    }    
    
    public function getStatusRawpriceEx()
    {
        $this->statusRawpriceEx;
    }
    
    public function getStyleListStatusRawpriceEx()
    {
        return [
            self::RAWPRICE_EX_NEW => '',
            self::RAWPRICE_EX_TO_TRANSFER => 'alert-warning',
            self::RAWPRICE_EX_TRANSFERRED => 'alert-success',
        ];
    }
    
    public function getStyleStatusRawpriceEx()
    {
        $list = self::getStyleListStatusRawpriceEx();
        if (isset($list[$this->statusPriceEx])) {
            return $list[$this->statusRawpriceEx];
        }

        return '';
    }
    
    public function setStatusRawpriceEx($statusRawpriceEx)
    {
        $this->statusRawpriceEx = $statusRawpriceEx;
    }    
    
    public function getStatusOemEx()
    {
        $this->statusOemEx;
    }
    
    public function setStatusOemEx($statusOemEx)
    {
        $this->statusOemEx = $statusOemEx;
    }    
    
    public function getStatusAttrEx()
    {
        $this->statusAttrEx;
    }
    
    public function setStatusAttrEx($statusAttrEx)
    {
        $this->statusAttrEx = $statusAttrEx;
    }    
    
    public function getStatusCarEx()
    {
        $this->statusCarEx;
    }
    
    public function setStatusCarEx($statusCarEx)
    {
        $this->statusCarEx = $statusCarEx;
    }    
    
    public function getStatusImgEx()
    {
        $this->statusImgEx;
    }
    
    public function setStatusImgEx($statusImgEx)
    {
        $this->statusImgEx = $statusImgEx;
    }    
    
    public function getStatusPriceEx()
    {
        $this->statusPriceEx;
    }
    
    public function setStatusPriceEx($statusPriceEx)
    {
        $this->statusPriceEx = $statusPriceEx;
    }    
    
    public function getStatusGroupEx()
    {
        $this->statusGroupEx;
    }
    
    public function setStatusGroupEx($statusGroupEx)
    {
        $this->statusGroupEx = $statusGroupEx;
    }    
    
    public function getStatusNameEx()
    {
        $this->statusNameEx;
    }
    
    public function setStatusNameEx($statusNameEx)
    {
        $this->statusNameEx = $statusNameEx;
    }    
    
    public function getTdDirect()
    {
        $this->tdDirect;
    }
    
    public function setTdDirect($tdDirect)
    {
        $this->tdDirect = $tdDirect;
    }    
    
    public function getGroupTokenUpdateFlag()
    {
        $this->groupTokenUpdateFlag;
    }
    
    public function setGroupTokenUpdateFlag($groupTokenUpdateFlag)
    {
        $this->groupTokenUpdateFlag = $groupTokenUpdateFlag;
    }    
    
    /**
     * Returns the array of good tokens assigned to this token.
     * @return array
     */
    public function getGoodTokens()
    {
        return $this->goodTokens;
    }        
        
    /**
     * Returns the array of good tokens assigned to this token.
     * @return array
     */
    public function getGoodTitles()
    {
        return $this->goodTitles;
    }        
        
}
