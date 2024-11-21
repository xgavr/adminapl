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
use Application\Entity\Order;
use Company\Entity\Office;
use Application\Entity\Contact;

/**
 * Description of QrCode
 * 
 * @ORM\Entity(repositoryClass="\Bank\Repository\QrCodeRepository")
 * @ORM\Table(name="qrcode")
 * @author Daddy
 */
class QrCode {
    

    const STATUS_ACTIVE = 1; //данные новые
    const STATUS_SUSPENDED = 2; //данные приостановленны
    const STATUS_USED = 3; //использован
    const STATUS_RETIRED = 9; //устарело

    const PAYMENT_NOT_STARTED = 1; //операции по QR-коду не существует
    const PAYMENT_RECEIVED = 2; //операция в обработке
    const PAYMENT_IN_PROGRESS = 3; //операция в обработке
    const PAYMENT_ACCEPTED = 4; //операция завершена успешно
    const PAYMENT_REJECTED = 5; //операция отклонена
    
    const QR_Static = 1; //QR наклейка
    const QR_Dynamic  = 2; // QR на кассе
        
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
              
    /**
     * Уникальный и неизменный идентификатор счёта юрлица
     * @ORM\Column(name="account")   
     */
    protected $account;

    /**
     * Идентификатор ТСП в СБП (12 символов)
     * @ORM\Column(name="merchant_id")   
     */
    protected $merchantId;
   
    /** 
     * Сумма в копейках
     * @ORM\Column(name="amount")  
     */
    protected $amount;

    /** 
     * Валюта операции
     * @ORM\Column(name="currency")  
     */
    protected $currency;

    /** 
     * Дополнительная информация от ТСП - номер заказа
     * @ORM\Column(name="payment_purpose")  
     */
    protected $paymentPurpose;

    /** 
     * Тип QR-кода
     * @ORM\Column(name="qrc_type")  
     */
    protected $qrcType;

    /**
     * Идентификатор QR-кода в СБП
     * @ORM\Column(name="qrc_id")   
     */
    protected $qrcId;

    /** 
     * Payload зарегистрированного QR-кода в СБП
     * @ORM\Column(name="payload")  
     */
    protected $payload;

    /** 
     * Ширина изображения (>=200, по умолчанию: 300)
     * @ORM\Column(name="image_width")  
     */
    protected $imageWidth;

    /** 
     * Высота изображения (>=200, по умолчанию: 300)
     * @ORM\Column(name="image_hieght")  
     */
    protected $imageHeight;

    /** 
     * Тип контента
     * @ORM\Column(name="image_media_type")  
     */
    protected $imageMediaType;

    /** 
     * Содержимое изображения (для image/png - в кодировке base64)
     * @ORM\Column(name="image_content")  
     */
    protected $imageContent;

    /** 
     * Название источника (системы создавшей QR-код)
     * @ORM\Column(name="source_name")  
     */
    protected $sourceName;

    /** 
     * Период использования QR-кода в минутах
     * @ORM\Column(name="ttl")  
     */
    protected $ttl;

    /** 
     * Номер заказа в Апл
     * @ORM\Column(name="order_apl_id")  
     */
    protected $orderAplId;

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
     * Код платежа
     * @ORM\Column(name="payment_code")  
     */
    protected $paymentCode;

    /** 
     * Сообщение статуса платежа
     * @ORM\Column(name="payment_message")  
     */
    protected $paymentMessage;

    /** 
     * Идентификатор операции, инициированной Dynamic QR-кодом
     * @ORM\Column(name="payment_trx_id")  
     */
    protected $paymentTrxId;

    /** 
     * Статус платежа
     * @ORM\Column(name="payment_status")  
     */
    protected $paymentStatus;

    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\BankAccount", inversedBy="qrcodes") 
     * @ORM\JoinColumn(name="bank_account_id", referencedColumnName="id")
     */
    protected $bankAccount;
    
    /**
     * @ORM\ManyToOne(targetEntity="Company\Entity\Office", inversedBy="qrcodes") 
     * @ORM\JoinColumn(name="office_id", referencedColumnName="id")
     */
    protected $office;
    
    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Order", inversedBy="qrcodes") 
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id")
     */
    protected $order;

    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Contact", inversedBy="qrcodes") 
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="id")
     */
    protected $contact;
    
    /**
     * @ORM\OneToMany(targetEntity="Bank\Entity\QrCodePayment", mappedBy="qrCode") 
     * @ORM\JoinColumn(name="id", referencedColumnName="qrcode_id")
     */
    protected $qrcodePayments;    
        
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
    
    public function getAccount() {
        return $this->account;
    }

    public function getMerchantId() {
        return $this->merchantId;
    }

    public function getAmount() {
        return $this->amount;
    }

    public function getAmountAsRub() {
        return round($this->amount/100, 2);
    }

    public function getCurrency() {
        return $this->currency;
    }

    public function getPaymentPurpose() {
        return $this->paymentPurpose;
    }

    public function getQrcId() {
        return $this->qrcId;
    }

    public function getPayload() {
        return $this->payload;
    }

    public function getPayloadLink() {
        return '<a href="'.$this->payload.'" target="_blank">'.$this->getTitle().'</a>';
    }

    public function getImageWidth() {
        return $this->imageWidth;
    }

    public function getImageHeight() {
        return $this->imageHeight;
    }

    public function getImageMediaType() {
        return $this->imageMediaType;
    }

    public function getImageContent() {
        return $this->imageContent;
    }
    
    public function getImg()
    {
        switch ($this->imageMediaType){
            case 'image/png':
                return "<img src='data:{$this->imageMediaType};base64,{$this->imageContent}' width='{$this->imageWidth}' height='{$this->imageWidth}' alt='".$this->getTitle()."'>";
        }
        
        return;
    }

    public function getCheckImg()
    {
        switch ($this->imageMediaType){
            case 'image/png':
                return "<img src='data:{$this->imageMediaType};base64,{$this->imageContent}' width='200' height='200' alt='".$this->getTitle()."'>";
        }
        
        return;
    }

    public function getMsgImg()
    {
        switch ($this->imageMediaType){
            case 'image/png':
                return "<img src='data:{$this->imageMediaType};base64,{$this->imageContent}' width='300' height='300' alt='".$this->getTitle()."'>";
        }
        
        return;
    }
    
    public function getLinkMsgImg()
    {
        return '<a href="'.$this->payload.'" target="_blank">'.$this->getMsgImg().'</a>';
    }
    
    public function getTitle()
    {
        return 'Оплата '.$this->getAmountAsRub().'Р по заказу №'.$this->getOrderAplId();
    }

    public function getSourceName() {
        return $this->sourceName;
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

    public function setAccount($account) {
        $this->account = $account;
    }

    public function setMerchantId($merchantId) {
        $this->merchantId = $merchantId;
    }

    public function setAmount($amount) {
        $this->amount = $amount;
    }

    public function setCurrency($currency) {
        $this->currency = $currency;
    }

    public function setPaymentPurpose($paymentPurpose) {
        $this->paymentPurpose = $paymentPurpose;
    }

    public function setQrcId($qrcId) {
        $this->qrcId = $qrcId;
    }

    public function setPayload($payload) {
        $this->payload = $payload;
    }

    public function setImageWidth($imageWidth) {
        $this->imageWidth = $imageWidth;
    }

    public function setImageHeight($imageHeight) {
        $this->imageHeight = $imageHeight;
    }

    public function setImageMediaType($imageMediaType) {
        $this->imageMediaType = $imageMediaType;
    }

    public function setImageContent($imageContent) {
        $this->imageContent = $imageContent;
    }

    public function setSourceName($sourceName) {
        $this->sourceName = $sourceName;
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
    public function getTtl() {
        return $this->ttl;
    }

    public function setTtl($ttl) {
        $this->ttl = $ttl;
    }

    public function getOrderAplId() {
        return $this->orderAplId;
    }

    public function setOrderAplId($orderAplId): void {
        $this->orderAplId = $orderAplId;
    }

    public function getPaymentCode() {
        return $this->paymentCode;
    }

    public function getPaymentMessage() {
        return $this->paymentMessage;
    }

    public function getPaymentTrxId() {
        return $this->paymentTrxId;
    }

    public function setPaymentCode($paymentCode) {
        $this->paymentCode = $paymentCode;
    }

    public function setPaymentMessage($paymentMessage) {
        $this->paymentMessage = $paymentMessage;
    }

    public function setPaymentTrxId($paymentTrxId) {
        $this->paymentTrxId = $paymentTrxId;
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
            self::STATUS_SUSPENDED => 'Приостановлен',
            self::STATUS_USED => 'Использован',
            self::STATUS_RETIRED => 'Завершен',
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
            self::PAYMENT_NOT_STARTED => 'Оплаты по QR-коду не существует',
            self::PAYMENT_RECEIVED => 'Оплата в обработке',
            self::PAYMENT_IN_PROGRESS => 'Оплата в обработке',
            self::PAYMENT_ACCEPTED => 'Оплата завершена успешно',
            self::PAYMENT_REJECTED => 'Оплата отклонена',
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

    /**
     * Returns pyamentType.
     * @return int     
     */
    public function getQrcType() 
    {
        return $this->qrcType;
    }

    /**
     * Returns possible qrc types as array.
     * @return array
     */
    public static function getQrcTypeList() 
    {
        return [
            self::QR_Static => '01',
            self::QR_Dynamic => '02',
        ];
    }    
    
    /**
     * Returns qrc type as string.
     * @return string
     */
    public function getQrcTypeAsString()
    {
        $list = self::getPaymentTypeList();
        if (isset($list[$this->qrcType]))
            return $list[$this->qrcType];
        
        return 'Unknown';
    }    
    
    /**
     * Sets qrc type.
     * @param int $qrcType     
     */
    public function setQrcType($qrcType) 
    {
        $this->qrcType = $qrcType;
    }   
    
    public function getQrcodePayments() {
        return $this->qrcodePayments;
    }

    public function setQrcodePayments($qrcodePayments) {
        $this->qrcodePayments = $qrcodePayments;
        return $this;
    }

        
    /**
     * Данные в форму
     * @return array 
     */
    public function toLog()
    {
        return [
            'account' => $this->getAccount(),
            'amount' => $this->getAmount(),
            'bankAccount' => $this->getBankAccount()->getId(),
            'contact' => $this->getContact()->getId(),
            'currency' => $this->getCurrency(),
            'dateCreated' => $this->getDateCreated(),
            'id' => $this->getId(),
            'imageContent' => $this->getImageContent(),
            'imageHeight' => $this->getImageHeight(),
            'imageMediaType' => $this->getImageMediaType(),
            'imageWidth' => $this->getImageWidth(),
            'merchantId' => $this->getMerchantId(),
            'office' => $this->getOffice()->getId(),
            'order' => $this->getOrder()->getId(),
            'payload' => $this->getPayload(),
            'paymentPurpose' => $this->getPaymentPurpose(),
            'qrcId' => $this->getQrcId(),
            'qrcType' => $this->getQrcType(),
            'sourceName' => $this->getSourceName(),
            'status' => $this->getStatus(),
            'ttl' => $this->getTtl(),            
        ];
    }
    
    /**
     * Данные в чек апл
     * @return array 
     */
    public function toCheck()
    {
        return [
            'img' => $this->getCheckImg(),
            'payload' => $this->getPayload(),
        ];
    }
    
    /**
     * Данные в письмо с кодом
     * @return array 
     */
    public function toMsg()
    {
        return [
            'img' => $this->getMsgImg(),
            'imgLink' => $this->getLinkMsgImg(),
            'payload' => $this->getPayload(),
            'payloadLink' => $this->getPayloadLink(),
            'title' => $this->getTitle(),
        ];
    }
}
