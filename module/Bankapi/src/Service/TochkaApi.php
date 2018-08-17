<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Bankapi\Service;

use Zend\Http\Client;
use Zend\Json\Decoder;

/**
 * Description of Tochka
 *
 * @author Администратор
 */
class TochkaApi {
    
    /*
     * Adapter
     */
    const HTTPS_ADAPTER = 'Zend\Http\Client\Adapter\Curl';  
    
    /*
     * Менеджер сессий
     * @var Zend\Seesion
     */
    private $sessionContainer;
    
    /*
     * @var string
     */
    private $client_id;
    
    /*
     * @var string
     */
    private $client_secret;
    
    public function __construct($sessionContainer, $authParams) 
    {
        $this->sessionContainer = $sessionContainer;
        $this->client_id = $authParams['client_id'];
        $this->client_secret = $authParams['client_secret'];
    }
    
    /*
     * Обмен кода авторизации на access_token и refresh_token
     * @var string $code
     * @var string $gran_type
     */    
    public function accessToken($code, $gran_type)
    {
        $postParameters = [
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'grant_type' => $gran_type,            
        ];
        
        if ($gran_type == 'authorization_code') $postParameters['code'] = $code;
        if ($gran_type == 'refresh_token') $postParameters['refresh_token'] = $code;

        $client = new Client();
        $client->setUri('https://enter.tochka.com/api/v1/oauth2/token');
        $client->setAdapter($this::HTTPS_ADAPTER);
        $client->setMethod('POST');
        $client->setParameterPost($postParameters);

        $headers = $client->getRequest()->getHeaders();
        $headers->addHeaders([
            'Content-Type: application/json',
        ]);
        
        $response = $client->send();
        
        if ($response->isSuccess()){
            $result = Decoder::decode($response->getBody());
            if ($gran_type == 'authorization_code'){
                return $this->accessToken($result['refresh_token'], 'refresh_token');
            }    
            if ($gran_type == 'refresh_token'){
                $this->sessionContainer->tochka_access_token = $result['access_token'];
                return true;
            }
        }
        
        return;
    }
    
    /*
     * Получение доступа от клиента
     */
    public function authorize()
    {
        $client = new Client();
        $client->setUri('https://enter.tochka.com/api/v1/authorize');
        $client->setAdapter($this::HTTPS_ADAPTER);
        $client->setMethod('GET');
        $client->setParameterGet([
            'response_type' => 'code',
            'client_id' => $this->client_id,
        ]);
        
        $response = $client->send();
        
        if ($response->isSuccess()){
            return $this->accessToken($response->getBody(), 'authorization_code');
        }
        
        return;
    }
    
    /*
     * Проверить авторизацию
     *      * 
     */
    public function isAuth()
    {
        if (!$this->sessionContainer->tochka_access_token){
            if (!$this->authorize()){
                throw new \Exception('Не удалось авторизироваться в Api Tochka');
            }
        }        

        return true;
    }
    
    /*
     * Список счетов
     */
    public function accountList()
    {
        $this->isAuth();
        
        $client = new Client();
        $client->setUri('https://enter.tochka.com/api/v1/account/list');
        $client->setAdapter($this::HTTPS_ADAPTER);
        $client->setMethod('GET');
        
        $headers = $client->getRequest()->getHeaders();
        $headers->addHeaders([
            'Content-Type: application/json',
            'Authorization: Bearer '.$this->sessionContainer->tochka_access_token,
        ]);

        $client->setHeaders($headers);
        
        $response = $client->send();
        
        if ($response->isSuccess()){
            return Decoder::decode($response->getBody());            
        }
        return;
    }
    
    /*
     * Выписка
     * @var string $request_id
     */
    public function statementResult($request_id)
    {
        $this->isAuth();
        
        $client = new Client();
        $client->setUri("https://enter.tochka.com/api/v1/statement/result/$request_id");
        $client->setAdapter($this::HTTPS_ADAPTER);
        $client->setMethod('GET');
        
        $headers = $client->getRequest()->getHeaders();
        $headers->addHeaders([
            'Content-Type: application/json',
            'Authorization: Bearer '.$this->sessionContainer->tochka_access_token,
        ]);

        $client->setHeaders($headers);
        
        $response = $client->send();
        
        if ($response->isSuccess()){
            return Decoder::decode($response->getBody()); 
        }
        return;
    }
    
    /*
     * Статус запроса выписки
     * @var string $request_id
     */
    public function statementStatus($request_id)
    {
        $this->isAuth();
        $client = new Client();
        $client->setUri("https://enter.tochka.com/api/v1/statement/status/$request_id");
        $client->setAdapter($this::HTTPS_ADAPTER);
        $client->setMethod('GET');
        
        $headers = $client->getRequest()->getHeaders();
        $headers->addHeaders([
            'Content-Type: application/json',
            'Authorization: Bearer '.$this->sessionContainer->tochka_access_token,
        ]);

        $client->setHeaders($headers);
        
        $response = $client->send();
        
        if ($response->isSuccess()){
            $result = Decoder::decode($response->getBody()); 
            if (isset($result['status'])){
                if ($result['status'] == 'ready'){
                    return $this->statementResult($request_id);
                }
                if ($result['status'] == 'queued'){
                    sleep(10);
                    return $this->statementStatus($request_id);
                }
            }
        }
        return;
    }
    
    /*
     * Запрос выписки
     * $var array $params
     */
    public function statement($params)
    {
        $this->isAuth();
        $client = new Client();
        $client->setUri('https://enter.tochka.com/api/v1/statement');
        $client->setAdapter($this::HTTPS_ADAPTER);
        $client->setMethod('POST');
        $client->setParameterPost([
            'account_code' => $params['account_code'],
            'bank_code' => $params['bank_code'],
            'date_end' => $params['date_end'],
            'date_start' => $params['date_start'],
        ]);
        
        $headers = $client->getRequest()->getHeaders();
        $headers->addHeaders([
            'Content-Type: application/json',
            'Authorization: Bearer '.$this->sessionContainer->tochka_access_token,
        ]);

        $client->setHeaders($headers);
        
        $response = $client->send();
        
        if ($response->isSuccess()){
            $result = Decoder::decode($response->getBody());            
            if (isset($result['request_id'])){
                return $this->statementStatus($result['request_id']);
            }
        }
        return;        
    }
    
    /*
     * Получить выписки по всем счетам за период
     * @var date $date_start
     * @var date $date_end
     * @return array
     */
    public function statements($date_start = null, $date_end = null)
    {
        if (!$date_start) $date_start = date('Y-m-d');
        if (!$date_end) $date_end = date('Y-m-d');
        
        $result = [];
        
        $accounts = $this->accountList();
        if (is_array($accounts)){
            foreach ($accounts as $account){
                $result[] = $this->statement([
                    'date_start' => $date_start,
                    'date_end' => $date_end,
                    'bank_code' => $account['bank_code'],
                    'account_code' => $account['code'],
                ]);
            }
            
            return $result;
        }
        return;
    }
}
