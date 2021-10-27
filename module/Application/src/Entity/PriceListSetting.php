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
 * @ORM\Table(name="price_list_setting")
 * @author Daddy
 */
class PriceListSetting {
        
     // Status constants.
    const STATUS_ACTIVE       = 1; // Active pricelist.
    const STATUS_RETIRED      = 2; // Retired pricelist.
   
    const format_YML       = 1; // Формат YML.
    const format_XLS      = 2; // Формат XLS.
   
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

    public function getInfo() 
    {
        return $this->info;
    }

    public function setInfo($info) 
    {
        $this->info = $info;
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
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_RETIRED => 'Retired'
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
