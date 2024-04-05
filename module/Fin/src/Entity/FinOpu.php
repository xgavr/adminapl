<?php
namespace Fin\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Company\Entity\Legal;

/**
 * This class represents a finopy.
 * @ORM\Entity(repositoryClass="\Fin\Repository\FinRepository")
 * @ORM\Table(name="fin_opu")
 */
class FinOpu
{
    const STATUS_FACT       = 1; // fact.
    const STATUS_PLAN      = 2; // plan.
    
    /**
     * @ORM\Id
     * @ORM\Column(name="id")
     * @ORM\GeneratedValue
     */
    protected $id;

    /** 
     * @ORM\Column(name="period")  
     */
    protected $period;
    
    /** 
     * @ORM\Column(name="status")  
     */
    protected $status;

    /** 
     * @ORM\Column(name="revenue_retail")  
     */
    protected $revenueRetail;

    /** 
     * @ORM\Column(name="revenue_tp")  
     */
    protected $revenueTp;

    /** 
     * @ORM\Column(name="revenue_total")  
     */
    protected $revenueTotal;

    /** 
     * @ORM\Column(name="purchase_retail")  
     */
    protected $purchaseRetail;

    /** 
     * @ORM\Column(name="margin_retail")  
     */
    protected $marginRetail;

    /** 
     * @ORM\Column(name="purchase_tp")  
     */
    protected $purchaseTp;

    /** 
     * @ORM\Column(name="purchase_total")  
     */
    protected $purchaseTotal;

    /** 
     * @ORM\Column(name="income_retail")  
     */
    protected $incomeRetail;

    /** 
     * @ORM\Column(name="income_tp")  
     */
    protected $incomeTp;

    /** 
     * @ORM\Column(name="margin_tp")  
     */
    protected $marginTp;

    /** 
     * @ORM\Column(name="order_count")  
     */
    protected $orderCount;

    /** 
     * @ORM\Column(name="avg_bill")  
     */
    protected $avgBill;
    
    /** 
     * @ORM\Column(name="income_total")  
     */
    protected $incomeTotal;

    /** 
     * @ORM\Column(name="cost_retail")  
     */
    protected $costRetail;

    /** 
     * @ORM\Column(name="cost_tp")  
     */
    protected $costTp;

    /** 
     * @ORM\Column(name="cost_fix")  
     */
    protected $costFix;

    /** 
     * @ORM\Column(name="cost_total")  
     */
    protected $costTotal;

    /** 
     * @ORM\Column(name="zp_retail")  
     */
    protected $zpRetail;

    /** 
     * @ORM\Column(name="zp_tp")  
     */
    protected $zpTp;

    /** 
     * @ORM\Column(name="zp_adm")  
     */
    protected $zpAdm;

    /** 
     * @ORM\Column(name="zp_total")  
     */
    protected $zpTotal;

    /** 
     * @ORM\Column(name="profit")  
     */
    protected $profit;

    /** 
     * @ORM\Column(name="tax")  
     */
    protected $tax;

    /** 
     * @ORM\Column(name="esn")  
     */
    protected $esn;

    /** 
     * @ORM\Column(name="profit_net")  
     */
    protected $profitNet;

    /** 
     * @ORM\Column(name="fund")  
     */
    protected $fund;
    
    /** 
     * @ORM\Column(name="new_client_count")  
     */
    protected $newClientCount;
    
    /** 
     * Стоимость нового заказа
     * @ORM\Column(name="cpo")  
     */
    protected $cpo;
    
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Legal", inversedBy="finOpus") 
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id")
     */
    private $company;
    
    /**
     * Constructor.
     */
    public function __construct() 
    {
    }
    
    public function getId() {
        return $this->id;
    }

    public function getPeriod() {
        return $this->period;
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
            self::STATUS_FACT => 'Факт',
            self::STATUS_PLAN => 'План'
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
    
    public function getRevenueRetail() {
        return $this->revenueRetail;
    }

    public function getRevenueTp() {
        return $this->revenueTp;
    }

    public function getRevenueTotal() {
        return $this->revenueTotal;
    }

    public function getPurchaseRetail() {
        return $this->purchaseRetail;
    }

    public function getPurchaseTp() {
        return $this->purchaseTp;
    }

    public function getPurchaseTotal() {
        return $this->purchaseTotal;
    }

    public function getMarginRetail() {
        return $this->marginRetail;
    }

    public function getMarginTp() {
        return $this->marginTp;
    }
    
    public function getCostRetail() {
        return $this->costRetail;
    }

    public function getCostTp() {
        return $this->costTp;
    }

    public function getCostFix() {
        return $this->costFix;
    }

    public function getCostTotal() {
        return $this->costTotal;
    }

    public function getZpRetail() {
        return $this->zpRetail;
    }

    public function getZpTp() {
        return $this->zpTp;
    }

    public function getZpAdm() {
        return $this->zpAdm;
    }

    public function getZpTotal() {
        return $this->zpTotal;
    }

    public function getProfit() {
        return $this->profit;
    }

    public function getTax() {
        return $this->tax;
    }

    public function getFund() {
        return $this->fund;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function setPeriod($period) {
        $this->period = $period;
        return $this;
    }

    public function setStatus($status) {
        $this->status = $status;
        return $this;
    }

    public function setRevenueRetail($revenueRetail) {
        $this->revenueRetail = $revenueRetail;
        return $this;
    }

    public function setRevenueTp($revenueTp) {
        $this->revenueTp = $revenueTp;
        return $this;
    }

    public function setRevenueTotal($revenueTotal) {
        $this->revenueTotal = $revenueTotal;
        return $this;
    }

    public function setPurchaseRetail($purchaseRetail) {
        $this->purchaseRetail = $purchaseRetail;
        return $this;
    }

    public function setPurchaseTp($purchaseTp) {
        $this->purchaseTp = $purchaseTp;
        return $this;
    }

    public function setPurchaseTotal($purchaseTotal) {
        $this->purchaseTotal = $purchaseTotal;
        return $this;
    }

    public function setMarginRetail($marginRetail) {
        $this->marginRetail = $marginRetail;
        return $this;
    }

    public function setMarginTp($marginTp) {
        $this->marginTp = $marginTp;
        return $this;
    }
    
    public function setOrderCount($orderCount) {
        $this->orderCount = $orderCount;
        return $this;
    }

    public function setAvgBill($avgBill) {
        $this->avgBill = $avgBill;
        return $this;
    }
    
    public function setCostRetail($costRetail) {
        $this->costRetail = $costRetail;
        return $this;
    }

    public function setCostTp($costTp) {
        $this->costTp = $costTp;
        return $this;
    }

    public function setCostFix($costFix) {
        $this->costFix = $costFix;
        return $this;
    }

    public function setCostTotal($costTotal) {
        $this->costTotal = $costTotal;
        return $this;
    }

    public function setZpRetail($zpRetail) {
        $this->zpRetail = $zpRetail;
        return $this;
    }

    public function setZpTp($zpTp) {
        $this->zpTp = $zpTp;
        return $this;
    }

    public function setZpAdm($zpAdm) {
        $this->zpAdm = $zpAdm;
        return $this;
    }

    public function setZpTotal($zpTotal) {
        $this->zpTotal = $zpTotal;
        return $this;
    }

    public function setProfit($profit) {
        $this->profit = $profit;
        return $this;
    }

    public function setTax($tax) {
        $this->tax = $tax;
        return $this;
    }

    public function setFund($fund) {
        $this->fund = $fund;
        return $this;
    }
    
    public function getIncomeRetail() {
        return $this->incomeRetail;
    }

    public function getIncomeTp() {
        return $this->incomeTp;
    }

    public function getIncomeTotal() {
        return $this->incomeTotal;
    }

    public function setIncomeRetail($incomeRetail) {
        $this->incomeRetail = $incomeRetail;
        return $this;
    }

    public function setIncomeTp($incomeTp) {
        $this->incomeTp = $incomeTp;
        return $this;
    }

    public function setIncomeTotal($incomeTotal) {
        $this->incomeTotal = $incomeTotal;
        return $this;
    }
    
    public function getEsn() {
        return $this->esn;
    }

    public function setEsn($esn) {
        $this->esn = $esn;
        return $this;
    }
    
    public function getProfitNet() {
        return $this->profitNet;
    }

    public function setProfitNet($profitNet) {
        $this->profitNet = $profitNet;
        return $this;
    }

    public function getOrderCount() {
        return $this->orderCount;
    }

    public function getAvgBill() {
        return $this->avgBill;
    }
    
    public function getNewClientCount() {
        return $this->newClientCount;
    }

    public function getCpo() {
        return $this->cpo;
    }

    public function setNewClientCount($newClientCount) {
        $this->newClientCount = $newClientCount;
        return $this;
    }

    public function setCpo($cpo) {
        $this->cpo = $cpo;
        return $this;
    }

    /**
     * 
     * @return Legal
     */
    public function getCompany() {
        return $this->company;
    }

    /**
     * 
     * @param Legal $company
     * @return $this
     */
    public function setCompany($company) {
        $this->company = $company;
        return $this;
    }

    public static function getMarkList()
    {
        return [
            'revenueRetail' => '<a href="/opu/retail?kind=revenueRetail" target="_blank">Выручка розница</a>',
            'purchaseRetail' => 'Закупка розница',
            'incomeRetail' => 'Доход розница',
            'marginRetail' => 'Маржа розница (%)',
            'orderCount' => 'Количество заказов',
            'avgBill' => 'Средний чек',
            'incomeRetail_' => '',
            'revenueTp' => 'Выручка ТП',
            'purchaseTp' => 'Закупка ТП',
            'costTp' => 'Расходы ТП',
            'incomeTp' => 'Доход ТП',
            'marginTp' => 'Маржа ТП (%)',
            'incomeTp_' => '',
            'incomeTotal' => 'Доход всего',
            'incomeTotal_' => '',
//            'costRetail' => 'Расходы текущие',
//            'costFix' => 'Расходы постоянные',
            'costTotal' => '<a href="/opu/cost" target="_blank">Расходы</a>',
            'zp_' => '',
//            'zpRetail' => 'Зарплата розница',
//            'zpTp' => 'Зарплата ТП',
//            'zpAdm' => 'Зарплата администрации',
//            'profit_' => '',
            'zpTotal' => '<a href="/opu/zp" target="_blank">Зарплата</a>',
            'esn' => 'ЕСН',
            'esn_' => '',
            'profit' => 'Прибыль',
            'tax' => 'Налог',
            'profitNet' => 'Чистая пибыль',
            'fund' => 'Фонды',
        ];    
    }
    
    public static function getSuccessList()
    {
        return [
            'revenueRetail' => 'Выручка розница',
            'revenueTp' => 'Оборот ТП',
        ];    
    }
    
    public static function getWarningList()
    {
        return [
            'purchaseRetail' => 'Закупка розница',
            'purchaseTp' => 'Закупка ТП',
            'costTp' => 'Расходы ТП',
            'costTotal' => 'Расходы',
            'zpTotal' => 'Зарплата',
            'esn' => 'ЕСН',
            'tax' => 'Налог',
        ];    
    }
    
    public static function getInfoList()
    {
        return [
            'incomeRetail' => 'Доход розница',
            'marginRetail' => 'Маржа розница (%)',
            'incomeTp' => 'Доход ТП',
            'incomeTotal' => 'Доход всего',
            'marginTp' => 'Маржа ТП (%)',
            'orderCount' => 'Количество заказов',
            'avgBill' => 'Средний чек',
            'profit' => 'Прибыль',
            'profitNet' => 'Чистая пибыль',
        ];    
    }
    
    public static function getRetailKindList()
    {
        return [
            'revenueRetail' => 'Выручка розница',
            'purchaseRetail' => 'Закупка розница',
            'incomeRetail' => 'Доход розница',
            'marginRetail' => 'Маржа розница (%)',
        ];    
    }
    
    public static function getMuteList()
    {
        return [
            'marginRetail' => 'Маржа розница (%)',
            'marginTp' => 'Маржа ТП (%)',
        ];    
    }
    
    /**
     * Массив для отчета
     * @return array
     */
    public static function emptyOpuYear()
    {
        $result = [];
        foreach (self::getMarkList() as $key=>$value){
             $resultRow['key'] = $key;
             $resultRow['mark'] = $value;
             $resultRow['01'] = 0;
             $resultRow['02'] = 0;
             $resultRow['03'] = 0;
             $resultRow['04'] = 0;
             $resultRow['05'] = 0;
             $resultRow['06'] = 0;
             $resultRow['07'] = 0;
             $resultRow['08'] = 0;
             $resultRow['09'] = 0;
             $resultRow['10'] = 0;
             $resultRow['11'] = 0;
             $resultRow['12'] = 0;
             $resultRow['13'] = 0;
             $result[$key] = $resultRow;
        }
        
        return $result;
    }
}



