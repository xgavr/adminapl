<?php

namespace Company\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Company\Entity\Legal;
use Company\Entity\Office;
use Bank\Entity\Payment;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Contract
 * @ORM\Entity(repositoryClass="\Company\Repository\LegalRepository")
 * @ORM\Table(name="contract")
 *
 * @author Daddy
 */
class Contract {

     // Contract status constants.
    const STATUS_ACTIVE       = 1; // Active.
    const STATUS_RETIRED      = 2; // Retired.

     // Contract kind constants.
    const KIND_SUPPLIER       = 1; // С поставщиком.
    const KIND_CUSTOMER      = 2; // С покупателем.
    const KIND_OTHER         = 3; // Прочее
    const KIND_COMISSIONER      = 4; // с комиссионером
    const KIND_COMITENT      = 5; // с комитентом

     // Contract pay constants.
    const PAY_CASH       = 1; // Оплата нал.
    const PAY_CASHLESS   = 2; // Оплата безнал.

     // Nds constants.
    const NDS_NO   = Payment::NDS_NO; // без НДС.
    const NDS_10   = Payment::NDS_10; // Оплата безнал.
    const NDS_20   = Payment::NDS_20; // Оплата безнал.

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
     * @ORM\Column(name="act")   
     */
    protected $act;

    /** 
     * @ORM\Column(name="date_created")  
     */
    protected $dateCreated;    
    
    /** 
     * @ORM\Column(name="date_start")  
     */
    protected $dateStart;    
    
    /** 
     * @ORM\Column(name="balance")  
     */
    protected $balance;

    /** 
     * @ORM\Column(name="status")  
     */
    protected $status;    
        
    /** 
     * @ORM\Column(name="kind")  
     */
    protected $kind;    

    /** 
     * @ORM\Column(name="pay")  
     */
    protected $pay;    

    /** 
     * @ORM\Column(name="nds")  
     */
    protected $nds;
    
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Legal", inversedBy="contracts") 
     * @ORM\JoinColumn(name="legal_id", referencedColumnName="id")
     */
    private $legal;

    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Office", inversedBy="contracts") 
     * @ORM\JoinColumn(name="office_id", referencedColumnName="id")
     */
    private $office;

    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Legal", inversedBy="contracts") 
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id")
     */
    private $company;
    
   /**
    * @ORM\OneToMany(targetEntity="Stock\Entity\Ptu", mappedBy="contract")
    * @ORM\JoinColumn(name="id", referencedColumnName="contract_id")
   */
   private $ptu;        

   /**
    * @ORM\OneToMany(targetEntity="Stock\Entity\Mutual", mappedBy="contract")
    * @ORM\JoinColumn(name="id", referencedColumnName="contract_id")
   */
   private $mutuals;        

    /**
     * Constructor.
     */
    public function __construct() 
    {
        $this->ptu = new ArrayCollection();
        $this->mutuals = new ArrayCollection();
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
        if ($this->name){
            return $this->name;
        }
        
        return 'Договор';
    }

    public function setName($name) 
    {
        if (!empty($name)){
            $this->name = trim($name);
        } else {
            $this->name = trim($name).' '. mb_strtolower($this->getKindAsString());
        }    
    }     

    public function getAct() 
    {
        if ($this->act){
            return $this->act;
        }
        
        return 'б/н';
    }
    
    public function getDocNo()
    {
        return $this->getAct();
    }

    public function setAct($act) 
    {
        $this->act = trim($act);
    }     

    /**
     * Представление договора
     * @param string $ContractName
     * @return string
     */
    public function getContractPresent($ContractName = 'Договор') 
    {
        return trim("$ContractName №".$this->act.' от '.date('d.m.Y', strtotime($this->dateStart)).' г.');
    }

    /**
     * Представление договора c нал/безнал
     * @param string $ContractName
     * @return string
     */
    public function getContractPresentPay($ContractName = 'Договор') 
    {
        return $this->getPayAsString()." №".$this->act.' от '.date('d.m.Y', strtotime($this->dateStart));
    }

    public function getBalance() {
        return $this->balance;
    }

    public function setBalance($balance) {
        $this->balance = $balance;
        return $this;
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
            self::STATUS_ACTIVE => 'Действующий',
            self::STATUS_RETIRED => 'Закрыт'
        ];
    }    
    
    /**
     * Returns contract status as string.
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
     * Returns kind.
     * @return int     
     */
    public function getKind() 
    {
        return $this->kind;
    }

    /**
     * Returns possible kinds as array.
     * @return array
     */
    public static function getKindList() 
    {
        return [
            self::KIND_SUPPLIER => 'С поставщиком',
            self::KIND_CUSTOMER => 'С покупателем',
            self::KIND_COMISSIONER => 'С комиссионером',
            self::KIND_COMITENT => 'С комитентом',
            self::KIND_OTHER => 'Прочее',
        ];
    }    
    
    /**
     * Returns contract kind as string.
     * @return string
     */
    public function getKindAsString()
    {
        $list = self::getKindList();
        if (isset($list[$this->kind]))
            return $list[$this->kind];
        
        return 'Unknown';
    }    
    
    /**
     * Sets kind.
     * @param int $kind     
     */
    public function setKind($kind) 
    {
        $this->kind = $kind;
    }   

    /**
     * Returns pay.
     * @return int     
     */
    public function getPay() 
    {
        return $this->pay;
    }

    /**
     * Returns possible pay as array.
     * @return array
     */
    public static function getPayList() 
    {
        return [
            self::PAY_CASH => 'Нал',
            self::PAY_CASHLESS => 'Безнал',
        ];
    }    
    
    /**
     * Returns contract pay as string.
     * @return string
     */
    public function getPayAsString()
    {
        $list = self::getPayList();
        if (isset($list[$this->pay]))
            return $list[$this->pay];
        
        return 'Unknown';
    }    
    
    /**
     * Returns possible apl cashless as array.
     * @return array
     */
    public static function getAplCashlessList() 
    {
        return [
            self::PAY_CASH => 0,
            self::PAY_CASHLESS => 1,
        ];
    }    
    
    /**
     * Returns contract apl cashless as string.
     * @return string
     */
    public function getAplCashlessAsString()
    {
        $list = self::getAplCashlessList();
        if (isset($list[$this->pay]))
            return $list[$this->pay];
        
        return 'Unknown';
    }    
    
    /**
     * Sets pay.
     * @param int $pay     
     */
    public function setPay($pay) 
    {
        $this->pay = $pay;
    }   
    
    /**
     * Returns НДС.
     * @return int     
     */
    public function getNds() 
    {
        return $this->nds;
    }

    /**
     * Returns possible nds as array.
     * @return array
     */
    public static function getNdsList() 
    {
        return Payment::getNdsList();
    }    
    
    /**
     * Returns possible nds percent as array.
     * @return array
     */
    public static function getNdsPercentList() 
    {
        return Payment::getNdsPercentList();
    }    

    /**
     * Расчитать ндс
     * @param float $amount
     * @param integer $nds
     */
    public static function nds($amount, $nds) 
    {
        return Payment::nds($amount, $nds);
    }


    /**
     * Returns nds as string.
     * @return string
     */
    public function getNdsAsString()
    {
        $list = self::getNdsList();
        if (isset($list[$this->nds]))
            return $list[$this->nds];
        
        return 'Unknown';
    }    
    
    /**
     * Returns nds percent as string.
     * @return string
     */
    public function getNdsPercentAsString()
    {
        $list = self::getNdsPercentList();
        if (isset($list[$this->nds]))
            return $list[$this->nds];
        
        return 'Unknown';
    }    

    /**
     * Sets nds.
     * @param int $nds     
     */
    public function setNds($nds) 
    {
        $this->nds = $nds;
    }       

    /**
     * Returns the date of contract creation.
     * @return string     
     */
    public function getDateCreated() 
    {
        return $this->dateCreated;
    }
    
    /**
     * Sets the date when this user was created.
     * @param string $dateCreated     
     */
    public function setDateCreated($dateCreated) 
    {
        $this->dateCreated = $dateCreated;
    }    
        
    /**
     * Returns the date start of contract.
     * @return string     
     */
    public function getDateStart() 
    {
        return $this->dateStart;
    }
    
    public function getDateStartAtomFormat() {
        $datetime = new \DateTime($this->dateStart);
        return $datetime->format(\DateTime::ATOM);
    }
    
    /**
     * Sets the date start when this contract was start.
     * @param string $dateStart     
     */
    public function setDateStart($dateStart) 
    {
        $this->dateStart = $dateStart;
    }    
            
    /*
     * @return \Company\Entity\Legal
     */    
    public function getLegal() 
    {
        return $this->legal;
    }

    /**
     * @param Legal $legal
     */    
    public function setLegal($legal) 
    {
        $this->legal = $legal;
        $legal->addContract($this);
    }     
        
    /*
     * @return \Company\Entity\Office
     */    
    public function getOffice() 
    {
        return $this->office;
    }

    /**
     * @param Office $office
     */    
    public function setOffice($office) 
    {
        $this->office = $office;
        $office->addContract($this);
    }             

    /*
     * @return Legal
     */    
    public function getCompany() 
    {
        return $this->company;
    }

    /**
     * @param Legal $company
     */    
    public function setCompany($company) 
    {
        $this->company = $company;
    }             

    /*
     * Возвращает связанный ptu.
     * @return Ptu
     */    
    public function getPtu() 
    {
        return $this->ptu;
    }    
    
    public function getMutuals() {
        return $this->mutuals;
    }
    
    /**
     * Массив для формы
     * @return array 
     */
    public function toArray()
    {
        $result = [
            'act' => $this->getAct(),
            'date' => $this->getDateStartAtomFormat(),
            'name' => $this->getName(),
            'kind' => $this->getKind(),
            'pay' => $this->getPay(),
            'id' => $this->getId(),
        ];
        
        return $result;
    }            
}
