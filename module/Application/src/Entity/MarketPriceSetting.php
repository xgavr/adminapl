<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Company\Entity\Region;
use Application\Entity\ScaleTreshold;
use Application\Entity\Rate;
use Application\Entity\Supplier;
use Application\Entity\Shipping;

/**
 * Description of Client
 * @ORM\Entity(repositoryClass="\Application\Repository\MarketRepository")
 * @ORM\Table(name="market_price_setting")
 * @author Daddy
 */
class MarketPriceSetting {
        
     // Status constants.
    const STATUS_ACTIVE       = 1; // Active pricelist.
    const STATUS_RETIRED      = 2; // Retired pricelist.
   
    const FORMAT_YML       = 1; // Формат YML.
    const FORMAT_YML_PP    = 3; // Формат YML для портала поставщиков.
    const FORMAT_XLSX      = 2; // Формат XLSX.
   
    const IMAGE_MATH       = 1; // Картирки точные
    const IMAGE_SIMILAR    = 2; // Картинки точные и похожие.
    const IMAGE_ALL        = 3; // С картинками или без.

    const NAME_GENERATED  = 1; // Наименования сгенерированные
    const NAME_ALL        = 2; // Наименования любые.

    const REST_AVAILABILITY  = 1; // Только наличие
    const REST_ALL        = 2; // Все остатки.

    const SUPPLIER_TAGGED   = 1; // Поставщики меченные.
    const SUPPLIER_ALL      = 2; // Поставщики все.

    const PRODUCER_ACTIVE   = 1; // Производители с движением.
    const PRODUCER_ALL      = 2; // Производители все.

    const GROUP_ACTIVE   = 1; // Группы с движением.
    const GROUP_ALL      = 2; // Группы все.

    const TOKEN_GROUP_ACTIVE   = 1; // Группы с движением.
    const TOKEN_GROUP_ALL      = 2; // Группы все.
    
    const CONSIDER_SET      = 1; // Цена за комплект.
    const CONSIDER_SET_NO      = 2; // Цена за одну штуку.

    const TD_IGNORE   = 1; // Товары все.
    const TD_MATH      = 2; // Товары с ТД.
    
    const RATE_IGNORE   = 1; // Расценки все.
    const RATE_INCLUDE  = 2; // Расценки включить.
    const RATE_EXCLUDE  = 3; // Расценки исключить.
    
    const DESCRIPTION_FORMAT_HTML = 1; //HTML
    const DESCRIPTION_FORMAT_TEXT = 2; //TEXT
        
    const DESCRIPTION_SET_NAME_COMMENT     = 1; //Наименование и комментариу
    const DESCRIPTION_SET_NAME_TD_COMMENT  = 2; //Наименование, текдок и комментарий

    const MOVEMENT_LIMIT   = 100; //Лимит для определения активности по движению
    const MAX_BLOCK_ROW_COUNT = 50000; // Максимально строк в блоке прайса
    const MAX_BLOCK_COUNT = 20; // Максимально блоков прайса
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
        
    /** 
     * @ORM\Column(name="status")  
     */
    protected $status;

    /**
     * Наименование настройки
     * @ORM\Column(name="name")   
     */
    protected $name;
    
    /**
     * Наименование файла выгрузки
     * @ORM\Column(name="filename")   
     */
    protected $filename;

    /**
     * Формат выгрузки
     * @ORM\Column(name="format")   
     */
    protected $format;

    /**
     * Фильтр по товарам
     * @ORM\Column(name="good_setting")   
     */
    protected $goodSetting;

    /**
     * Фильтр по наименованиям
     * @ORM\Column(name="name_setting")   
     */
    protected $nameSetting;

    /**
     * Фильтр по наличию
     * @ORM\Column(name="rest_setting")   
     */
    protected $restSetting;

    /**
     * Количество картинок в оферте
     * @ORM\Column(name="image_count")   
     */
    protected $imageCount;

    /**
     * Количество картинок в оферте
     * @ORM\Column(name="extra_margin")   
     */
    protected $extraMargin;

    /**
     * Фильтр по поставщикам
     * @ORM\Column(name="supplier_setting")   
     */
    protected $supplierSetting;

    /**
     * Фильтр по производителям
     * @ORM\Column(name="producer_setting")   
     */
    protected $producerSetting;

    /**
     * Фильтр по группам ТД
     * @ORM\Column(name="group_setting")   
     */
    protected $groupSetting;

    /**
     * Фильтр по группам наименований
     * @ORM\Column(name="token_group_setting")   
     */
    protected $tokenGroupSetting;

    /**
     * Фильтр по связи с ТД
     * @ORM\Column(name="td_setting")   
     */
    protected $tdSetting;

    /**
     * Использование расценок
     * @ORM\Column(name="rate_setting")   
     */
    protected $rateSetting;

    /**
     * Цена за комплект
     * @ORM\Column(name="consider_set")   
     */
    protected $considerSet;

    /**
     * Формат описания
     * @ORM\Column(name="description_format")   
     */
    protected $descriptionFormat;

    /**
     * Состав описания
     * @ORM\Column(name="description_set")   
     */
    protected $descriptionSet;
    
    /**
     * Минимальная цена в прайсе
     * @ORM\Column(name="min_price")   
     */
    protected $minPrice;

    /**
     * Максимальная цена в прайсе
     * @ORM\Column(name="max_price")   
     */
    protected $maxPrice;

    /**
     * Максимальное количество записей в прайсе
     * @ORM\Column(name="max_row_count")   
     */
    protected $maxRowCount;

    /**
     * Разбивать на блоки по указанному количеству записей
     * @ORM\Column(name="block_row_count")   
     */
    protected $blockRowCount;
    
    /**
     * Колонка цен
     * @ORM\Column(name="pricecol")   
     */
    protected $pricecol;
    
    /**
     * Фильтр количества движений
     * @ORM\Column(name="movement_limit")   
     */
    protected $movementLimit;
    
    /**
     * Описание настройки
     * @ORM\Column(name="info")   
     */
    protected $info;
    
    /**
     * Дата последней выгрузки
     * @ORM\Column(name="date_unload")   
     */
    protected $dateUnload;
    
    /**
     * Строк выгружено
     * @ORM\Column(name="row_unload")   
     */
    protected $rowUnload = 0;
    
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Region", inversedBy="pricelistsettings") 
     * @ORM\JoinColumn(name="region_id", referencedColumnName="id")
     */
    private $region;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Supplier", inversedBy="pricelistsettings") 
     * @ORM\JoinColumn(name="supplier_id", referencedColumnName="id")
     */
    private $supplier;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Shipping", inversedBy="pricelistsettings") 
     * @ORM\JoinColumn(name="shipping_id", referencedColumnName="id")
     */
    private $shipping;
    
    /**
     * @ORM\ManyToMany(targetEntity="Application\Entity\Rate")
     * @ORM\JoinTable(name="market_rate",
     *      joinColumns={@ORM\JoinColumn(name="market_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="rate_id", referencedColumnName="id")}
     *      )
     */
    private $rates;
    
    /**
     * Constructor.
     */
    public function __construct() 
    {
        $this->rates = new ArrayCollection();
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
        $this->name = $name;
    }     

    public function getFilename() 
    {
        return $this->filename;
    }

    public function setFilename($filename) 
    {
        $this->filename = $filename;
    }     

    public function getImageCount() 
    {
        return $this->imageCount;
    }

    public function getImageCountOrNull() 
    {
        if ($this->imageCount){
            return $this->imageCount;
        }        
        return NULL;
    }

    public function setImageCount($imageCount) 
    {
        $this->imageCount = $imageCount;
    }     

    public function getExtraMargin() 
    {
        return $this->extraMargin;
    }

    public function getExtraMarginView() 
    {
        if ($this->extraMargin > 0){
            return ' + '.$this->extraMargin.'%';
        }
        if ($this->extraMargin < 0){
            return ' - '.$this->extraMargin.'%';
        }
        return;
    }
    
    /**
     * Получить цену с дополнительной наценкой
     * @param array $opts Колонки цен
     * @param integer $lot минимальное количество
     * @return float
     */
    public function getExtraPrice($opts, $lot = 1)
    {
        $result = $opts[$this->pricecol];
        if ($this->extraMargin){
            $result += round($result*$this->extraMargin/100, -1);
        }
        
        if ($this->considerSet == $this::CONSIDER_SET){
            $result *= $lot;
        }
        return $result;
    }

    public function setExtraMargin($extraMargin) 
    {
        $this->extraMargin = $extraMargin;
    }     

    public function getSupplierSetting() 
    {
        return $this->supplierSetting;
    }

    /**
     * Returns possible supplier as array.
     * @return array
     */
    public static function getSupplierSettingList() 
    {
        return [
            self::SUPPLIER_TAGGED => 'Помеченные',
            self::SUPPLIER_ALL => 'Все'
        ];
    }    
    
    /**
     * Returns supplier as string.
     * @return string
     */
    public function getSupplierSettingAsString()
    {
        $list = self::getSupplierSettingList();
        if (isset($list[$this->supplierSetting]))
            return $list[$this->supplierSetting];
        
        return 'Unknown';
    }    

    public function setSupplierSetting($supplierSetting) 
    {
        $this->supplierSetting = $supplierSetting;
    }     

    public function getProducerSetting() 
    {
        return $this->producerSetting;
    }

    /**
     * Returns possible producer as array.
     * @return array
     */
    public static function getProducerSettingList() 
    {
        return [
            self::PRODUCER_ACTIVE => 'Активные',
            self::PRODUCER_ALL => 'Все'
        ];
    }    
    
    /**
     * Returns producer as string.
     * @return string
     */
    public function getProducerSettingAsString()
    {
        $list = self::getProducerSettingList();
        if (isset($list[$this->producerSetting]))
            return $list[$this->producerSetting];
        
        return 'Unknown';
    }    

    public function setProducerSetting($producerSetting) 
    {
        $this->producerSetting = $producerSetting;
    }     

    public function getGroupSetting() 
    {
        return $this->groupSetting;
    }

    /**
     * Returns possible group as array.
     * @return array
     */
    public static function getGroupSettingList() 
    {
        return [
            self::GROUP_ACTIVE => 'Активные',
            self::GROUP_ALL => 'Все'
        ];
    }    
    
    /**
     * Returns group as string.
     * @return string
     */
    public function getGroupSettingAsString()
    {
        $list = self::getGroupSettingList();
        if (isset($list[$this->groupSetting]))
            return $list[$this->groupSetting];
        
        return 'Unknown';
    }    

    public function setGroupSetting($groupSetting) 
    {
        $this->groupSetting = $groupSetting;
    }     

    public function getTokenGroupSetting() 
    {
        return $this->tokenGroupSetting;
    }

    /**
     * Returns possible token group as array.
     * @return array
     */
    public static function getTokenGroupSettingList() 
    {
        return [
            self::TOKEN_GROUP_ACTIVE => 'Активные',
            self::TOKEN_GROUP_ALL => 'Все'
        ];
    }    
    
    /**
     * Returns token group as string.
     * @return string
     */
    public function getTokenGroupSettingAsString()
    {
        $list = self::getTokenGroupSettingList();
        if (isset($list[$this->tokenGroupSetting]))
            return $list[$this->tokenGroupSetting];
        
        return 'Unknown';
    }    

    public function setTokenGroupSetting($tokenGroupSetting) 
    {
        $this->tokenGroupSetting = $tokenGroupSetting;
    }     

    public function getMinPrice() 
    {
        return $this->minPrice;
    }

    public function setMinPrice($minPrice) 
    {
        $this->minPrice = $minPrice;
    }     

    public function getMaxPrice() 
    {
        return $this->maxPrice;
    }

    public function setMaxPrice($maxPrice) 
    {
        $this->maxPrice = $maxPrice;
    }     
    
    /**
     * Количество строк в блоке
     * @return integer
     */
    public function getMaxRowCount() 
    {
        return $this->maxRowCount;
    }

    public function setMaxRowCount($maxRowCount) 
    {
        $this->maxRowCount = $maxRowCount;
    }     

    /**
     * Количество блоков прайса по maxRowCount
     * @return integer
     */
    public function getBlockRowCount() 
    {
        return $this->blockRowCount;
    }

    public function setBlockRowCount($blockRowCount) 
    {
        $this->blockRowCount = $blockRowCount;
    }     

    public function getPricecol() 
    {
        return $this->pricecol;
    }

    public function getPricecolAsString() 
    {
        return ScaleTreshold::getPricecolAsString($this->pricecol);
    }

    public function setPricecol($pricecol) 
    {
        $this->pricecol = $pricecol;
    }     

    public function getMovementLimit() 
    {
        return $this->movementLimit;
    }

    public function setMovementLimit($movementLimit) 
    {
        $this->movementLimit = $movementLimit;
    }     

    public function getInfo() 
    {
        return $this->info;
    }

    public function setInfo($info) 
    {
        $this->info = $info;
    }     

    public function getDateUnload() 
    {
        return $this->dateUnload;
    }

    public function setDateUnload($dateUnload) 
    {
        $this->dateUnload = $dateUnload;
    }     

    public function getRowUnload() 
    {
        return $this->rowUnload;
    }

    public function setRowUnload($rowUnload) 
    {
        $this->rowUnload = $rowUnload;
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
            self::STATUS_ACTIVE => 'Ежедневно',
            self::STATUS_RETIRED => 'Разово'
        ];
    }    
    
    /**
     * Returns status as string.
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
     * Sets status.
     * @param int $status     
     */
    public function setStatus($status) 
    {
        $this->status = $status;
    }   
 
    /**
     * Returns format.
     * @return int     
     */
    public function getFormat() 
    {
        return $this->format;
    }
    
    /**
     * Returns format ext.
     * @return int     
     */
    public function getFilenameExt($suffix = '') 
    {
        if ($this->format == self::FORMAT_XLSX){
            return $this->filename.$this->id.'.xlsx';
        }
        if ($this->format == self::FORMAT_YML || $this->format == self::FORMAT_YML_PP){
            return $this->filename.$this->id.'.yml';
        }
        
        return $this->filename.$this->id.'.dat';
    }
    

    /**
     * Returns format ext block.
     * @param integer $block
     * @return int     
     */
    public function getBlockFilenameExt($block = 0) 
    {
        if ($this->format == self::FORMAT_XLSX){
            return $this->filename.$this->id.'_'.str_pad($block, 2, '0', STR_PAD_LEFT).'.xlsx';
        }
        if ($this->format == self::FORMAT_YML || $this->format == self::FORMAT_YML_PP){
            return $this->filename.$this->id.'_'.str_pad($block, 2, '0', STR_PAD_LEFT).'.yml';
        }
        
        return $this->filename.$this->id.'_'.str_pad($block, 2, '0', STR_PAD_LEFT).'.dat';
    }
    
    /**
     * Returns format ext offset.
     * @param integer $offset
     * @return int     
     */
    public function getOffsetFilenameExt($offset = 0) 
    {
        $block = ceil($offset/(($this->maxRowCount) ? $this->maxRowCount:self::MAX_BLOCK_ROW_COUNT));
        return $this->getBlockFilenameExt($block);
    }

    /**
     * Returns format zip.
     * @return string     
     */
    public function getFilenameZip() 
    {
        return $this->filename.$this->id.'.zip';
    }
    
    /**
     * Returns format zip.
     * @param integer $block
     * @return string     
     */
    public function getBlockFilenameZip($block = 0) 
    {
        return $this->filename.$this->id.'_'.str_pad($block, 2, '0', STR_PAD_LEFT).'.zip';
    }
    
    /**
     * Returns format zip.
     * @param integer $offset
     * @return string     
     */
    public function getOffsetFilenameZip($offset = 0) 
    {
        $block = ceil($offset/(($this->maxRowCount) ? $this->maxRowCount:self::MAX_BLOCK_ROW_COUNT));
        return $this->getBlockFilenameZip($block);
    }
    
    /**
     * Returns possible formats as array.
     * @return array
     */
    public static function getFormatList() 
    {
        return [
            self::FORMAT_YML => 'YML',
            self::FORMAT_YML_PP => 'YML (zakupki.mos.ru)',
            self::FORMAT_XLSX => 'XLSX'
        ];
    }    
    
    /**
     * Returns price format as string.
     * @return string
     */
    public function getFormatAsString()
    {
        $list = self::getFormatList();
        if (isset($list[$this->format]))
            return $list[$this->format];
        
        return 'Unknown';
    }    
    
    /**
     * Sets format.
     * @param int $format     
     */
    public function setFormat($format) 
    {
        $this->format = $format;
    }   
 
    /**
     * Returns good setting.
     * @return int     
     */
    public function getGoodSetting() 
    {
        return $this->goodSetting;
    }
    
    /**
     * Returns possible good setting as array.
     * @return array
     */
    public static function getGoodSettingList() 
    {
        return [
            self::IMAGE_MATH => 'Точные',
            self::IMAGE_SIMILAR => 'Похожие',
            self::IMAGE_ALL => 'Все'
        ];
    }    
    
    /**
     * Returns good setting as string.
     * @return string
     */
    public function getGoodSettingAsString()
    {
        $list = self::getGoodSettingList();
        if (isset($list[$this->goodSetting]))
            return $list[$this->goodSetting];
        
        return 'Unknown';
    }    
    
    /**
     * Sets goodSetting.
     * @param int $goodSetting     
     */
    public function setGoodSetting($goodSetting) 
    {
        $this->goodSetting = $goodSetting;
    }   

    /**
     * Returns name setting.
     * @return int     
     */
    public function getNameSetting() 
    {
        return $this->nameSetting;
    }
    
    /**
     * Returns possible name setting as array.
     * @return array
     */
    public static function getNameSettingList() 
    {
        return [
            self::NAME_GENERATED => 'Сгенерированные',
            self::NAME_ALL => 'Любые'
        ];
    }    
    
    /**
     * Returns name setting as string.
     * @return string
     */
    public function getNameSettingAsString()
    {
        $list = self::getNameSettingList();
        if (isset($list[$this->nameSetting]))
            return $list[$this->nameSetting];
        
        return 'Unknown';
    }    
    
    /**
     * Sets nameSetting.
     * @param int $nameSetting     
     */
    public function setNameSetting($nameSetting) 
    {
        $this->nameSetting = $nameSetting;
    }   

    /**
     * Returns rest setting.
     * @return int     
     */
    public function getRestSetting() 
    {
        return $this->restSetting;
    }
    
    /**
     * Returns possible rest setting as array.
     * @return array
     */
    public static function getRestSettingList() 
    {
        return [
            self::REST_AVAILABILITY => 'Наличие',
            self::REST_ALL => 'Все'
        ];
    }    
    
    /**
     * Returns rest setting as string.
     * @return string
     */
    public function getRestSettingAsString()
    {
        $list = self::getRestSettingList();
        if (isset($list[$this->restSetting]))
            return $list[$this->restSetting];
        
        return 'Unknown';
    }    
    
    /**
     * Sets restSetting.
     * @param int $restSetting     
     */
    public function setRestSetting($restSetting) 
    {
        $this->restSetting = $restSetting;
    }   

    /**
     * Returns td setting.
     * @return int     
     */
    public function getTdSetting() 
    {
        return $this->tdSetting;
    }
    
    /**
     * Returns possible td setting as array.
     * @return array
     */
    public static function getTdSettingList() 
    {
        return [
            self::TD_IGNORE => 'Неважно',
            self::TD_MATH => 'Есть'
        ];
    }    
    
    /**
     * Returns td setting as string.
     * @return string
     */
    public function getTdSettingAsString()
    {
        $list = self::getTdSettingList();
        if (isset($list[$this->tdSetting]))
            return $list[$this->tdSetting];
        
        return 'Unknown';
    }    
    
    /**
     * Sets tdSetting.
     * @param int $tdSetting     
     */
    public function setTdSetting($tdSetting) 
    {
        $this->tdSetting = $tdSetting;
    }   
    
    /**
     * Returns rate setting.
     * @return int     
     */
    public function getRateSetting() 
    {
        return $this->rateSetting;
    }
    
    /**
     * Returns possible td setting as array.
     * @return array
     */
    public static function getRateSettingList() 
    {
        return [
            self::RATE_IGNORE => 'Игнорировать',
            self::RATE_INCLUDE => 'Включить',
            self::RATE_EXCLUDE => 'Исключить'
        ];
    }    
    
    /**
     * Returns rate setting as string.
     * @return string
     */
    public function getRateSettingAsString()
    {
        $list = self::getRateSettingList();
        if (isset($list[$this->rateSetting]))
            return $list[$this->rateSetting];
        
        return 'Unknown';
    }    
    
    /**
     * Sets rateSetting.
     * @param int $rateSetting     
     */
    public function setRateSetting($rateSetting) 
    {
        $this->rateSetting = $rateSetting;
    }   

    /**
     * Returns consider set.
     * @return int     
     */
    public function getConsiderSet() 
    {
        return $this->considerSet;
    }
    
    /**
     * Returns possible consider set as array.
     * @return array
     */
    public static function getConsiderSetList() 
    {
        return [
            self::CONSIDER_SET => 'Цена за комплект',
            self::CONSIDER_SET_NO => 'Цена за 1 шт'
        ];
    }    
    
    /**
     * Returns consider set as string.
     * @return string
     */
    public function getConsiderSetAsString()
    {
        $list = self::getConsiderSetList();
        if (isset($list[$this->considerSet]))
            return $list[$this->considerSet];
        
        return 'Unknown';
    }    
    
    /**
     * Sets considerSet.
     * @param int $considerSet     
     */
    public function setConsiderSet($considerSet) 
    {
        $this->considerSet = $considerSet;
    }   

    /**
     * Returns description format.
     * @return int     
     */
    public function getDescriptionFormat() 
    {
        return $this->descriptionFormat;
    }
    
    /**
     * Returns possible descriptionFormat as array.
     * @return array
     */
    public static function getDescriptionFormatList() 
    {
        return [
            self::DESCRIPTION_FORMAT_HTML => 'HTML',
            self::DESCRIPTION_FORMAT_TEXT => 'TEXT'
        ];
    }    
    
    /**
     * Returns descriptionFormat as string.
     * @return string
     */
    public function getDescriptionFormatAsString()
    {
        $list = self::getDescriptionFormatList();
        if (isset($list[$this->descriptionFormat]))
            return $list[$this->descriptionFormat];
        
        return 'Unknown';
    }    
    
    /**
     * Sets descriptionFormat.
     * @param int $descriptionFormat     
     */
    public function setDescriptionFormat($descriptionFormat) 
    {
        $this->descriptionFormat = $descriptionFormat;
    }   

    
    /**
     * Returns description set.
     * @return int     
     */
    public function getDescriptionSet() 
    {
        return $this->descriptionSet;
    }
    
    /**
     * Returns possible descriptionSet as array.
     * @return array
     */
    public static function getDescriptionSetList() 
    {
        return [
            self::DESCRIPTION_SET_NAME_COMMENT => 'Наименование и коментарий',
            self::DESCRIPTION_SET_NAME_TD_COMMENT => 'Наименование,текдок и комментарий'
        ];
    }    
    
    /**
     * Returns descriptionSet as string.
     * @return string
     */
    public function getDescriptionSetAsString()
    {
        $list = self::getDescriptionSetList();
        if (isset($list[$this->descriptionSet]))
            return $list[$this->descriptionSet];
        
        return 'Unknown';
    }    
    
    /**
     * Sets descriptionSet.
     * @param int $descriptionSet     
     */
    public function setDescriptionSet($descriptionSet) 
    {
        $this->descriptionSet = $descriptionSet;
    }   

    public function getRegion() 
    {
        return $this->region;
    }

    /**
     * 
     * @param Region $region
     */
    public function setRegion($region) 
    {
        $this->region = $region;        
    }                 
    
    /**
     * 
     * @return Supplier
     */
    public function getSupplier() 
    {
        return $this->supplier;
    }

    /**
     * 
     * @param Supplier $supplier
     */
    public function setSupplier($supplier) 
    {
        $this->supplier = $supplier;        
    }                 

    /**
     * 
     * @return Shipping
     */
    public function getShipping() 
    {
        return $this->shipping;
    }

    /**
     * 
     * @param Shipping $shipping
     */
    public function setShipping($shipping) 
    {
        $this->shipping = $shipping;        
    }                 

    /**
     * 
     * @return array
     */
    public function getRates()
    {
        return $this->rates;
    }
    
    /**
     * Returns the string of assigned rate names.
     */
    public function getRatesAsString()
    {
        $rateList = '';
        
        $count = count($this->rates);
        if (!$count) return 'Все';
        $i = 0;
        foreach ($this->rates as $rate) {
            $rateList .= $rate->getName();
            if ($i<$count-1)
                $rateList .= ', ';
            $i++;
        }
        
        return $rateList;
    }
    
    public function getRatesAsArray()
    {
        $rateList = [];
        
        foreach ($this->rates as $rate) {
            $rateList[] = $rate->getId();
        }
        
        return $rateList;
    }

    /**
     * Assigns a rate to market.
     * @param Rate $rate
     */
    public function addRate($rate)
    {
        $this->rates->add($rate);
    }    
    
    /**
     * Массив для формы
     * @return array 
     */
    public function toArray()
    {
        $result = [
            'status' => $this->getStatus(),
            'name' => $this->getName(),
            'filename' => $this->getFilename(),
            'format' => $this->getFormat(),
            'goodSetting' => $this->getGoodSetting(),
            'nameSetting' => $this->getNameSetting(),
            'restSetting' => $this->getRestSetting(),
            'imageCount' => $this->getImageCount(),
            'extraMargin' => $this->getExtraMargin(),
            'supplierSetting' => $this->getSupplierSetting(),
            'producerSetting' => $this->getProducerSetting(),
            'tokenGroupSetting' => $this->getTokenGroupSetting(),
            'groupSetting' => $this->getGroupSetting(),
            'minPrice' => $this->getMinPrice(),
            'maxPrice' => $this->getMaxPrice(),
            'maxRowCount' => $this->getMaxRowCount(),
            'blockRowCount' => $this->getBlockRowCount(),
            'pricecol' => $this->getPricecol(),
            'info' => $this->getInfo(),
            'region' => $this->getRegion()->getId(),
            'supplier' => ($this->getSupplier()) ? $this->getSupplier()->getId():null,
            'shipping' => ($this->getShipping()) ? $this->getShipping()->getId():null,
            'rates' => $this->getRatesAsArray(),
            'tdSetting' => $this->getTdSetting(),
            'rateSetting' => $this->getRateSetting(),
            'considerSet' => $this->getConsiderSet(),
            'descriptionFormat' => $this->getDescriptionFormat(),
            'descriptionSet' => $this->getDescriptionSet(),
            'movementLimit' => $this->getMovementLimit(),
        ];
        
        return $result;
    }
}
