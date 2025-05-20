<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Admin\Filter\TransferName;

/**
 * Description of Producer
 * @ORM\Entity(repositoryClass="\Application\Repository\OemRepository")
 * @ORM\Table(name="oem")
 * @author Daddy
 */
class Oem {
    
    const STATUS_ACTIVE       = 1; // Active.
    const STATUS_RETIRED      = 2; // Retired.
    
    const SOURCE_TD       = 1; // ТекДок.
    const SOURCE_SUP      = 2; // Прайс.
    const SOURCE_MAN      = 3; // Вручную.
    const SOURCE_CROSS    = 4; // Кросслист.
    const SOURCE_INTERSECT = 5; // Пересечение.
    const SOURCE_MY_CODE = 6; //Артикул товара
    const SOURCE_IID = 8; //Номер у поставщика
    const SOURCE_EXT_SOURCE = 9; //Внешние источники
    
    const RATING_UPDATED      = 1; // Рейтинг обновлен.
    const RATING_FOR_UPDATE   = 2; // Рейтинг не обновлен.

    const SOURCE_TD_NAME = 'ТекДок';
    const INTERSECT_NAME = 'ОЕ кросс';

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
     * @ORM\Column(name="rating")  
     */
    protected $rating;        

    /**
     * @ORM\Column(name="order_count")  
     */
    protected $orderCount;        

    /**
     * @ORM\Column(name="return_count")  
     */
    protected $returnCount;        

    /**
     * @ORM\Column(name="update_rating")  
     */
    protected $updateRating;        

    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Goods", inversedBy="oems") 
     * @ORM\JoinColumn(name="good_id", referencedColumnName="id")
     */
    protected $good;    
    
    /**
     * @ORM\Column(name="intersect_good_id")  
     */
    protected $intersectGoodId;        
    
    public function __construct() 
    {
        $this->selections = new ArrayCollection();
        $this->bids = new ArrayCollection();
    }    

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

    public function getTransferBrandName() 
    {
        $filter = new TransferName();
        return $filter->filter($this->brandName);
    }

    public function setBrandName($brandName) 
    {
        $this->brandName = $brandName;
    }     

    public function getIntersectGoodId() 
    {
        return $this->intersectGoodId;
    }

    public function setIntersectGoodId($intersectGoodId) 
    {
        $this->intersectGoodId = $intersectGoodId;
    }     

    public function getRating() {
        return $this->rating;
    }

    public function setRating($rating) {
        $this->rating = $rating;
        return $this;
    }

    public function getOrderCount() {
        return $this->orderCount;
    }

    public function setOrderCount($orderCount) {
        $this->orderCount = $orderCount;
        return $this;
    }

    public function getReturnCount() {
        return $this->returnCount;
    }

    public function setReturnCount($returnCount) {
        $this->returnCount = $returnCount;
        return $this;
    }

    public function getUpdateRating() {
        return $this->updateRating;
    }

    /**
     * Returns possible update rating as array.
     * @return array
     */
    public static function getUpdateRatingList() 
    {
        return [
            self::RATING_UPDATED => 'Рейтинг обновлен',
            self::RATING_FOR_UPDATE => 'Рейтинг не обновлен'
        ];
    }    
    
    /**
     * Returns update rating as string.
     * @return string
     */
    public function getUpdateRatingAsString()
    {
        $list = self::getUpdateRatingList();
        if (isset($list[$this->updateRating]))
            return $list[$this->updateRating];
        
        return 'Unknown';
    }    
    
    public function setUpdateRating($updateRating) {
        $this->updateRating = $updateRating;
        return $this;
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
     * Returns possible statuses as array.
     * @return array
     */
    public static function getAplPublishList() 
    {
        return [
            self::STATUS_ACTIVE => 1,
            self::STATUS_RETIRED => 0
        ];
    }    
    
    /**
     * Returns make status as string.
     * @return string
     */
    public function getAplPublish()
    {
        $list = self::getAplPublishList();
        if (isset($list[$this->status]))
            return $list[$this->status];
        
        return 1;
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
            self::SOURCE_EXT_SOURCE => 'Внешние источники',
            self::SOURCE_INTERSECT => self::INTERSECT_NAME,
            self::SOURCE_TD => self::SOURCE_TD_NAME,
            self::SOURCE_SUP => 'Поставщик',
            self::SOURCE_MAN => 'Введено вручную',
            self::SOURCE_CROSS => 'Кросс лист',
            self::SOURCE_MY_CODE => 'Свой артикул',
            self::SOURCE_IID => 'Номер у поставщика',
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
     * Returns possible sources as array.
     * @return array
     */
    public static function getSourceTagList() 
    {
        return [
            self::SOURCE_TD => 100,
            self::SOURCE_SUP => 1000,
            self::SOURCE_MAN => 1000,
            self::SOURCE_CROSS => 1000,
            self::SOURCE_INTERSECT => 1000,
            self::SOURCE_MY_CODE => 1000,
            self::SOURCE_IID => 1000,
        ];
    }    
    
    /**
     * Returns make source as string.
     * @return string
     */
    public function getSourceTagAsString()
    {
        $list = self::getSourceTagList();
        if (isset($list[$this->source]))
            return $list[$this->source];
        
        return 1000;
    }    
    
    /**
     * Returns possible apl cpub as array.
     * @return array
     */
    public static function getAplCpubList() 
    {
        return [
            self::SOURCE_TD => null,
            self::SOURCE_SUP => null,
            self::SOURCE_MAN => 1,
            self::SOURCE_CROSS => 1,
            self::SOURCE_INTERSECT => null,
            self::SOURCE_MY_CODE => null,
            self::SOURCE_IID => null,
        ];
    }    
    
    /**
     * Returns apl cpub as string.
     * @return string
     */
    public function getAplCpubAsString()
    {
        $list = self::getAplCpubList();
        if (isset($list[$this->source]))
            return $list[$this->source];
        
        return 0;
    }    

    /**
     * Sets source.
     * @param int $source     
     */
    public function setSource($source) 
    {
        $this->source = $source;
    }   
    
    /**
     * Returns the array of selection assigned to this.
     * @return array
     */
    public function getSelections()
    {
        return $this->selections;
    }
        
    /**
     * Assigns.
     * @param \Application\Entity\Selection $selection
     */
    public function addSelection($selection)
    {
        $this->selections[] = $selection;
    }
    
    /**
     * Returns the array of bid assigned to this.
     * @return array
     */
    public function getBids()
    {
        return $this->bids;
    }
        
    /**
     * Assigns.
     * @param \Application\Entity\Bid $bid
     */
    public function addBid($bid)
    {
        $this->bids[] = $bid;
    }
    
    /**
     * Массив для формы
     * @return array 
     */
    public function toArray()
    {
        $result = [
            'aplId' => $this->getId(),
            'id' => $this->getId(),
            'brand' => $this->getBrandName(),
            'intersectGoodId' => $this->getIntersectGoodId(),
            'oe' => $this->getOe(),
            'oeNumber' => $this->getOeNumber(),
            'orderCount' => $this->getOrderCount(),
            'rating' => $this->getRating(),
            'returnCount' => $this->getReturnCount(),
            'status' => $this->getStatus(),
            'transferBrand' => $this->getTransferBrandName(),
        ];
        
        return $result;
    }        
    
}
