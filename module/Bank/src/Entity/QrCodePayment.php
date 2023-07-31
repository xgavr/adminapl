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
use Company\Entity\BankAccount;
use Application\Entity\Order;
use Company\Entity\Office;
use Application\Entity\Contact;
use Bank\Entity\QrCode;
use Cash\Entity\CashDoc;

/**
 * Description of QrCode
 * 
 * @ORM\Entity(repositoryClass="\Bank\Repository\QrCodeRepository")
 * @ORM\Table(name="qrcode_payment")
 * @author Daddy
 */
class QrCodePayment {
    

    const STATUS_ACTIVE = 1; //данные новые
    const STATUS_RETIRED = 9; //данные удалены

    const TYPE_PAYMENT = 1; //поступление
    const TYPE_REFUND = 2; //возврат
    
    const PAYMENT_CONFIRMING = 1; //операция в процессе подтверждения ОПКЦ СБП
    const PAYMENT_CONFIRMED = 2; //операция подтверждена
    const PAYMENT_INITIATED = 3; //операция отправлена на обработку
    const PAYMENT_WAITING_FOR_CONFIRM = 4; //ожидание подтверждения
    const PAYMENT_WAITING_FOR_ACCEPT = 5; //ожидание завершения
    const PAYMENT_ACCEPTING = 6; //операция в обработке ОПКЦ СБП
    const PAYMENT_ACCEPTED = 7; //операция успешно завершена
    const PAYMENT_IN_PROGRESS = 8; //операция в обработке РЦ СБП
    const PAYMENT_REJECTED = 9; //операция отклонена
    const PAYMENT_ERROR = 10; //ошибка выполнения операции
    const PAYMENT_TIMEOUT = 11; //тайм-аут выполнения операции
        
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /** 
     * Идентификатор операции, инициированной Dynamic QR-кодом или ID запроса возврата
     * @ORM\Column(name="ref_transaction_id")  
     */
    protected $refTransactionId;    
              
    /** 
     * Cумма операции в рублях
     * @ORM\Column(name="amount")  
     */
    protected $amount;

    /** 
     * Назначение платежа
     * @ORM\Column(name="purpose")  
     */
    protected $purpose;

    /** 
     * Текстовое представление статуса
     * @ORM\Column(name="payment_message")  
     */
    protected $paymentMessage;

    /** 
     * @ORM\Column(name="date_created")  
     */
    protected $dateCreated;
        
    /** 
     * Статус объекта
     * @ORM\Column(name="status")  
     */
    protected $status;

    /** 
     * Тип операции
     * @ORM\Column(name="payment_type")  
     */
    protected $paymentType;

    /** 
     * Статус операции
     * @ORM\Column(name="payment_status")  
     */
    protected $paymentStatus;

    /**
     * @ORM\ManyToOne(targetEntity="Bank\Entity\QrCode", inversedBy="qrcodePayments") 
     * @ORM\JoinColumn(name="qrcode_id", referencedColumnName="id")
     */
    protected $qrCode;
    
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\BankAccount", inversedBy="qrcodePayments") 
     * @ORM\JoinColumn(name="bank_account_id", referencedColumnName="id")
     */
    protected $bankAccount;
    
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Office", inversedBy="qrcodePayments") 
     * @ORM\JoinColumn(name="office_id", referencedColumnName="id")
     */
    protected $office;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Order", inversedBy="qrcodePayments") 
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id")
     */
    protected $order;

    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Contact", inversedBy="qrcodePayments") 
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="id")
     */
    protected $contact;

    /**
     * @ORM\OneToOne(targetEntity="Cash\Entity\CashDoc", inversedBy="qrcodePayment") 
     * @ORM\JoinColumn(name="cash_doc_id", referencedColumnName="id")
     */
    protected $cashDoc;
    
        
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
    
    public function getAmount() {
        return $this->amount;
    }

    public function getQrcId() {
        return $this->qrcId;
    }

    public function getDateCreated() {
        return $this->dateCreated;
    }

    /**
     * 
     * @return BankAccount
     */
    public function getBankAccount() {
        return $this->bankAccount;
    }

    /**
     * 
     * @return Office
     */
    public function getOffice() {
        return $this->office;
    }

    /**
     * 
     * @return Order
     */
    public function getOrder() {
        return $this->order;
    }

    /**
     * 
     * @return Contact
     */
    public function getContact() {
        return $this->contact;
    }

    public function setAmount($amount) {
        $this->amount = $amount;
    }

    public function setPaymentPurpose($paymentPurpose) {
        $this->purpose = $paymentPurpose;
    }

    public function setQrcId($qrcId) {
        $this->qrcId = $qrcId;
    }

    public function setDateCreated($dateCreated) {
        $this->dateCreated = $dateCreated;
    }

    /**
     * 
     * @param BankAccount $bankAccount
     */
    public function setBankAccount($bankAccount) {
        $this->bankAccount = $bankAccount;
    }

    /**
     * 
     * @param Office $office
     */
    public function setOffice($office) {
        $this->office = $office;
    }

    /**
     * 
     * @param Order $order
     */
    public function setOrder($order) {
        $this->order = $order;
    }

    /**
     * 
     * @param Contact $contact
     */
    public function setContact($contact) {
        $this->contact = $contact;
    }

    public function getPaymentMessage() {
        return $this->paymentMessage;
    }

    public function setPaymentMessage($paymentMessage) {
        $this->paymentMessage = $paymentMessage;
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
            self::STATUS_RETIRED => 'Отменен',
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
     * Returns payment status.
     * @return int     
     */
    public function getPaymentStatus() 
    {
        return $this->paymentStatus;
    }

    /**
     * Returns possible payment statuses as array.
     * @return array
     */
    public static function getPaymentStatusList() 
    {
        return [
            self::PAYMENT_CONFIRMING => 'Операция в процессе подтверждения ОПКЦ СБП',
            self::PAYMENT_CONFIRMED => 'Операция подтверждена',
            self::PAYMENT_INITIATED => 'Операция отправлена на обработку',
            self::PAYMENT_WAITING_FOR_CONFIRM => 'Ожидание подтверждения',
            self::PAYMENT_WAITING_FOR_ACCEPT => 'Ожидание завершения',
            self::PAYMENT_ACCEPTING => 'Операция в обработке ОПКЦ СБП',
            self::PAYMENT_ACCEPTED => 'Операция успешно завершена',
            self::PAYMENT_IN_PROGRESS => 'Операция в обработке РЦ СБП',
            self::PAYMENT_REJECTED => 'Операция отклонена',
            self::PAYMENT_ERROR => 'Ошибка выполнения операции',
            self::PAYMENT_TIMEOUT => 'Тайм-аут выполнения операции',
        ];
    }    
    
    /**
     * Returns payment status as string.
     * @return string
     */
    public function getPaymentStatusAsString()
    {
        $list = self::getPaymentStatusList();
        if (isset($list[$this->paymentStatus]))
            return $list[$this->paymentStatus];
        
        return 'Unknown';
    }    
    
    /**
     * Sets payment status.
     * @param int $paymentStatus     
     */
    public function setPaymentStatus($paymentStatus) 
    {
        $this->paymentStatus = $paymentStatus;
    }   
    
    public function getRefTransactionId() {
        return $this->refTransactionId;
    }

    public function getPurpose() {
        return $this->purpose;
    }

    public function getPaymentType() {
        return $this->paymentType;
    }

    /**
     * Returns possible payment type as array.
     * @return array
     */
    public static function getPaymentTypeList() 
    {
        return [
            self::TYPE_PAYMENT => 'Поступление',
            self::TYPE_REFUND => 'Возврат',
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
     * Returns possible cashDoc kind as array.
     * @return array
     */
    public static function getCashDocKindList() 
    {
        return [
            self::TYPE_PAYMENT => CashDoc::KIND_IN_PAYMENT_CLIENT,
            self::TYPE_REFUND => CashDoc::KIND_OUT_RETURN_CLIENT,
        ];
    }    
    
    /**
     * Returns cashDoc kind as integer.
     * @return string
     */
    public function getCashDocKind()
    {
        $list = self::getCashDocKindList();
        if (isset($list[$this->paymentType]))
            return $list[$this->paymentType];
        
        return 'Unknown';
    }    
    
    /**
     * 
     * @return QrCode
     */
    public function getQrCode() {
        return $this->qrCode;
    }

    public function getCashDoc() {
        return $this->cashDoc;
    }

    public function setRefTransactionId($refTransactionId) {
        $this->refTransactionId = $refTransactionId;
        return $this;
    }

    public function setPurpose($purpose) {
        $this->purpose = $purpose;
        return $this;
    }

    public function setPaymentType($paymentType) {
        $this->paymentType = $paymentType;
        return $this;
    }

    public function setQrCode($qrCode) {
        $this->qrCode = $qrCode;
        return $this;
    }

    public function setCashDoc($cashDoc) {
        $this->cashDoc = $cashDoc;
        return $this;
    }

}
