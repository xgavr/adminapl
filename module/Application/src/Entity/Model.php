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
 * Description of Make
 * @ORM\Entity(repositoryClass="\Application\Repository\MakeRepository")
 * @ORM\Table(name="model")
 * @author Daddy
 */

class Model {
    
     // Make status constants.
    const STATUS_ACTIVE       = 1; // Active.
    const STATUS_RETIRED      = 2; // Retired.
    
    const PASSENGER_YES       = 1; //
    const PASSENGER_NO       = 2; //    
    
    const COMMERC_YES       = 1; //
    const COMMERC_NO       = 2; //
    
    const MOTO_YES       = 1; //
    const MOTO_NO       = 2; //
    
    const TRANSFER_YES       = 1; // обмен с апл выполнен
    const TRANSFER_NO       = 2; //
    
    const COSTRUCTION_MAX_PERIOD = 999999;
    
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
     * @ORM\Column(name="name_ru")   
     */
    protected $nameRu;
        
    /**
     * @ORM\Column(name="td_id")   
     */
    protected $tdId;
    
    /**
     * @ORM\Column(name="apl_id")   
     */
    protected $aplId;
    
    /**
     * @ORM\Column(name="fullname")  
     */
    protected $fullName;    

    /**
     * @ORM\Column(name="constructioninterval")  
     */
    protected $interval;    

    /**
     * @ORM\Column(name="status")  
     */
    protected $status;    
       
    /**
     * @ORM\Column(name="passenger")  
     */
    protected $passenger;
    
    /**
     * @ORM\Column(name="commerc")  
     */
    protected $commerc;

    /**
     * @ORM\Column(name="moto")  
     */
    protected $moto;
        
    /**
     * @ORM\Column(name="transfer_flag")  
     */
    protected $transferFlag;
        
    /**
     * @ORM\Column(name="construction_from")  
     */
    protected $constructionFrom = self::COSTRUCTION_MAX_PERIOD;
        
    /**
     * @ORM\Column(name="construction_to")  
     */
    protected $constructionTo = self::COSTRUCTION_MAX_PERIOD;
    
    /**
     * @ORM\Column(name="good_count")  
     */
    protected $goodCount;

    /**
     * @ORM\Column(name="sale_count")  
     */
    protected $saleCount;

    /**
     * @ORM\Column(name="sale_month")  
     */
    protected $saleMonth;    
        
    /**
     * @ORM\ManyToOne(targetEntity="\Application\Entity\Make", inversedBy="models") 
     * @ORM\JoinColumn(name="make_id", referencedColumnName="id")
     * 
     */
    protected $make;
    
    /**
     * @ORM\OneToMany(targetEntity="\Application\Entity\Car", mappedBy="model")
     * @ORM\JoinColumn(name="id", referencedColumnName="model_id")
     */
    protected $cars;    
    
    public function __construct() {
        $this->cars = new ArrayCollection();
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

    public function getNameRu() {
        return $this->nameRu;
    }

    public function setNameRu($nameRu) {
        $this->nameRu = $nameRu;
        return $this;
    }
        
    public function getTransferName() 
    {
        $filter = new \Admin\Filter\TransferName();
        return $filter->filter($this->fullName);
    }

    public function getFileName() 
    {
        return $this->getMake()->getTdId().'_'.$this->getTdId();
    }
    
    public function setName($name) 
    {
        $this->name = $name;
    }     

    public function getFullName() 
    {
        return $this->fullName;
    }

    public function getDisplayName() 
    {
        if ($this->fullName){
            return $this->fullName;
        }
        return $this->getTransferName();
    }
    
    public function getDispalyMakeName() 
    {
        return ($this->getNameRu() ?? $this->getName()). ' ' . $this->getDisplayName();
    }
    
    public function getDispalyMakeNameRu() 
    {
        return $this->make->getName(). ' ' . $this->getNameRu();
    }        
    
    public function setFullName($fullName) 
    {
        $this->fullName = $fullName;
    }     

    public function getInterval() 
    {
        if ($this->constructionFrom < self::COSTRUCTION_MAX_PERIOD){
            $result = substr($this->constructionFrom, -2).'.'.substr($this->constructionFrom, 0, 4).'-';
            if ($this->constructionTo < date('Ym')){
                $result .= substr($this->constructionTo, -2).'.'.substr($this->constructionTo, 0, 4);
            }
            return $result;
        }
        return $this->interval;
    }

    public function setInterval($interval) 
    {
        $this->interval = $interval;
    }     

    public function getTdId() 
    {
        return $this->tdId;
    }

    public function setTdId($tdId) 
    {
        $this->tdId = $tdId;
    }     

    public function getAplId() 
    {
        return $this->aplId;
    }

    public function setAplId($aplId) 
    {
        $this->aplId = $aplId;
    }     
    
    public function getPasenger() 
    {
        return $this->passenger;
    }

    public function setPassenger($passenger) 
    {
        $this->passenger = $passenger;
    }     
    
    public function getCommerc() 
    {
        return $this->commerc;
    }

    public function setCommerc($commerc) 
    {
        $this->commerc = $commerc;
    }     
    
    public function getMoto() 
    {
        return $this->moto;
    }

    public function setMoto($moto) 
    {
        $this->moto = $moto;
    }     
    
    public function getConstructionFrom() 
    {
        return $this->constructionFrom;
    }

    public function setConstructionFrom($constructionFrom) 
    {
        $this->constructionFrom = $constructionFrom;
    }     
    
    public function getConstructionTo() 
    {
        return $this->constructionTo;
    }

    public function setConstructionTo($constructionTo) 
    {
        $this->constructionTo = $constructionTo;
    }     
    
    public function getGoodCount() 
    {
        return $this->goodCount;
    }

    public function setGoodCount($goodCount) 
    {
        $this->goodCount = $goodCount;
    }         
      
    public function getSaleCount() {
        return $this->saleCount;
    }

    public function setSaleCount($saleCount) {
        $this->saleCount = $saleCount;
    }

    public function getSaleMonth() {
        return $this->saleMonth;
    }

    public function setSaleMonth($saleMonth) {
        $this->saleMonth = $saleMonth;
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
            self::STATUS_ACTIVE => 'Используется',
            self::STATUS_RETIRED => 'Не используется'
        ];
    }    
    
    /**
     * Returns make status as string.
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
    
    
    public function getTransferFlag() 
    {
        return $this->transferFlag;
    }

    public function setTransferFlag($transferFlag) 
    {
        $this->transferFlag = $transferFlag;
    }     
    
    
    /*
     * Возвращает связанный make.
     * @return \Application\Entity\Make
     */    
    public function getMake() 
    {
        return $this->make;
    }
    
    /**
     * Задает связанный make.
     * @param \Application\Entity\Make $make
     */    
    public function setMake($make) 
    {
        $this->make = $make;
        $make->addModel($this);
    }         
    
    /**
     * Возвращает cars для этого model.
     * @return array
     */
    public function getCars() 
    {
        return $this->cars;
    }
    
    /**
     * Добавляет новою car к этому model.
     * @param $car
     */
    public function addCar($car) 
    {
        $this->models[] = $car;
    }
    
    /**
     * Массив для формы
     * @return array 
     */
    public function toArray()
    {
        $result = [
            'id' => $this->getId(),
            'aplId' => $this->getAplId(),
            'constructionFrom' => $this->getConstructionFrom(),
            'constructionTo' => $this->getConstructionTo(),
            'displayName' => $this->getDisplayName(),
            'fullName' => $this->getFullName(),
            'fullMakeName' => $this->getDispalyMakeName(),
            'fullMakeNameRu' => $this->getDispalyMakeNameRu(),
            'goodCount' => $this->getGoodCount(),
            'constructionInterval' => $this->getInterval(),
            'name' => $this->getName(),
            'nameRu' => $this->getNameRu(),
            'saleCount' => $this->getSaleCount(),
            'saleMonth' => $this->getSaleMonth(),
            'status' => $this->getStatus(),
            'makeId' => $this->getMake()->getId(),
            'yearFrom' => (int) substr($this->getConstructionFrom(), 0, 4), // Если формат YYYYMM
            'yearTo' => (int) substr($this->getConstructionTo(), 0, 4),     // Если формат YYYYMM       
        ];
        
        return $result;        
    }    
}
