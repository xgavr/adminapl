<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 * 
 */
namespace Bank\Service;

use User\Entity\User;
use Bank\Entity\Payment;


/**
 * Description of PaymentManager
 *
 * @author Daddy
 */
class PaymentManager 
{
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Tochka Statetment manager
     * @var \Bankapi\Service\Tochka\Payment
     */
    private $tochkaPayment;
    
    /**
     * LogManager manager
     * @var \Admin\Service\LogManager
     */
    private $logManager;

    public function __construct($entityManager, $tochkaPyament, $logManager)
    {
        $this->entityManager = $entityManager;
        $this->tochkaPayment = $tochkaPyament;    
        $this->logManager = $logManager;        
    }
    
    /**
     * Текущий пользователь
     * @return User
     */
    public function currentUser()
    {
        return $this->logManager->currentUser(); 
    }
    
    /**
     * Новая платежка
     * 
     * @param array $data
     * @return Payment
     */
    public function addPayment($data)
    {
        $payment = new Payment();
        $payment->setAmount($data['amount']);
        $payment->setCounterpartyAccountNumber($data['counterpartyAccountNumber']);
        $payment->setCounterpartyBankBik($data['counterpartyBankBik']);
        $payment->setCounterpartyInn($data['counterpartyInn']);
        $payment->setCounterpartyKpp($data['counterpartyKpp']);
        $payment->setCounterpartyName($data['counterpartyName']);
        $payment->setDateCreated(date('Y-m-d H:i:s'));
        $payment->setPaymentDate($data['paymentDate']);
        $payment->setPaymentPriority(5);
        $payment->setPaymentType($data['paymentType']);
        $payment->setPurpose($data['purpose']);
        $payment->setPurposeCode($data['purposeCode']);
        $payment->setStatus(Payment::STATUS_ACTIVE);
        $payment->setSupplierBillId($data['supplierBillId']);
        $payment->setTaxInfoDocumentDate($data['taxInfoDocumentDate']);
        $payment->setTaxInfoDocumentNumber($data['taxInfoDocumentNumber']);
        $payment->setTaxInfoKbk($data['taxInfoKbk']);
        $payment->setTaxInfoOkato($data['taxInfoOkato']);
        $payment->setTaxInfoPeriod($data['taxInfoPeriod']);
        $payment->setTaxInfoReasonCode($data['taxInfoReasonCode']);
        $payment->setTaxInfoStatus($data['taxInfoStatus']);
        $payment->setUser($this->currentUser());
        $payment->setNds($data['nds']);
        $payment->setBankAccount($data['bankAccount']);
        $payment->setSupplier($data['supplier']);
        
        $this->entityManager->persist($payment);
        $this->entityManager->flush();
        
        return $payment;
    }

    /**
     * Обновить платежку
     * 
     * @param Payment $payment
     * @param array $data
     * @return Payment
     */
    public function updatePayment($payment, $data)
    {
        $payment->setAmount($data['amount']);
        $payment->setCounterpartyAccountNumber($data['counterpartyAccountNumber']);
        $payment->setCounterpartyBankBik($data['counterpartyBankBik']);
        $payment->setCounterpartyInn($data['counterpartyInn']);
        $payment->setCounterpartyKpp($data['counterpartyKpp']);
        $payment->setCounterpartyName($data['counterpartyName']);
        $payment->setPaymentDate($data['paymentDate']);
        $payment->setPaymentPriority(5);
        $payment->setPaymentType($data['paymentType']);
        $payment->setPurpose($data['purpose']);
        $payment->setPurposeCode($data['purposeCode']);
        $payment->setSupplierBillId($data['supplierBillId']);
        $payment->setTaxInfoDocumentDate($data['taxInfoDocumentDate']);
        $payment->setTaxInfoDocumentNumber($data['taxInfoDocumentNumber']);
        $payment->setTaxInfoKbk($data['taxInfoKbk']);
        $payment->setTaxInfoOkato($data['taxInfoOkato']);
        $payment->setTaxInfoPeriod($data['taxInfoPeriod']);
        $payment->setTaxInfoReasonCode($data['taxInfoReasonCode']);
        $payment->setTaxInfoStatus($data['taxInfoStatus']);
        $payment->setNds($data['nds']);
        $payment->setBankAccount($data['bankAccount']);
        $payment->setSupplier($data['supplier']);
        
        $this->entityManager->persist($payment);
        $this->entityManager->flush();
        
        return $payment;
    }
    
    /**
     * Отправить платеж в банк
     * @param Payment $payment
     */
    public function sendPayment($payment)
    {
        $data = [
            "account_code" => $payment->getBankAccount()->getRs(),
            "bank_code" =>  $payment->getBankAccount()->getBik(),
            "counterparty_account_number" => $payment->getCounterpartyAccountNumber(),
            "counterparty_bank_bic" => $payment->getCounterpartyBankBik(),
            "counterparty_inn" => $payment->getСounterpartyInn(),
            "counterparty_kpp" => $payment->getСounterpartyKpp(),
            "counterparty_name" => $payment->getСounterpartyName(),
            "payment_amount" => $payment->getAmount(),
            "payment_date" => $payment->getPaymentDate(),
            "payment_number" => $payment->getId(),
            "payment_priority" => $payment->getPaymentPriority(),
            "payment_purpose" => $payment->getPaymentPurpose(),
            "payment_purpose_code" => $payment->getPaymentPurposeCode(),
            "supplier_bill_id" => $payment->getSupplierBillId(),
            "tax_info_document_date" => $payment->getTaxInfoDocumentDate(),
            "tax_info_document_number" => $payment->getTaxInfoDocumentNumber(),
            "tax_info_kbk" => $payment->getTaxInfoKbk(),
            "tax_info_okato" => $payment->getTaxInfoOkato(),
            "tax_info_period" => $payment->getTaxInfoPeriod(),
            "tax_info_reason_code" => $payment->getTaxInfoReasonCode(),
            "tax_info_status" => $payment->getTaxInfoStatus(),        
        ];
        
        $result = $this->tochkaPayment->payment($data);
        
        $payment->getRequestId(empty($result['request_id']) ? null:$result['request_id']);
        $payment->getStatusMessage($result['message']);
        $this->entityManager->persist($payment);
        $this->entityManager->flush();
        
        return;
    }

    /**
     * Получить статус платежа
     * @param Payment $payment
     */
    public function statusPayment($payment)
    {
        if ($payment->getRequestId()){
            $result = $this->tochkaPayment->paymentStatus($payment->getRequestId());

            $payment->getRequestId(empty($result['request_id']) ? null:$result['request_id']);
            $payment->getStatusMessage($result['message']);
            $this->entityManager->persist($payment);
            $this->entityManager->flush();
        }    
        return;
    }
}
