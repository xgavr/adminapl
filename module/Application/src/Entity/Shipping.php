<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Company\Entity\Office;

/**
 * Description of Make
 * @ORM\Entity(repositoryClass="\Application\Repository\CourierRepository")
 * @ORM\Table(name="shipping")
 * @author Daddy
 */

class Shipping {
    
     // Make status constants.
    const STATUS_ACTIVE       = 1; // Active.
    const STATUS_RETIRED      = 2; // Retired.
    
    const RATE_TRIP       = 1; // За поездку.
    const RATE_DISTANCE   = 2; // За км
    const RATE_TK         = 3; // Транспортная компания
    const RATE_PICKUP     = 4; // Самовывоз

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
     * @ORM\Column(name="apl_id")   
     */
    protected $aplId;
    
    /**
     * @ORM\Column(name="comment")  
     */
    protected $comment;    

    /**
     * @ORM\Column(name="status")  
     */
    protected $status;    
       
    /**
     * @ORM\Column(name="rate")  
     */
    protected $rate;    
       
    /**
     * @ORM\Column(name="rate_trip")  
     */
    protected $rateTrip;
    
    /**
     * @ORM\Column(name="rate_trip_1")  
     */
    protected $rateTrip1;
    
    /**
     * @ORM\Column(name="rate_trip_2")  
     */
    protected $rateTrip2;
    
    /**
     * @ORM\Column(name="rate_distance")  
     */
    protected $rateDistance;
    
    /**
     * @ORM\Column(name="sorting")  
     */
    protected $sorting;

    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Office", inversedBy="shippings") 
     * @ORM\JoinColumn(name="office_id", referencedColumnName="id")
     */
    private $office;
    
    
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

    public function getName() 
    {
        return $this->name;
    }

    public function setName($name) 
    {
        $this->name = $name;
    }     

    public function getComment() 
    {
        return $this->comment;
    }

    public function setComment($comment) 
    {
        $this->comment = $comment;
    }     

    public function getAplId() 
    {
        return $this->aplId;
    }

    public function setAplId($aplId) 
    {
        $this->aplId = $aplId;
    }     
    
    public function getRateTrip() 
    {
        return (int) $this->rateTrip;
    }

    /**
     * Цена доставки от стоимости заказа
     * @param float $orderTotal
     * @return float
     */
    public function getOrderRateTrip($orderTotal) 
    {
        if ($orderTotal >= $this->office->getShippingLimit2()){
            return $this->rateTrip2;
        }
        if ($orderTotal >= $this->office->getShippingLimit1()){
            return $this->rateTrip1;
        }
        return (int) $this->rateTrip;
    }
    
    public function setRateTrip($rateTrip) 
    {
        $this->rateTrip = $rateTrip;
    }     
    
    public function getRateTrip1() 
    {
        return (int) $this->rateTrip1;
    }

    public function setRateTrip1($rateTrip1) 
    {
        $this->rateTrip1 = $rateTrip1;
    }     
    
    public function getRateTrip2() 
    {
        return (int) $this->rateTrip2;
    }

    public function setRateTrip2($rateTrip2) 
    {
        $this->rateTrip2 = $rateTrip2;
    }     
    
    public function getRateDistance() 
    {
        return (int) $this->rateDistance;
    }

    public function setRateDistance($rateDistance) 
    {
        $this->rateDistance = $rateDistance;
    }     
    
    public function getSorting() 
    {
        return $this->sorting;
    }

    public function setSorting($sorting) 
    {
        $this->sorting = $sorting;
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
        if (isset($list[$this->status])) {
            return $list[$this->status];
        }

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
     * Returns rate.
     * @return int     
     */
    public function getRate() 
    {
        return $this->rate;
    }

    
    /**
     * Returns possible rates as array.
     * @return array
     */
    public static function getRatesList() 
    {
        return [
            self::RATE_TRIP => 'За поездку',
            self::RATE_DISTANCE => 'За км',
            self::RATE_TK => 'Транспортная компания',
            self::RATE_PICKUP => 'Самовывоз'
        ];
    }    
    
    /**
     * Returns make rate as string.
     * @return string
     */
    public function getRateAsString()
    {
        $list = self::getRatesList();
        if (isset($list[$this->rate])) {
            return $list[$this->rate];
        }

        return 'Unknown';
    }    
    
    /**
     * Returns apl rate as string.
     * @return string
     */
    public function getAplRateAsString()
    {
        if ($this->rate == self::RATE_TRIP){
            return 'trip';
        }
        return '';
    }    

    /**
     * Returns apl shipping.
     * @return string
     */
    public function getAplShipping()
    {
        if ($this->rate == self::RATE_PICKUP){
            return 0;
        }
        return 1;
    }    

    /**
     * Sets rate.
     * @param int $rate     
     */
    public function setRate($rate) 
    {
        $this->rate = $rate;
    }   
    
    /**
     * Set shipping
     * @param Office $office
     */
    public function setOffice($office)
    {
        $this->office = $office;
        $office->addShippng($this);
    }

    /**
     * Returns the office.
     * @return Office     
     */
    public function getOffice() 
    {
        return $this->office;
    }
       
    public function toArray()
    {
        $result = [
            'id' => $this->getId(),
            'aplId' => $this->getAplId(),
            'comment' => $this->getComment(),
            'name' => $this->getName(),
            'office' => $this->getOffice()->getId(),
            'rate' => $this->getRate(),
            'rateDistance' => $this->getRateDistance(),
            'rateTrip' => $this->getRateTrip(),
            'rateTrip1' => $this->getRateTrip1(),
            'rateTrip2' => $this->getRateTrip2(),
            'sorting' => $this->getSorting(),
            'status' => $this->getStatus(),
            'shippingLimit1' => $this->getOffice()->getShippingLimit1(),
            'shippingLimit2' => $this->getOffice()->getShippingLimit2(),
        ];
        
        return $result;        
    }
}
