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
    
    const INTERSECT_COEF = 0.24; // коэффициент отсечение при пересечении.
    const CHECK_MAX_ROW = 15; // максимальное количество строк для проверки
    const CHECK_COUNT = 3; //множитель для количества проверок
    
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
     * @ORM\Column(name="rawprice_count")
     */
    protected $rawpriceCount = 0;
    
    /**
     * @ORM\Column(name="supplier_count")
     */
    protected $supplierCount = 0;
    
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
