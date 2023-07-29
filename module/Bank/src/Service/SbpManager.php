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
use Bank\Entity\QrCode;
use Application\Entity\Order;
use Bank\Entity\QrCodePayment;


/**
 * Description of SbpManager
 *
 * @author Daddy
 */
class SbpManager 
{
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Tochka Sbp manager
     * @var \Bankapi\Service\Tochka\SbpManager
     */
    private $sbpManager;
    
    /**
     * LogManager manager
     * @var \Admin\Service\LogManager
     */
    private $logManager;

    /**
     * AdminManager manager
     * @var \Admin\Service\AdminManager
     */
    private $adminManager;

    public function __construct($entityManager, $sbpManager, $logManager, $adminManager)
    {
        $this->entityManager = $entityManager;
        $this->sbpManager = $sbpManager;    
        $this->logManager = $logManager;        
        $this->adminManager = $adminManager;        
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
     * Инфо о клиенте СБП
     * @return array
     */
    public function getLegal()
    {
        $settings = $this->adminManager->getSbpSettings();

        return $this->sbpManager->getLegal($settings['legal_id']);
    }
    
    /**
     * Регистрация qrcode
     * 
     * @param array $data
     * [
     *  'amount' - сумма в рублях
     *  'orderAplId' - номер заказа в апл
     * ]
     * @return Payment
     */
    public function registerQrCode($data)
    {        
        $amount = round($data['amount']*100);
        
        if (empty($amount)){
            return;
        }
        
        $qrCode = $this->entityManager->getRepository(QrCode::class)
                ->qrCodeByOrderAplId($data['orderAplId'], $amount);
        if ($qrCode){
            return $qrCode;
        }

        $settings = $this->adminManager->getSbpSettings();
        
        $bankAccount = $this->entityManager->getRepository(BankAccount::class)
                ->find($settings['account']);
        
        $result = [];
        
        if ($bankAccount){
            
            $account = $bankAccount->getSbpAccountId();
            $merchant_id = $settings['merchant_id'];
            $qrCodeData = [
                'Data' => [
                    "amount" => $amount,
                    "currency" => "RUB",
                    "paymentPurpose" => "Оплата заказа №{$data['orderAplId']}",
                    "qrcType" => QrCode::getQrcTypeList()[QrCode::QR_Dynamic],
                    "imageParams" => [
                        "width" => 200,
                        "height" => 200,
                        "mediaType" => "image/png"
                    ],
                    "sourceName" => $data['orderAplId'],  
                    "ttl" => 0  
                ],
            ];
            
            $result = $this->sbpManager->registerQrCode($account, $merchant_id, $qrCodeData);
            //var_dump($result);
            if (!empty($result['Data'])){
                $resultData = $result['Data'];
                
                $qrCode = $this->entityManager->getRepository(QrCode::class)
                        ->findOneBy(['qrcId' => $resultData['qrcId']]);
                if ($qrCode){
                    return $qrCode;
                }
                
                $qrCode = new QrCode();            
                $qrCode->setAccount($account);
                $qrCode->setAmount($amount);
                $qrCode->setBankAccount($bankAccount);
                $qrCode->setCurrency($qrCodeData['Data']['currency']);
                $qrCode->setDateCreated(date('Y-m-d H:i:s'));
                $qrCode->setImageContent($resultData['image']['content']);
                $qrCode->setImageHeight($resultData['image']['height']);
                $qrCode->setImageMediaType($resultData['image']['mediaType']);
                $qrCode->setImageWidth($resultData['image']['width']);
                $qrCode->setMerchantId($merchant_id);
                $qrCode->setPayload($resultData['payload']);
                $qrCode->setPaymentPurpose($qrCodeData['Data']['paymentPurpose']);
                $qrCode->setQrcId($resultData['qrcId']);
                $qrCode->setQrcType($qrCodeData['Data']['qrcType']);
                $qrCode->setSourceName($qrCodeData['Data']['sourceName']);
                $qrCode->setOrderAplId($data['orderAplId']);
                $qrCode->setStatus($qrCode::STATUS_ACTIVE);
                $qrCode->setPaymentStatus($qrCode::PAYMENT_NOT_STARTED);
                $qrCode->setTtl($qrCodeData['Data']['ttl']);
                
                $order = $this->entityManager->getRepository(Order::class)
                        ->findOneBy(['aplId' => $data['orderAplId']]);
        
                if ($order){
                    $qrCode->setContact($order->getContact());
                    $qrCode->setOffice($order->getOffice());
                    $qrCode->setOrder($order);
                }    
            
                $this->entityManager->persist($qrCode);
                $this->entityManager->flush();
                return $qrCode;                
            }
        }    
        
        return $result;
    }

    /**
     * Обновить qr код
     * 
     * @param QrCode $qrCode
     * 
     * @return QrCode
     */
    public function updateQrCode($qrCode)
    {
        $result = $this->sbpManager->getQrCode($qrCode->getQrcId());

        if (!empty($result['Data'])){
        
            $resultData = $result['Data'];
            
            $qrCode->setAccount($resultData['accountId']);
            $qrCode->setAmount($resultData['amount']);
            $qrCode->setCurrency($resultData['currency']);
            $qrCode->setImageContent($resultData['image']['content']);
            $qrCode->setImageHeight($resultData['image']['height']);
            $qrCode->setImageMediaType($resultData['image']['mediaType']);
            $qrCode->setImageWidth($resultData['image']['width']);
            $qrCode->setMerchantId($resultData['merchantId']);
            $qrCode->setPayload($resultData['payload']);
            $qrCode->setPaymentPurpose($resultData['paymentPurpose']);
            $qrCode->setSourceName($resultData['sourceName']);            
            $qrCode->setTtl($resultData['ttl']);
            
            switch ($resultData['status']){
                case 'Active': $qrCode->setStatus($qrCode::STATUS_ACTIVE); break;
                case 'Suspended': $qrCode->setStatus($qrCode::STATUS_SUSPENDED); break;
            }

            if (!$qrCode->getOrder()){
                $order = $this->entityManager->getRepository(Order::class)
                        ->findOneBy(['aplId' => $qrCode->getOrderAplId()]);

                if ($order){
                    $qrCode->setContact($order->getContact());
                    $qrCode->setOffice($order->getOffice());
                    $qrCode->setOrder($order);
                }    
            }    
            
            $this->entityManager->persist($qrCode);
            $this->entityManager->flush();

            return $qrCode;
        }
        
        return $result;
    }
    
    /**
     * Удалить qrcode
     * @param QrCode $qrCode
     */
    public function removeQrCode($qrCode)
    {
        $this->entityManager->remove($qrCode);
        $this->entityManager->flush();
        
        return;
    }
        
    /**
     * Обновить статус платежа по qr коду
     * @param QrCode $qrCode
     */
    public function updatePaymentStatus($qrCode, $data) 
    {
        $qrCode->setPaymentCode(empty($data['code']) ? null:$data['code']);
        $qrCode->setPaymentMessage(empty($data['message']) ? null:$data['message']);
        $qrCode->setPaymentTrxId(empty($data['trxId']) ? null:$data['trxId']);
        
        switch ($data['status']){
            case 'NotStarted': $qrCode->setPaymentStatus(QrCode::PAYMENT_NOT_STARTED); break;
            case 'Received': $qrCode->setPaymentStatus(QrCode::PAYMENT_RECEIVED); break;
            case 'InProgress': $qrCode->setPaymentStatus(QrCode::PAYMENT_IN_PROGRESS); break;
            case 'Accepted': 
                $qrCode->setPaymentStatus(QrCode::PAYMENT_ACCEPTED);
                $qrCode->setStatus(QrCode::STATUS_RETIRED); 
                break;
            case 'Rejected': 
                $qrCode->setPaymentStatus(QrCode::PAYMENT_REJECTED);
                $qrCode->setStatus(QrCode::STATUS_RETIRED); 
                break;
        }
        
        if (!$qrCode->getOrder() && $qrCode->getOrderAplId()){
            $order = $this->entityManager->getRepository(Order::class)
                    ->findOneBy(['aplId' => $qrCode->getOrderAplId]);
            if ($order){
                $qrCode->setContact($order->getContact());
                $qrCode->setOffice($order->getOffice());
                $qrCode->setOrder($order);
            }
        }    
        
        if ($qrCode->getDateCreated() < date('Y-m-d H:i:s', strtotime('- 3 days'))){
            $qrCode->setStatus(QrCode::STATUS_RETIRED);
        }
        
        $this->entityManager->persist($qrCode);
        $this->entityManager->flush();
        
        return;
    }
    
    /**
     * Добавить данные о платеже по qr коду
     * 
     * @param QrCode $qrCode
     * @param array $data
     * 
     * @return QrCodePayment
     */
    public function addQrCodePayment($qrCode, $data)
    {
        $tarnsactionId = null;
        if (!empty($data['refTransactionId'])){
            $tarnsactionId = $data['refTransactionId'];
        }    
        if (!empty($data['requestId'])){
            $tarnsactionId = $data['requestId'];
        }    
        
        if ($tarnsactionId){
            $payment = $this->entityManager->getRepository(QrCodePayment::class)
                    ->findOneBy(['refTransactionId' => $tarnsactionId]);
            
            if (!$payment){
                $payment = new QrCodePayment();            
                $payment->setDateCreated(date('Y-m-d H:i:s'));
                $payment->setCashDoc(null);
                $payment->setBankAccount($qrCode->getBankAccount());
                $payment->setContact($qrCode->getContact());
                $payment->setOffice($qrCode->getOffice());
                $payment->setOrder($qrCode->getOrder());
                $payment->setQrCode($qrCode);
                $payment->setStatus(QrCodePayment::STATUS_ACTIVE);
                $payment->setPaymentStatus(QrCodePayment::PAYMENT_CONFIRMING);
            }

            if (!empty($data['amount'])){
                $payment->setAmount($data['amount']);            
            }

            if (!empty($data['message'])){
                $payment->setPaymentMessage($data['message']);            
            }
            if (!empty($data['statusDescription'])){
                $payment->setPaymentMessage($data['statusDescription']);            
            }

            if (!empty($data['purpose'])){
                $payment->setPurpose($data['purpose']);
            }    

            if (!empty($data['refTransactionId'])){
                $payment->setRefTransactionId($data['refTransactionId']);
                $payment->setPaymentType(QrCodePayment::TYPE_PAYMENT);
                $payment->setAmount($qrCode->getAmountAsRub());
            }    
            if (!empty($data['requestId'])){
                $payment->setRefTransactionId(data['requestId']);
                $payment->setPaymentType(QrCodePayment::TYPE_REFUND);
            }    

            if (!empty($data['status'])){
                switch ($data['status']){ 
                    case 'Confirming': 
                        $payment->setPaymentStatus(QrCodePayment::PAYMENT_CONFIRMING);
                        break;
                    case 'Confirming': 
                        $payment->setPaymentStatus(QrCodePayment::PAYMENT_CONFIRMING);
                        break;
                    case 'Confirmed': 
                        $payment->setPaymentStatus(QrCodePayment::PAYMENT_CONFIRMED);
                        break;
                    case 'Initiated': 
                        $payment->setPaymentStatus(QrCodePayment::PAYMENT_INITIATED);
                        break;
                    case 'Accepting': 
                        $payment->setPaymentStatus(QrCodePayment::PAYMENT_ACCEPTING);
                        break;
                    case 'Accepted': 
                        $payment->setPaymentStatus(QrCodePayment::PAYMENT_ACCEPTED);
                        break;
                    case 'InProgress': 
                        $payment->setPaymentStatus(QrCodePayment::PAYMENT_IN_PROGRESS);
                        break;
                    case 'Rejected': 
                        $payment->setPaymentStatus(QrCodePayment::PAYMENT_REJECTED);
                        break;
                    case 'Error': 
                        $payment->setPaymentStatus(QrCodePayment::PAYMENT_ERROR);
                        break;
                    case 'Timeout': 
                        $payment->setPaymentStatus(QrCodePayment::PAYMENT_TIMEOUT);
                        break;
                    case 'WaitingForConfirm': 
                        $payment->setPaymentStatus(QrCodePayment::PAYMENT_WAITING_FOR_CONFIRM);
                        break;
                    case 'WaitingForAccept': 
                        $payment->setPaymentStatus(QrCodePayment::PAYMENT_WAITING_FOR_ACCEPT);
                        break;
                }    
            }

            $this->entityManager->persist($payment);
            $this->entityManager->flush();

            return $payment;        
        }
        
        return;
    }
    
    /**
     * Получить платежи по qr коду
     * @param QrCode $qrCode
     */
    public function getPayment($qrCode)
    {
        $result = [];
        $settings = $this->adminManager->getSbpSettings();
        $customerCode = $settings['customer_code'];
        if ($customerCode){
            $result = $this->sbpManager->getPaymentData($customerCode, $qrCode->getQrcId(), date('Y-m-d', strtotime($qrCode->getDateCreated())));
            if (!empty($result['Data'])){
                foreach ($result['Data']['Payments'] as $payment){
                    $payment = $this->addQrCodePayment($qrCode, $payment);
                }
            }
        }    
        
        return $result;
    }
    
    /**
     * Проверить активные qr коды
     * 
     * @return null
     */
    public function checkAllDynamic()
    {
        ini_set('memory_limit', '512M');
        set_time_limit(900);
        $startTime = time();
        
        $codes = $this->entityManager->getRepository(QrCode::class)
                ->findBy(['status' => QrCode::STATUS_ACTIVE, 'qrcType' => QrCode::QR_Dynamic]);
        
        $flush = false;
        foreach ($codes as $qrCode){
            $flag = false;
            if (!$qrCode->getOrder() && $qrCode->getOrderAplId()){
                $order = $this->entityManager->getRepository(Order::class)
                        ->findOneBy(['aplId' => $qrCode->getOrderAplId]);
                if ($order){
                    $qrCode->setContact($order->getContact());
                    $qrCode->setOffice($order->getOffice());
                    $qrCode->setOrder($order);
                    $flag = true;
                }
            }    
            
            if ($qrCode->getDateCreated() > date('Y-m-d H:i:s', strtotime('- 3 days'))){
                $qrCode->setStatus(QrCode::STATUS_RETIRED);
                $flag = true;
            }
            
            if ($flag){
                $this->entityManager->persist($qrCode);
                $flush = true;
            }
            
            if (time() > $startTime + 840){
                break;
            }
        }
        
        if ($flush){
            $this->entityManager->flush();
        }
        
        return;
    }
    

    /**
     * Обновить статусы qr кодов
     * @param QrCode $qrCode
     * @return array
     */
    public function updatePaymentStatuses($qrCode = null)
    {
        $qrcIds = [];
        $result = [];

        if (empty($qrCode)){
            $codes = $this->entityManager->getRepository(QrCode::class)
                    ->findBy(['status' => QrCode::STATUS_ACTIVE, 'qrcType' => QrCode::QR_Dynamic]);

            foreach ($codes as $qrCode){
                $qrcIds[] = $qrCode->getQrcId();
            }
        } else {
            $qrcIds[] = $qrCode->getQrcId();
        }    
        
        if (count($qrcIds)){
            $result = $this->sbpManager->getPaymentStatuses(implode(',', $qrcIds));
            if (!empty($result['Data'])){
                foreach ($result['Data']['paymentList'] as $payment){
                    $qrCode = $this->entityManager->getRepository(QrCode::class)
                            ->findOneBy(['qrcId' => $payment['qrcId']]);
                    if ($qrCode){
                        $this->updatePaymentStatus($qrCode, $payment);
                        $this->entityManager->refresh($qrCode);
//                        if ($qrCode->getPaymentStatus() == QrCode::PAYMENT_ACCEPTED){
//                            $this->addQrCodePayment($qrCode, [
//                                'refTransactionId' => $payment['trxId'],
//                                'status' => $payment['status'],
//                            ]);
//                        }
                    }    
                }
            }
        }
        
        return $result;
    }
        
    /**
     * Возврат оплаты
     * 
     * @param QrCodePayment $qrcodePayment
     * @param float $amount
     * @return array 
     */
    public function refund($qrcodePayment, $amount)
    {
        $purpose = 'Воззврат оплаты по заказу '.$qrcodePayment->getQrCode()->getOrderAplId();
        
        $data = ['Data' => 
            [
                'bankCode' => $qrcodePayment->getBankAccount()->getBik(),
                'accountCode' => $qrcodePayment->getBankAccount()->getRs(),
                'amount' => $amount,
                'currency' => $qrcodePayment->getQrCode()->getCurrency(),
                'qrcId' => $qrcodePayment->getQrCode()->getQrcId(),
                'purpose' => $purpose,
                'refTransactionId' => $qrcodePayment->getRefTransactionId(),
            ],    
        ];
        
        $result = $this->sbpManager->refund($data);
        
        if (!empty($result['Data'])){
            $resultData = $result['Data'];
            $this->addQrCodePayment($qrcodePayment->getQrCode(), [
                'requestId' => $resultData['requestId'],
                'amount' => $amount,
                'status' => $resultData['status'],
                'purpose' => $purpose,
            ]);
        }
        
        return $result;
    }

    /**
     * Обновление статуса возврата оплаты
     * 
     * @param QrCodePayment $qrcodePayment
     * 
     * @return array 
     */
    public function updateRefund($qrcodePayment)
    {
        
        $result = $this->sbpManager->refundData($qrcodePayment->getRefTransactionId());
        
        if (!empty($result['Data'])){
            $resultData = $result['Data'];
            $this->addQrCodePayment($qrcodePayment->getQrCode(), [
                'requestId' => $resultData['requestId'],
                'status' => $resultData['status'],
                'statusDescription' => $resultData['statusDescription'],
            ]);
        }
        
        return $result;
    }
}
