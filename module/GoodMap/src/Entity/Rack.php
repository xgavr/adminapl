<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace GoodMap\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Company\Entity\Office;
use GoodMap\Entity\Shelf;

/**
 * Description of Rack
 * @ORM\Entity(repositoryClass="\GoodMap\Repository\GoodMapRepository")
 * @ORM\Table(name="rack")
 * @author Daddy
 */
class Rack {
    
     // Rack status constants.
    const STATUS_ACTIVE       = 1; // Active.
    const STATUS_RETIRED      = 2; // Retired.
   
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="code")   
     */
    protected $code;

    /**
     * @ORM\Column(name="name")   
     */
    protected $name;

    /**
     * @ORM\Column(name="comment")   
     */
    protected $comment;

    /** 
     * @ORM\Column(name="fold_count")  
     */
    protected $foldCount;
    
    /** 
     * @ORM\Column(name="status")  
     */
    protected $status;
    
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Office", inversedBy="racks") 
     * @ORM\JoinColumn(name="office_id", referencedColumnName="id")
     */
    private $office;
    
    /**
     * @ORM\OneToMany(targetEntity="GoodMap\Entity\Shelf", mappedBy="rack") 
     * @ORM\JoinColumn(name="id", referencedColumnName="rack_id")
     */
    private $shelfs;        
        
    /**
     * Constructor.
     */
    public function __construct() 
    {
       $this->shelfs = new ArrayCollection();
    }
    
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function getCode() {
        return $this->code;
    }

    public function setCode($code) {
        $this->code = $code;
        return $this;
    }    
    
    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function getComment() {
        return $this->comment;
    }

    public function setComment($comment) {
        $this->comment = $comment;
        return $this;
    }

    public function getFoldCount() {
        return $this->foldCount;
    }

    public function setFoldCount($foldCount) {
        $this->foldCount = $foldCount;
        return $this;
    }
        
    public function getStatus() {
        return $this->status;
    }

    /**
     * Returns possible statuses as array.
     * @return array
     */
    public static function getStatusList() 
    {
        return [
            self::STATUS_ACTIVE => 'Активный',
            self::STATUS_RETIRED => 'Удален',
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
    
    public function setStatus($status) {
        $this->status = $status;
        return $this;
    }

    /**
     * 
     * @return Office
     */
    public function getOffice() {
        return $this->office;
    }

    public function setOffice($office) {
        $this->office = $office;
        return $this;
    }

    /**
     * 
     * @param Shelf $shelf
     */
    public function addShelf($shelf)
    {
        $this->shelfs[] = $shelf;
    }    
    
    /**
     * 
     * @return array
     */
    public function getShelfs() {
        return $this->shelfs;
    }

}
