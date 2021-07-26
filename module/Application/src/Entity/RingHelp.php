<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Application\Entity\RingHelpGroup;

/**
 * Description of Client
 * @ORM\Entity(repositoryClass="\Application\Repository\RingRepository")
 * @ORM\Table(name="ring_help")
 * @author Daddy
 */
class RingHelp {
        
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
     * @ORM\ManyToOne(targetEntity="Application\Entity\RingHelpGroup", inversedBy="ringHelps") 
     * @ORM\JoinColumn(name="ring_help_group_id", referencedColumnName="id")
     */
    private $ringHelpGroup;
    
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
    
    public function getRingHelpGroup() 
    {
        return $this->ringHelpGroup;
    }

    /**
     * 
     * @param ReingHelpGroup $ringHelpGroup
     */
    public function setRingHelpGroup($ringHelpGroup) 
    {
        $this->ringHelpGroup = $ringHelpGroup;
        $ringHelpGroup->addRingHelp($this);
        
    }                 
}
