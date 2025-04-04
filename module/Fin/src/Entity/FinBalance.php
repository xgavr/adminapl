<?php
namespace Fin\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Company\Entity\Legal;

/**
 * This class represents a findds.
 * @ORM\Entity(repositoryClass="\Fin\Repository\BalanceRepository")
 * @ORM\Table(name="fin_balance")
 */
class FinBalance
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
     * @ORM\Column(name="goods")  
     */
    protected $goods;

    /** 
     * @ORM\Column(name="cash")  
     */
    protected $cash;

    /** 
     * @ORM\Column(name="supplier_debtor")  
     */
    protected $supplierDebtor;

    /** 
     * @ORM\Column(name="client_debtor")  
     */
    protected $clientDebtor;

    /** 
     * @ORM\Column(name="deposit")  
     */
    protected $deposit;

    /** 
     * @ORM\Column(name="other_assets")  
     */
    protected $otherAssets;

    /** 
     * @ORM\Column(name="total_assets")  
     */
    protected $totalAssets;

    /** 
     * @ORM\Column(name="supplier_credit")  
     */
    protected $supplierCredit;

    /** 
     * @ORM\Column(name="client_credit")  
     */
    protected $clientCredit;

    /** 
     * @ORM\Column(name="zp")  
     */
    protected $zp;

    /** 
     * @ORM\Column(name="loans")  
     */
    protected $loans;
    
    /** 
     * @ORM\Column(name="other_passive")  
     */
    protected $otherPassive;
    
    /** 
     * @ORM\Column(name="income")  
     */
    protected $income;
    
    /** 
     * @ORM\Column(name="dividends")  
     */
    protected $dividends;
    
    /** 
     * @ORM\Column(name="total_passive")  
     */
    protected $totalPassive;
    
    /** 
     * @ORM\Column(name="balance")  
     */
    protected $balance;
    
    /** 
     * @ORM\Column(name="ktl")  
     */
    protected $ktl;
    
    /** 
     * @ORM\Column(name="kfl")  
     */
    protected $kfl;
    
    /** 
     * @ORM\Column(name="ro")  
     */
    protected $ro;
    
    /** 
     * @ORM\Column(name="al")  
     */
    protected $al;
    
    /** 
     * @ORM\Column(name="fn")  
     */
    protected $fn;
    
    /** 
     * @ORM\Column(name="rsk")  
     */
    protected $rsk;
    
    /** 
     * @ORM\Column(name="ra")  
     */
    protected $ra;
    
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

    public function getGoods() {
        return $this->goods;
    }

    public function setGoods($goods) {
        $this->goods = $goods;
        return $this;
    }

    public function getCash() {
        return $this->cash;
    }

    public function setCash($cash) {
        $this->cash = $cash;
        return $this;
    }

    public function getSupplierDebtor() {
        return $this->supplierDebtor;
    }

    public function setSupplierDebtor($supplierDebtor) {
        $this->supplierDebtor = $supplierDebtor;
        return $this;
    }

    public function getClientDebtor() {
        return $this->clientDebtor;
    }

    public function setClientDebtor($clientDebtor) {
        $this->clientDebtor = $clientDebtor;
        return $this;
    }

    public function getDeposit() {
        return $this->deposit;
    }

    public function setDeposit($deposit) {
        $this->deposit = $deposit;
        return $this;
    }

    public function getOtherAssets() {
        return $this->otherAssets;
    }

    public function setOtherAssets($otherAssets) {
        $this->otherAssets = $otherAssets;
        return $this;
    }

    public function getTotalAssets() {
        return $this->totalAssets;
    }

    private function assets()
    {
        return
                $this->getCash() +
                $this->getClientDebtor() +
                $this->getDeposit() +
                $this->getOtherAssets() + 
                $this->getSupplierDebtor() +
                $this->getGoods()
                ;
    }
    
    public function setTotalAssets() {
        $this->totalAssets = 
                    $this->assets()
                ;
        
        return $this;
    }

    public function getSupplierCredit() {
        return $this->supplierCredit;
    }

    public function setSupplierCredit($supplierCredit) {
        $this->supplierCredit = $supplierCredit;
        return $this;
    }

    public function getClientCredit() {
        return $this->clientCredit;
    }

    public function setClientCredit($clientCredit) {
        $this->clientCredit = $clientCredit;
        return $this;
    }

    public function getZp() {
        return $this->zp;
    }

    public function setZp($zp) {
        $this->zp = $zp;
        return $this;
    }

    public function getLoans() {
        return $this->loans;
    }

    public function setLoans($loans) {
        $this->loans = $loans;
        return $this;
    }

    public function getOtherPassive() {
        return $this->otherPassive;
    }

    public function setOtherPassive($otherPassive) {
        $this->otherPassive = $otherPassive;
        return $this;
    }

    public function getIncome() {
        return $this->income;
    }

    public function setIncome() {
        $this->income = $this->getTotalAssets() - $this->passives();
        return $this;
    }

    public function getDividends() {
        return $this->dividends;
    }

    public function setDividends() {
        $this->dividends = max(0, round(($this->getIncome() - $this->getGoods())/2, -3));
        return $this;
    }    
    
    public function getTotalPassive() {
        return $this->totalPassive;
    }
    
    private function shortPassives()
    {
        return 
                $this->getSupplierCredit() +
                $this->getLoans() + 
                $this->getOtherPassive() + 
                $this->getClientCredit() + 
                $this->getZp()
                ;
    }

    private function passives()
    {
        return 
            $this->shortPassives()
                ;
    }

    public function setTotalPassive() {
        $this->totalPassive =
                $this->passives()
              + $this->getIncome()
                ;
        return $this;
    }
    
    public function getBalance() {
        return $this->balance;
    }

    public function setBalance() {
        $this->balance = $this->getTotalAssets() - $this->getTotalPassive();
        return $this;
    }

    public function getKtl() {
        return $this->ktl;
    }

    public function setKtl() {
        $this->ktl = 0;
        if ($this->shortPassives()){
            $this->ktl = round($this->assets()/$this->shortPassives(), 2);
        }    
        return $this;
    }

    public function getKfl() {
        return $this->kfl;
    }

    public function setKfl() {
        $this->kfl = 0;
        if ($this->assets()){
            $this->kfl = max(0, round(($this->assets()-$this->shortPassives())*100/$this->assets()));
        }    
        return $this;
    }

    public function getRo() {
        return $this->ro;
    }

    public function setRo($income) {
        $this->ro = 0;
        if ($this->assets()){
            $this->ro = round($income/$this->assets(), 2);
        }
        return $this;
    }

    public function getAl() {
        return $this->al;
    }

    public function setAl($al) {
        $this->al = $al;
        return $this;
    }

    public function getFn() {
        return $this->fn;
    }

    public function setFn($fn) {
        $this->fn = $fn;
        return $this;
    }

    public function getRsk() {
        return $this->rsk;
    }

    public function setRsk($rsk) {
        $this->rsk = $rsk;
        return $this;
    }

    public function getRa() {
        return $this->ra;
    }

    public function setRa($ra) {
        $this->ra = $ra;
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
            'totalAssets' => 'Активы',
//            'currentAssets' => 'Оборотные активы',
            'goods' => 'Товары',
            'cash' => 'Деньги (банк + касса + подотчет)',
            'supplierDebtor' => '<a href="/supplier-revision" target="_blank">Поставщики должны нам</a>',
            'clientDebtor' => '<a href="/client" target="_blank">Покупатели должны нам</a>',
            'deposit' => 'Депозиты',
            'otherAssets' => 'Прочие активы',
            'totalAssets_' => '',
            'totalPassive' => 'Пассивы',
            'supplierCredit' => '<a href="/supplier-revision" target="_blank">Мы должны поставщикам</a>',
            'clientCredit' => '<a href="/client" target="_blank">Мы должны покупателям</a>',
            'zp' => '<a href="/balance/zp" target="_blank">Долг по зарплате</a>',
            'loans' => 'Кредиты',
            'otherPassive' => 'Прочие обязательства',
            'income' => 'Накопленная прибыль/<span style="color: red">убыток</span>',
            'balance_' => '',
            'balance' => 'Баланс (Активы=Пассивы)',
            'dividends' => 'Дивиденты рекомендуемые',
            'dividends_' => '',
            'finmark' => 'Финансовые показатели',
            'ktl' => 'Коэффициент текущей ликвидности',
            'kfl' => 'Коэффициент финансовой ликвидности, %',
            'ro' => 'Ресурсоотдача',
            'al' => 'Абсолютная ликвидность',
            'fn' => 'Финансовая независимость',
            'rsk' => 'Рентабельность СК',
            'ra' => 'Рентабельность активов',
        ];    
    }
    
    public static function getSuccessList()
    {
        return [
            'totalAssets' => 'Денег на начало месяца',
            'totalPassive' => 'Поступило всего',
        ];    
    }
    
    public static function getWarningList()
    {
        return [
//            'currentAssets' => 'Оборотные активы',
            'goods' => 'Товары',
            'cash' => 'Деньги',
            'supplierDebtor' => 'Поставщики должны нам',
            'clientDebtor' => 'Покупатели должны нам',
            'deposit' => 'Депозиты',
            'otherAssets' => 'Прочие активы',
            
            'supplierCredit' => 'Мы должны поставщикам',
            'clientCredit' => 'Мы должны покупателям',
            'zp' => 'Долг по зарплате',
            'loans' => 'Кредиты',
            'otherPassive' => 'Прочие обязательства',
            'income' => 'Накопленная прибыль/убыток',          
        ];    
    }
    
    public static function getInfoList()
    {
        return [
            'balance' => 'Баланс (Активы=Пассивы)',
            'dividends' => 'Дивиденты рекомендуемые',
            'finmark' => 'Финансовые показатели',
            'ktl' => 'Коэффициент текущей ликвидности',
            'kfl' => 'Коэффициент финансовой ликвидности',
            'ro' => 'Ресурсоотдача',
            'al' => 'Абсолютная ликвидность',
            'fn' => 'Финансовая независимость',
            'rsk' => 'Рентабельность СК',
            'ra' => 'Рентабельность активов',
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
            'dividends' => 'Дивиденты рекомендуемые',
            'ktl' => 'Коэффициент текущей ликвидности',
            'kfl' => 'Коэффициент финансовой ликвидности',
            'ro' => 'Ресурсоотдача',
            'al' => 'Абсолютная ликвидность',
            'fn' => 'Финансовая независимость',
            'rsk' => 'Рентабельность СК',
            'ra' => 'Рентабельность активов',
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



