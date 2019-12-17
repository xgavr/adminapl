<?php

namespace Company\Entity;

use Doctrine\ORM\Mapping as ORM;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Contract
 * @ORM\Entity(repositoryClass="\Company\Repository\LegalRepository")
 * @ORM\Table(name="contract")
 *
 * @author Daddy
 */
class Contract {

     // Contract status constants.
    const STATUS_ACTIVE       = 1; // Active.
    const STATUS_RETIRED      = 2; // Retired.

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
     * @ORM\Column(name="act")   
     */
    protected $act;

    /** 
     * @ORM\Column(name="date_created")  
     */
    protected $dateCreated;    
    
    /** 
     * @ORM\Column(name="date_start")  
     */
    protected $dateStart;    
    
    /** 
     * @ORM\Column(name="status")  
     */
    protected $status;    
        
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Legal", inversedBy="contract") 
     * @ORM\JoinColumn(name="legal_id", referencedColumnName="id")
     */
    private $legal;

    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Office", inversedBy="contract") 
     * @ORM\JoinColumn(name="office_id", referencedColumnName="id")
     */
    private $office;

    /**
     * Constructor.
     */
    public function __construct() 
    {
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

    public function getAct() 
    {
        return $this->act;
    }

    public function setAct($act) 
    {
        $this->act = trim($act);
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
            self::STATUS_ACTIVE => 'Действующий',
            self::STATUS_RETIRED => 'Закрыт'
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
    
    /**
     * Sets status.
     * @param int $status     
     */
    public function setStatus($status) 
    {
        $this->status = $status;
    }   
    
    /**
     * Returns the date of user creation.
     * @return string     
     */
    public function getDateCreated() 
    {
        return $this->dateCreated;
    }
    
    /**
     * Sets the date when this user was created.
     * @param string $dateCreated     
     */
    public function setDateCreated($dateCreated) 
    {
        $this->dateCreated = $dateCreated;
    }    
        
    /**
     * Returns the date start of contract.
     * @return string     
     */
    public function getDateStart() 
    {
        return $this->dateStart;
    }
    
    /**
     * Sets the date start when this contract was start.
     * @param string $dateStart     
     */
    public function setDateStart($dateStart) 
    {
        $this->dateStart = $dateStart;
    }    
            
    /*
     * @return \Company\Entity\Legal
     */    
    public function getLegal() 
    {
        return $this->legal;
    }

    /**
     * @param \Company\Entity\Legal $legal
     */    
    public function setLegal($legal) 
    {
        $this->legal = $legal;
        $legal->addContract($this);
    }     
        
    /*
     * @return \Company\Entity\Office
     */    
    public function getOffice() 
    {
        return $this->office;
    }

    /**
     * @param \Company\Entity\Office $office
     */    
    public function setOffice($office) 
    {
        $this->office = $office;
        $office->addContract($this);
    }             
}
