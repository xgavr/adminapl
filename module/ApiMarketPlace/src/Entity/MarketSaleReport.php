<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ApiMarketPlace\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use ApiMarketPlace\Entity\MarketSaleReportItem;

/**
 * Description of Marketplace
 * @ORM\Entity(repositoryClass="\ApiMarketPlace\Repository\MarketplaceRepository")
 * @ORM\Table(name="market_sale_report")
 * @author Daddy
 */
class MarketSaleReport {
    
    const STATUS_ACTIVE       = 1; // Active.
    const STATUS_RETIRED      = 2; // Retired.
    
     // St status doc constants.
    const STATUS_DOC_RECD       = 1; // Получено.
    const STATUS_DOC_NOT_RECD  = 2; // Не получено.

     // St status doc constants.
    const STATUS_EX_NEW  = 1; // Не отправлено.
    const STATUS_EX_RECD  = 2; // Получено из АПЛ.
    const STATUS_EX_APL  = 3; // Отправлено в АПЛ.

    const STATUS_ACCOUNT_OK  = 1;// обновлено 
    const STATUS_ACCOUNT_NO  = 2;// не обновлено
    const STATUS_TAKE_NO  = 3;// не проведено
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * номер отчета
     * @ORM\Column(name="num")   
     */
    protected $num;

    /**
     * дата отчета
     * @ORM\Column(name="doc_date")   
     */
    protected $docDate;

    /**
     * начало периода
     * @ORM\Column(name="start_date")   
     */
    protected $startDate;

    /**
     * конец периода
     * @ORM\Column(name="stop_date")   
     */
    protected $stopDate;

    /**
     * сумма отчета
     * @ORM\Column(name="doc_amount")   
     */
    protected $docAmount;

    /**
     * сумма начислено
     * @ORM\Column(name="total_amount")   
     */
    protected $totalAmount;

    /**
     * сумма НДС
     * @ORM\Column(name="vat_amount")   
     */
    protected $vatAmount;

    /**
     * валюта отчета
     * @ORM\Column(name="currency_code")   
     */
    protected $currencyCode;

    /**
     * @ORM\Column(name="status")   
     */
    protected $status;

    /** 
     * @ORM\Column(name="status_doc")  
     */
    protected $statusDoc;

    /** 
     * @ORM\Column(name="status_ex")  
     */
    protected $statusEx;

    /** 
     * @ORM\Column(name="status_account")  
     */
    protected $statusAccount;
    
    /** 
     * дата создания
     * @ORM\Column(name="date_created")  
     */
    protected $dateCreated;  
            
    /**
     * @ORM\ManyToOne(targetEntity="ApiMarketPlace\Entity\Marketplace", inversedBy="marketSaleReports") 
     * @ORM\JoinColumn(name="marketplace_id", referencedColumnName="id")
     */
    private $marketplace;
    
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Contract", inversedBy="marketSaleReports") 
     * @ORM\JoinColumn(name="contract_id", referencedColumnName="id")
     */
    private $contract;

    /**
    * @ORM\OneToMany(targetEntity="ApiMarketPlace\Entity\MarketSaleReportItem", mappedBy="marketSaleReport")
    * @ORM\JoinColumn(name="id", referencedColumnName="marketplace_order_id")
     */
    private $marketSaleReportItems;    
    
    /**
     * Constructor.
     */
    public function __construct() 
    {
        $this->marketSaleReportItems = new ArrayCollection();
    }    
            
    public function getId() 
    {
        return $this->id;
    }
    
    public function getLogKey() 
    {
        return 'msr:'.$this->id;
    }


    public function setId($id) 
    {
        $this->id = $id;
    }     


    public function getNum() {
        return $this->num;
    }

    public function getDocDate() {
        return $this->docDate;
    }

    public function getDocDateAtomFormat() {
        $datetime = new \DateTime($this->docDate);
        return $datetime->format(\DateTime::ATOM);
    }

    public function getDocPresent() {
        return $this->marketplace->getName().' Отчет о реализации №'.$this->num.' от '.date('d-m-Y', strtotime($this->docDate));
    }

    public function getStartDate() {
        return $this->startDate;
    }

    public function getStartDateAtomFormat() {
        $datetime = new \DateTime($this->startDate);
        return $datetime->format(\DateTime::ATOM);
    }

    public function getStopDate() {
        return $this->stopDate;
    }

    public function getStopDateAtomFormat() {
        $datetime = new \DateTime($this->stopDate);
        return $datetime->format(\DateTime::ATOM);
    }
    
    public function getDocAmount() {
        return $this->docAmount;
    }

    public function getVatAmount() {
        return $this->vatAmount;
    }

    public function getCurrencyCode() {
        return $this->currencyCode;
    }

    public function getMarketplace() {
        return $this->marketplace;
    }

    public function getContract() {
        return $this->contract;
    }

    public function setNum($num): void {
        $this->num = $num;
    }

    public function setDocDate($docDate): void {
        $this->docDate = $docDate;
    }

    public function setStartDate($startDate): void {
        $this->startDate = $startDate;
    }

    public function setStopDate($stopDate): void {
        $this->stopDate = $stopDate;
    }

    public function setDocAmount($docAmount): void {
        $this->docAmount = $docAmount;
    }

    public function setVatAmount($vatAmount): void {
        $this->vatAmount = $vatAmount;
    }

    public function setCurrencyCode($currencyCode): void {
        $this->currencyCode = $currencyCode;
    }

    public function setMarketplace($marketplace): void {
        $this->marketplace = $marketplace;
    }

    public function setContract($contract): void {
        $this->contract = $contract;
    }

    public function getTotalAmount() {
        return $this->totalAmount;
    }

    public function setTotalAmount($totalAmount): void {
        $this->totalAmount = $totalAmount;
    }

        
    /**
     * Returns the date of marketplace creation.
     * @return string     
     */
    public function getDateCreated() 
    {
        return $this->dateCreated;
    }
    
    /**
     * Sets the date when this marketplace was created.
     * @param string $dateCreated     
     */
    public function setDateCreated($dateCreated) 
    {
        $this->dateCreated = $dateCreated;
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
     * Returns marketplace status as string.
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
    
    public function getStatusDoc() {
        return $this->statusDoc;
    }

    /**
     * Returns possible statuses as array.
     * @return array
     */
    public static function getStatusDocList() 
    {
        return [
            self::STATUS_DOC_RECD => 'Получено',
            self::STATUS_DOC_NOT_RECD => 'Не получено'
        ];
    }    
    
    /**
     * Returns user status as string.
     * @return string
     */
    public function getStatusDocAsString()
    {
        $list = self::getStatusList();
        if (isset($list[$this->statusDoc]))
            return $list[$this->statusDoc];
        
        return 'Unknown';
    }    
    
    public function getStatusEx() {
        return $this->statusEx;
    }

    /**
     * Returns possible statuses as array.
     * @return array
     */
    public static function getStatusExList() 
    {
        return [
            self::STATUS_EX_NEW => 'Новый',
            self::STATUS_EX_APL => 'Отправлен в АПЛ',
            self::STATUS_EX_RECD => 'Получен из АПЛ',
        ];
    }    
    
    /**
     * Returns user status as string.
     * @return string
     */
    public function getStatusExAsString()
    {
        $list = self::getStatusList();
        if (isset($list[$this->statusEx]))
            return $list[$this->statusEx];
        
        return 'Unknown';
    }    
    
    public function getStatusAccount() {
        return $this->statusAccount;
    }

    /**
     * Returns possible statusAccount as array.
     * @return array
     */
    public static function getStatusAccountList() 
    {
        return [
            self::STATUS_ACCOUNT_OK => 'Обновлено',
            self::STATUS_ACCOUNT_NO=> 'Не обновлено',
            self::STATUS_TAKE_NO=> 'Не проведено',
        ];
    }    
    
    /**
     * Returns statusAccount as string.
     * @return string
     */
    public function getStatusAccountAsString()
    {
        $list = self::getStatusAccountList();
        if (isset($list[$this->statusAccount]))
            return $list[$this->statusAccount];
        
        return 'Unknown';
    }    
    
    public function setStatusDoc($statusDoc): void {
        $this->statusDoc = $statusDoc;
    }

    public function setStatusEx($statusEx): void {
        $this->statusEx = $statusEx;
    }

    public function setStatusAccount($statusAccount): void {
        $this->statusAccount = $statusAccount;
    }

    /**
     * Returns the array of marketSaleReportItems assigned to this.
     * @return array
     */
    public function getMarketSaleReportItems()
    {
        return $this->marketSaleReportItems;
    }
        
    /**
     * Assigns.
     * @param MarketSaleReportItem $marketSaleReportItem
     */
    public function addMarketSaleReportItem($marketSaleReportItem)
    {
        $this->marketSaleReportItems[] = $marketSaleReportItem;
    }    
    
    /**
     * Массив для формы
     * @return array 
     */
    public function toArray()
    {
        $result = [
            'status' => $this->getStatus(),
            'num' => $this->getNum(),
            'contract' => $this->getContract()->toArray(),
            'currencyCode' => $this->getCurrencyCode(),
            'docAmount' => $this->getDocAmount(),
            'docDate' => $this->getDocDateAtomFormat(),
            'marketplace' => $this->getMarketplace()->getId(),
            'startDate' => $this->getStartDateAtomFormat(),
            'stopDate' => $this->getStopDateAtomFormat(),
            'vatAmount' => $this->getVatAmount(),
            'id' => $this->getId(),
            'legal' => $this->getContract()->getLegal()->toArray(),
            'items' => $this->itemsToArray(),
        ];
        
        return $result;
    }    

    /**
     * Массив для формы
     * @return array 
     */
    public function itemsToArray()
    {
        $result = [];
        foreach ($this->marketSaleReportItems as $item){
            $result[] = $item->toArray();
        }    
        
        return $result;
    }    
}
