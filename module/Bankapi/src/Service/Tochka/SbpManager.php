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
     * Возвращает статус платежного поручения в банке
     * 
     * @param string $request_id
     * @return array|Exception
     */
    public function paymentStatusV2($request_id)
    {
//        var_dump($request_id); exit; 
        $this->auth->isAuth();
        $client = new Client();
        $client->setUri($this->auth->getUri2('payment', 'status').'/'.$request_id);
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

