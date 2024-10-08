<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace GoodMap\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use GoodMap\Entity\Rack;
use GoodMap\Entity\Cell;

/**
 * Description of Shelf
 * @ORM\Entity(repositoryClass="\GoodMap\Repository\GoodMapRepository")
 * @ORM\Table(name="shelf")
 * @author Daddy
 */
class Shelf {
    
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
     * @ORM\ManyToOne(targetEntity="GoodMap\Entity\Rack", inversedBy="shelfs") 
     * @ORM\JoinColumn(name="rack_id", referencedColumnName="id")
     */
    private $rack;
        
    /**
     * @ORM\OneToMany(targetEntity="GoodMap\Entity\Cell", mappedBy="shelf") 
     * @ORM\JoinColumn(name="id", referencedColumnName="shelf_id")
     */
    private $cells;        
    
    /**
     * Constructor.
     */
    public function __construct() 
    {
       $this->cells = new ArrayCollection();
    }
    
    public function getId() {
        return $this->id;
    }

    public function getCode() {
        return $this->code;
    }

    public function setCode($code) {
        $this->code = $code;
        return $this;
    }

    public function setId($id) {
        $this->id = $id;
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
     * @return Rack
     */
    public function getRack() {
        return $this->rack;
    }

    /**
     * 
     * @param Rack $rack
     * @return $this
     */
    public function setRack($rack) {
        $this->rack = $rack;
        $rack->addShelf($this);
        return $this;
    }

    /**
     * 
     * @param Cell $cell
     */
    public function addCell($cell)
    {
        $this->cells[] = $cell;
    }    
    
    /**
     * 
     * @return array
     */
    public function getCells() {
        return $this->cells;
    }
}
