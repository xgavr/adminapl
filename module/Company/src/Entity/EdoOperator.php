<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Company\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Company\Entity\Office;

/**
 * Description of EdoOperator
 * @ORM\Entity(repositoryClass="\Company\Repository\LegalRepository")
 * @ORM\Table(name="edo_operator")
 * @author Daddy
 */
class EdoOperator {
    
     // Status constants.
    const STATUS_ACTIVE       = 1; // Active office.
    const STATUS_RETIRED      = 2; // Retired office.    
    
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
     * @ORM\Column(name="code")   
     */
    protected $code;
    
    /**
     * @ORM\Column(name="inn")   
     */
    protected $inn;

    /**
     * @ORM\Column(name="site")   
     */
    protected $site;

    /**
     * @ORM\Column(name="info")   
     */
    protected $info;

    /**
     * @ORM\Column(name="date_created")   
     */
    protected $dateCreated;

    /**
     * @ORM\Column(name="status")   
     */
    protected $status;
    
    /**
    * @ORM\OneToMany(targetEntity="\Company\Entity\Legal", mappedBy="edoOperator")
    * @ORM\JoinColumn(name="id", referencedColumnName="edo_operator_id")
     */
    private $legals;
    
    /**
     * Constructor.
     */
    public function __construct() 
    {
        $this->legals = new ArrayCollection();
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

    public function getCode() 
    {
        return $this->code;
    }

    public function setCode($code) 
    {
        $this->code = $code;
    }     

    public function getInn() 
    {
        return $this->inn;
    }

    public function setInn($inn) 
    {
        $this->inn = $inn;
    }     

    public function getSite() 
    {
        return $this->site;
    }

    public function setSite($site) 
    {
        $this->site = $site;
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
     * Returns the date of bank account creation.
     * @return string     
     */
    public function getDateCreated() 
    {
        return $this->dateCreated;
    }
    
    /**
     * Sets the date when this bank account was created.
     * @param string $dateCreated     
     */
    public function setDateCreated($dateCreated) 
    {
        $this->dateCreated = $dateCreated;
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
     * @return array
     */
    public function getlegals()
    {
        return $this->legals;
    }
        
    /**
     * Добавляет новый legal к этому operator.
     * @param $legal
     */   
    public function addLegal($legal)
    {
        $this->legals[] = $legal;
    }        
}
