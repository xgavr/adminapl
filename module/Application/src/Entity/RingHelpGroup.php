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
 * Description of Client
 * @ORM\Entity(repositoryClass="\Application\Repository\RingRepository")
 * @ORM\Table(name="ring_help_group")
 * @author Daddy
 */
class RingHelpGroup {
        
     // Status constants.
    const STATUS_ACTIVE       = 1; // Active user.
    const STATUS_RETIRED      = 2; // Retired user.
   
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
        
    /** 
     * @ORM\Column(name="mode")  
     */
    protected $mode;

    /** 
     * @ORM\Column(name="status")  
     */
    protected $status;

    /** 
     * @ORM\Column(name="sort")  
     */
    protected $sort;

    /**
     * @ORM\Column(name="name")   
     */
    protected $name;
    
    /**
     * @ORM\Column(name="info")   
     */
    protected $info;
    
   /**
    * @ORM\OneToMany(targetEntity="Application\Entity\RingHelp", mappedBy="ringHelpGroup")
    * @ORM\JoinColumn(name="id", referencedColumnName="ring_help_group_id")
   */
   private $ringHelps;
    
    /**
     * Constructor.
     */
    public function __construct() 
    {
        $this->ringHelps = new ArrayCollection();
    }
    
    public function getId() 
    {
        return $this->id;
    }

    public function setId($id) 
    {
        $this->id = $id;
    }     

    public function getMode() 
    {
        return $this->mode;
    }

    public function setMode($mode) 
    {
        $this->mode = $mode;
    }     

    public function getName() 
    {
        return $this->name;
    }

    public function setName($name) 
    {
        $this->name = $name;
    }     

    public function getInfo() 
    {
        return $this->info;
    }

    public function setInfo($info) 
    {
        $this->info = $info;
    }     

    public function getSort() 
    {
        return $this->sort;
    }

    public function setSort($sort) 
    {
        $this->sort = $sort;
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
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_RETIRED => 'Retired'
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
    
    public function getRingHelps() 
    {
        return $this->ringHelps;
    }

    public function addRingHelp($ringHelp) 
    {
        $this->ringHelps[] = $ringHelp;
    }         
}
