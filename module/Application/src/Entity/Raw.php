<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Application\Filter\Basename;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Rawprice
 * @ORM\Entity(repositoryClass="\Application\Repository\RawRepository")
 * @ORM\Table(name="raw")
 * @author Daddy
 */
class Raw {
    
     // Supplier status constants.
    const STATUS_ACTIVE       = 1; // Active raw.
    const STATUS_RETIRED      = 2; // Retired raw.
    const STATUS_PARSED       = 3; //Разобран
    const STATUS_LOAD         = 4; //В процессе загрузки
    const STATUS_PARSE        = 5; //Разбирается
    const STATUS_FAILED       = 6; // Не удалось загрузить.
    const STATUS_PRE_RETIRED  = 7; // Удалить после разбора.
    
    const STAGE_NOT                 = 1; //поля не разобраны
    const STAGE_PRODUCER_PARSED     = 2; //производители разобраны 
    const STAGE_ARTICLE_PARSED      = 3; //артикулы разобраны 
    const STAGE_OEM_PARSED          = 4; //номера замен разобраны
    const STAGE_TOKEN_PARSED        = 5; //наименования разобраны
    const STAGE_PRODUCER_ASSEMBLY   = 6; //производители собраны
    const STAGE_GOOD_ASSEMBLY       = 7; //карточки товара собрана
    const STAGE_PRICE_UPDATET       = 8; //цены рассчитаны
    const STAGE_GOOD_TOKEN          = 9; //токены товаров
    const STAGE_TOKEN_GROUP_PARSED  = 10; //группы наименований разобраны
    const STAGE_DESCRIPTION         = 11; // обновлены описания
    const STAGE_BEST_NAME           = 12; //наименования обновлены
    
    const EX_NEW = 1;   // прайс готов для передачи
    const EX_TO_TRANSFER = 2; //прайс готов к передаче
    const EX_TRANSFERED = 3; //прайс передан
    const EX_TO_DELETE = 4; //прайс нужно удалить
    const EX_DELETED = 5; //прайс удален
    
           
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
     * @ORM\Column(name="filename")   
     */
    protected $filename;

    /**
     * @ORM\Column(name="rows")   
     */
    protected $rows;

    /** 
     * @ORM\Column(name="status")  
     */
    protected $status;
    
    /** 
     * @ORM\Column(name="status_ex")  
     */
    protected $statusEx = self::EX_NEW;
    
    /** 
     * @ORM\Column(name="parse_stage")  
     */
    protected $parseStage = self::STAGE_NOT;

    /** 
     * @ORM\Column(name="date_created")  
     */
    protected $dateCreated;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Supplier", inversedBy="raw") 
     * @ORM\JoinColumn(name="supplier_id", referencedColumnName="id")
     */
    private $supplier;
    
    /**
    * @ORM\OneToMany(targetEntity="Application\Entity\Rawprice", mappedBy="raw")
    * @ORM\JoinColumn(name="id", referencedColumnName="raw_id")
     */
    private $rawprice;
    
    /**
     * Constructor.
     */
    public function __construct() 
    {
        $this->rawprice = new ArrayCollection();
    }
    
    
    public function getId() 
    {
        return $this->id;
    }

    public function setId($id) 
    {
        $this->id = $id;
    }         

    public function getFilename() 
    {
        return $this->filename;
    }

    public function getBasename() 
    {
        $basenameFilter = new Basename();
        return $basenameFilter->filter($this->getFilename());
    }

    public function setFilename($filename) 
    {
        $this->filename = $filename;
    }     

    public function getName() 
    {
        return $this->name;
    }

    public function setName($name) 
    {
        $this->name = $name;
    }     

    public function getRows() 
    {
        return $this->rows;
    }

    public function setRows($rows) 
    {
        $this->rows = $rows;
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
            self::STATUS_ACTIVE => 'Новый',
            self::STATUS_RETIRED => 'Удалить',
            self::STATUS_PARSED => 'Разобран',
            self::STATUS_PARSE => 'Разбирается',
            self::STATUS_LOAD => 'Загружается',
            self::STATUS_FAILED => 'Не удалось загрузить',
            self::STATUS_PRE_RETIRED => 'Устарел'
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
    
    public static function getStatusName($status)
    {
        $list = self::getStatusList();
        if (isset($list[$status]))
            return $list[$status];
        
        return 'Unknown';        
    }
        
    public function getStatusActive()
    {
        return self::STATUS_ACTIVE;
    }        
    
    
    public function getStatusRetired()
    {
        return self::STATUS_RETIRED;
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
     * Returns statusEx.
     * @return int     
     */
    public function getStatusEx() 
    {
        return $this->statusEx;
    }

    /**
     * Returns possible statuses as array.
     * @return array
     */
    public static function getStatusExList() 
    {
        return [
            self::EX_NEW => 'Новый',
            self::EX_TO_TRANSFER => 'Готов к передаче',
            self::EX_TRANSFERED => 'Передан',
            self::EX_TO_DELETE => 'Готов к удалению',
            self::EX_DELETED => 'Удален',
        ];
    }    
    
    /**
     * Returns user statusEx as string.
     * @return string
     */
    public function getStatusExAsString()
    {
        $list = self::getStatusExList();
        if (isset($list[$this->statusEx]))
            return $list[$this->statusEx];
        
        return 'Unknown';
    }    
    
    public function getStatusExName($statusEx)
    {
        $list = self::getStatusExList();
        if (isset($list[$statusEx]))
            return $list[$statusEx];
        
        return 'Unknown';        
    }
        
    /**
     * Sets statusEx.
     * @param int $statusEx     
     */
    public function setStatusEx($statusEx) 
    {
        $this->statusEx = $statusEx;
    }   

    /**
     * Returns parseStage.
     * @return int     
     */
    public function getParseStage() 
    {
        return $this->parseStage;
    }

    /**
     * Returns possible parseStage as array.
     * @return array
     */
    public static function getParseStageList() 
    {
        return [
            self::STAGE_NOT => 'Производители не разобраны',
            self::STAGE_PRODUCER_PARSED => 'Производители разобраны',
            self::STAGE_ARTICLE_PARSED => 'Артикулы разобраны',
            self::STAGE_OEM_PARSED => 'Номера замен разобраны',
            self::STAGE_TOKEN_PARSED => 'Наименования разобраны',
            self::STAGE_PRODUCER_ASSEMBLY => 'Производители собраны',
            self::STAGE_GOOD_ASSEMBLY => 'Товары собраны',
            self::STAGE_PRICE_UPDATET => 'Цены рассчитаны',
            self::STAGE_GOOD_TOKEN => 'Токены товаров разобраны',
            self::STAGE_TOKEN_GROUP_PARSED => 'Группы наименований разобраны',
            self::STAGE_DESCRIPTION => 'Описания обновлены',
            self::STAGE_BEST_NAME => 'Наименования обновлены',
        ];
    }    
    
    /**
     * Returns parseStage as string.
     * @return string
     */
    public function getParseStageAsString()
    {
        $list = self::getParseStageList();
        if (isset($list[$this->parseStage]))
            return $list[$this->parseStage];
        
        return 'Unknown';
    }    
    
    public static function getParseStageName($parseStage)
    {
        $list = self::getParseStageList();
        if (isset($list[$parseStage]))
            return $list[$parseStage];
        
        return 'Unknown';        
    }
        
    /**
     * Sets parseStage.
     * @param int $parseStage     
     */
    public function setParseStage($parseStage) 
    {
        $this->parseStage = $parseStage;
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
     * Возвращает связанный supplier.
     * @return \Application\Entity\Supplier
     */    
    public function getSupplier() 
    {
        return $this->supplier;
    }

    /**
     * Задает связанный supplier.
     * @param \Application\Entity\Supplier $supplier
     */    
    public function setSupplier($supplier) 
    {
        $this->supplier = $supplier;
        $supplier->addRaw($this);
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
    
}
