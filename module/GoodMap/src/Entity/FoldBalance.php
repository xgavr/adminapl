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
use GoodMap\Entity\Shelf;
use GoodMap\Entity\Cell;
use Company\Entity\Office;
use Application\Entity\Goods;

/**
 * Description of Fold
 * @ORM\Entity(repositoryClass="\GoodMap\Repository\GoodMapRepository")
 * @ORM\Table(name="fold_balance")
 * @author Daddy
 */
class FoldBalance {
    
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
     * @ORM\Column(name="rest")  
     */
    protected $rest;
    
    /** 
     * @ORM\Column(name="status")  
     */
    protected $status;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Goods", inversedBy="foldBalances") 
     * @ORM\JoinColumn(name="good_id", referencedColumnName="id")
     */
    private $good;
            
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Office", inversedBy="foldBalances") 
     * @ORM\JoinColumn(name="office_id", referencedColumnName="id")
     */
    private $office;    
        
    /**
     * @ORM\ManyToOne(targetEntity="GoodMap\Entity\Rack", inversedBy="foldBalances") 
     * @ORM\JoinColumn(name="rack_id", referencedColumnName="id")
     */
    private $rack;
        
    /**
     * @ORM\ManyToOne(targetEntity="GoodMap\Entity\Shelf", inversedBy="foldBalances") 
     * @ORM\JoinColumn(name="shelf_id", referencedColumnName="id")
     */
    private $shelf;
        
    /**
     * @ORM\ManyToOne(targetEntity="GoodMap\Entity\Cell", inversedBy="foldBalances") 
     * @ORM\JoinColumn(name="cell_id", referencedColumnName="id")
     */
    private $cell;
        
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function getRest() {
        return $this->rest;
    }

    public function setRest($rest) {
        $this->rest = $rest;
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
     * @return Good
     */
    public function getGood() {
        return $this->good;
    }

    /**
     * 
     * @param Good $good
     * @return $this
     */
    public function setGood($good) {
        $this->good = $good;
        return $this;
    }

    /**
     * 
     * @return Office
     */
    public function getOffice() {
        return $this->office;
    }

    /**
     * 
     * @param Office $office
     * @return $this
     */
    public function setOffice($office) {
        $this->office = $office;
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
        return $this;
    }

    /**
     * 
     * @return Cell
     */
    public function getCell() {
        return $this->cell;
    }

    /**
     * 
     * @param Cell $cell
     * @return $this
     */
    public function setCell($cell) {
        $this->cell = $cell;
        return $this;
    }
}
