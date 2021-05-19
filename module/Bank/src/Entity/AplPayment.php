<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Bank\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Description of Bank
 * @ORM\Entity(repositoryClass="\Bank\Repository\BankRepository")
 * @ORM\Table(name="apl_payment")
 * @author Daddy
 */
class AplPayment {
    
    const STATUS_NO_MATCH   = 1; // нет совпадений.
    const STATUS_MATCH      = 2; // совпало.

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="apl_payment_id")   
     */
    protected $aplPaymentId;
   
    /**
     * @ORM\Column(name="apl_payment_date")   
     */
    protected $aplPaymentDate;
   
    /**
     * @ORM\Column(name="apl_payment_sum")   
     */
    protected $aplPaymentSum;
   
    /**
     * @ORM\Column(name="apl_payment_type")   
     */
    protected $aplPaymentType;
   
    /**
     * @ORM\Column(name="apl_payment_type_id")   
     */
    protected $aplPaymentTypeId;
    
    /**
     * @ORM\Column(name="status")   
     */
    protected $status = self::STATUS_NO_MATCH;
    
    
    /**
     * @ORM\ManyToMany(targetEntity="\Bank\Entity\Acquiring", mappedBy="aplPayments")
     */
    protected $acquirings;    
    
    public function __construct() {
       $this->acquirings = new ArrayCollection();
    }    
   
    /**
     * Возвращает Id
     * @return int
     */    
    public function getId() 
    {
        return $this->id;
    }

    /**
     * Устанавливает Id
     * @param int $id
     */
    public function setId($id) 
    {
        $this->id = $id;
    }     

    /**
     * 
     * @return integer
     */
    public function getAplPaymentId() 
    {
        return $this->aplPaymentId;
    }
    
    /**
     * 
     * @param integer $aplPaymentId
     */
    public function setAplPaymentId($aplPaymentId) 
    {
        $this->aplPaymentId = $aplPaymentId;
    }     

    /**
     * 
     * @return float
     */
    public function getAplPaymentSum() 
    {
        return $this->aplPaymentSum;
    }
    
    /**
     * 
     * @param float $aplPaymentSum
     */
    public function setAplPaymentSum($aplPaymentSum) 
    {
        $this->aplPaymentSum = $aplPaymentSum;
    }     

    /**
     * 
     * @return string
     */
    public function getAplPaymentType() 
    {
        return $this->aplPaymentType;
    }
    
    /**
     * 
     * @param string $aplPaymentType
     */
    public function setAplPaymentType($aplPaymentType) 
    {
        $this->aplPaymentType = $aplPaymentType;
    }     

    /**
     * 
     * @return integer
     */
    public function getAplPaymentTypeId() 
    {
        return $this->aplPaymentTypeId;
    }
    
    /**
     * 
     * @param integer $aplPaymentTypeId
     */
    public function setAplPaymentTypeId($aplPaymentTypeId) 
    {
        $this->aplPaymentTypeId = $aplPaymentTypeId;
    }     

    /**
     * ДАТА
     * @return date
     */
    public function getAplPaymentDate() 
    {
        return $this->aplPaymentDate;
    }

    /**
     * ДАТА
     * @param date $aplPaymentDate
     */
    public function setAplPaymentDate($aplPaymentDate) 
    {
        $this->aplPaymentDate = date('Y-m-d H:i:s', strtotime($aplPaymentDate));                
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
            self::STATUS_NO_MATCH => 'Нет совпадений',
            self::STATUS_MATCH => 'Совпало'
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
    
    
    public function getAcquirings() 
    {
        return $this->acquirings;
    }
    
    /**
     * 
     * @param \Bank\Entity\Asquiring $asquiring
     */
    public function addAcquiring($asquiring) 
    {
        $this->acquirings[] = $asquiring;        
    }         
    
    /**
     * 
     * @param \Bank\Entity\Asquiring $asquiring
     */
    public function removeAcquiringAssociation($asquiring) 
    {
        $this->acquirings->removeElement($asquiring);
    }        
            
    /**
     * Получить RRN
     * @return array
     */
    public function getAsquiringsRrn()
    {
        $result = [];
        foreach ($this->acquirings as $acquiring){
            $result[] = $acquiring->getRrn();
        }
        
        return $result;
    }
}
