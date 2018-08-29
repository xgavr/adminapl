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
    
    const TOKEN_AUTH = 'authorization_code';
    const TOKEN_ACCESS = 'access_token';
    const TOKEN_REFRESH = 'refresh_token';
    
    /**
     * Adapter
     */
    const HTTPS_ADAPTER = 'Zend\Http\Client\Adapter\Curl';  
    
    /**
     * @var string
     */
    private $client_id;
    
    /**
     * @var string
     */
    private $client_secret;
    
    /**
     * @var string
     */
    private $uri;

    /**
     * @var string
     */
    public $mode;

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
    
    /**
     * Хранение кодов
     * @param string $code код
     * @param string $gran_type тип кода
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
        $writer->toFile(self::TOKEN_FILE, $config);
        
        return;
    }
    
    /**
     * Получить код доступа
     *@param string $gran_type тип кода
     */
    public function readCode($gran_type)
    {
        if (file_exists(self::TOKEN_FILE)){
            $config = new \Zend\Config\Config(include self::TOKEN_FILE);
            return $config->$gran_type;
        }
        
        return;
    }


    /**
     * Обработка ошибок
     * @param \Zend\Http\Response $response
     */
    public function exception($response)
    {
        switch ($response->getStatusCode()) {
            case 400: //Invalid code
            case 401: //The access token is invalid or has expired
            case 403: //The access token is missing
                $this->saveCode('', self::TOKEN_AUTH);
                $this->saveCode('', self::TOKEN_ACCESS);                
            default:
                $error = Decoder::decode($response->getContent(), \Zend\Json\Json::TYPE_ARRAY);
                $error_msg = $response->getStatusCode();
                if (isset($error['error'])){
                    $error_msg .= ' ('.$error['error'].')';
                }
                if (isset($error['error_description'])){
                    $error_msg .= ' '.$error['error_description'];
                }
                if (isset($error['message'])){
                    $error_msg .= ' '.$error['message'];
                }
                throw new \Exception($error_msg);
        }
        
        throw new \Exception('Неопознаная ошибка');
    }

    /**
     * Обмен кода авторизации на access_token и refresh_token
     * @param string $code код
     * @param string $gran_type тип кода
     */    
    public function accessToken($code, $gran_type)
    {
        
        $postParameters = [
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'grant_type' => $gran_type,            
        ];
        
        if ($gran_type == self::TOKEN_AUTH) $postParameters['code'] = $code;
        if ($gran_type == self::TOKEN_REFRESH) $postParameters['refresh_token'] = $code;

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
            if ($gran_type == self::TOKEN_AUTH){
                $this->saveCode($code, self::TOKEN_AUTH);
                return $this->accessToken($result->refresh_token, self::TOKEN_REFRESH);
            }    
            if ($gran_type == self::TOKEN_REFRESH){
                $this->saveCode($result->access_token, self::TOKEN_ACCESS);
                return true;
            }
        }
        
        return $this->exception($response);
    }
    
    /**
     * Получить ссылку на вход для авторизации
     * @return string 
     */
    public function authUrl()
    {
        return $this->uri.'/authorize?response_type=code&client_id='.$this->client_id;
    }
    
    /**
     * Проверить авторизацию
     * @return bool
     */
    public function isAuth()
    {
        if (!$this->readCode(self::TOKEN_ACCESS)){
            throw new \Exception('Требуется авторизация в банке!');
        }        

        return true;
    }
    
    /**
     * Получить список счетов
     * @return array|\Exception
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
            'Authorization: Bearer '.$this->readCode(self::TOKEN_ACCESS),
        ]);

        $client->setHeaders($headers);
        
        $response = $client->send();
        
        if ($response->isSuccess()){
            return Decoder::decode($response->getBody(), \Zend\Json\Json::TYPE_ARRAY);            
        }

        return $this->exception($response);
    }
    
    /**
     * Получить выписку за период
     * @param string $request_id
     * @return array|\Exception
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
            'Authorization: Bearer '.$this->readCode(self::TOKEN_ACCESS),
        ]);

        $client->setHeaders($headers);
        
        $response = $client->send();
        
        if ($response->isSuccess()){
            return Decoder::decode($response->getBody(), \Zend\Json\Json::TYPE_ARRAY); 
        }
        
        return $this->exception($response);
    }
    
    /**
     * Статус запроса выписки
     * @param string $request_id
     * @return array|\Exception
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
            'Authorization: Bearer '.$this->readCode(self::TOKEN_ACCESS),
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
        
        return $this->exception($response);
    }
    
    /**
     * Запрос выписки
     * @param array $params
     * 
     */
    public function statementRequest($params)
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
            'Authorization: Bearer '.$this->readCode(self::TOKEN_ACCESS),
        ]);

        $client->setHeaders($headers);
        
        $response = $client->send();
        
        if ($response->isSuccess()){
            $result = Decoder::decode($response->getBody()); 
            if (isset($result->request_id)){
                return $this->statementStatus($result->request_id);
            }
        }
        
        return $this->exception($response);
    }
    
    /**
     * Получить выписку по счету за период
     * 
     * @param string $bank_code БИК
     * @param string $account_code Номер счета
     * @param date $date_start
     * @param date $date_end
     * 
     * @return array|null
     */
    public function statement($bank_code, $account_code, $date_start = null, $date_end = null)
    {
//        if (!$date_start) $date_start = date('Y-m-d');
        if (!$date_start) $date_start = date('Y-m-d', strtotime("-1 days"));
        if (!$date_end) $date_end = date('Y-m-d');

        return $this->statementRequest([
            'date_start' => $date_start,
            'date_end' => $date_end,
            'bank_code' => $bank_code,
            'account_code' => $account_code,
        ]);
    }
    
    /**
     * Получить выписки по всем счетам за период
     * @param date $date_start
     * @param date $date_end
     * @return array|null
     */
    public function statements($date_start = null, $date_end = null)
    {
//        if (!$date_start) $date_start = date('Y-m-d');
        if (!$date_start) $date_start = date('Y-m-d', strtotime("-1 days"));
        if (!$date_end) $date_end = date('Y-m-d');
        
        $result['date_start'] = $date_start;
        $result['date_end'] = $date_end;
        $result['statements'] = [];
        
        $accounts = $this->accountList();
        if (is_array($accounts)){
            foreach ($accounts as $account){
                if ($this->mode == 'sandbox'){ //в песочнице номер счета: account_code, в api - code
                    $result['statements'][$account['bank_code']][$account['account_code']] = $this->statement(
                        $account['bank_code'],
                        $account['account_code'],
                        $date_start,
                        $date_end
                    );
                } else {
                    $result['statements'][$account['bank_code']][$account['code']] = $this->statement(
                        $account['bank_code'],
                        $account['code'],
                        $date_start,
                        $date_end
                    );                    
                }    
            }
            
            return $result;
        }
        
        return;
    }
}
