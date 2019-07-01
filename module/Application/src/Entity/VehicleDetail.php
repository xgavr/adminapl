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
 * @ORM\Table(name="vehicle_detail")
 * @author Daddy
 */
class VehicleDetail {
    
    const CANNOT_VALUE_EDIT = 2; //нельзя редактировать занчения
    const CAN_VALUE_EDIT = 1; //vj;yj редактировать занчения
    
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
     * @ORM\Column(name="status_edit")   
     */
    protected $statusEdit;
    
    /**
    * @ORM\OneToMany(targetEntity="Application\Entity\VehicleDetailValue", mappedBy="vehicleDetail")
    * @ORM\JoinColumn(name="id", referencedColumnName="vehicle_detail_id")
     */    
    protected $vehicleDetailValues;    

    public function __construct() {
       $this->vehicleDetailValues = new ArrayCollection();      
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
        return $this->nameApl;
    }

    public function setStatusEdit($statusEdit) 
    {
        $this->statusEdit = $statusEdit;
    }     

    public function getStatusEdit() 
    {
        return $this->statusEdit;
    }

    public function setNameApl($nameApl) 
    {
        $this->nameApl = $nameApl;
    }     

    /*
     * Возвращает values.
     * @return array
     */    
    public function getVehicleDetailValues() 
    {
        return $this->vehicleDetailValues;
    }
    
    public function addVehicleDetailValue($vehicleDetailValue)
    {
        $this->vehicleDetailValues[] = $vehicleDetailValue;
    }


}
