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
     * @ORM\Column(name="date_created")  
     */
    protected $dateCreated;        

    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Producer", inversedBy="unknown_producer") 
     * @ORM\JoinColumn(name="producer_id", referencedColumnName="id")
     */
    protected $producer;    

    /**
    * @ORM\OneToMany(targetEntity="Application\Entity\Rawprice", mappedBy="unknown_producer")
    * @ORM\JoinColumn(name="id", referencedColumnName="unknown_producer_id")
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

    public function getName() 
    {
        return $this->name;
    }

    public function setName($name) 
    {
        $this->name = trim($name);
    }     

    public function getDateCreated() 
    {
        return $this->dateCreated;
    }

    public function setDateCreated($dateCreated) 
    {
        $this->dateCreated = $dateCreated;
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
