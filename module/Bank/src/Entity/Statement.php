<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Bank\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Cash\Entity\CashDoc;
use Bank\Entity\Payment;
use Company\Entity\CostMutual;

/**
 * Description of Bank
 * @ORM\Entity(repositoryClass="\Bank\Repository\BankRepository")
 * @ORM\Table(name="bank_statement")
 * @author Daddy
 */
class Statement {
    
    const STATUS_ACTIVE = 1; //данные новые
    const STATUS_RETIRED = 2; //данные удалены

    const SWAP1_TRANSFERED = 1; //данные переданы
    const SWAP1_TO_TRANSFER = 2; //данные не перданы
    
    const PAY_NEW = 1; //новый
    const PAY_CHECK = 2; //проверен
    const PAY_WARNING = 3; // не создан док в кассе
    
    const STATUS_ACCOUNT_OK  = 1;// обновлено 
    const STATUS_ACCOUNT_NO  = 2;// не обновлено
    const STATUS_TAKE_NO  = 3;// не проведено    
    
    const STATUS_TOKEN_NO    = 1;// токены не получены 
    const STATUS_TOKEN_TOKEN    = 2;// токены получены 
    const STATUS_TOKEN_COUNT  = 3;// токены посчитаны
    const STATUS_TOKEN_GROUP  = 4;// группа присвоена
    
    const KIND_IN_BAYER = 1; //оплата от покупателя
    const KIND_IN_SUPPLIER = 2; //возврат от поставщика 
    const KIND_IN_LOAN = 3; //займ от контрагента 
    const KIND_IN_CREDIT = 4; //кредит 
    const KIND_IN_LOAN_RETURN = 5; //возврат займа 
    const KIND_IN_USER_RETURN = 6; //возврат с подотчета 
    const KIND_IN_TAX_RETURN = 7; //возврат с налога 
    const KIND_IN_OTHER_CALC = 8; //прочие расчеты 
    const KIND_IN_FACTORING = 9; //факторинг 
    const KIND_IN_DEPOSIT = 10; //депозит 
    const KIND_IN_CASH = 11; //инкасация 
    const KIND_IN_COLLECTION = 12; //инкасация 
    const KIND_IN_CART = 13; //поступление по картам 
    const KIND_IN_LOAN_USER = 14; //займ от работника 
    const KIND_IN_CAPITAL = 15; //взнос в капитал 
    const KIND_IN_OTHER = 16; //прочее 
    const KIND_IN_SELF = 17; //перевод на другой счет
    const KIND_IN_FIN_SERVICE = 18; //поступление от фин сервисов
    const KIND_IN_DEPOSIT_PERCENT = 19; //выплата процентов 
    const KIND_IN_PERSON = 20; //перевод от частного лица
    
    const KIND_OUT_SUPPLIER = 101; //оплата поставщику
    const KIND_OUT_BAYER = 102; //возврат покупателю
    const KIND_OUT_TAX = 103; //уплата налога
    const KIND_OUT_LOAN_RETURN = 104; //возврат займа
    const KIND_OUT_CREDIT_RETURN = 105; //возврат кредита
    const KIND_OUT_LOAN = 106; //выдача займа
    const KIND_OUT_OTHER_CALC = 107; //прочие расчеты
    const KIND_OUT_DEPO = 108; //депозит
    const KIND_OUT_CASH = 109; //снятие наличных
    const KIND_OUT_USER = 110; //подотчет
    const KIND_OUT_ZP = 111; //зп
    const KIND_OUT_ZP_USER = 112; //зп
    const KIND_OUT_CONTRACT_USER = 113; //зп
    const KIND_OUT_ZP_DEPO = 114; //зп
    const KIND_OUT_DIVIDENT = 115; //дивиденты
    const KIND_OUT_LOAN_USER = 116; //займ работнику
    const KIND_OUT_OTHER = 117; //прочее
    const KIND_OUT_BANK_COMMISSION = 118; //комиссия банка
    const KIND_OUT_TAX_OTHER = 119; //чужие налоги
    const KIND_OUT_SELF_EMPL_REEST = 120; //самозанятым
    const KIND_OUT_SELF_EMPL = 121; //самозанятым
    const KIND_OUT_ALIMONY = 122; //исп лист
    const KIND_OUT_CART_PAY = 123; //оплата по платежной карте
    const KIND_OUT_SELF = 124; //перевод на другой счет
    
    const KIND_UNKNOWN = 299; //неизвестно 
    
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * @ORM\Column(name="bic")   
     */
    protected $bik;
   
    /**
     * @ORM\Column(name="account")   
     */
    protected $account;
   
    /**
     * @ORM\Column(name="counterparty_account_number")   
     */
    protected $counterpartyAccountNumber;
   
    /**
     * @ORM\Column(name="counterparty_bank_bic")   
     */
    protected $counterpartyBankBik;
   
    /**
     * @ORM\Column(name="counterparty_bank_name")   
     */
    protected $counterpartyBankName;
   
    /**
     * @ORM\Column(name="counterparty_inn")   
     */
    protected $counterpartyInn;
   
    /**
     * @ORM\Column(name="counterparty_kpp")   
     */
    protected $counterpartyKpp;
   
    /**
     * @ORM\Column(name="counterparty_name")   
     */
    protected $counterpartyName;
   
    /**
     * @ORM\Column(name="operation_type")   
     */
    protected $operationType;
   
    /**
     * @ORM\Column(name="payment_amount")   
     */
    protected $amount;
   
    /** 
     * @ORM\Column(name="payment_bank_system_id")  
     */
    protected $bankSystemId;
    
    /** 
     * @ORM\Column(name="payment_charge_date")  
     */
    protected $chargeDate;
    
    /** 
     * @ORM\Column(name="payment_date")  
     */
    protected $paymentDate;
    
    /** 
     * @ORM\Column(name="payment_number")  
     */
    protected $paymentNumber;

    /** 
     * @ORM\Column(name="payment_purpose")  
     */
    protected $purpose;

    /** 
     * @ORM\Column(name="supplier_bill_id")  
     */
    protected $supplierBillId;

    /** 
     * @ORM\Column(name="tax_info_document_date")  
     */
    protected $taxInfoDocumentDate;

    /** 
     * @ORM\Column(name="tax_info_document_number")  
     */
    protected $taxInfoDocumentNumber;

    /** 
     * @ORM\Column(name="tax_info_kbk")  
     */
    protected $taxInfoKbk;

    /** 
     * @ORM\Column(name="tax_info_okato")  
     */
    protected $taxInfoOkato;

    /** 
     * @ORM\Column(name="tax_info_period")  
     */
    protected $taxInfoPeriod;

    /** 
     * @ORM\Column(name="tax_info_reason_code")  
     */
    protected $taxInfoReasonCode;

    /** 
     * @ORM\Column(name="tax_info_status")  
     */
    protected $taxInfoStatus;

    /** 
     * @ORM\Column(name="x_payment_id")  
     */
    protected $xPaymentId;
    
    /** 
     * @ORM\Column(name="swap1")  
     */
    protected $swap1 = self::SWAP1_TO_TRANSFER;
    
    /** 
     * @ORM\Column(name="pay")  
     */
    protected $pay = self::PAY_NEW;

    /** 
     * @ORM\Column(name="status_account")  
     */
    protected $statusAccount = self::STATUS_ACCOUNT_NO;

    /** 
     * @ORM\Column(name="status_token")  
     */
    protected $statusToken = self::STATUS_TOKEN_NO;

    /** 
     * @ORM\Column(name="kind")  
     */
    protected $kind = self::KIND_UNKNOWN;

    /** 
     * Статус объекта
     * @ORM\Column(name="status")  
     */
    protected $status;
    
    /** 
     * @ORM\Column(name="amount_service")  
     */
    protected $amountService = 0;

    /**
    * @ORM\OneToOne(targetEntity="Cash\Entity\CashDoc", inversedBy="statement")
    * @ORM\JoinColumn(name="cash_doc_id", referencedColumnName="id")
     */
    private $cashDoc;
    
   /**
    * @ORM\OneToMany(targetEntity="Company\Entity\CostMutual", mappedBy="statement")
    * @ORM\JoinColumn(name="id", referencedColumnName="doc_id")
   */
   private $costMutuals;           

   /**
     * @ORM\ManyToMany(targetEntity="Bank\Entity\StatementToken")
     * @ORM\JoinTable(name="statement_token_token",
     *      joinColumns={@ORM\JoinColumn(name="statement_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="statement_token_id", referencedColumnName="id")}
     *      )
     */
    private $statementTokens;
    
    /**
     * Constructor.
     */
    public function __construct() 
    {
        $this->statementTokens = new ArrayCollection();
        $this->costMutuals = new ArrayCollection();
    }
    
    /**
     * Возвращает Id
     * @return int
     */    
    public function getId() 
    {
        return $this->id;
    }

    public function getLogKey() 
    {
        return 'bank:'.$this->id;
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
     * Возвращает БИК
     * @return string
     */
    public function getBik() 
    {
        return $this->bik;
    }
    
    /**
     * Устанвливает БИК
     * @param string $bik
     */
    public function setBik($bik) 
    {
        $this->bik = $bik;
    }     

    /**
     * Возвращает расчетный счет.
     * @return string
     */
    public function getAccount() 
    {
        return $this->account;
    }

    /**
     * Устанавливает расчетный счет
     * @param string $account
     */
    public function setAccount($account) 
    {
        $this->account = $account;
    }     

    /**
     * Возвращает расчетный счет.
     * @return string
     */
    public function getCounterpartyAccountNumber() 
    {
        return $this->counterpartyAccountNumber;
    }

    /**
     * Устанавливает расчетный счет
     * @param string $counterpartyAccountNumber
     */
    public function setCounterpartyAccountNumber($counterpartyAccountNumber) 
    {
        $this->counterpartyAccountNumber = $counterpartyAccountNumber;
    }     

    /**
     * Возвращает БИК.
     * @return string
     */
    public function getCounterpartyBankBik() 
    {
        return $this->counterpartyBankBik;
    }

    /**
     * Устанавливает БИК
     * @param string $counterpartyBankBik
     */
    public function setCounterpartyBankBik($counterpartyBankBik) 
    {
        $this->counterpartyBankBik = $counterpartyBankBik;
    }     

    /**
     * Устанавливает БИК
     * @param string $counterpartyBankBik
     */
    public function setCounterpartyBankBic($counterpartyBankBik) 
    {
        $this->counterpartyBankBik = $counterpartyBankBik;
    }     

    /**
     * Возвращает банк.
     * @return string
     */
    public function getСounterpartyBankName() 
    {
        return $this->counterpartyBankName;
    }

    /**
     * Устанавливает банк
     * @param string $counterpartyBankName
     */             
    public function setCounterpartyBankName($counterpartyBankName) 
    {
        $this->counterpartyBankName = $counterpartyBankName;
    }     

    /**
     * Возвращает инн.
     * @return string
     */
    public function getСounterpartyInn() 
    {
        return $this->counterpartyInn;
    }

    /**
     * Устанавливает инн
     * @param string $counterpartyInn
     */
    public function setCounterpartyInn($counterpartyInn) 
    {
        $this->counterpartyInn = $counterpartyInn;
    }     

    /**
     * Возвращает кпп.
     * @return string
     */
    public function getСounterpartyKpp() 
    {
        return $this->counterpartyKpp;
    }

    /**
     * Устанавливает кпп
     * @param string $counterpartyKpp
     */
    public function setCounterpartyKpp($counterpartyKpp) 
    {
        $this->counterpartyKpp = $counterpartyKpp;
    }     

    /**
     * Возвращает имя.
     * @return string
     */
    public function getCounterpartyName() 
    {
        return $this->counterpartyName;
    }

    /**
     * Устанавливает имя
     * @param string $counterpartyName
     */
    public function setCounterpartyName($counterpartyName) 
    {
        $this->counterpartyName = $counterpartyName;
    }     

    /**
     * Возвращает тип операции.
     * @return string
     */
    public function getОperationType() 
    {
        return $this->operationType;
    }

    /**
     * Устанавливает тип операции
     * @param string $operationType
     */
    public function setOperationType($operationType) 
    {
        $this->operationType = $operationType;
    }     
    
    /**
     * Возвращает сумму.
     * @return float
     */
    public function getAmount() 
    {
        return $this->amount;
    }

    /**
     * Устанавливает сумму
     * @param float $amount
     */
    public function setAmount($amount) 
    {
        $this->amount = $amount;
    }     
    
    /**
     * Устанавливает сумму
     * @param float $amount
     */
    public function setPaymentAmount($amount) 
    {
        $this->amount = $amount;
    }     
    
    /**
     * Возвращает bankSystemId.
     * @return string
     */
    public function getBankSystemId() 
    {
        return $this->bankSystemId;
    }

    /**
     * Устанавливает bankSystemId
     * @param string $bankSystemId
     */
    public function setBankSystemId($bankSystemId) 
    {
        $this->bankSystemId = $bankSystemId;
    }     
    
    /**
     * Устанавливает bankSystemId
     * @param string $bankSystemId
     */
    public function setPaymentBankSystemId($bankSystemId) 
    {
        $this->bankSystemId = $bankSystemId;
    }     
    
    /**
     * Возвращает chargeDat.
     * @return date
     */
    public function getChargeDate() 
    {
        return $this->chargeDate;
    }

    public function getDocDateAtomFormat() {
        $datetime = new \DateTime($this->chargeDate);
        return $datetime->format(\DateTime::ATOM);
    }
    
    /**
     * Устанавливает chargeDate
     * @param date $chargeDate
     */
    public function setChargeDate($chargeDate) 
    {
        $this->chargeDate = date('Y-m-d', strtotime($chargeDate));
    }     
    
    /**
     * Устанавливает chargeDate
     * @param date $chargeDate
     */
    public function setPaymentChargeDate($chargeDate) 
    {
        $this->chargeDate = date('Y-m-d', strtotime($chargeDate));
    }     
    
    /**
     * Возвращает paymentDate.
     * @return date
     */
    public function getPaymentDate() 
    {
        return $this->paymentDate;
    }

    /**
     * Устанавливает paymentDate
     * @param date $paymentDate
     */
    public function setPaymentDate($paymentDate) 
    {
        $this->paymentDate = date('Y-m-d', strtotime($paymentDate));
    }     
    
    /**
     * Возвращает paymentNumber.
     * @return string
     */
    public function getPaymentNumber() 
    {
        return $this->paymentNumber;
    }

    /**
     * Устанавливает paymentNumber
     * @param string $paymentNumber
     */
    public function setPaymentNumber($paymentNumber) 
    {
        $this->paymentNumber = $paymentNumber;
    }     
    
    /**
     * Возвращает назначение платежа.
     * @return string
     */
    public function getPaymentPurpose() 
    {
        return $this->purpose;
    }

    /**
     * Устанавливает назначение платежа
     * @param string $purpose
     */
    public function setPurpose($purpose) 
    {
        $this->purpose = $purpose;
    }     

    /**
     * Устанавливает назначение платежа
     * @param string $purpose
     */
    public function setPaymentPurpose($purpose) 
    {
        $this->purpose = $purpose;
    }     

    /**
     * Возвращает supplierBillId.
     * @return string
     */
    public function getSupplierBillId() 
    {
        return $this->supplierBillId;
    }

    /**
     * Устанавливает supplierBillId
     * @param string $supplierBillId
     */
    public function setSupplierBillId($supplierBillId) 
    {
        $this->supplierBillId = $supplierBillId;
    }     
    
    /**
     * Возвращает taxInfoDocumentDate.
     * @return date
     */
    public function getTaxInfoDocumentDate() 
    {
        return $this->taxInfoDocumentDate;
    }

    /**
     * Устанавливает taxInfoDocumentDate
     * @param date $taxInfoDocumentDate
     */
    public function setTaxInfoDocumentDate($taxInfoDocumentDate) 
    {
        $this->taxInfoDocumentDate = $taxInfoDocumentDate;
    }     
    
    /**
     * Возвращает taxInfoDocumentNumber.
     * @return string
     */
    public function getTaxInfoDocumentNumber() 
    {
        return $this->taxInfoDocumentDate;
    }

    /**
     * Устанавливает taxInfoDocumentNumber
     * @param string $taxInfoDocumentNumber
     */
    public function setTaxInfoDocumentNumber($taxInfoDocumentNumber) 
    {
        $this->taxInfoDocumentNumber = $taxInfoDocumentNumber;
    }     
    
    /**
     * Возвращает taxInfoKbk.
     * @return string
     */
    public function getTaxInfoKbk() 
    {
        return $this->taxInfoKbk;
    }

    /**
     * Устанавливает taxInfoKbk
     * @param string $taxInfoKbk
     */
    public function setTaxInfoKbk($taxInfoKbk) 
    {
        $this->taxInfoKbk = $taxInfoKbk;
    }     
    
    /**
     * Возвращает taxInfoOkato.
     * @return string
     */
    public function getTaxInfoOkato() 
    {
        return $this->taxInfoOkato;
    }

    /**
     * Устанавливает taxInfoOkato
     * @param string $taxInfoOkato
     */
    public function setTaxInfoOkato($taxInfoOkato) 
    {
        $this->taxInfoOkato = $taxInfoOkato;
    }     
    
    /**
     * Возвращает taxInfoPeriod.
     * @return string
     */
    public function getTaxInfoPeriod() 
    {
        return $this->taxInfoPeriod;
    }

    /**
     * Устанавливает taxInfoPeriod
     * @param string $taxInfoPeriod
     */
    public function setTaxInfoPeriod($taxInfoPeriod) 
    {
        $this->taxInfoPeriod = $taxInfoPeriod;
    }     
    
    /**
     * Возвращает taxInfoReasonCode.
     * @return string
     */
    public function getTaxInfoReasonCode() 
    {
        return $this->taxInfoReasonCode;
    }

    /**
     * Устанавливает taxInfoReasonCode
     * @param string $taxInfoReasonCode
     */
    public function setTaxInfoReasonCode($taxInfoReasonCode) 
    {
        $this->taxInfoReasonCode = $taxInfoReasonCode;
    }     
    
    /**
     * Возвращает taxInfoStatus.
     * @return string
     */
    public function getTaxInfoStatus() 
    {
        return $this->taxInfoStatus;
    }

    /**
     * Устанавливает taxInfoStatus
     * @param string $taxInfoStatus
     */
    public function setTaxInfoStatus($taxInfoStatus) 
    {
        $this->taxInfoStatus = $taxInfoStatus;
    }     
    
    /**
     * Возвращает xPaymentId.
     * @return string
     */
    public function getXPaymentId() 
    {
        return $this->xPaymentId;
    }

    /**
     * Устанавливает xPaymentId
     * @param string $xPaymentId
     */
    public function setXPaymentId($xPaymentId) 
    {
        $this->xPaymentId = $xPaymentId;
    }     
    
    /**
     * Возвращает флаг обмена 1.
     * @return int
     */
    public function getSwap1() 
    {
        return $this->swap1;
    }

    /**
     * Устанавливает флаг обмена
     * @param int $swap1
     */
    public function setSwap1($swap1) 
    {
        $this->swap1 = $swap1;
    }     
    
    /**
     * Возвращает флаг pay.
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
            self::PAY_NEW => 'Новый',
            self::PAY_CHECK => 'Проверен',
            self::PAY_WARNING => 'Проверить',
        ];
    }    
    
    /**
     * Returns pay as string.
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
     * Устанавливает флаг pay
     * @param int $pay
     */
    public function setPay($pay) 
    {
        $this->pay = $pay;
    }     
    
    /**
     * Returns statusAccount.
     * @return int     
     */
    public function getStatusAccount() 
    {
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
        
    /**
     * Sets statusAccount.
     * @param int $statusAccount     
     */
    public function setStatusAccount($statusAccount) 
    {
        $this->statusAccount = $statusAccount;
    }   

    /**
     * Returns statusToken.
     * @return int     
     */
    public function getStatusToken() 
    {
        return $this->statusToken;
    }

    /**
     * Returns possible statusToken as array.
     * @return array
     */
    public static function getStatusTokenList() 
    {
        return [
            self::STATUS_TOKEN_NO => 'Токены не получены',
            self::STATUS_TOKEN_TOKEN => 'Токены получены',
            self::STATUS_TOKEN_COUNT => 'Токены посчитаны',
            self::STATUS_TOKEN_GROUP => 'Группа присвоена',
        ];
    }    
    
    /**
     * Returns statusToken as string.
     * @return string
     */
    public function getStatusTokenAsString()
    {
        $list = self::getStatusTokenList();
        if (isset($list[$this->statusToken]))
            return $list[$this->statusToken];
        
        return 'Unknown';
    }    
        
    /**
     * Sets statusToken.
     * @param int $statusToken     
     */
    public function setStatusToken($statusToken) 
    {
        $this->statusToken = $statusToken;
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
     * Returns possible kind as array.
     * @return array
     */
    public static function getKindInList() 
    {
        return [
            self::KIND_IN_CART => 'Поступление по платежным картам',
            self::KIND_IN_FIN_SERVICE => 'Поступление от финансовых сервисов',
            self::KIND_IN_SELF => 'Перевод на свой счет',
            self::KIND_IN_BAYER => 'Оплата от покупателя',
            self::KIND_IN_SUPPLIER => 'Возврат от поставщика',
            self::KIND_IN_LOAN => 'Получение займа от контрагента',
            self::KIND_IN_CREDIT => 'Получение кредита в банке',
            self::KIND_IN_LOAN_RETURN => 'Возврат займа контрагентом',
            self::KIND_IN_USER_RETURN => 'Возврат от подотчетного лица',
            self::KIND_IN_TAX_RETURN => 'Возврат налога',
            self::KIND_IN_OTHER_CALC => 'Прочие расчеты с контрагентами',
            self::KIND_IN_FACTORING => 'Оплата от факторинговой компании',
            self::KIND_IN_DEPOSIT => 'Депозит',
            self::KIND_IN_DEPOSIT_PERCENT => 'Выплата процентов',
            self::KIND_IN_CASH => 'Взнос наличными из кассы',
            self::KIND_IN_COLLECTION => 'Инкассация',
            self::KIND_IN_LOAN_USER => 'Возврат займа работником',
            self::KIND_IN_CAPITAL => 'Взнос в уставный капитал',
            self::KIND_IN_OTHER => 'Прочее поступление',
            self::KIND_IN_PERSON => 'Оплата от частного лица',
            
            self::KIND_UNKNOWN => 'Не определено',
        ];
    }    
    
    /**
     * Returns possible kind as array.
     * @return array
     */
    public static function getKindOutList() 
    {
        return [
            self::KIND_OUT_SUPPLIER => 'Оплата постащику',
            self::KIND_OUT_SELF => 'Перевод на свой счет',
            self::KIND_OUT_CART_PAY => 'Оплата корпоративной картой',
            self::KIND_OUT_TAX => 'Уплата налога',
            self::KIND_OUT_BAYER => 'Возврат покупателю',
            self::KIND_OUT_LOAN_RETURN => 'Возврат займа контрагенту',
            self::KIND_OUT_CREDIT_RETURN => 'Возврат кредита банку',
            self::KIND_OUT_LOAN => 'Выдача займа контрагенту',
            self::KIND_OUT_OTHER_CALC => 'Прочие расчеты с контрагентами',
            self::KIND_OUT_DEPO => 'Депозит',
            self::KIND_OUT_CASH => 'Снятие наличных в кассу',
            self::KIND_OUT_USER => 'Перечисление подотчетному лицу',
            self::KIND_OUT_ZP => 'Перечисление заработной платы по ведомостям',
            self::KIND_OUT_ZP_USER => 'Перечисление заработной платы работнику',
            self::KIND_OUT_CONTRACT_USER => 'Перечисление сотруднику по договору подряда',
            self::KIND_OUT_ZP_DEPO => 'Перечисление депонированной заработной платы',
            self::KIND_OUT_DIVIDENT => 'Перечисление дивидендов',
            self::KIND_OUT_LOAN_USER => 'Выдача займа работнику',
            self::KIND_OUT_OTHER => 'Прочее списание',
            self::KIND_OUT_BANK_COMMISSION => 'Комиссия банка',
            self::KIND_OUT_TAX_OTHER => 'Уплата налога за третьих лиц',
            self::KIND_OUT_SELF_EMPL_REEST => 'Выплаты самозанятым по реестру',
            self::KIND_OUT_SELF_EMPL => 'Выплата самозанятому',
            self::KIND_OUT_ALIMONY => 'Перечисление по исполнительному листу работника',
            
            self::KIND_UNKNOWN => 'Не определено',
        ];
    }    
    
    /**
     * 
     * @return array
     */    
    public static function getKindList()
    {
        return self::getKindInList() + self::getKindOutList();
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
        
        return 'Не указано';
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
            self::STATUS_ACTIVE => 'Учитывать',
            self::STATUS_RETIRED => 'Не учитывать',
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
    
    /**
     * Sets status.
     * @param int $status     
     */
    public function setStatus($status) 
    {
        $this->status = $status;
    }   
    
    public function getAmountService() {
        return $this->amountService;
    }

    public function setAmountService($amountService) {
        $this->amountService = $amountService;
        return $this;
    }
    
    /**
     * 
     * @return CashDoc
     */
    public function getCashDoc() 
    {
        return $this->cashDoc;
    }
    
    public function getCashDocAsArray()
    {
        if ($this->cashDoc){
            return $this->getCashDoc()->toArray();
        }
        return;
    }
    
    /**
     * @param CashDoc $cashDoc
     */
    public function setCashDoc($cashDoc)
    {
        $this->cashDoc = $cashDoc;
    }
    
    /**
     * 
     * @return ArrayCollection
     */
    public function getStatementTokens() {
        return $this->statementTokens;
    }
    
    public function getCostMutuals() {
        return $this->costMutuals;
    }
    
    /**
     * Данные для ответной платежки
     */
    public function toReturnPayment()
    {
        return [
            'counterpartyAccountNumber' => $this->getCounterpartyAccountNumber(),
            'counterpartyBankBik' => $this->getCounterpartyBankBik(),
            'counterpartyInn' => $this->getСounterpartyInn(),
            'counterpartyKpp' => $this->getСounterpartyKpp(),
            'counterpartyName' => $this->getCounterpartyName(),
            'amount' => $this->getAmount(),
            'paymentDate' => date('Y-m-d'),
            'purpose' => $this->getPaymentPurpose(),
            'nds' => Payment::NDS_NO,
            'supplierBillId' => 0,
            'status' => Payment::STATUS_ACTIVE,
            'paymentType' => Payment::PAYMENT_TYPE_NORMAL,            
        ];        
    }
    
    /**
     * Массив для формы
     * @return array
     */
    public function toArray()
    {
        return [
            'account' => $this->getAccount(),
            'amount' => $this->getAmount(),
            'amountService' => $this->getAmountService(),
            'bankSystemId' => $this->getBankSystemId(),
            'bik' => $this->getBik(),
            'docDate' => $this->getDocDateAtomFormat(),
            'counterpartyAccount' => $this->getCounterpartyAccountNumber(),
            'counterpartyBik' => $this->getCounterpartyBankBik(),
            'counterpartyName' => $this->getCounterpartyName(),
            'id' => $this->getId(),
            'kind' => $this->getKind(),
            'pay' => $this->getPay(),
            'paymentNumber' => $this->getPaymentNumber(),
            'purpose' => $this->getPaymentPurpose(),
            'supplierBillId' => $this->getSupplierBillId(),
            'counterpartyBankName' => $this->getСounterpartyBankName(),
            'counterpartyInn' => $this->getСounterpartyInn(),
            'counterpartyKpp' => $this->getСounterpartyKpp(),
            'cashDoc' => $this->getCashDocAsArray(),
            'status' => $this->getStatus(),
        ];
    }                        
}

