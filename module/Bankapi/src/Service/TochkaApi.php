<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Bankapi\Service;

use Zend\Http\Client;
use Zend\Json\Decoder;
use Zend\Json\Encoder;
use Zend\Log\Writer\Stream;
use Zend\Log\Logger;

/**
 * Description of Tochka
 *
 * @author Администратор
 */
class TochkaApi {
    
    const LOG_FOLDER = './data/log/'; //папка логов
    const LOG_FILE = './data/log/bankapi_tochka.log'; //лог
    const TOKEN_FOLDER = './data/token/'; //папка 
    const TOKEN_FILE = './data/token/bankapi_tochka.php'; //лог
    
    const URI_PRODUCTION = 'https://enter.tochka.com';
    const URI_DEBUGGING = 'https://private-anon-b91c8e0e22-tochka.apiary-proxy.com';
    const MODE_API = 'api';
    const MODE_SANDBOX = 'sandbox';
    
    const VERSION = 'v1';
    
    /*
     * Adapter
     */
    const HTTPS_ADAPTER = 'Zend\Http\Client\Adapter\Curl';  
    
    /*
     * @var string
     */
    private $client_id;
    
    /*
     * @var string
     */
    private $client_secret;
    
    /*
     * @var string
     */
    private $uri;

    /*
     * @var string
     */
    private $mode;

    public function __construct($authParams) 
    {
        $this->client_id = $authParams['client_id'];
        $this->client_secret = $authParams['client_secret'];

        if ($authParams['debug']){
            $this->uri = self::URI_DEBUGGING;
        } else {
            $this->uri = self::URI_PRODUCTION;
        }

        $this->mode = $authParams['mode'];
        if ($this->mode == 'sandbox'){
            $this->uri .= '/'.self::MODE_SANDBOX;
        } else {
            $this->uri .= '/'.self::MODE_API;
        }
        
        $this->uri .= '/'.self::VERSION;
        
        if (!is_dir($this::LOG_FOLDER)){
            mkdir($this::LOG_FOLDER);
        }
        if (!is_dir($this::TOKEN_FOLDER)){
            mkdir($this::TOKEN_FOLDER);
        }
    }
    
    /*
     * Хранение кодов
     * @var string $code
     * @var string $gran_type
     */
    public function saveCode($code, $gran_type)
    {
        if (file_exists(self::TOKEN_FILE)){
            $config = new \Zend\Config\Config(include self::TOKEN_FILE, true);
        } else {
            $config = new \Zend\Config\Config([], true);
        }
        
        $config->$gran_type = $code;
        
        $writer = new \Zend\Config\Writer\PhpArray();
        $writer->toFile(realpath(self::TOKEN_FILE), $config);
        
        return;
    }
    
    /*
     * Получить код доступа
     *@var string @gran_type
     */
    public function readCode($gran_type)
    {
        if (file_exists(self::TOKEN_FILE)){
            $config = new \Zend\Config\Config(include self::TOKEN_FILE);
            return $config->$gran_type;
        }
        
        return;
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
        $client->setUri($this->uri.'/oauth2/token');
        $client->setAdapter($this::HTTPS_ADAPTER);
        $client->setMethod('POST');
        $client->setRawBody(Encoder::encode($postParameters));

        $headers = $client->getRequest()->getHeaders();
        $headers->addHeaders([
             'Content-Type: application/json',
        ]);
        
        $response = $client->send();
                
        if ($response->isSuccess()){
            $result = Decoder::decode($response->getBody());
            if ($gran_type == 'authorization_code'){
                $this->saveCode($code, 'authorization_code');
                return $this->accessToken($result->refresh_token, 'refresh_token');
            }    
            if ($gran_type == 'refresh_token'){
                $this->saveCode($result->access_token, 'access_token');
                return true;
            }
        }
        
        return $response->getContent();
    }
    
    /*
     * Получить ссылку на вход для авторизации
     */
    public function authUrl()
    {
        return $this->uri.'/authorize?response_type=code&client_id='.$this->client_id;
    }

    /*
     * Получение доступа от клиента
     */
    public function authorize()
    {
        $client = new Client();
        $client->setUri($this->uri.'/authorize');
        $client->setAdapter($this::HTTPS_ADAPTER);
        $client->setMethod('GET');
        $client->setParameterGet([
            'response_type' => 'code',
            'client_id' => $this->client_id,
        ]);
        
//        var_dump($this->client_id);
        $response = $client->send();
        
        if ($response->isSuccess()){
            return $response->getStatusCode().': '.$response->getContent();
        }
        
        return $url;
    }
    
    /*
     * Проверить авторизацию
     *      * 
     */
    public function isAuth()
    {
        if (!$this->readCode('access_token')){
            //$this->authorize();
            throw new \Exception('Требуется авторизация в банке!');
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
        $client->setUri($this->uri.'/account/list');
        $client->setAdapter($this::HTTPS_ADAPTER);
        $client->setMethod('GET');
        
        $headers = $client->getRequest()->getHeaders();
        $headers->addHeaders([
            'Content-Type: application/json',
            'Authorization: Bearer '.$this->readCode('access_token'),
        ]);

        $client->setHeaders($headers);
        
        $response = $client->send();
        
        if ($response->isSuccess()){
            return Decoder::decode($response->getBody());            
        }
        return $response->getStatusCode();
    }
    
    /*
     * Выписка
     * @var string $request_id
     */
    public function statementResult($request_id)
    {
        $this->isAuth();
        
        $client = new Client();
        $client->setUri($this->uri.'/statement/result/'.$request_id);
        $client->setAdapter($this::HTTPS_ADAPTER);
        $client->setMethod('GET');
        
        $headers = $client->getRequest()->getHeaders();
        $headers->addHeaders([
            'Content-Type: application/json',
            'Authorization: Bearer '.$this->readCode('access_token'),
        ]);

        $client->setHeaders($headers);
        
        $response = $client->send();
        
        if ($response->isSuccess()){
            return Decoder::decode($response->getBody()); 
        }
        
        return $response->getStatusCode();
    }
    
    /*
     * Статус запроса выписки
     * @var string $request_id
     */
    public function statementStatus($request_id)
    {
        $this->isAuth();
        $client = new Client();
        $client->setUri($this->uri.'/statement/status/'.$request_id);
        $client->setAdapter($this::HTTPS_ADAPTER);
        $client->setMethod('GET');
        
        $headers = $client->getRequest()->getHeaders();
        $headers->addHeaders([
            'Content-Type: application/json',
            'Authorization: Bearer '.$this->readCode('access_token'),
        ]);

        $client->setHeaders($headers);
        
        $response = $client->send();
        
        if ($response->isSuccess()){
            $result = Decoder::decode($response->getBody()); 
            if (isset($result->status)){
                if ($result->status == 'ready'){
                    return $this->statementResult($request_id);
                }
                if ($result->status == 'queued'){
                    sleep(5);
                    return $this->statementStatus($request_id);
                }
            }
        }
        
        return $response->getStatusCode();
    }
    
    /*
     * Запрос выписки
     * $var array $params
     */
    public function statement($params)
    {
        $postParameters = [
            'account_code' => $params['account_code'],
            'bank_code' => $params['bank_code'],
            'date_end' => $params['date_end'],
            'date_start' => $params['date_start'],            
        ];
        
        $this->isAuth();
        $client = new Client();
        $client->setUri($this->uri.'/statement');
        $client->setAdapter($this::HTTPS_ADAPTER);
        $client->setMethod('POST');
        $client->setRawBody(Encoder::encode($postParameters));
        
        $headers = $client->getRequest()->getHeaders();
        $headers->addHeaders([
            'Content-Type: application/json',
            'Authorization: Bearer '.$this->readCode('access_token'),
        ]);

        $client->setHeaders($headers);
        
        $response = $client->send();
        
        if ($response->isSuccess()){
            $result = Decoder::decode($response->getBody()); 
            if (isset($result->request_id)){
                return $this->statementStatus($result->request_id);
            }
        }
        
        return $response->getStatusCode();
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
                    'bank_code' => $account->bank_code,
                    'account_code' => $account->account_code,
                ]);
            }
            
            return $result;
        }
        
        return;
    }
}
