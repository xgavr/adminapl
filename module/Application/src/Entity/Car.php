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
 * Description of Car
 * @ORM\Entity(repositoryClass="\Application\Repository\CarRepository")
 * @ORM\Table(name="car")
 * @author Daddy
 */

class Car {
    
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
        
    const FILL_VOLUMES_YES       = 1; // автонормы обновлены
    const FILL_VOLUMES_NO       = 2; //

    const FILL_VOLUMES_TRANSFER_YES       = 1; // обмен с апл выполнен
    const FILL_VOLUMES_TRANSFER_NO       = 2; //

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
     * @ORM\Column(name="update_flag")  
     */
    protected $updateFlag;
        
    /**
     * @ORM\Column(name="transfer_flag")  
     */
    protected $transferFlag;
        
    /**
     * @ORM\Column(name="fill_volumes_flag")  
     */
    protected $fillVolumesFlag;

    /**
     * @ORM\Column(name="transfer_fill_volumes_flag")  
     */
    protected $transferFillVolumesFlag;

    /**
     * @ORM\Column(name="good_count")  
     */
    protected $goodCount;

    /**
     * @ORM\ManyToOne(targetEntity="\Application\Entity\Model", inversedBy="model") 
     * @ORM\JoinColumn(name="model_id", referencedColumnName="id")
     * 
     */
    protected $model;
    
    /**
    * @ORM\OneToMany(targetEntity="\Application\Entity\CarAttributeValue", mappedBy="car")
    * @ORM\JoinColumn(name="id", referencedColumnName="car_id")
     */
    protected $carAttributeValues;    
    
    /**
    * @ORM\OneToMany(targetEntity="\Application\Entity\CarFillVolume", mappedBy="car")
    * @ORM\JoinColumn(name="id", referencedColumnName="car_id")
     */
    protected $carFillVolumes;    
    
    /**
    * @ORM\OneToMany(targetEntity="\Application\Entity\VehicleDetailCar", mappedBy="car")
    * @ORM\JoinColumn(name="id", referencedColumnName="car_id")
     */
    protected $vehicleDetailsCar;    
    
    /**
     * @ORM\ManyToMany(targetEntity="\Application\Entity\Goods", mappedBy="cars")
     */
    protected $goods;    

    public function __construct() {
       $this->carAttributeValues = new ArrayCollection();
       $this->carFillVolumes = new ArrayCollection();
       $this->vehicleDetailsCar = new ArrayCollection();
       $this->goods = new ArrayCollection();
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

    public function getTransferName() 
    {
        $filter = new \Admin\Filter\TransferName();
        return $filter->filter($this->name);
    }

    public function setName($name) 
    {
        $this->name = $name;
    }     

    public function getFullName() 
    {
        return $this->fullName;
    }

    public function getTransferFullName() 
    {
        $filter = new \Admin\Filter\TransferName();
        return $filter->filter($this->fullName);
    }


    public function setFullName($fullName) 
    {
        $this->fullName = $fullName;
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
    
    public function getUpdateFlag() 
    {
        return $this->updateFlag;
    }

    public function setUpdateFlag($updateFlag) 
    {
        $this->updateFlag = $updateFlag;
    }     
    
    public function getTransferFlag() 
    {
        return $this->transferFlag;
    }

    public function setTransferFlag($transferFlag) 
    {
        $this->transferFlag = $transferFlag;
    }     
    
    public function getFillVolumesFlag() 
    {
        return $this->fillVolumesFlag;
    }

    public function setFillVolumesFlag($fillVolumesFlag) 
    {
        $this->fillVolumesFlag = $fillVolumesFlag;
    }     
    
    public function getTransferFillVolumesFlag() 
    {
        return $this->transferFillVolumesFlag;
    }

    public function setTransferFillVolumesFlag($transferFillVolumesFlag) 
    {
        $this->transferFillVolumesFlag = $transferFillVolumesFlag;
    }     
    
    public function getGoodCount() 
    {
        return $this->goodCount;
    }

    public function setGoodCount($goodCount) 
    {
        $this->goodCount = $goodCount;
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
    
    /*
     * Возвращает связанный model.
     * @return \Application\Entity\Model
     */    
    public function getModel() 
    {
        return $this->model;
    }
    
    /**
     * Задает связанный model.
     * @param \Application\Entity\Model $model
     */    
    public function setModel($model) 
    {
        $this->model = $model;
        $model->addCar($this);
    }         

    /*
     * Возвращает values.
     * @return array
     */    
    public function getCarAtributeValues() 
    {
        return $this->carAttributeValues;
    }
    
    public function addCarAttributeValue($carAttributeValue)
    {
        $this->carAttributeValues[] = $carAttributeValue;
    }
    
    /*
     * Возвращает volumes.
     * @return array
     */    
    public function getCarFillVolumes() 
    {
        return $this->carFillVolumes;
    }
    
    public function addCarFillVolume($carFillVolume)
    {
        $this->carFillVolumes[] = $carFillVolume;
    }
    
    /*
     * Возвращает values.
     * @return array
     */    
    public function getVehicleDetailsCar() 
    {
        return $this->vehicleDetailsCar;
    }
    
    /**
     * 
     * @param \Application\Entity\VehicleDetailCar $vehicleDetailCar
     */
    public function addVehicleDetailCar($vehicleDetailCar)
    {
        $this->vehicleDetailsCar[] = $vehicleDetailCar;
    }
    
    // Возвращает товары, связанные с данной машиной.
    public function getGoods() 
    {
        return $this->goods;
    }
    
    // Добавляет товар в коллекцию товаров, связанных с этой машиной.
    public function addGood($good) 
    {
        $this->goods[] = $good;        
    }     
}
