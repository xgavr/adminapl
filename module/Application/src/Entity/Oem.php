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
 * @ORM\Entity(repositoryClass="\Application\Repository\OemRepository")
 * @ORM\Table(name="oem")
 * @author Daddy
 */
class Oem {
    
    const STATUS_ACTIVE       = 1; // Active.
    const STATUS_RETIRED      = 2; // Retired.
    
    const SOURCE_TD       = 1; // Active.
    const SOURCE_SUP      = 2; // Retired.
    const SOURCE_MAN      = 3; // Retired.
    const SOURCE_CROSS    = 4; // Retired.

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="oe")   
     */
    protected $oe;
    
    /**
     * @ORM\Column(name="oe_number")  
     */
    protected $oeNumber;        

    /**
     * @ORM\Column(name="brand_name")  
     */
    protected $brandName;        

    /**
     * @ORM\Column(name="status")  
     */
    protected $status;        

    /**
     * @ORM\Column(name="source")  
     */
    protected $source;        

    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Goods", inversedBy="oem") 
     * @ORM\JoinColumn(name="good_id", referencedColumnName="id")
     */
    protected $good;    
        
    public function getId() 
    {
        return $this->id;
    }

    public function setId($id) 
    {
        $this->id = $id;
    }     

    public function getOe() 
    {
        return $this->oe;
    }

    public function setOe($oe) 
    {
        $this->oe = mb_strcut(trim($oe), 0, 24, 'UTF-8');
    }     

    public function getOeNumber() 
    {
        return trim($this->oeNumber, " '`");
    }

    public function setOeNumber($oeNumber) 
    {
        $this->oeNumber = mb_strcut(trim($oeNumber), 0, 36, 'UTF-8');
    }     

    public function getBrandName() 
    {
        return $this->brandName;
    }

    public function setBrandName($brandName) 
    {
        $this->brandName = $brandName;
    }     

    /**
     * Возвращает связанный goods.
     * @return \Application\Entity\Goods
     */    
    public function getGood() 
    {
        return $this->good;
    }

    /**
     * Задает связанный goods.
     * @param \Application\Entity\Goods $good
     */    
    public function setGood($good) 
    {
        $this->good = $good;
        $good->addOem($this);
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
            self::STATUS_ACTIVE => 'Используется',
            self::STATUS_RETIRED => 'Не используется'
        ];
    }    
    
    /**
     * Returns make status as string.
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
     * Returns source.
     * @return int     
     */
    public function getSource() 
    {
        return $this->source;
    }

    
    /**
     * Returns possible sources as array.
     * @return array
     */
    public static function getSourceList() 
    {
        return [
            self::SOURCE_TD => 'ТекДок',
            self::SOURCE_SUP => 'Поставщик',
            self::SOURCE_MAN => 'Введено вручную',
            self::SOURCE_CROSS => 'Получено по кроссу',
        ];
    }    
    
    /**
     * Returns make source as string.
     * @return string
     */
    public function getSourceAsString()
    {
        $list = self::getSourceList();
        if (isset($list[$this->source]))
            return $list[$this->source];
        
        return 'Unknown';
    }    
    
    /**
     * Sets source.
     * @param int $source     
     */
    public function setSource($source) 
    {
        $this->source = $source;
    }   
    
}
