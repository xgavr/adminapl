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
 * @ORM\Table(name="vehicle_detail_car")
 * @author Daddy
 */
class VehicleDetailCar {
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
            
    /**
    * @ORM\ManyToOne(targetEntity="Application\Entity\Car", inversedBy="vehicleDetailsCar")
    * @ORM\JoinColumn(name="car_id", referencedColumnName="id")
     */
    protected $car;
    
    /**
    * @ORM\ManyToOne(targetEntity="Application\Entity\VehicleDetail", inversedBy="vehicleDetailsCar")
    * @ORM\JoinColumn(name="vehicle_detail_id", referencedColumnName="id")
     */
    protected $vehicleDetail;

    /**
    * @ORM\ManyToOne(targetEntity="Application\Entity\VehicleDetailValue", inversedBy="vehicleDetailsCar")
    * @ORM\JoinColumn(name="vehicle_detail_value_id", referencedColumnName="id")
     */
    protected $vehicleDetailValue;

    public function getId() 
    {
        return $this->id;
    }

    public function setId($id) 
    {
        $this->id = $id;
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
        $car->addCarAttributeValue($this);
    }

    /*
     * Возвращает type.
     * @return array
     */    
    public function getVehicleDetail() 
    {
        return $this->vehicleDetail;
    }
    
    /**
     * 
     * @param \Application\Entity\VehicleDetail $vehicleDetail
     */
    public function setVehicleDetail($vehicleDetail)
    {
        $this->vehicleDetail = $vehicleDetail;
        $vehicleDetail->addVehicleDetailCar($this);
    }

    /*
     * Возвращает type.
     * @return array
     */    
    public function getVehicleDetailValue() 
    {
        return $this->vehicleDetailValue;
    }
    
    /**
     * 
     * @param \Application\Entity\VehicleDetailValue $vehicleDetailValue
     */
    public function setVehicleDetailValue($vehicleDetailValue)
    {
        $this->vehicleDetailValue = $vehicleDetailValue;
        $vehicleDetailValue->addVehicleDetailCar($this);
    }

}
