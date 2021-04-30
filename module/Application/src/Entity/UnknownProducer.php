<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Description of Producer
 * @ORM\Entity(repositoryClass="\Application\Repository\ProducerRepository")
 * @ORM\Table(name="unknown_producer")
 * @author Daddy
 */
class UnknownProducer {
    
    const STATUS_ACTIVE       = 1; // Active unknown producer.
    const STATUS_RETIRED      = 2; // Retired unknown producer.   

    const INTERSECT_COEF = 0.24; // коэффициент отсечение при пересечении.
    const INTERSECT_UPDATE_FLAG = 10; // требуется проверка на пересечение
    
    const CHECK_MAX_ROW = 100; // максимальное количество строк для проверки
    const CHECK_COUNT = 10; //множитель для количества проверок
    
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
     * @ORM\Column(name="status")  
     */
    protected $status = self::STATUS_ACTIVE;

    /**
     * @ORM\Column(name="name_td")   
     */
    protected $nameTd;
    
    /**
     * @ORM\Column(name="date_created")  
     */
    protected $dateCreated;        

    /**
     * @ORM\Column(name="rawprice_count")
     */
    protected $rawpriceCount = 0;
    
    /**
     * @ORM\Column(name="supplier_count")
     */
    protected $supplierCount = 0;
    
    /**
     * @ORM\Column(name="intersect_update_flag")
     */
    protected $intersectUpdateFlag = 0;

    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Producer", inversedBy="unknownProducer") 
     * @ORM\JoinColumn(name="producer_id", referencedColumnName="id")
     */
    protected $producer;    

    /**
    * @ORM\OneToMany(targetEntity="Application\Entity\Rawprice", mappedBy="unknownProducer")
    * @ORM\JoinColumn(name="id", referencedColumnName="unknown_producer_id")
     */
    private $rawprice;
    
    /**
     * @ORM\OneToMany(targetEntity="Application\Entity\Article", mappedBy="unknownProducer") 
     * @ORM\JoinColumn(name="id", referencedColumnName="unknown_producer_id")
     */
    private $code;
    
        
    /**
     * Constructor.
     */
    public function __construct() 
    {
        $this->rawprice = new ArrayCollection();
        $this->code = new ArrayCollection();
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
        $filter = new \Application\Filter\ProducerName();
        $this->name = $filter->filter($name);
    }     

    /**
     * Returns status.
     * @return int     
     */
    public function getStatus() 
    {
        return $this->status;
    }

    public function getStatusCheckbox() 
    {
        if ($this->status == self::STATUS_ACTIVE){
            return 'checked';
        }
        return '';
    }

    /**
     * Returns possible statuses as array.
     * @return array
     */
    public static function getStatusList() 
    {
        return [
            self::STATUS_ACTIVE => 'Действующий',
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
    
    public static function getStatusName($status)
    {
        $list = self::getStatusList();
        if (isset($list[$status]))
            return $list[$status];
        
        return 'Unknown';        
    }
    
    public function setStatus($status)
    {
        $this->status = $status;
    }
    
    public function getNameTd() 
    {
        return $this->nameTd;
    }

    public function setNameTd($nameTd) 
    {
        $this->nameTd = $nameTd;
    }     

    public function getDateCreated() 
    {
        return $this->dateCreated;
    }

    public function setDateCreated($dateCreated) 
    {
        $this->dateCreated = $dateCreated;
    }     
    
    public function getRawpriceCount() 
    {
        return $this->rawpriceCount;
    }

    public function setRawpriceCount($rawpriceCount) 
    {
        $this->rawpriceCount = $rawpriceCount;
    }     

    public function getSupplierCount() 
    {
        return $this->supplierCount;
    }

    public function setSupplierCount($supplierCount) 
    {
        $this->supplierCount = $supplierCount;
    }     

    public function getIntersectUpdateFlag() 
    {
        return $this->intersectUpdateFlag;
    }

    public function setIntersectUpdateFlag($intersectUpdateFlag) 
    {
        $this->intersectUpdateFlag = $intersectUpdateFlag;
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
        if ($producer){
            $producer->addUnknownProducer($this);
        }    
    }     
    
    /**
     * Returns the array of contacts assigned to this.
     * @return array
     */
//    public function getRawprice()
//    {
//        return $this->rawprice;
//    }
        
    /**
     * Assigns.
     */
    public function addRawprice($rawprice)
    {
        $this->rawprice[] = $rawprice;
    }
      
    /*
     * Возвращает связанный article.
     * @return \Application\Entity\Article
     */    
    public function getCode() 
    {
        return $this->code;
    }

    /**
     * Задает связанный code.
     * @param \Application\Entity\Article $code
     */    
    public function addCode($code) 
    {
        $this->code[] = $code;
    }     
    
}
