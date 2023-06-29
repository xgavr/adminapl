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
        $qrCode->setPaymentCode($data['code']);
        $qrCode->setPaymentMessage($data['message']);
        $qrCode->setPaymentTrxId($data['trxId']);
        
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
        
        if ($qrCode->getDateCreated() > date('Y-m-d H:i:s', strtotime('- 3 days'))){
            $qrCode->setStatus(QrCode::STATUS_RETIRED);
        }
        
        $this->entityManager->persist($qrCode);
        $this->entityManager->flush();
        
        return;
    }
    
    /**
     * Обновить статусы qr кодов
     */
    public function updatePaymentStatuses()
    {
        $codes = $this->entityManager->getRepository(QrCode::class)
                ->findBy(['status' => QrCode::STATUS_ACTIVE, 'qrcType' => QrCode::QR_Dynamic]);
        
        $qrcIds = [];
        $result = [];
        
        foreach ($codes as $qrCode){
            $qrcIds[] = $qrCode->getQrcId();
        }
        
        if (count($qrcIds)){
            $result = $this->sbpManager->getPaymentStatuses(implode(',', $qrcIds));
//            var_dump($qrcIds);
//            var_dump($result);
            if (!empty($result['Data'])){
                foreach ($result['Data']['paymentList'] as $payment){
                    $this->updatePaymentStatus($payment['qrcId'], $payment);
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
}
