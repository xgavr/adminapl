<?php
namespace Fin\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Company\Entity\Legal;

/**
 * This class represents a findds.
 * @ORM\Entity(repositoryClass="\Fin\Repository\DdsRepository")
 * @ORM\Table(name="fin_dds")
 */
class FinDds
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
     * @ORM\Column(name="bank_begin")  
     */
    protected $bankBegin;

    /** 
     * @ORM\Column(name="cash_begin")  
     */
    protected $cashBegin;

    /** 
     * @ORM\Column(name="accountant_begin")  
     */
    protected $accountantBegin;

    /** 
     * @ORM\Column(name="total_begin")  
     */
    protected $totalBegin;

    /** 
     * @ORM\Column(name="bank_end")  
     */
    protected $bankEnd;

    /** 
     * @ORM\Column(name="cash_end")  
     */
    protected $cashEnd;

    /** 
     * @ORM\Column(name="accountant_end")  
     */
    protected $accountantEnd;

    /** 
     * @ORM\Column(name="total_end")  
     */
    protected $totalEnd;

    /** 
     * @ORM\Column(name="revenue_in")  
     */
    protected $revenueIn;

    /** 
     * @ORM\Column(name="revenue_out")  
     */
    protected $revenueOut;

    /** 
     * @ORM\Column(name="supplier_out")  
     */
    protected $supplierOut;

    /** 
     * @ORM\Column(name="supplier_in")  
     */
    protected $supplierIn;
    
    /** 
     * @ORM\Column(name="zp")  
     */
    protected $zp;

    /** 
     * @ORM\Column(name="tax")  
     */
    protected $tax;

    /** 
     * @ORM\Column(name="cost")  
     */
    protected $cost;

    /** 
     * @ORM\Column(name="loans_in")  
     */
    protected $loansIn;

    /** 
     * @ORM\Column(name="loans_out")  
     */
    protected $loansOut;

    /** 
     * @ORM\Column(name="deposit_in")  
     */
    protected $depositIn;

    /** 
     * @ORM\Column(name="deposit_out")  
     */
    protected $depositOut;

    /** 
     * @ORM\Column(name="other_in")  
     */
    protected $otherIn;

    /** 
     * @ORM\Column(name="other_out")  
     */
    protected $otherOut;

    /** 
     * @ORM\Column(name="total_in")  
     */
    protected $totalIn;

    /** 
     * @ORM\Column(name="total_out")  
     */
    protected $totalOut;

    /** 
     * @ORM\Column(name="good_begin")  
     */
    protected $goodBegin;

    /** 
     * @ORM\Column(name="good_end")  
     */
    protected $goodEnd;

    /** 
     * @ORM\Column(name="good_in")  
     */
    protected $goodIn;
    
    /** 
     * @ORM\Column(name="good_out")  
     */
    protected $goodOut;
    
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
    
    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function getPeriod() {
        return $this->period;
    }

    public function setPeriod($period) {
        $this->period = $period;
        return $this;
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
    
    public function setStatus($status) {
        $this->status = $status;
        return $this;
    }

    public function getBankBegin() {
        return $this->bankBegin;
    }

    public function setBankBegin($bankBegin) {
        $this->bankBegin = $bankBegin;
        return $this;
    }

    public function getCashBegin() {
        return $this->cashBegin;
    }

    public function setCashBegin($cashBegin) {
        $this->cashBegin = $cashBegin;
        return $this;
    }

    public function getAccountantBegin() {
        return $this->accountantBegin;
    }

    public function setAccountantBegin($accountantBegin) {
        $this->accountantBegin = $accountantBegin;
        return $this;
    }

    public function getTotalBegin() {
        return $this->totalBegin;
    }

    public function setTotalBegin($totalBegin) {
        $this->totalBegin = $totalBegin;
        return $this;
    }

    public function getBankEnd() {
        return $this->bankEnd;
    }

    public function setBankEnd($bankEnd) {
        $this->bankEnd = $bankEnd;
        return $this;
    }

    public function getCashEnd() {
        return $this->cashEnd;
    }

    public function setCashEnd($cashEnd) {
        $this->cashEnd = $cashEnd;
        return $this;
    }

    public function getAccountantEnd() {
        return $this->accountantEnd;
    }

    public function setAccountantEnd($accountantEnd) {
        $this->accountantEnd = $accountantEnd;
        return $this;
    }

    public function getTotalEnd() {
        return $this->totalEnd;
    }

    public function setTotalEnd($totalEnd) {
        $this->totalEnd = $totalEnd;
        return $this;
    }

    public function getRevenueIn() {
        return $this->revenueIn;
    }

    public function setRevenueIn($revenueIn) {
        $this->revenueIn = $revenueIn;
        return $this;
    }

    public function getRevenueOut() {
        return $this->revenueOut;
    }

    public function setRevenueOut($revenueOut) {
        $this->revenueOut = $revenueOut;
        return $this;
    }

    public function getSupplierOut() {
        return $this->supplierOut;
    }

    public function setSupplierOut($supplierOut) {
        $this->supplierOut = $supplierOut;
        return $this;
    }

    public function getSupplierIn() {
        return $this->supplierIn;
    }

    public function setSupplierIn($supplierIn) {
        $this->supplierIn = $supplierIn;
        return $this;
    }

    public function getZp() {
        return $this->zp;
    }

    public function setZp($zp) {
        $this->zp = $zp;
        return $this;
    }

    public function getTax() {
        return $this->tax;
    }

    public function setTax($tax) {
        $this->tax = $tax;
        return $this;
    }

    public function getCost() {
        return $this->cost;
    }

    public function setCost($cost) {
        $this->cost = $cost;
        return $this;
    }

    public function getLoansIn() {
        return $this->loansIn;
    }

    public function setLoansIn($loansIn) {
        $this->loansIn = $loansIn;
        return $this;
    }

    public function getLoansOut() {
        return $this->loansOut;
    }

    public function setLoansOut($loansOut) {
        $this->loansOut = $loansOut;
        return $this;
    }

    public function getDepositIn() {
        return $this->depositIn;
    }

    public function setDepositIn($depositIn) {
        $this->depositIn = $depositIn;
        return $this;
    }

    public function getDepositOut() {
        return $this->depositOut;
    }

    public function setDepositOut($depositOut) {
        $this->depositOut = $depositOut;
        return $this;
    }

    public function getOtherIn() {
        return $this->otherIn;
    }

    public function setOtherIn($otherIn) {
        $this->otherIn = $otherIn;
        return $this;
    }

    public function getOtherOut() {
        return $this->otherOut;
    }

    public function setOtherOut($otherOut) {
        $this->otherOut = $otherOut;
        return $this;
    }

    public function getTotalIn() {
        return $this->totalIn;
    }

    public function setTotalIn($totalIn) {
        $this->totalIn = $totalIn;
        return $this;
    }

    public function getTotalOut() {
        return $this->totalOut;
    }

    public function setTotalOut($totalOut) {
        $this->totalOut = $totalOut;
        return $this;
    }

    public function getGoodBegin() {
        return $this->goodBegin;
    }

    public function setGoodBegin($goodBegin) {
        $this->goodBegin = $goodBegin;
        return $this;
    }

    public function getGoodEnd() {
        return $this->goodEnd;
    }

    public function setGoodEnd($goodEnd) {
        $this->goodEnd = $goodEnd;
        return $this;
    }

    public function getGoodIn() {
        return $this->goodIn;
    }

    public function setGoodIn($goodIn) {
        $this->goodIn = $goodIn;
        return $this;
    }

    public function getGoodOut() {
        return $this->goodOut;
    }

    public function setGoodOut($goodOut) {
        $this->goodOut = $goodOut;
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
            'totalBegin' => 'Денег на начало месяца',
            'bankBegin' => 'Банк',
            'cashBegin' => 'Касса',
            'accountantBegin' => 'Подотчет',
            'goodBegin' => 'Товары на начало месяца',
            'totalIn_' => '',
            'totalIn' => 'Поступило всего',
            'revenueIn' => 'Покупатели',
            'supplierIn' => 'Поставщики (возврат)',
            'loansIn' => 'Кредиты',
            'depositIn' => 'Депозиты (проценты)',
            'otherIn' => 'Прочее',
            'goodIn' => 'Товаров поступило',
            'totalOut' => 'Выбыло всего',
            'supplierOut' => 'Поставщикам',
            'revenueOut' => 'Покупатели (возврат)',
            'zp' => 'Зарплата',
            'tax' => 'Налоги',
            'cost' => 'Расходы',
            'loansOut' => 'Возврат кредитов',
//            'degositOut' => 'Депозиты',
            'otherОut' => 'Прочее',
            'goodOut' => 'Товаров выбыло',
            'totalOut_' => '',
            'totalEnd' => 'Денег на конец месяца',
            'bankEnd' => 'Банк',
            'cashEnd' => 'Касса',
            'accountantEnd' => 'Подотчет',
            'goodEnd' => 'Товары на конец месяца',
        ];    
    }
    
    public static function getSuccessList()
    {
        return [
            'totalBegin' => 'Денег на начало месяца',
            'totalIn' => 'Поступило всего',
            'totalOut' => 'Выбыло всего',
            'totalEnd' => 'Денег на конец месяца',
        ];    
    }
    
    public static function getWarningList()
    {
        return [
            'bankBegin' => 'Банк',
            'cashBegin' => 'Касса',
            'accountantBegin' => 'Подотчет',
            
            'revenueIn' => 'Покупатели',
            'supplierIn' => 'Поставщики (возврат)',
            'loansIn' => 'Кредиты',
            'depositIn' => 'Депозиты',
            'otherIn' => 'Прочее',
            
            'supplierOut' => 'Поставщикам',
            'revenueOut' => 'Покупатели (возврат)',
            'zp' => 'Зарплата',
            'tax' => 'Налоги',
            'cost' => 'Расходы',
            'loansOut' => 'Возврат кредитов',
            'degositOut' => 'Депозиты',
            'otherОut' => 'Прочее',
            
            'bankEnd' => 'Банк',
            'cashEnd' => 'Касса',
            'accountantEnd' => 'Подотчет',            
        ];    
    }
    
    public static function getInfoList()
    {
        return [
            'goodBegin' => 'Товары на начало',
            'goodIn' => 'Товары поступило',
            'goodOut' => 'Товары выбыло',
            'goodEnd' => 'Товары на конец',
        ];    
    }
    
    public static function getRetailKindList()
    {
        return [
        ];    
    }
    
    public static function getMuteList()
    {
        return [
            'goodBegin' => 'Товары на начало',
            'goodIn' => 'Товары поступило',
            'goodOut' => 'Товары выбыло',
            'goodEnd' => 'Товары на конец',
        ];    
    }
    
    /**
     * Массив для отчета
     * @return array
     */
    public static function emptyYear()
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



