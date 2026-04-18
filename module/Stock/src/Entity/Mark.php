<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Stock\Entity;

use Doctrine\ORM\Mapping as ORM;
use Application\Entity\Goods;
use Application\Entity\Order;


/**
 * Description of Mark - коды маркировки
 * @ORM\Entity(repositoryClass="\Stock\Repository\MarkRepository")
 * @ORM\Table(name="marks")
 * @author Daddy
 */
class Mark {
    
    const STATUS_ACTIVE       = 1; // Active.
    const STATUS_RETIRED      = 2; // Retired.
    
    const MARK_ACTIVE       = 1; // В обороте.
    const MARK_RETIRED      = 2; // Выбыл.
    const MARK_UNKNOWN      = 3; // Неизвестно.
    const MARK_NOT_LISTED      = 4; // Не числится за нами.
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="apl_id")   
     */
    protected $aplId;
    
    /**
     * @ORM\Column(name="mark")   
     */
    protected $mark;
    
    /**
     * @ORM\Column(name="mark_group")   
     */
    protected $markGroup;

    /**
     * @ORM\Column(name="date_created")   
     */
    protected $created;
    
    /**
     * @ORM\Column(name="date_updated")   
     */
    protected $updated;
    
    /**
     * @ORM\Column(name="status")   
     */
    protected $status;

    /** 
     * @ORM\Column(name="mark_status")  
     */
    protected $markStatus;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Goods", inversedBy="marks") 
     * @ORM\JoinColumn(name="good_id", referencedColumnName="id")
     */
    private $good;
            
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Order", inversedBy="marks") 
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id")
     */
    private $order;       
    
    public function __construct() {
    }
   
    public function getId() 
    {
        return $this->id;
    }

    public function setId($id) 
    {
        $this->id = $id;
    }     

    public function getAplId() {
        return $this->aplId;
    }

    public function setAplId($aplId) {
        $this->aplId = $aplId;
    }

    public function getMark() {
        return $this->mark;
    }

    public function getMark31() {
        return substr($this->mark, 0, 31);
    }

    public function setMark($mark) {
        $this->mark = $mark;
    }

    public function getMarkGroup() {
        return $this->markGroup;
    }

    public function setMarkGroup($markGroup) {
        $this->markGroup = $markGroup;
    }

    public function getCreated() {
        return $this->created;
    }

    public function setCreated($created) {
        $this->created = $created;
    }

    public function getUpdated() {
        return $this->updated;
    }

    public function setUpdated($updated) {
        $this->updated = $updated;
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
    
    /**
     * Sets status.
     * @param int $status     
     */
    public function setStatus($status) 
    {
        $this->status = $status;
    }   
    
    public function getMarkStatus() {
        return $this->markStatus;
    }
    
    /**
     * 
     * @param string|null $remoteStatus
     * @return type
     */
    public static function getRemoteMarkStatus($remoteStatus) {
        
        if (empty($remoteStatus)){
            return self::MARK_NOT_LISTED;
        }
        
        switch ($remoteStatus){
            case 'INTRODUCED': return self::MARK_ACTIVE;
            case 'RETIRED': return self::MARK_RETIRED;
        }
        
        return self::MARK_UNKNOWN;
    }
    
    /**
     * Returns possible mark statuses as array.
     * @return array
     */
    public static function getMarkStatusList() 
    {
        return [
            self::MARK_ACTIVE => 'В обороте',
            self::MARK_RETIRED => 'Выбыл',
            self::MARK_NOT_LISTED => 'Не числятся',
            self::MARK_UNKNOWN => 'Не известно',
        ];
    }    
    
    /**
     * Returns mark status as string.
     * @return string
     */
    public function getMarkStatusAsString()
    {
        $list = self::getMarkStatusList();
        if (isset($list[$this->markStatus]))
            return $list[$this->markStatus];
        
        return 'Unknown';
    }        

    public function setMarkStatus($markStatus) {
        $this->markStatus = $markStatus;
    }
        
    /**
     * Returns the good.
     * @return Goods     
     */
    public function getGood() 
    {
        return $this->good;
    }
    
    /**
     * 
     * @param Goods $good
     */
    public function setGood($good) {
        $this->good = $good;
    }

    /**
     * 
     * @param Order $order
     */
    public function setOrder($order) {
        $this->order = $order;
    }
    
    /*
     * @return Order
     */    
    public function getOrder() 
    {
        return $this->order;
    }                         
}
