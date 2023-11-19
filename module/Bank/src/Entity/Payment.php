<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 * 
 * https://enter.tochka.com/doc/v1/payment.html#id2
 */

namespace Bank\Entity;

use Doctrine\ORM\Mapping as ORM;
use User\Entity\User;
use Company\Entity\BankAccount;
use Application\Entity\Supplier;

/**
 * Description of Bank
 * 
 * @ORM\Entity(repositoryClass="\Bank\Repository\PaymentRepository")
 * @ORM\Table(name="bank_payment")
 * @author Daddy
 */
class Payment {
    

    const STATUS_ACTIVE = 1; //данные новые
    const STATUS_TRANSFER = 2; //данные переданы
    const STATUS_SUCCESS = 3; //данные получены
    const STATUS_RETIRED = 4; //данные удалены
    const STATUS_ERROR = 5; //ошибка в данных
    
    const PAYMENT_TYPE_NORMAL = 1; //обычный платеж
    const PAYMENT_TYPE_TAX = 2; // налог
    
    const NDS_NO = 1; //без НДС
    const NDS_10 = 10; // НДС 20%
    const NDS_20 = 20; // НДС 20%
    
    const TAX_STATUS_01 = '01'; // КБК ЕНП
    const TAX_STATUS_02 = '02'; // Платежка-уведомление
    const TAX_STATUS_06 = '06'; // ВЭД
    const TAX_STATUS_08 = '08'; // В бюджет, кроме ФНС
    const TAX_STATUS_13 = '13'; // Иностранец
    const TAX_STATUS_17 = '17'; // ИП ВЭД
    const TAX_STATUS_31 = '31'; // Алименты, исполнительный лист
    
    const PAYMENT_AUTO_ONE = 1; // разовый платеж
    const PAYMENT_AUTO_WEEK = 2;// авто платеж еженедельный
    const PAYMENT_AUTO_MONTH = 3;// авто платеж ежемесячный
        
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
              
    /**
     * @ORM\Column(name="counterparty_account_number")   
     */
    protected $counterpartyAccountNumber;

    /**
     * @ORM\Column(name="counterparty_bank_bic")   
     */
    protected $counterpartyBankBik;

    /**
     * Кор. счёт банка получателя
     * @ORM\Column(name="counterparty_bank_corr_account")   
     */
    protected $counterpartyBankCorrAccount;

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
     * @ORM\Column(name="payment_amount")   
     */
    protected $amount;

    /** 
     * @ORM\Column(name="payment_date")  
     */
    protected $paymentDate;
    
    /** 
     * @ORM\Column(name="payment_priority")  
     */
    protected $paymentPriority = 5;

    /** 
     * @ORM\Column(name="payment_purpose")  
     */
    protected $purpose;

    /** 
     * @ORM\Column(name="nds")  
     */
    protected $nds;

    /** 
     * @ORM\Column(name="payment_purpose_code")  
     */
    protected $purposeCode = '';

    /** 
     * @ORM\Column(name="supplier_bill_id")  
     */
    protected $supplierBillId = 0;

    /** 
     * @ORM\Column(name="tax_info_document_date")  
     */
    protected $taxInfoDocumentDate = '0';

    /** 
     * @ORM\Column(name="tax_info_document_number")  
     */
    protected $taxInfoDocumentNumber = '0';

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
     * @ORM\Column(name="status")  
     */
    protected $status;

    /** 
     * @ORM\Column(name="payment_type")  
     */
    protected $paymentType;

    /** 
     * @ORM\Column(name="status_message")  
     */
    protected $statusMessage;

    /** 
     * @ORM\Column(name="request_id")  
     */
    protected $requestId;

    /** 
     * @ORM\Column(name="date_created")  
     */
    protected $dateCreated;

    /** 
     * @ORM\Column(name="payment_auto")  
     */
    protected $paymentAuto;

    /** 
     * @ORM\Column(name="payment_auto_day")  
     */
    protected $paymentAutoDay;

    /** 
     * @ORM\Column(name="payment_auto_stop_date")  
     */
    protected $paymentAutoStopDate;
    
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\BankAccount", inversedBy="payments") 
     * @ORM\JoinColumn(name="bank_account_id", referencedColumnName="id")
     */
    protected $bankAccount;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Supplier", inversedBy="payments") 
     * @ORM\JoinColumn(name="supplier_id", referencedColumnName="id")
     */
    protected $supplier;

    /**
     * @ORM\ManyToOne(targetEntity="User\Entity\User", inversedBy="payments") 
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;
    
        
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

    public function getCounterpartyBankCorrAccount() {
        return $this->counterpartyBankCorrAccount;
    }

    public function setCounterpartyBankCorrAccount($counterpartyBankCorrAccount) {
        $this->counterpartyBankCorrAccount = $counterpartyBankCorrAccount;
        return $this;
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
        return ($this->counterpartyKpp) ? $this->counterpartyKpp:'';
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
     * Возвращает сумму.
     * @return float
     */
    public function getAmount() 
    {
        return $this->amount;
    }

    /**
     * Возвращает сумму.
     * @param string $delimeter
     * @return float
     */
    public function getFormatAmount($delimeter = ',') 
    {
        return number_format($this->amount, 2, $delimeter, '');
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
     * Возвращает paymentDate.
     * @return date
     */
    public function getPaymentDate() 
    {
        return $this->paymentDate;
    }

    /**
     * Возвращает paymentDate в формате банка.
     * @return date
     */
    public function getFormatPaymentDate() 
    {
        return date('d.m.Y', strtotime($this->paymentDate));
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
     * Возвращает paymentPriority.
     * @return string
     */
    public function getPaymentPriority() 
    {
        return $this->paymentPriority;
    }

    /**
     * Устанавливает paymentPriority
     * @param string $paymentPriority
     */
    public function setPaymentPriority($paymentPriority) 
    {
        $this->paymentPriority = $paymentPriority;
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
     * Возвращает опциональное поле.
     * @return string
     */
    public function getPaymentPurposeCode() 
    {
        return $this->purposeCode;
    }

    /**
     * Устанавливает опциональное поле
     * @param string $purposeCode
     */
    public function setPurposeCode($purposeCode) 
    {
        $this->purposeCode = $purposeCode;
    }     

    /**
     * Возвращает supplierBillId.
     * @return string
     */
    public function getSupplierBillId() 
    {
        return ($this->supplierBillId) ? $this->supplierBillId:0;
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
        return ($this->taxInfoDocumentDate) ? $this->taxInfoDocumentDate:0;
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
        return ($this->taxInfoDocumentDate) ? $this->taxInfoDocumentDate:0;
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
        return ($this->taxInfoKbk) ? $this->taxInfoKbk:0;
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
        return ($this->taxInfoOkato) ? $this->taxInfoOkato:'';
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
        return ($this->taxInfoPeriod) ? $this->taxInfoPeriod:0;
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
        return ($this->taxInfoReasonCode) ? $this->taxInfoReasonCode:'0';
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
        return ($this->taxInfoStatus) ? $this->taxInfoStatus:self::TAX_STATUS_01;
    }
    
    /**
     * Returns possible statuses as array.
     * @return array
     */
    public static function getTaxInfoStatusList() 
    {
        return [
            self::TAX_STATUS_01 => '01 ЕНП',
            self::TAX_STATUS_08 => '08 В бюджет, кроме ЕНП',
            self::TAX_STATUS_31 => '31 Исполнительный лист',
            self::TAX_STATUS_02 => '02 ИП',
            self::TAX_STATUS_06 => '06 ВЭД',
            self::TAX_STATUS_13 => '13 Не резидент',
            self::TAX_STATUS_17 => '13 ИП ВЭД',
        ];
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
     * Возвращает сообщение от банка.
     * @return string
     */
    public function getStatusMessage() 
    {
        return $this->statusMessage;
    }

    /**
     * Устанавливает сообщение от банка
     * @param string $statusMessage
     */
    public function setStatusMessage($statusMessage) 
    {
        $this->statusMessage = $statusMessage;
    }     

    /**
     * Возвращает код ответа банка.
     * @return string
     */
    public function getRequestId() 
    {
        return $this->requestId;
    }

    /**
     * Устанавливает код ответа банка
     * @param string $requestId
     */
    public function setRequestId($requestId) 
    {
        $this->requestId = $requestId;
    }     

    /**
     * Returns the date of payment creation.
     * @return string     
     */
    public function getDateCreated() 
    {
        return $this->dateCreated;
    }
    
    /**
     * Sets the date when this payment was created.
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
            self::STATUS_ACTIVE => 'Новый',
            self::STATUS_TRANSFER => 'Отправлен в банк',
            self::STATUS_SUCCESS => 'Проведен',
            self::STATUS_RETIRED => 'Отменен',
            self::STATUS_ERROR => 'Ошибка',
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

    /**
     * Returns pyamentType.
     * @return int     
     */
    public function getPaymentType() 
    {
        return $this->paymentType;
    }

    /**
     * Returns possible payment types as array.
     * @return array
     */
    public static function getPaymentTypeList() 
    {
        return [
            self::PAYMENT_TYPE_NORMAL => 'Обычный',
            self::PAYMENT_TYPE_TAX => 'Налог',
        ];
    }    
    
    /**
     * Returns payment type as string.
     * @return string
     */
    public function getPaymentTypeAsString()
    {
        $list = self::getPaymentTypeList();
        if (isset($list[$this->paymentType]))
            return $list[$this->paymentType];
        
        return 'Unknown';
    }    
    
    /**
     * Sets payment type.
     * @param int $paymentType     
     */
    public function setPaymentType($paymentType) 
    {
        $this->paymentType = $paymentType;
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
        return [
            self::NDS_NO => 'без НДС',
            self::NDS_10 => 'в т.ч. НДС 10%',
            self::NDS_20 => 'в т.ч. НДС 20%',
        ];
    }    
    
    /**
     * Returns possible nds percent as array.
     * @return array
     */
    public static function getNdsPercentList() 
    {
        return [
            self::NDS_NO => 'без НДС',
            self::NDS_10 => '10%',
            self::NDS_20 => '20%',
        ];
    }    

    /**
     * Расчитать ндс
     * @param float $amount
     * @param integer $nds
     */
    public static function nds($amount, $nds) 
    {
        switch ($nds){
            case Payment::NDS_10:
                return round($amount*10/110, 2);
            case Payment::NDS_20:
                return round($amount*20/120, 2);
            default: return 0;    
        }
        
        return;
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
     * Возвращает расчетный счет.
     * @return BankAccount
     */
    public function getBankAccount() 
    {
        return $this->bankAccount;
    }

    /**
     * Устанавливает расчетный счет
     * @param BankAccount $bankAccount
     */
    public function setBankAccount($bankAccount) 
    {
        $this->bankAccount = $bankAccount;
    }     

    /**
     * Возвращает supplier.
     * @return Supplier
     */
    public function getSupplier() 
    {
        return $this->supplier;
    }

    /**
     * Возвращает supplier id.
     * @return Supplier
     */
    public function getSupplierId() 
    {
        if ($this->supplier){
            return $this->supplier->getId();
        }
        
        return;
    }
    /**
     * Устанавливает supplier
     * @param Supplier $supplier
     */
    public function setSupplier($supplier) 
    {
        if ($supplier instanceof Supplier){
            $this->supplier = $supplier;
        } else {
            $this->supplier = null;
        }    
    }     

    /**
     * Возвращает user.
     * @return User
     */
    public function getUser() 
    {
        return $this->user;
    }

    /**
     * Устанавливает user
     * @param User $user
     */
    public function setUser($user) 
    {
        $this->user = $user;
    }     
    
    public function getPaymentAuto() {
        return $this->paymentAuto;
    }

    /**
     * Returns possible payment auto as array.
     * @return array
     */
    public static function getPaymentAutoList() 
    {
        return [
            self::PAYMENT_AUTO_ONE => 'Нет',
            self::PAYMENT_AUTO_MONTH => 'Ежемесячно',
            self::PAYMENT_AUTO_WEEK => 'Еженедельно',
        ];
    }    
    
    /**
     * Returns payment auto as string.
     * @return string
     */
    public function getPaymentAutoAsString()
    {
        $list = self::getPaymentAutoList();
        if (isset($list[$this->paymentAuto]))
            return $list[$this->paymentAuto];
        
        return 'Unknown';
    }    
    
    public function setPaymentAuto($paymentAuto) {
        $this->paymentAuto = $paymentAuto;
        return $this;
    }

    public function getPaymentAutoDay() {
        return $this->paymentAutoDay;
    }

    public function setPaymentAutoDay($paymentAutoDay) {
        $this->paymentAutoDay = $paymentAutoDay;
        return $this;
    }

    public function getPaymentAutoStopDate() {
        return $this->paymentAutoStopDate;
    }

    public function setPaymentAutoStopDate($paymentAutoStopDate) {
        $this->paymentAutoStopDate = $paymentAutoStopDate;
        return $this;
    }

        
    /**
     * Данные в форму
     * @return array 
     */
    public function toLog()
    {
        return [
            'bankAccount' => $this->getBankAccount()->getId(),
            'supplier' => $this->getSupplierId(),
            'counterpartyAccountNumber' => $this->getCounterpartyAccountNumber(),
            'counterpartyBankCorrAccount' => $this->getCounterpartyBankCorrAccount(),
            'counterpartyBankBik' => $this->getCounterpartyBankBik(),
            'counterpartyInn' => $this->getСounterpartyInn(),
            'counterpartyKpp' => $this->getСounterpartyKpp(),
            'counterpartyName' => $this->getCounterpartyName(),
            'amount' => $this->getAmount(),
            'paymentDate' => $this->getPaymentDate(),
            'purpose' => $this->getPaymentPurpose(),
            'nds' => $this->getNds(),
            'supplierBillId' => $this->getSupplierBillId(),
            'taxInfoDocumentDate' => $this->getTaxInfoDocumentDate(),
            'taxInfoDocumentNumber' => $this->getTaxInfoDocumentNumber(),
            'taxInfoKbk' => $this->getTaxInfoKbk(),
            'taxInfoOkato' => $this->getTaxInfoOkato(),
            'taxInfoPeriod' => $this->getTaxInfoPeriod(),
            'taxInfoReasonCode' => $this->getTaxInfoReasonCode(),
            'taxInfoStatus' => $this->getTaxInfoStatus(),
            'status' => $this->getStatus(),
            'paymentType' => $this->getPaymentType(),            
        ];
    }
}
