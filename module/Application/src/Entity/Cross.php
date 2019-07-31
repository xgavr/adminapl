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
 * Description of Cross
 * @ORM\Entity(repositoryClass="\Application\Repository\CrossRepository")
 * @ORM\Table(name="cross_")
 * @author Daddy
 */
class Cross {
    
     // Supplier status constants.
    const STATUS_ACTIVE       = 1; // Active raw.
    const STATUS_RETIRED      = 2; // Retired raw.
    const STATUS_PARSED       = 3; //Разобран
    const STATUS_LOAD         = 4; //В процессе загрузки
    const STATUS_PARSE        = 5; //Разбирается
    const STATUS_FAILED       = 6; // Не удалось загрузить.
    const STATUS_PRE_RETIRED  = 7; // Удалить после разбора.
        
           
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="filename")   
     */
    protected $filename;

    /**
     * @ORM\Column(name="row_count")   
     */
    protected $rowCount;

    /** 
     * @ORM\Column(name="status")  
     */
    protected $status;
    
    /** 
     * @ORM\Column(name="description")  
     */
    protected $description;

    /** 
     * @ORM\Column(name="date_created")  
     */
    protected $dateCreated;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Supplier", inversedBy="crosses") 
     * @ORM\JoinColumn(name="supplier_id", referencedColumnName="id")
     */
    private $supplier;
    
    /**
    * @ORM\OneToMany(targetEntity="Application\Entity\CrossList", mappedBy="cross")
    * @ORM\JoinColumn(name="id", referencedColumnName="cross_id")
     */
    private $lines;
    
    /**
     * Constructor.
     */
    public function __construct() 
    {
        $this->lines = new ArrayCollection();
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

    public function getRowCount() 
    {
        return $this->rowCount;
    }

    public function setRowCount($rowCount) 
    {
        $this->rowCount = $rowCount;
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
    
    public function getStatusName($status)
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
     * 
     * @return array
     */
    public function getDescription()
    {
        if ($this->description){
            return \Zend\Json\Json::decode($this->description);
        }    
        
        return;
    }
    
    /**
     * 
     * @param array $description
     */
    public function setDescription($description)
    {
        $this->description = \Zend\Json\Json::encode($description);
    }
    
    /**
     * Returns the date of cross creation.
     * @return string     
     */
    public function getDateCreated() 
    {
        return $this->dateCreated;
    }
    
    /**
     * Sets the date when this cross was created.
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
        $supplier->addCross($this);
    }    
    
    /**
     * Returns the array of lines assigned to this.
     * @return array
     */
    public function getLines()
    {
        return $this->rawprice;
    }
        
    /**
     * Assigns.
     */
    public function addLine($line)
    {
        $this->lines[] = $line;
    }
    
}
