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
 * @ORM\Table(name="car_fill_unit")
 * @author Daddy
 */
class CarFillUnit {
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
     * @ORM\Column(name="title")   
     */
    protected $title;
    
    /**
    * @ORM\OneToMany(targetEntity="\Application\Entity\CarFillVolume", mappedBy="carFillUnit")
    * @ORM\JoinColumn(name="id", referencedColumnName="car_fill_unit_id")
     */
    protected $carFillVolumes;        

    public function __construct() {
       $this->carFillVolumes = new ArrayCollection();
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

    public function getTitle() 
    {
        if ($this->title == 'cm³'){
            return 'см3';
        }
        return $this->title;
    }

    public function setTitle($title) 
    {
        $this->title = $title;
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
    
}
