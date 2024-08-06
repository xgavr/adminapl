<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Bankapi\Service\Sber;

use Laminas\Http\Client;
use Laminas\Json\Decoder;
use Laminas\Json\Encoder;
use OAuth2\Encryption\Jwt;

/**
 * Работа с вебхуками
 *
 * @author Daddy
 */
class Webhook {

    /**
     * @var \Bankapi\Service\Sber\Authenticate
     */
    private $auth;
    
    public function __construct($auth) 
    {
        $this->auth = $auth;
    }
    
    /**
     * Метод для получения списка вебхуков приложения
     * https://enter.tochka.com/uapi/webhook/{apiVersion}/{client_id}
     * 
     * @return array
     */
    public function getWebhooks()
    {
        $this->auth->isAuth();
        $clientId = $this->auth->getClientId();
        
        $client = new Client();
        $client->setUri($this->auth->getUri2('webhook', $clientId));
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
     * Метод для создания вебхуков
     * https://enter.tochka.com/uapi/webhook/{apiVersion}/{client_id}
     * 
     * @param string $url
     * 
     * @return array
     */
    public function createWebhook($url)
    {
        $this->auth->isAuth();
        $clientId = $this->auth->getClientId();
        
        $data = [
            'webhooksList' => [
                'incomingPayment',
                'incomingSbpPayment',
            ], 
            'url' => $url,
        ];
        
        $client = new Client();
        $client->setUri($this->auth->getUri2('webhook', $clientId));
        $client->setAdapter($this->auth::HTTPS_ADAPTER);
        $client->setMethod('PUT');
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
     * Метод для изменения URL и типа вебхука
     * https://enter.tochka.com/uapi/webhook/{apiVersion}/{client_id}
     * 
     * @param string $url
     * @return array
     */
    public function editWebhook($url)
    {
        $this->auth->isAuth();
        $clientId = $this->auth->getClientId();
        
        $data = [
            'webhooksList' => [
                'incomingPayment',
                'incomingSbpPayment',
            ], 
            'url' => $url,
        ];
        
        $client = new Client();
        $client->setUri($this->auth->getUri2('webhook', $clientId));
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
     * Метод для удаления вебхука
     * https://enter.tochka.com/uapi/webhook/{apiVersion}/{client_id}
     * 
     * @return array
     */
    public function deleteWebhook()
    {
        $this->auth->isAuth();
        $clientId = $this->auth->getClientId();
        
        $client = new Client();
        $client->setUri($this->auth->getUri2('webhook', $clientId));
        $client->setAdapter($this->auth::HTTPS_ADAPTER);
        $client->setMethod('DELETE');
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
     * Метод для проверки отправки вебхука
     * https://enter.tochka.com/uapi/webhook/{apiVersion}/{client_id}/test_send
     * 
     * @param string $webhookType incomingSbpPayment,incomingSbpPayment
     * 
     * @return array
     */
    public function testWebhook($webhookType = 'incomingSbpPayment')
    {
        $this->auth->isAuth();
        $clientId = $this->auth->getClientId();

        $data = [
            'webhookType' => $webhookType,             
        ];        
        
        $client = new Client();
        $client->setUri($this->auth->getUri2('webhook', $clientId.'/test_send'));
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
     * Чтение вебхука
     * @param string $jwtToken
     */
    public function readWebhook($jwtToken)
    {
        $jwt = new Jwt();
        $key = $this->auth->getJwtPublicKey();
        $result = $jwt->decode($jwtToken, $key);
        
        return $result;
    }
}

