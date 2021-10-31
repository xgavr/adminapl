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
    const FORMAT_XLS      = 2; // Формат XLS.
   
    const IMAGE_MATH       = 1; // Картирки точные
    const IMAGE_SIMILAR    = 2; // Картинки точные и похожие.
    const IMAGE_ALL        = 3; // Картинки все.

    const SUPPLIER_TAGGED   = 1; // Поставщики меченные.
    const SUPPLIER_ALL      = 2; // Поставщики все.

    const PRODUCER_ACTIVE   = 1; // Производители с движением.
    const PRODUCER_ALL      = 2; // Производители все.

    const GROUP_ACTIVE   = 1; // Группы с движением.
    const GROUP_ALL      = 2; // Группы все.

    const TOKEN_GROUP_ACTIVE   = 1; // Группы с движением.
    const TOKEN_GROUP_ALL      = 2; // Группы все.

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
     * Количество картинок в оферте
     * @ORM\Column(name="image_count")   
     */
    protected $imageCount;

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
     * @ORM\ManyToOne(targetEntity="Company\Entity\Region", inversedBy="pricelistsettings") 
     * @ORM\JoinColumn(name="region_id", referencedColumnName="id")
     */
    private $region;
    
    /**
     * Constructor.
     */
    public function __construct() 
    {
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

    public function setImageCount($imageCount) 
    {
        $this->imageCount = $imageCount;
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

    public function getMaxRowCount() 
    {
        return $this->maxRowCount;
    }

    public function setMaxRowCount($maxRowCount) 
    {
        $this->maxRowCount = $maxRowCount;
    }     

    public function getBlockRowCount() 
    {
        return $this->blockRowCount;
    }

    public function setBlockRowCount($blockRowCount) 
    {
        $this->blockRowCount = $blockRowCount;
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
            self::STATUS_ACTIVE => 'Выгружать',
            self::STATUS_RETIRED => 'Отключен'
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
     * Returns possible formats as array.
     * @return array
     */
    public static function getFormatList() 
    {
        return [
            self::FORMAT_YML => 'YML',
            self::FORMAT_XLS => 'XLS'
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
            self::IMAGE_ALL => 'Любые'
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
    
}
