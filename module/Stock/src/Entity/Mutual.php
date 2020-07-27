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
use Company\Entity\Contract;


/**
 * Description of Mutual
 * @ORM\Entity(repositoryClass="\Stock\Repository\MutualRepository")
 * @ORM\Table(name="mutual")
 * @author Daddy
 */
class Mutual {
    
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
     * @ORM\ManyToOne(targetEntity="Company\Entity\Legal", inversedBy="mutuals") 
     * @ORM\JoinColumn(name="legal_id", referencedColumnName="id")
     */
    private $legal;
    
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Contract", inversedBy="mutuals") 
     * @ORM\JoinColumn(name="contract_id", referencedColumnName="id")
     */
    private $contract;
    
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Office", inversedBy="mutuals") 
     * @ORM\JoinColumn(name="office_id", referencedColumnName="id")
     */
    private $office;    
    
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Legal", inversedBy="mutuals") 
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
     * Returns the legal.
     * @return Legal     
     */
    public function getLegal() 
    {
        return $this->legal;
    }

    /**
     * Returns the contract.
     * @return Contract     
     */
    public function getContract() 
    {
        return $this->contract;
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
