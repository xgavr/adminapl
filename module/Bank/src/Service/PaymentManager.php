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
                        $message = [];
                        foreach ($result['errors'] as $error){
                            $message[] = "({$error['code']}) {$error['message']}";
                        }
                        $payment->setStatusMessage(implode(';', $message));
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
        sleep(1);
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
     * Получить статус платежа v2
     * @param Payment $payment
     * "Initiated" Все необходимые реквизиты для платежа получены, платёж готов к проверке на возможность проведения
        "Wait For Owner Requisites" Часть реквизитов для платежа получена, кроме реквизитов плательщика
        "NotAllowed"  Платёж нельзя провести: либо у пользователя нет прав для подписи, либо платёж заблокирован комплаенсом
        "Allowed"  Платёж готов к подписанию, все проверки пройдены
        "WaitingForSign" Платёж ждёт подписи
        "WaitingForCreate" Платёж подписан, ждёт создания внутри систем банка
        "Created" Платёж создан
        "Paid" Платёж оплачен
        "Canceled" Платёж отменен
        "Rejected" Платёж отменён
     */
    public function statusPaymentV2($payment)
    {
        $result = [];
        if ($payment->getRequestId()){
            $result = $this->tochkaPayment->paymentStatusV2($payment->getRequestId());
            if (!empty($result['message'])){
                $payment->setStatusMessage($result['message']);                
            }
            if (!empty($result['Data'])){
                $data = $result['Data'];
                if ($data['status'] == 'Created'){
                    $payment->setStatus(Payment::STATUS_SUCCESS);
                }
                if ($data['status'] == 'WaitingForCreate'){
                    $payment->setStatus(Payment::STATUS_SUCCESS);
                }
                if (count($data['errors'])){
                    $payment->setStatus(Payment::STATUS_ERROR);
                    if (!empty($result['errors'])){
                        $message = [];
                        foreach ($result['errors'] as $error){
                            $message[] = "({$error['code']}) {$error['message']}";
                        }
                        $payment->setStatusMessage(implode(';', $message));
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
    public function sendPaymentV2($payment)
    {
        $data['Data'] = [
            "accountCode" => $payment->getBankAccount()->getRs(),
            "bankCode" =>  $payment->getBankAccount()->getBik(),
            "counterpartyAccountNumber" => $payment->getCounterpartyAccountNumber(),
            "counterpartyBankBic" => $payment->getCounterpartyBankBik(),
            "counterpartyINN" => $payment->getСounterpartyInn(),
            "counterpartyKPP" => $payment->getСounterpartyKpp(),
            "counterpartyName" => $payment->getCounterpartyName(),
            "paymentAmount" => $payment->getFormatAmount('.'),
            "paymentDate" => $payment->getPaymentDate(),
            "paymentNumber" => $payment->getId(),
            "paymentPriority" => $payment->getPaymentPriority(),
            "paymentPurpose" => $payment->getPaymentPurpose(),
            "codePurpose" => $payment->getPaymentPurposeCode(),
            "supplierBillId" => $payment->getSupplierBillId(),
            "taxInfoDocumentDate" => $payment->getTaxInfoDocumentDate(),
            "taxInfoDocumentNumber" => $payment->getTaxInfoDocumentNumber(),
            "taxInfoKBK" => $payment->getTaxInfoKbk(),
            "taxInfoOKATO" => $payment->getTaxInfoOkato(),
            "taxInfoPeriod" => $payment->getTaxInfoPeriod(),
            "taxInfoReasonCode" => $payment->getTaxInfoReasonCode(),
            "taxInfoStatus" => $payment->getTaxInfoStatus(),        
        ];
        
//        var_dump($data); exit;
        $result = $this->tochkaPayment->paymentV2($data);

//        var_dump($result); exit;
        
        $payment->setStatusMessage(empty($result['message']) ? null:$result['message']);
               
        if (!empty($result['Data'])){
            if (!empty($result['Data']['requestId'])){
                $payment->setRequestId(empty($result['Data']['requestId']) ? null:$result['Data']['requestId']);
                $payment->setStatus(Payment::STATUS_TRANSFER);            
            }
        }    
        
        $this->entityManager->persist($payment);
        $this->entityManager->flush();
        $this->entityManager->refresh($payment);
        
        $attempt = 0;
        while ($attempt < 5){
            sleep(1);
            $statusResult = $this->statusPaymentV2($payment);
            $this->entityManager->refresh($payment);
            if ($payment->getStatus() != Payment::STATUS_TRANSFER){
               return $statusResult;
            }
            $attempt++;
        }    
        
        return $statusResult;
    }
    
    /**
     * Отправить все платежи
     * @param string $version
     * @return null
     */
    public function sendAll($version = 1)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(900);
        $startTime = time();
        
        $payments = $this->entityManager->getRepository(Payment::class)
                ->findBy(['status' => Payment::STATUS_ACTIVE, 'requestId' => null]);
        
        foreach ($payments as $payment){
            if ($version == 1){
                $this->sendPayment($payment);
            }    
            if ($version == 2){
                $this->sendPaymentV2($payment);
            }    
            $this->entityManager->refresh($payment);
            if ($payment->getStatus() != Payment::STATUS_SUCCESS){
                break;
            }
            if (time() > $startTime + 840){
                break;
            }
        }
        
        return;
    }
}
