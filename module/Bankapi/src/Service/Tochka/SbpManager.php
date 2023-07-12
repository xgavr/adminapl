<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Bankapi\Service\Tochka;

use Laminas\Http\Client;
use Laminas\Json\Decoder;
use Laminas\Json\Encoder;
use Company\Entity\Office;

/**
 * Система быстрых платежей банка Точка
 *
 * @author Daddy
 */
class SbpManager {

    /**
     * @var \Bankapi\Service\Tochka\Authenticate
     */
    private $auth;
    
    public function __construct($auth) 
    {
        $this->auth = $auth;
    }        
    
    /**
     * Выполняет регистрацию юрлица в СБП.
     * https://enter.tochka.com/uapi/sbp/{apiVersion}/register-sbp-legal-entity
     * 
     * @param string $customerCode
     * @param string $bankCode
     * 
     * @return array|Exception
     */
    public function registerLegal($customerCode, $bankCode)
    {
        $data = [
            'Data' => [
                'customerCode' => $customerCode,
                'bankCode' => $bankCode,
            ],
        ];
        $this->auth->isAuth();
        $client = new Client();
        $client->setUri($this->auth->getUri2('sbp', 'register-sbp-legal-entity'));
        $client->setAdapter($this->auth::HTTPS_ADAPTER);
        $client->setMethod('POST');
        $client->setRawBody(Encoder::encode($data));
        $client->setOptions(['timeout' => 60]);
        
//        var_dump($this->auth->getUri2('payment', 'for-sign')); exit;
        
        $headers = $client->getRequest()->getHeaders();
        $headers->addHeaders([
            'Content-Type: application/json',
            'Authorization: Bearer '.$this->auth->readCode($this->auth::TOKEN_ACCESS),
        ]);

        $client->setHeaders($headers);
        
        $response = $client->send();
        
        if ($response->isOk()){
            return Decoder::decode($response->getBody(), \Laminas\Json\Json::TYPE_ARRAY);            
        }
        
        return $this->auth->exception($response);        
    }

    /**
     * получения данных юрлица в Системе быстрых платежей.
     * https://enter.tochka.com/uapi/sbp/{apiVersion}/legal-entity/{legalId}
     * 
     * @param string $legalId
     * 
     * @return array|Exception
     */
    public function getLegal($legalId)
    {
        $this->auth->isAuth();
        $client = new Client();
        $client->setUri($this->auth->getUri2('sbp', 'legal-entity/'.$legalId));
        $client->setAdapter($this->auth::HTTPS_ADAPTER);
        $client->setMethod('GET');
        $client->setOptions(['timeout' => 60]);
        
//        var_dump($this->auth->getUri2('payment', 'for-sign')); exit;
        
        $headers = $client->getRequest()->getHeaders();
        $headers->addHeaders([
            'Content-Type: application/json',
            'Authorization: Bearer '.$this->auth->readCode($this->auth::TOKEN_ACCESS),
        ]);

        $client->setHeaders($headers);
        
        $response = $client->send();
        
        if ($response->isOk()){
            return Decoder::decode($response->getBody(), \Laminas\Json\Json::TYPE_ARRAY);            
        }
        
        return $this->auth->exception($response);        
    }    
    
    /**
     * Выполняет регистрацию юрлица в СБП.
     * https://enter.tochka.com/uapi/sbp/{apiVersion}/merchant/legal-entity/{legalId}
     * 
     * @param string $sbpLegalId
     * @param Office $office
     * 
     * @return array|Exception
     */
    public function registerMerchant($sbpLegalId, $office)
    {
        $data = [
            'Data' => [
                'address' => $office->getLegalContactSmsAddress(),
                'city' => 'Москва',
                'countryCode' => 'RU',
                'countrySubDivisionCode' => '45',
                'zipCode' => '111141',
                'brandName' => 'АПЛ Сервис',
                'capabilities' => '011',
                'contactPhoneNumber' => $office->getLegalContactPhone(),
                'mcc' => '4121',
            ],
        ];
        $this->auth->isAuth();
        $client = new Client();
        $client->setUri($this->auth->getUri2('sbp', 'merchant/legal-entity/'.$sbpLegalId));
        $client->setAdapter($this->auth::HTTPS_ADAPTER);
        $client->setMethod('POST');
        $client->setRawBody(Encoder::encode($data));
        $client->setOptions(['timeout' => 60]);
        
//        var_dump($client->getUri()); exit;
        
        $headers = $client->getRequest()->getHeaders();
        $headers->addHeaders([
            'Content-Type: application/json',
            'Authorization: Bearer '.$this->auth->readCode($this->auth::TOKEN_ACCESS),
        ]);

        $client->setHeaders($headers);
        
        $response = $client->send();
        
        if ($response->isOk()){
            return Decoder::decode($response->getBody(), \Laminas\Json\Json::TYPE_ARRAY);            
        }
        
        return $this->auth->exception($response);        
    }

    /**
     * Регистрация qr кода
     * Data:
     *  "amount": 0,
        "currency": "RUB",
        "paymentPurpose": "?",
        "qrcType": "01",
        "imageParams": {},
        "sourceName": "string",
        "ttl": 0
     * @param string $account
     * @param string $merchant_id
     * @param array $data
     * 
     * @return array|Exception
     */
    public function registerQrCode($account, $merchant_id, $data)
    {
//        var_dump($request_id); exit; 
        $this->auth->isAuth();
        $client = new Client();
        $client->setUri($this->auth->getUri2('sbp', 'qr-code/merchant/'.$merchant_id.'/'.$account));
        $client->setAdapter($this->auth::HTTPS_ADAPTER);
        $client->setMethod('POST');
        $client->setRawBody(Encoder::encode($data));
        $client->setOptions(['timeout' => 60]);
        
        $headers = $client->getRequest()->getHeaders();
        $headers->addHeaders([
            'Content-Type: application/json',
            'Authorization: Bearer '.$this->auth->readCode($this->auth::TOKEN_ACCESS),
        ]);

        $client->setHeaders($headers);
        
        $response = $client->send();
        
        if ($response->isOk()){
            return Decoder::decode($response->getBody(), \Laminas\Json\Json::TYPE_ARRAY);            
        }
        
        return $this->auth->exception($response);        
    }
        
    /**
     * Метод для получения информации о QR-коде
     * @param string $qrcid
     * 
     * @return array|Exception
     */
    public function getQrCode($qrcid)
    {
        $this->auth->isAuth();
        $client = new Client();
        $client->setUri($this->auth->getUri2('sbp', 'qr-code/'.$qrcid));
        $client->setAdapter($this->auth::HTTPS_ADAPTER);
        $client->setMethod('GET');
        $client->setOptions(['timeout' => 60]);
        
        $headers = $client->getRequest()->getHeaders();
        $headers->addHeaders([
            'Content-Type: application/json',
            'Authorization: Bearer '.$this->auth->readCode($this->auth::TOKEN_ACCESS),
        ]);

        $client->setHeaders($headers);
        
        $response = $client->send();
        
        if ($response->isOk()){
            return Decoder::decode($response->getBody(), \Laminas\Json\Json::TYPE_ARRAY);            
        }
        
        return $this->auth->exception($response);                
    }
    
    /**
     * Метод для получения статусов операций по динамическим QR-кодам
     * https://enter.tochka.com/uapi/sbp/{apiVersion}/qr-codes/{qrc_ids}/payment-status
     * 
     * @param string $qrCodes
     * 
     * @return array|Exception
     */
    public function getPaymentStatuses($qrCodes)
    {
        $this->auth->isAuth();
        $client = new Client();
        $client->setUri($this->auth->getUri2('sbp', 'qr-codes/'.$qrCodes.'/payment-status'));
        $client->setAdapter($this->auth::HTTPS_ADAPTER);
        $client->setMethod('GET');
        $client->setOptions(['timeout' => 60]);
        
        $headers = $client->getRequest()->getHeaders();
        $headers->addHeaders([
            'Content-Type: application/json',
            'Authorization: Bearer '.$this->auth->readCode($this->auth::TOKEN_ACCESS),
        ]);

        $client->setHeaders($headers);
        
        $response = $client->send();
        
        if ($response->isOk()){
            return Decoder::decode($response->getBody(), \Laminas\Json\Json::TYPE_ARRAY);            
        }
        
        return $this->auth->exception($response);                        
    }
    
    /**
     * Метод для получения списка платежей в Системе быстрых платежей
     * https://enter.tochka.com/uapi/sbp/{apiVersion}/get-sbp-payments
     * 
     * @param string $customerCode
     * @param string $qrcid
     * 
     * @return array|Exception
     */
    public function getPaymentData($customerCode, $qrcid)
    {
        $this->auth->isAuth();
        $client = new Client();
        $client->setUri($this->auth->getUri2('sbp', 'qr-code/'.$qrcid.'/get-sbp-payments'));
        $client->setAdapter($this->auth::HTTPS_ADAPTER);
        $client->setMethod('GET');
        $client->setParameterGet([
            'customerCode' => $customerCode,
            'qrcId' => $qrcid,
        ]);
        $client->setOptions(['timeout' => 60]);
        
        $headers = $client->getRequest()->getHeaders();
        $headers->addHeaders([
            'Content-Type: application/json',
            'Authorization: Bearer '.$this->auth->readCode($this->auth::TOKEN_ACCESS),
        ]);

        $client->setHeaders($headers);
        
        $response = $client->send();
        
        if ($response->isOk()){
            return Decoder::decode($response->getBody(), \Laminas\Json\Json::TYPE_ARRAY);            
        }
        
        return $this->auth->exception($response);                        
    }    

    /**
     * Метод запрашивает возврат платежа через Систему быстрых платежей
     * https://enter.tochka.com/uapi/sbp/{apiVersion}/refund
     * 
     * Data:
        "bankCode": "044525104",
        "accountCode": "40817810802000000008",
        "amount": "10",
        "currency": "RUB",
        "qrcId": "AS10007GLJ1216F4905A1MTT3CP7GK3N",
        "purpose": "Оплата по счету № 1 от 01.01.2021. Без НДС",
        "refTransactionId": "48232c9a-ce82-1593-3cb6-5c85a1ffef8f"      
     * 
     * @param string $data
     * 
     * @return array|Exception
     */
    public function refund($data)
    {
        $this->auth->isAuth();
        $client = new Client();
        $client->setUri($this->auth->getUri2('sbp', 'refund'));
        $client->setAdapter($this->auth::HTTPS_ADAPTER);
        $client->setMethod('POST');
        $client->setRawBody(Encoder::encode($data));
        $client->setOptions(['timeout' => 60]);
        
        $headers = $client->getRequest()->getHeaders();
        $headers->addHeaders([
            'Content-Type: application/json',
            'Authorization: Bearer '.$this->auth->readCode($this->auth::TOKEN_ACCESS),
        ]);

        $client->setHeaders($headers);
        
        $response = $client->send();
        
        if ($response->isOk()){
            return Decoder::decode($response->getBody(), \Laminas\Json\Json::TYPE_ARRAY);            
        }
        
        return $this->auth->exception($response);                        
    }    

    /**
     * Метод для получения информация о платеже-возврате по Системе быстрых платежей
     * https://enter.tochka.com/uapi/sbp/{apiVersion}/refund/{request_id}
     * 
     * @param string $requestId
     * 
     * @return array|Exception
     */
    public function refundData($requestId)
    {
        $this->auth->isAuth();
        $client = new Client();
        $client->setUri($this->auth->getUri2('sbp', 'refund/'.$requestId));
        $client->setAdapter($this->auth::HTTPS_ADAPTER);
        $client->setMethod('GET');
        $client->setOptions(['timeout' => 60]);
        
        $headers = $client->getRequest()->getHeaders();
        $headers->addHeaders([
            'Content-Type: application/json',
            'Authorization: Bearer '.$this->auth->readCode($this->auth::TOKEN_ACCESS),
        ]);

        $client->setHeaders($headers);
        
        $response = $client->send();
        
        if ($response->isOk()){
            return Decoder::decode($response->getBody(), \Laminas\Json\Json::TYPE_ARRAY);            
        }
        
        return $this->auth->exception($response);                        
    }    
}

