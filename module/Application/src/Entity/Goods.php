<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Application\Entity\ScaleTreshold;
use Laminas\Json\Decoder;
use Laminas\Json\Encoder;
use Application\Entity\Producer;
use Application\Entity\Images;
use Application\Entity\Oem;

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
    const AVAILABLE_TRUE    = 1; // В наличии у поставщиков.
    const AVAILABLE_APL    = 2; // В наличии в Апл.
    const AVAILABLE_FALSE   = 9; // Нет в наличии.
    
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
    const OEM_INTERSECT = 3; // пересечения не обновлены
    const OEM_SUP_CROSS = 4; // номера поставщиков и кроссы не обновлены
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
    
    const FASADE_EX_NEW            = 1; // не передано
    const FASADE_EX_TRANSFERRED    = 2; // передано.
    
    const DEFAULT_GROUP_APL_ID    = -1; //группа апл по умолчнию 
    
    const TD_DIRECT = 1; //точно совпадает с товаровм в ТД
    const TD_NO_DIRECT = 2; //не совпадает с товаровм в ТД
    
    const CHECK_OEM_OK = 1; //номера проверены
    const CHECK_OEM_NO = 2;
    
    const CHECK_DESCRIPTION_OK = 1; //описание проверено
    const CHECK_DESCRIPTION_NO = 2;
    
    const CHECK_IMAGE_OK = 1; //картинка проверена
    const CHECK_IMAGE_NO = 2;
    
    const CHECK_CAR_OK = 1; //машина проверена
    const CHECK_CAR_NO = 2;
    
    const GROUP_TOKEN_UPDATE_FLAG = 2; // месяц обновления наименования товара
    
    const SEARCH_CODE = 1; //поиск по артикулу
    const SEARCH_APLID = 2; //поиск по aplId
    const SEARCH_ID = 3; //поиск по id
    const SEARCH_OE = 4; //поиск по ое
    const SEARCH_COMISS = 5; //товары на комиссии
    const SEARCH_NAME = 6; //поиск по наименованию
    const SEARCH_TP = 7; //товары в торговых площадках
    const SEARCH_PRODUCER = 8; //производитель
    
    const REST_ALL = 1; //все
    const REST_REST = 2; //есть остаток
    const REST_AVIALABLE = 3; //доступные
    
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
     * @ORM\Column(name="in_strore")   
     */
    protected $inStore = 0;

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
     * @ORM\Column(name="market_place_price")   
     */
    protected $marketPlacePrice = 0.0;

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
     * @ORM\Column(name="date_price")  
     */
    protected $datePrice;
        
    /**
     * @ORM\Column(name="upd_week")  
     */
    protected $updWeek;        
    
    /**
     * @ORM\Column(name="retail_count")  
     */
    protected $retailCount = 0;        
    
    /**
     * @ORM\Column(name="sale_month")  
     */
    protected $saleMonth = 0;        
    
    /**
     * @ORM\Column(name="check_oem")  
     */
    protected $checkOem;        
    
    /**
     * @ORM\Column(name="check_description")  
     */
    protected $checkDescription;        
    
    /**
     * @ORM\Column(name="check_image")  
     */
    protected $checkImage;        
    
    /**
     * @ORM\Column(name="check_car")  
     */
    protected $checkCar;
    
    /**
     * @ORM\Column(name="fasade_ex")  
     */
    protected $fasadeEx;        
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Producer", inversedBy="goods") 
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
    * @ORM\OneToMany(targetEntity="Application\Entity\GoodTitle", mappedBy="good")
    * @ORM\JoinColumn(name="id", referencedColumnName="good_id")
     */
    private $goodTitles;    

   /**
    * @ORM\OneToMany(targetEntity="Application\Entity\SupplierOrder", mappedBy="good")
    * @ORM\JoinColumn(name="id", referencedColumnName="good_id")
   */
   private $supplierOrders;

   /**
    * @ORM\OneToMany(targetEntity="Stock\Entity\Movement", mappedBy="good")
    * @ORM\JoinColumn(name="id", referencedColumnName="good_id")
   */
   private $movements;

   /**
    * @ORM\OneToMany(targetEntity="Stock\Entity\GoodBalance", mappedBy="good")
    * @ORM\JoinColumn(name="id", referencedColumnName="good_id")
   */
   private $goodBalances;

   /**
    * @ORM\OneToMany(targetEntity="Stock\Entity\ComitentBalance", mappedBy="good")
    * @ORM\JoinColumn(name="id", referencedColumnName="good_id")
   */
   private $comitentBalances;

   /**
    * @ORM\OneToMany(targetEntity="Application\Entity\GoodSupplier", mappedBy="good")
    * @ORM\JoinColumn(name="id", referencedColumnName="good_id")
   */
   private $goodSuppliers;

   /**
    * @ORM\OneToMany(targetEntity="Application\Entity\GoodRelated", mappedBy="good")
    * @ORM\JoinColumn(name="id", referencedColumnName="good_id")
   */
   private $goodRelations;

   /**
    * @ORM\OneToMany(targetEntity="GoodMap\Entity\FoldBalance", mappedBy="good")
    * @ORM\JoinColumn(name="id", referencedColumnName="good_id")
   */
   private $foldBalances;


    /**
     * @ORM\ManyToMany(targetEntity="Fasade\Entity\GroupSite", inversedBy="goods")
     * @ORM\JoinTable(name="good_group_site",
     *      joinColumns={@ORM\JoinColumn(name="good_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="group_site_id", referencedColumnName="id")}
     *      )
     */
    protected $categories;
   
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
      $this->foldBalances = new ArrayCollection();
      $this->goodRelations = new ArrayCollection();
      $this->categories = new ArrayCollection();
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

    public function getAplIdLinkCode() 
    {
        if ($this->aplId){
            return "<a href='https://autopartslist.ru/catalog/view/id/{$this->aplId}' target=_blank>{$this->code}</a>";
        }
        return;
    }

    /**
     * Ссылка на Апл
     * @param string $title
     * @return string
     */
    public function getAplIdLinkId($title = null) 
    {
        if (!$title){
            $title = $this->aplId;
        }
        if ($this->aplId){
            return "<a href='https://autopartslist.ru/catalog/view/id/{$this->aplId}' target=_blank>{$title}</a>";
        }
        return 'Товара нет в Апл';
    }
    
    public function setAplId($aplId) 
    {
        $this->aplId = $aplId;
    }     

    public function getName() 
    {
        if ($this->name){
            return $this->name;
        }
        return 'Нет названия';
    }

    public function getNameShort() 
    {
        if ($this->tokenGroup){
            if (!empty($this->tokenGroup->getName())){
                return $this->tokenGroup->getName();
            }    
        }
        return $this->getName();
    }

    public function getTitleShort() 
    {
        if ($this->tokenGroup){
            if (!empty($this->tokenGroup->getName())){
                return $this->getCode().' '.$this->tokenGroup->getName();
            }    
        }
        return $this->getCode().' '.$this->getName();
    }

    public function getInputName() 
    {
        return $this->code.';'.$this->producer->getName().';'.$this->getNameShort();
    }

    public function getNameProducerCode() 
    {
        return $this->name.' '.$this->producer->getName().' '.$this->code;
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
    
    /**
     * Получить колонки цен
     * @param float $price
     * @param float $meanPrice
     * @return array
     */
    public static function optPrices($price, $meanPrice)
    {
        $result = [];
        $priceCols = ScaleTreshold::retailPriceCols($price, $meanPrice);
        if (is_array($priceCols)){
            foreach ($priceCols as $priceCol){
                if (isset($priceCol['price'])){
                    $result[] = $priceCol['price'];
                }    
            }
        }    
        return $result;
    }

    public function getOpts()
    {
        return $this->optPrices($this->price, $this->meanPrice);
    }

    public function getOptsJson()
    {
        $opts = $this->getOpts();
        if (is_array($opts)){
            return Encoder::encode($opts);
        }
        
        return;
    }

    public function getOptsJsonEditableFormat()
    {
        $opts = $this->getOpts();
        $result = [];
        if (is_array($opts)){
            foreach ($opts as $key => $value){
                $result[] = [
                    'value' => $key,
                    'text' => $value,
                ];
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

    public function getMarketPlacePrice()
    {
        return $this->marketPlacePrice;
    }
    
    public function getMarketPlacePriceOrPrice()
    {
        return ($this->marketPlacePrice) ? $this->marketPlacePrice:$this->price;
    }

    public function setMarketPlacePrice($marketPlacePrice)
    {
        $this->marketPlacePrice = $marketPlacePrice;
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
     * @return Producer
     */    
    public function getProducer() 
    {
        return $this->producer;
    }
    
    /**
     * Задает связанный producer.
     * @param Producer $producer
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
    
    /**
     * Опции наличия
     * @return array
     */
    public static function getAvailableList()
    {
        return [
            self::AVAILABLE_TRUE => 'В наличии',
            self::AVAILABLE_APL => 'В наличии',
            self::AVAILABLE_FALSE => 'Нет в наличии',
        ];
    }
    
    public function getAvailableAsString()
    {
        $list = self::getAvailableList();
        if (isset($list[$this->available])) {
            return $list[$this->available];
        }

        return 'Нет в наличии';
    }    

    /**
     * Опции наличия
     * @return array
     */
    public static function getAvailableHtmlList()
    {
        return [
            self::AVAILABLE_TRUE => '<label class="text-primary">В наличии</label>',
            self::AVAILABLE_APL => '<label class="text-success">В наличии</label>',
            self::AVAILABLE_FALSE => '<label class="text-danger">Нет в наличии</label>',
        ];
    }
    
    public function getAvailableAsHtml()
    {
        $list = self::getAvailableHtmlList();
        if (isset($list[$this->available])) {
            return $list[$this->available];
        }

        return '<label class="text-warning">Нет в наличии</label>';
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

    public function getDatePrice() 
    {
        return $this->datePrice;
    }

    public function setDatePrice($datePrice) 
    {
        $this->datePrice = $datePrice;
    }     

    public function getRetailCount() 
    {
        return $this->retailCount;
    }

    public function setRetailCount($retailCount) 
    {
        $this->retailCount = $retailCount;
    }     

    public function getSaleMonth() {
        return $this->saleMonth;
    }

    public function setSaleMonth($saleMonth) {
        $this->saleMonth = $saleMonth;
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
     * 
     * @return array
     */
    public function getImagesAsArray() 
    {
        $result = [];
        foreach ($this->images as $image){
            $result[] = $image->toArray();
        }    
        return $result;
    }
    
    /**
     * .
     * @return int
     */
    public function getImageCount() 
    {
        return $this->images->count();
    }
    
    /**
     * Добавляет новою картинку к этому товару.
     * @param Images $image
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
     * Возвращает номера для этого товара.
     * @return array
     */
    public function getOemsAsArray() 
    {
        $result = [];
        foreach ($this->oems as $oem){
            $result[] = $oem->toArray();
        }
        
        return $result;
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
    
    /*
     * Возвращает связанный tokenGroup id.
     * @return int
     */    
    public function getTokenGroupId() 
    {
        if ($this->tokenGroup){
            if ($this->tokenGroup->getId()){
                return $this->tokenGroup->getId();                
            }
        }
        
        return;
    }

    /*
     * Возвращает связанный tokenGroup name.
     * @return int
     */    
    public function getTokenGroupName() 
    {
        if ($this->tokenGroup){
            if ($this->tokenGroup->getId()){
                return $this->tokenGroup->getName();                
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
    
    // Возвращает аттрибуты для данного товара.
    public function getAttributeValuesAsArray() 
    {
        $result = [];
        foreach ($this->attributeValues as $attributeValue){
            $result[] = [
                'id' => $attributeValue->getAttribute()->getId(),
                'name' => $attributeValue->getAttribute()->getName(),
                'value' => $attributeValue->getAttributeValue()->getValue(),
                'status' => $attributeValue->getAttribute()->getStatus()
            ];
        }
        return $result;
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
            self::OEM_FOR_UPDATE => 'alert-warning',
            self::OEM_INTERSECT => 'alert-info',
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
    
    /**
     * Опции поиска
     * @return array
     */
    public static function getSearchList()
    {
        return [
            self::SEARCH_CODE => 'Поиск по артикулу',
            self::SEARCH_APLID => 'Поиск по Apl Id',
            self::SEARCH_ID => 'Поиск по id',
            self::SEARCH_OE => 'Поиск по ОЕ',
            self::SEARCH_NAME => 'Поиск по наименованию',
            self::SEARCH_PRODUCER => 'Поиск по производителю',
            self::SEARCH_COMISS => 'На комиссии',
            self::SEARCH_TP => 'В маркетплейсах',
        ];
    }
    
    /**
     * Опции поиска для каталога
     * @return array
     */
    public static function getCatalogSearchList()
    {
        return [
            self::SEARCH_CODE => 'Поиск по артикулу',
            self::SEARCH_APLID => 'Поиск по Apl Id',
            self::SEARCH_ID => 'Поиск по id',
            self::SEARCH_OE => 'Поиск по ОЕ',
            self::SEARCH_NAME => 'Поиск по наименованию',
            self::SEARCH_PRODUCER => 'Поиск по производителю',
        ];
    }
    
    /**
     * Опции остатка
     * @return array
     */
    public static function getRestList()
    {
        return [
            self::REST_ALL => 'Все',
            self::REST_AVIALABLE => 'Доступные',
            self::REST_REST => 'В наличии',
        ];
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
    
    public function getStyleListStatusImgEx()
    {
        return [
            self::IMG_EX_NEW => '',
            self::IMG_EX_TRANSFERRED => 'alert-success',
        ];
    }
    
    public function getStyleStatusImgEx()
    {
        $list = self::getStyleListStatusImgEx();
        if (isset($list[$this->statusImgEx])) {
            return $list[$this->statusImgEx];
        }

        return '';
    }
    
    public function setStatusImgEx($statusImgEx)
    {
        $this->statusImgEx = $statusImgEx;
    }    
    
    public function getCheckOem() {
        return $this->checkOem;
    }

    public static function getCheckOemList()
    {
        return [
            self::CHECK_OEM_NO => 'Номера не проверены',
            self::CHECK_OEM_OK => 'Номера проверены',
        ];
    }
    
    public function getCheckOemAsString()
    {
        $list = self::getCheckOemList();
        if (isset($list[$this->checkOem])) {
            return $list[$this->checkOem];
        }

        return '';
    }
    
    public function setCheckOem($checkOem) {
        $this->checkOem = $checkOem;
        return $this;
    }

    public function getCheckDescription() {
        return $this->checkDescription;
    }

    public static function getCheckDescriptionList()
    {
        return [
            self::CHECK_DESCRIPTION_NO => 'Описание не проверено',
            self::CHECK_DESCRIPTION_OK => 'Описание проверено',
        ];
    }
    
    public function getCheckDescriptionAsString()
    {
        $list = self::getCheckDescriptionList();
        if (isset($list[$this->checkDescription])) {
            return $list[$this->checkDescription];
        }

        return '';
    }
    
    public function setCheckDescription($checkDescription) {
        $this->checkDescription = $checkDescription;
        return $this;
    }

    public function getCheckImage() {
        return $this->checkImage;
    }

    public static function getCheckImageList()
    {
        return [
            self::CHECK_IMAGE_OK => 'Картинка проверена',
            self::CHECK_IMAGE_NO => 'Картинка не проверена',
        ];
    }
    
    public function getCheckImageAsString()
    {
        $list = self::getCheckImageList();
        if (isset($list[$this->checkImage])) {
            return $list[$this->checkImage];
        }

        return '';
    }
    
    
    public function setCheckImage($checkImage) {
        $this->checkImage = $checkImage;
        return $this;
    }

    public function getCheckCar() {
        return $this->checkCar;
    }

    public static function getCheckCarList()
    {
        return [
            self::CHECK_CAR_NO => 'Машина не проверена',
            self::CHECK_CAR_OK => 'Машина проверена',
        ];
    }
    
    public function getCheckCarAsString()
    {
        $list = self::getCheckCarList();
        if (isset($list[$this->checkCar])) {
            return $list[$this->checkCar];
        }

        return '';
    }
    
    
    public function setCheckCar($checkCar) {
        $this->checkCar = $checkCar;
        return $this;
    }

    public static function getCheckList()
    {
        return[
          'checkOem_'.self::CHECK_OEM_NO => self::getCheckOemList()[self::CHECK_OEM_NO],  
          'checkOem_'.self::CHECK_OEM_OK => self::getCheckOemList()[self::CHECK_OEM_OK],  
          'checkDescription_'.self::CHECK_DESCRIPTION_NO => self::getCheckDescriptionList()[self::CHECK_DESCRIPTION_NO],  
          'checkDescription_'.self::CHECK_DESCRIPTION_OK => self::getCheckDescriptionList()[self::CHECK_DESCRIPTION_OK],  
          'checkImage_'.self::CHECK_IMAGE_NO => self::getCheckImageList()[self::CHECK_IMAGE_NO],  
          'checkImage_'.self::CHECK_IMAGE_OK => self::getCheckImageList()[self::CHECK_IMAGE_OK],  
          'checkCar_'.self::CHECK_CAR_NO => self::getCheckCarList()[self::CHECK_CAR_NO],  
          'checkCar_'.self::CHECK_CAR_OK => self::getCheckCarList()[self::CHECK_CAR_OK],  
        ];
    }
    
    public function getFasadeEx() {
        return $this->fasadeEx;
    }

    public function setFasadeEx($fasadeEx) {
        $this->fasadeEx = $fasadeEx;
        return $this;
    }
    
    public function getInStore()
    {
        $this->inStore;
    }
    
    public function setInStore($inStore)
    {
        $this->inStore = $inStore;
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
    
    public function getBestNameUpdateFlag()
    {
        $this->groupTokenUpdateFlag;
    }
    
    public function setGroupTokenUpdateFlag($groupTokenUpdateFlag)
    {
        $this->groupTokenUpdateFlag = $groupTokenUpdateFlag;
    }    
    
    public function getUpdWeek() 
    {
        return $this->updWeek;
    }

    public function setUpdWeek() 
    {
        $this->updWeek = date('W');
    }     
    
    /**
     * Returns the array of good tokens assigned to this good
     * @return array
     */
    public function getGoodTokens()
    {
        return $this->goodTokens;
    }        
        
    /**
     * Returns the array of good tokens assigned to this good.
     * @return array
     */
    public function getGoodTitles()
    {
        return $this->goodTitles;
    }        
        
    /**
     * Returns the array of supplier orders assigned to this good.
     * @return array
     */
    public function getSupplierOrders()
    {
        return $this->supplierOrders;
    }                

    /**
     * Returns the array of movements assigned to this good.
     * @return array
     */
    public function getMovements()
    {
        return $this->movements;
    }                

    /**
     * Returns the array of good balances assigned to this good.
     * @return array
     */
    public function getBalances()
    {
        return $this->goodBalances;
    }                

    /**
     * Returns the array of good comitent balances assigned to this good.
     * @return array
     */
    public function getComitentBalances()
    {
        return $this->comitentBalances;
    }                

    /**
     * Returns the array of good suppliers assigned to this good.
     * @return array
     */
    public function getGoodSuppliers()
    {
        return $this->goodSuppliers;
    }    
    
    /**
     * Кратность, минимальное количество
     * @return array
     */
    public function getLot()
    {
         $result = 1;
         foreach ($this->goodSuppliers as $goodSupplier){
             $result = max($result, $goodSupplier->getLot());
         }
         
         return $result;
    }    
    
    public function getFoldBalances() {
        return $this->foldBalances;
    }

    public function getGoodRelations() {
        return $this->goodRelations;
    }

    // Возвращает категории для данного товара.
    public function getCategories() 
    {
        return $this->categories;
    }      
    
    /**
     * 
     * @return array
     */
    public function getCategoriesAsArray() 
    {
        $result = [];
        foreach ($this->categories as $groupSite){
            $result[] = $groupSite->toArray();
        }    
        return $result;
    }
    
      
    /**
     * // Добавляет новую категорию к данному товару.
     * @param GroupSite $groupSite
     */
    public function addCategory($groupSite) 
    {
        $this->categories[] = $groupSite;        
    }
    
    /**
     * 
     * @param GroupSite $groupSite
     */
    public function removeCategoryAssociation($groupSite) 
    {
        $this->categories->removeElement($groupSite);
    } 
    
    /**
     * Лог
     * @return array
     */
    public function toLog()
    {
        return [
            'fixPrice' => $this->getFixPrice(),
            'inStore' => $this->getInStore(),
            'marketPlacePrice' => $this->getMarketPlacePrice(),
        ];
    }   
    
    /**
     * Массив для формы
     * @return array 
     */
    public function toArray()
    {
        $result = [
            'aplId' => $this->getAplId(),
            'description' => $this->getDescription(),
            'id' => $this->getId(),
            'code' => $this->getCode(),
            'name' => $this->getName(),
            'price' => $this->getPrice(),
            'nameShort' => $this->getNameShort(),
            'nameInput' => $this->getInputName(),
            'producer' => $this->getProducer()->toArray(),
            'group' => $this->getGenericGroup()->toArray(),
            'tokenGroup' => ($this->getTokenGroup()) ? $this->getTokenGroup()->toArray():[],
            'info' => '',
            'available' => $this->getAvailable(),
            'saleCount' => $this->getRetailCount(), 
            'saleMonth' => $this->getSaleMonth(),  
            'lot' => $this->getLot(),
        ];
        
        return $result;
    }        
    
    /**
     * Наименования из прайсов
     * @return array 
     */
    public function articleTitles()
    {
        $result = [];
        
        foreach ($this->getArticles() as $article){
            foreach ($article->getArticleTitles() as $articleTitle){
                $result[] = $articleTitle->getTitle();
            }
        }
        
        return $result;
    }
    
}
