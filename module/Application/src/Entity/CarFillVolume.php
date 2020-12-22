<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Phone
 * @ORM\Entity(repositoryClass="\Application\Repository\CarRepository")
 * @ORM\Table(name="car_fill_volume")
 * @author Daddy
 */
class CarFillVolume {
    
    const STATUS_ACTIVE       = 1; // Active.
    const STATUS_RETIRED      = 2; // Retired.    
    
    const LANG_RU       = 1; // Ru
    const LANG_EN      = 2; // En    
    
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
     * @ORM\Column(name="lang")  
     */
    protected $lang;    
    
    /**
     * @ORM\Column(name="volume")   
     */
    protected $volume;
        
    /**
     * @ORM\Column(name="info")   
     */
    protected $info;

    /**
    * @ORM\ManyToOne(targetEntity="Application\Entity\CarFillTitle", inversedBy="carFillVolumes")
    * @ORM\JoinColumn(name="car_fill_title_id", referencedColumnName="id")
     */
    protected $carFillTitile;

    /**
    * @ORM\ManyToOne(targetEntity="Application\Entity\CarFillType", inversedBy="carFillVolumes")
    * @ORM\JoinColumn(name="car_fill_type_id", referencedColumnName="id")
     */
    protected $carFillType;

    /**
    * @ORM\ManyToOne(targetEntity="Application\Entity\CarFillUnit", inversedBy="carFillVolumes")
    * @ORM\JoinColumn(name="car_fill_unit_id", referencedColumnName="id")
     */
    protected $carFillUnit;

    /**
    * @ORM\ManyToOne(targetEntity="Application\Entity\Car", inversedBy="carFillVolumes")
    * @ORM\JoinColumn(name="car_id", referencedColumnName="id")
     */
    protected $car;
    
    public function getId() 
    {
        return $this->id;
    }

    public function setId($id) 
    {
        $this->id = $id;
    }     

    public function getVolume() 
    {
        return $this->volume;
    }

    public function setVolume($volume) 
    {
        $this->volume = $volume;
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

    /**
     * Returns status.
     * @return int     
     */
    public function getLang() 
    {
        return $this->lang;
    }

    
    /**
     * Returns possible langs as array.
     * @return array
     */
    public static function getLangList() 
    {
        return [
            self::LANG_EN => 'EN',
            self::LANG_RU => 'RU'
        ];
    }    
    
    /**
     * Returns make lang as string.
     * @return string
     */
    public function getLangsAsString()
    {
        $list = self::getLangList();
        if (isset($list[$this->lang]))
            return $list[$this->lang];
        
        return 'Unknown';
    }    
    
    /**
     * Sets lang.
     * @param int $lang     
     */
    public function setLang($lang) 
    {
        $this->lang = $lang;
    }       

    /*
     * Возвращает title.
     * @return array
     */    
    public function getCarFillTitle() 
    {
        return $this->carFillTitile;
    }
    
    public function setCarFillTitle($carFillTitle)
    {
        $this->carFillTitile = $carFillTitle;
        $carFillTitle->addCarFillVolume($this);
    }

    /*
     * Возвращает type.
     * @return array
     */    
    public function getCarFillType() 
    {
        return $this->carFillType;
    }
    
    public function setCarFillType($carFillType)
    {
        $this->carFillType = $carFillType;
        $carFillType->addCarFillVolume($this);
    }

    /*
     * Возвращает unit.
     * @return array
     */    
    public function getCarFillUnit() 
    {
        return $this->carFillUnit;
    }
    
    public function setCarFillUnit($carFillUnit)
    {
        $this->carFillUnit = $carFillUnit;
        $carFillUnit->addCarFillVolume($this);
    }

    /*
     * Возвращает car.
     * @return array
     */    
    public function getCar() 
    {
        return $this->car;
    }
    
    public function setCar($car)
    {
        $this->car = $car;
        $car->addCarFillVolume($this);
    }

}
