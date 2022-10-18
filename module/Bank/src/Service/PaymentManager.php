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
use Application\Entity\Supplier;
use Company\Entity\BankAccount;
use Company\Entity\Office;
use Company\Entity\Contract;
use Company\Entity\Legal;


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
        $payment->setPaymentType(empty($data['paymentType']) ? Payment::PAYMENT_TYPE_NORMAL:$data['paymentType']);
        $payment->setPurpose($data['purpose']);
        $payment->setPurposeCode(empty($data['purposeCode']) ? '':$data['purposeCode']);
        $payment->setStatus(Payment::STATUS_ACTIVE);
        $payment->setSupplierBillId(empty($data['supplierBillId']) ? 0:$data['supplierBillId']);
        $payment->setTaxInfoDocumentDate(empty($data['taxInfoDocumentDate']) ? null:$data['taxInfoDocumentDate']);
        $payment->setTaxInfoDocumentNumber(empty($data['taxInfoDocumentNumber']) ? null:$data['taxInfoDocumentNumber']);
        $payment->setTaxInfoKbk(empty($data['taxInfoKbk']) ? null:$data['taxInfoKbk']);
        $payment->setTaxInfoOkato(empty($data['taxInfoOkato']) ? null:$data['taxInfoOkato']);
        $payment->setTaxInfoPeriod(empty($data['taxInfoPeriod']) ? null:$data['taxInfoPeriod']);
        $payment->setTaxInfoReasonCode(empty($data['taxInfoReasonCode']) ? null:$data['taxInfoReasonCode']);
        $payment->setTaxInfoStatus(empty($data['taxInfoStatus']) ? null:$data['taxInfoStatus']);
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
        $payment->setPaymentType(empty($data['paymentType']) ? Payment::PAYMENT_TYPE_NORMAL:$data['paymentType']);
        $payment->setPurpose($data['purpose']);
        $payment->setStatus($data['status']);
        $payment->setPurposeCode(empty($data['purposeCode']) ? '':$data['purposeCode']);
        $payment->setSupplierBillId(empty($data['supplierBillId']) ? 0:$data['supplierBillId']);
        $payment->setTaxInfoDocumentDate(empty($data['taxInfoDocumentDate']) ? null:$data['taxInfoDocumentDate']);
        $payment->setTaxInfoDocumentNumber(empty($data['taxInfoDocumentNumber']) ? null:$data['taxInfoDocumentNumber']);
        $payment->setTaxInfoKbk(empty($data['taxInfoKbk']) ? null:$data['taxInfoKbk']);
        $payment->setTaxInfoOkato(empty($data['taxInfoOkato']) ? null:$data['taxInfoOkato']);
        $payment->setTaxInfoPeriod(empty($data['taxInfoPeriod']) ? null:$data['taxInfoPeriod']);
        $payment->setTaxInfoReasonCode(empty($data['taxInfoReasonCode']) ? null:$data['taxInfoReasonCode']);
        $payment->setTaxInfoStatus(empty($data['taxInfoStatus']) ? null:$data['taxInfoStatus']);
        $payment->setNds($data['nds']);
        $payment->setBankAccount($data['bankAccount']);
        $payment->setSupplier($data['supplier']);
        
        $this->entityManager->persist($payment);
        $this->entityManager->flush();
        
        return $payment;
    }
    
    /**
     * Удалить платежку
     * @param Payment $payment
     */
    public function removePayment($payment)
    {
        $this->entityManager->remove($payment);
        $this->entityManager->flush();
        
        return;
    }
    
    /**
     * Реквизиты поставщика
     * @param Supplier $supplier
     * @param Legal $company
     * @return array
     */
    public function supplierDetail($supplier, $company)
    {
        $data = [];
        $legal = $this->entityManager->getRepository(Supplier::class)
                ->findDefaultSupplierLegal($supplier, date('Y-m-d'));
        
        if ($legal){
            $data['inn'] = $legal->getInn();
            $data['kpp'] = $legal->getKpp();
            $data['name'] = $legal->getName();

            $bankAccount = $this->entityManager->getRepository(BankAccount::class)
                    ->findDefaultBankAccount($legal);
            if ($bankAccount){
                $data['rs'] = $bankAccount->getRs();
                $data['bik'] = $bankAccount->getBik();
            }

            $contract = $this->entityManager->getRepository(Office::class)
                    ->findCurrentContract($company, $legal, date('Y-m-d'), Contract::PAY_CASHLESS);
            if ($contract){
                $data['purpose'] = 'Оплата по '.$contract->getContractPresent('договору');
                $data['nds'] = $contract->getNds();
            }
        } 
        
        return $data;
    }
    
    /**
     * Оплата постащикам
     * @param array $data
     */
    public function suppliersPayment($data)
    {
        if (!empty($data['amount'])){
            $bankAccount = $data['bankAccount'];
            $company = $bankAccount->getLegal();
            foreach ($data['amount'] as $row){
                $supplier = $this->entityManager->getRepository(Supplier::class)
                        ->find($row['supplier']);
                if ($supplier && !empty($row['amount'])){
                    $detail = $this->supplierDetail($supplier, $company);
                    if (!empty($detail['rs']) && !empty($detail['bik']) && !empty($detail['inn']) && !empty($detail['name']) && !empty($detail['purpose'])){
                        $payment = [
                            'amount' => $row['amount'],
                            'counterpartyAccountNumber' => $detail['rs'],
                            'counterpartyBankBik' => $detail['bik'],
                            'counterpartyInn' => $detail['inn'],
                            'counterpartyKpp' => $detail['kpp'],
                            'counterpartyName' => $detail['name'],
                            'paymentDate' => $data['paymentDate'],
                            'nds' => $detail['nds'],
                            'purpose' => $detail['purpose'].' '.Payment::getNdsList()[$detail['nds']].' '.Payment::nds($row['amount'], $detail['nds']),
                            'bankAccount' => $bankAccount,
                            'supplier' => $supplier,
                        ];
                        $this->addPayment($payment);
                    }    
                }
            }
        }
        
        return;
    }
    
    /**
     * Получить статус платежа
     * @param Payment $payment
     */
    public function statusPayment($payment)
    {
        $result = [];
        if ($payment->getRequestId()){
            $result = $this->tochkaPayment->paymentStatus($payment->getRequestId());
            if (!empty($result['message'])){
                $payment->setStatusMessage($result['message']);                
            }
            if (!empty($result['status'])){
                if ($result['status'] == 'success'){
                    $payment->setStatus(Payment::STATUS_SUCCESS);
                }
                if ($result['status'] == 'error'){
                    $payment->setStatus(Payment::STATUS_ERROR);
                    if (!empty($result['errors'])){
                        $message = '';
                        foreach ($result['errors'] as $key => $value){
                            $message .= "($key) $value;";
                        }
                        $payment->setStatusMessage($message);
                    }
                }
            }
            $this->entityManager->persist($payment);
            $this->entityManager->flush();
        }    
        return $result;
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
            "counterparty_name" => $payment->getCounterpartyName(),
            "payment_amount" => $payment->getFormatAmount(),
            "payment_date" => $payment->getFormatPaymentDate(),
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
        
//        var_dump($data); exit;
        $result = $this->tochkaPayment->payment($data);
        
//        var_dump($result);
        $payment->setStatusMessage(empty($result['message']) ? null:$result['message']);

        if (!empty($result['request_id'])){
            $payment->setRequestId(empty($result['request_id']) ? null:$result['request_id']);
            $payment->setStatus(Payment::STATUS_TRANSFER);            
        }    
        
        $this->entityManager->persist($payment);
        $this->entityManager->flush();
        $this->entityManager->refresh($payment);
        
        sleep(1);
        $this->statusPayment($payment);
        
        return $result;
    }

    /**
     * Отправить все платежи
     * @return null
     */
    public function sendAll()
    {
        $payments = $this->entityManager->getRepository(Payment::class)
                ->findBy(['status' => Payment::STATUS_ACTIVE, 'requestId' => null]);
        
        foreach ($payments as $payment){
            $this->sendPayment($payment);
            if ($payment->getStatus() !== Payment::STATUS_SUCCESS){
                break;
            }
        }
        
        return;
    }
}
