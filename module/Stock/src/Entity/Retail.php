<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Stock\Entity;

use Doctrine\ORM\Mapping as ORM;
use Company\Entity\Legal;
use Company\Entity\Office;
use Application\Entity\Contact;
use Stock\Entity\Pt;
use Application\Entity\Order;


/**
 * Description of Mutual
 * @ORM\Entity(repositoryClass="\Stock\Repository\MutualRepository")
 * @ORM\Table(name="retail")
 * @author Daddy
 */
class Retail {
    
    const STATUS_ACTIVE       = 1; // Active.
    const STATUS_RETIRED      = 2; // Retired.
    const STATUS_COMMISSION    = 3; // commission.
    
    const REVISE_OK       = 1; // Сверка ок.
    const REVISE_NOT      = 2; // Сверки нет.

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="doc_key")   
     */
    protected $docKey;
    
    /** 
     * @ORM\Column(name="date_oper")  
     */
    protected $dateOper;
    
    /**
     * @ORM\Column(name="status")   
     */
    protected $status;

    /**
     * @ORM\Column(name="revise")   
     */
    protected $revise;

    /** 
     * @ORM\Column(name="amount")  
     */
    protected $amount;

    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Contact", inversedBy="retails") 
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="id")
     */
    private $contact;
        
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Office", inversedBy="retails") 
     * @ORM\JoinColumn(name="office_id", referencedColumnName="id")
     */
    private $office;    
    
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Legal", inversedBy="retails") 
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id")
     */
    private $company;

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

    public function setDocKey($docKey) 
    {
        $this->docKey = $docKey;
    }     

    public function getDocKey() 
    {
        return $this->docKey;
    }

    /**
     * Returns the date of operation.
     * @return string     
     */
    public function getDateOper() 
    {
        return $this->dateOper;
    }
    
    /**
     * Sets the date when this oper was created.
     * @param string $dateOper     
     */
    public function setDateOper($dateOper) 
    {
        $this->dateOper = $dateOper;
    }    
                
    /**
     * Sets  amount.
     * @param float $amount     
     */
    public function setAmount($amount) 
    {
        $this->amount = $amount;
    }    
    
    /**
     * Returns the amount of doc.
     * @return float     
     */
    public function getAmount() 
    {
        return $this->amount;
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
     * Returns possible order status.
     * @param Order $order
     * @return integer
     */
    public static function getStatusFromOrder($order) 
    {
        switch ($order->getStatus()){
            case Order::STATUS_SHIPPED: return self::STATUS_ACTIVE;
            default: return self::STATUS_RETIRED;    
        }
    }    
    
    /**
     * Returns possible pt status.
     * @param Pt $pt
     * @return integer
     */
    public static function getStatusFromPt($pt) 
    {
        switch ($pt->getStatus()){
            case Pt::STATUS_RETIRED: return self::STATUS_RETIRED;
            default: return self::STATUS_ACTIVE;    
        }
    }    
    
    /**
     * Returns possible vt status.
     * @param Vt $pt
     * @return integer
     */
    public static function getStatusFromVt($vt) 
    {
        switch ($vt->getStatus()){
            case Vt::STATUS_RETIRED: return self::STATUS_RETIRED;
            case Vt::STATUS_COMMISSION: return self::STATUS_COMMISSION;
            default: return self::STATUS_ACTIVE;    
        }
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
            self::STATUS_COMMISSION => 'На комиссии',
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
    
    /**
     * Returns revise.
     * @return int     
     */
    public function getRevise() 
    {
        return $this->revise;
    }

    /**
     * Returns possible revises as array.
     * @return array
     */
    public static function getReviseList() 
    {
        return [
            self::REVISE_OK => 'Проверено',
            self::REVISE_NOT => 'Не проверено'
        ];
    }    
    
    /**
     * Returns revise as string.
     * @return string
     */
    public function getReviseAsString()
    {
        $list = self::getReviseList();
        if (isset($list[$this->revise]))
            return $list[$this->revise];
        
        return 'Unknown';
    }    
    
    /**
     * Sets revise.
     * @param int $revise     
     */
    public function setRevise($revise) 
    {
        $this->revise = $revise;
    }   
    
    /**
     * Returns the contact.
     * @return Contact     
     */
    public function getContact() 
    {
        return $this->contact;
    }


    /**
     * Returns the office.
     * @return Office     
     */
    public function getOffice() 
    {
        return $this->office;
    }

    /**
     * Returns the company.
     * @return Legal     
     */
    public function getCompany() 
    {
        return $this->company;
    }

}
