<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace GoodMap\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use GoodMap\Entity\Shelf;

/**
 * Description of Cell
 * @ORM\Entity(repositoryClass="\GoodMap\Repository\GoodMapRepository")
 * @ORM\Table(name="cell")
 * @author Daddy
 */
class Cell {
    
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
     * @ORM\ManyToOne(targetEntity="GoodMap\Entity\Shelf", inversedBy="cells") 
     * @ORM\JoinColumn(name="shelf_id", referencedColumnName="id")
     */
    private $shelf;
        
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
     * @return Shelf
     */
    public function getShelf() {
        return $this->shelf;
    }

    /**
     * 
     * @param Shelf $shelf
     * @return $this
     */
    public function setShelf($shelf) {
        $this->shelf = $shelf;
        $shelf->addCell($this);
        return $this;
    }


}
