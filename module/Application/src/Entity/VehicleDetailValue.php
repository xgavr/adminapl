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
 * Description of Phone
 * @ORM\Entity(repositoryClass="\Application\Repository\CarRepository")
 * @ORM\Table(name="vehicle_detail_value")
 * @author Daddy
 */
class VehicleDetailValue {
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
     * @ORM\Column(name="name_apl")   
     */
    protected $nameApl;
        
    /**
    * @ORM\ManyToOne(targetEntity="Application\Entity\VehicleDetail", inversedBy="vehicleDetailValues")
    * @ORM\JoinColumn(name="vehicle_detail_id", referencedColumnName="id")
     */
    protected $vehicleDetail;
    
    /**
    * @ORM\OneToMany(targetEntity="Application\Entity\VehicleDetailCar", mappedBy="vehicleDetailValue")
    * @ORM\JoinColumn(name="id", referencedColumnName="vehicle_detail_value_id")
     */    
    protected $vehicleDetailsCar;    

    public function __construct() {
       $this->vehicleDetailsCar = new ArrayCollection();      
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

    public function getNameApl() 
    {
        if (empty($this->nameApl)){
            return $this->getName();
        }
        return $this->nameApl;
    }

    public function setNameApl($nameApl) 
    {
        $this->nameApl = $nameApl;
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
        $vehicleDetail->addVehicleDetailValue($this);
    }

    
    /*
     * Возвращает values.
     * @return array
     */    
    public function getVehicleDetailsCar() 
    {
        return $this->vehicleDetailsCar;
    }
    
    public function addVehicleDetailCar($vehicleDetailsCar)
    {
        $this->vehicleDetailsCar[] = $vehicleDetailsCar;
    }


}
