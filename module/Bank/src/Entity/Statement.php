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

/**
 * Description of Bank
 * @ORM\Entity(repositoryClass="\Bank\Repository\BankRepository")
 * @ORM\Table(name="bank_statement")
 * @author Daddy
 */
class Statement {
    

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
    * @ORM\OneToOne(targetEntity="Cash\Entity\CashDoc", inversedBy="statement")
    * @ORM\JoinColumn(name="cash_doc_id", referencedColumnName="id")
     */
    private $cashDoc;
    
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
     * 
     * @return CashDoc
     */
    public function getCashDoc() 
    {
        return $this->cashDoc;
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
}
