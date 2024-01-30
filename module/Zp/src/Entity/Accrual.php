<?php
namespace Zp\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * This class represents a accrual.
 * @ORM\Entity(repositoryClass="\Zp\Repository\ZpRepository")
 * @ORM\Table(name="accrual")
 */
class Accrual
{
    const STATUS_ACTIVE       = 1; //.
    const STATUS_RETIRED      = 2; // .

    const BASE_NONE          = 1; //.
    const BASE_INCOME_TOTAL  = 2; // доход общий.
    const BASE_INCOME_RETAIL = 3; // доход розницы.
    const BASE_INCOME_TP     = 4; // доход ТП.
    const BASE_INCOME_ORDER  = 5; // доход по заказам.
    
    const KIND_PERCENT       = 1; // процент от базы
    const KIND_FIX           = 2; // сумма.
    
    const PAYMENT_WORK_TIME  = 1; // повременная оплата, оклад
    const PAYMENT_WORK       = 2; // сдельная оплата.
    const PAYMENT_AVERAGE    = 3; // оплата по среднему, отпуск.
    const PAYMENT_ONE_TIME   = 4; // разовая оплата.
    const PAYMENT_COURIER    = 5; // оплата доставки.
    const PAYMENT_OTHER      = 6; // прочая оплата.
    const PAYMENT_TAX        = 7; // налог.
    const PAYMENT_PAYMENT    = 99; // выплата на руки.
    
    /**
     * @ORM\Id
     * @ORM\Column(name="id")
     * @ORM\GeneratedValue
     */
    protected $id;

    /** 
     * @ORM\Column(name="apl_id")  
     */
    protected $aplId;

    /** 
     * @ORM\Column(name="name")  
     */
    protected $name;
        
    /** 
     * @ORM\Column(name="comment")  
     */
    protected $comment;
        
    /** 
     * @ORM\Column(name="status")  
     */
    protected $status;

    /** 
     * @ORM\Column(name="basis")  
     */
    protected $basis;

    /** 
     * @ORM\Column(name="oper_kind")  
     */
    protected $kind;

    /** 
     * @ORM\Column(name="payment")  
     */
    protected $payment;

    /**
    * @ORM\OneToMany(targetEntity="Zp\Entity\PersonalMutual", mappedBy="accrual")
    * @ORM\JoinColumn(name="id", referencedColumnName="accrual_id")
     */
    private $personalMutuals;
    
    /**
     * Constructor.
     */
    public function __construct() 
    {
        $this->personalMutuals = new ArrayCollection();
    }
    
    public function getId() {
        return $this->id;
    }
    
    public function getAplId() {
        return $this->aplId;
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function getComment() {
        return $this->comment;
    }
    
    public function getBasis() {
        return $this->basis;
    }

    /**
     * Returns possible basises as array.
     * @return array
     */
    public static function getBasisList() 
    {
        return [
            self::BASE_NONE => 'Не установлен',
            self::BASE_INCOME_RETAIL => 'Доход розницы',
            self::BASE_INCOME_TP => 'Доход ТП',
            self::BASE_INCOME_TOTAL => 'Доход общий',
            self::BASE_INCOME_ORDER => 'Доход по заказам',
        ];
    }    

    /**
     * Returns user basis as string.
     * @return string
     */
    public function getBasisAsString()
    {
        $list = self::getBasisList();
        if (isset($list[$this->basis]))
            return $list[$this->basis];
        
        return 'Unknown';
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
            self::STATUS_ACTIVE => 'Активный',
            self::STATUS_RETIRED => 'Удален',
        ];
    }    

    /**
     * Returns status as string.
     * @return string
     */
    public function getStatusAsString()
    {
        $list = self::getStatusList();
        if (isset($list[$this->status]))
            return $list[$this->status];
        
        return 'Unknown';
    }    

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function setComment($comment) {
        $this->comment = $comment;
        return $this;
    }
    
    public function setStatus($status) {
        $this->status = $status;
        return $this;
    }
    
    public function setAplId($aplId) {
        $this->aplId = $aplId;
        return $this;
    }
    
    public function setBasis($basis) {
        $this->basis = $basis;
        return $this;
    }

    public function getKind() {
        return $this->kind;
    }

    /**
     * Returns possible kinds as array.
     * @return array
     */
    public static function getKindList() 
    {
        return [
            self::KIND_PERCENT => '% от базы',
            self::KIND_FIX => 'Фиксированная сумма',
        ];
    }    

    /**
     * Returns kind as string.
     * @return string
     */
    public function getKindAsString()
    {
        $list = self::getKindList();
        if (isset($list[$this->kind]))
            return $list[$this->kind];
        
        return 'Unknown';
    }    
    
    public function setKind($kind) {
        $this->kind = $kind;
        return $this;
    }

    public function getPayment() {
        return $this->payment;
    }

    /**
     * Returns possible payments as array.
     * @return array
     */
    public static function getPaymentList() 
    {
        return [
            self::PAYMENT_WORK => 'Сдельная оплата',
            self::PAYMENT_WORK_TIME => 'Оклад',
            self::PAYMENT_ONE_TIME => 'Разовая оплата',
            self::PAYMENT_AVERAGE => 'По среднему, отпуск',
            self::PAYMENT_OTHER => 'Прочие выплаты',
            self::PAYMENT_TAX => 'Налог',
            self::PAYMENT_PAYMENT => 'Выплата ЗП',
            self::PAYMENT_COURIER => 'Оплата доставки',
        ];
    }    

    /**
     * Returns payment as string.
     * @return string
     */
    public function getPaymentAsString()
    {
        $list = self::getPaymentList();
        if (isset($list[$this->payment]))
            return $list[$this->payment];
        
        return 'Unknown';
    }    
    
    public function setPayment($payment) {
        $this->payment = $payment;
        return $this;
    }

    public function getPersonalMutuals() {
        return $this->personalMutuals;
    }

}



