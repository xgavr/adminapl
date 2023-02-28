<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 * 
 *  * Описание Api Точки
 * https://enter.tochka.com/doc/v1/index.html
 * 
 */

namespace Bankapi\Service\Tochka;

use Laminas\Http\Client;
use Laminas\Json\Decoder;
use Laminas\Json\Encoder;

/**
 * Description of Authenticate
 *
 * @author Daddy
 */
class Authenticate {
    
    const URI_PRODUCTION = 'https://enter.tochka.com';
    const URI_PRODUCTION2 = 'https://enter.tochka.com/uapi/';
    const URI_DEBUGGING = 'https://private-anon-b91c8e0e22-tochka.apiary-proxy.com';
    const MODE_API = 'api';
    const MODE_API2 = 'uapi';
    const MODE_SANDBOX = 'sandbox';
    
    const VERSION = 'v1';
    const VERSION2 = 'v1.0';
    
    const TOKEN_AUTH = 'authorization_code';
    const TOKEN_ACCESS = 'access_token';
    const TOKEN_REFRESH = 'refresh_token';
    
    const TOKEN_FILENAME = 'bankapi_tochka.php'; //файл, где хранятся токены
    
    /**
     * Adapter
     */
    const HTTPS_ADAPTER = 'Laminas\Http\Client\Adapter\Curl';  
    
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
    private $uri2;

    /**
     * @var string
     */
    private $mode;

    /**
     * @var string
     */
    private $token_dir;

    /**
     * @var string
     */
    private $permanent_access_token;

    /**
     * @var string
     */
    private $token_filename;
    

    public function __construct($authParams) 
    {
        $this->client_id = $authParams['client_id'];
        $this->client_secret = $authParams['client_secret'];
        $this->token_dir = $authParams['token_dir'];
        $this->permanent_access_token = $authParams['access_token'];

        if ($authParams['debug']){
            $this->uri = self::URI_DEBUGGING;
        } else {
            $this->uri = self::URI_PRODUCTION;
            $this->uri2 = self::URI_PRODUCTION2;
        }

        $this->mode = $authParams['mode'];
        if ($this->mode == 'sandbox'){
            $this->uri .= '/'.self::MODE_SANDBOX;
        } else {
            $this->uri .= '/'.self::MODE_API;
        }
        
        $this->uri .= '/'.self::VERSION;
        
        if (!is_dir($this->token_dir)){
            mkdir($this->token_dir);
        }
        
        $this->token_filename = $this->token_dir.self::TOKEN_FILENAME;        
    }
    
    /**
     * Получить uri api
     * 
     * @return string 
     */
    public function getUri()
    {
        return $this->uri;
    }
    
    /**
     * Получить uri api2
     * @param string $method
     * @param string $action
     * @return string 
     */
    public function getUri2($method, $action)
    {
        return $this->uri2.$method.'/'.self::VERSION2.'/'.$action;
    }

    /**
     * Получить режим
     * 
     * @return string 
     */
    public function getMode()
    {
        return $this->mode;
    }
    
    /**
     * Хранение кодов в папке token_dir
     * @param string $code код
     * @param string $grant_type тип кода
     */
    public function saveCode($code, $grant_type)
    {
        if (file_exists($this->token_filename)){
            $config = new \Laminas\Config\Config(include $this->token_filename, true);
        } else {
            $config = new \Laminas\Config\Config([], true);
        }
        
        $config->$grant_type = $code;
        
        $writer = new \Laminas\Config\Writer\PhpArray();
        $writer->toFile($this->token_filename, $config);
        
        return;
    }
    
    /**
     * Получить код доступа
     *@param string $grant_type тип кода
     */
    public function readCode($grant_type)
    {
        if ($grant_type == self::TOKEN_ACCESS){
            if ($this->permanent_access_token){
                return $this->permanent_access_token;
            }
        }
        
        if (file_exists($this->token_filename)){
            $config = new \Laminas\Config\Config(include $this->token_filename);
            return $config->$grant_type;
        }
        
        return;
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
     * Удаление токенов
     * @return void
     */
    public function reAuth()
    {
        $this->saveCode('', self::TOKEN_AUTH);
        $this->saveCode('', self::TOKEN_ACCESS);                
        
        return;
    }
    
    /**
     * Обработка ошибок
     * @param \Laminas\Http\Response $response
     */
    public function exception($response)
    {
        switch ($response->getStatusCode()) {
            case 400: //Invalid code
            case 401: //The access token is invalid or has expired
            case 403: //The access token is missing
                $this->reAuth();
            default:
                $error = Decoder::decode($response->getContent(), \Laminas\Json\Json::TYPE_ARRAY);
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
                if (isset($error['Errors'])){
                    foreach ($error['Errors'] as $error){
                        $error_msg .= PHP_EOL.' '.$error['errorCode'].' '.$error['message'].' '.$error['url'];
                        
                    }
                }
                throw new \Exception($error_msg);
        }
        
        throw new \Exception('Неопознаная ошибка');
    }    


    /**
     * Обмен кода авторизации на access_token и refresh_token
     * @param string $code код
     * @param string $grant_type тип кода
     */    
    public function accessToken($code, $grant_type)
    {
        
        $postParameters = [
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'grant_type' => $grant_type,            
        ];
        
        if ($grant_type == self::TOKEN_AUTH) $postParameters['code'] = $code;
        if ($grant_type == self::TOKEN_REFRESH) $postParameters['refresh_token'] = $code;

        $client = new Client();
        $client->setUri($this->uri.'/oauth2/token');
        $client->setAdapter($this::HTTPS_ADAPTER);
        $client->setMethod('POST');
        $client->setRawBody(Encoder::encode($postParameters));
        $client->setOptions(['timeout' => 30]);
        
        $headers = $client->getRequest()->getHeaders();
        $headers->addHeaders([
             'Content-Type: application/json',
        ]);
        
        $response = $client->send();
                
        if ($response->isOk()){
            $result = Decoder::decode($response->getBody());
            if ($grant_type == self::TOKEN_AUTH){
                $this->saveCode($code, self::TOKEN_AUTH);
                return $this->accessToken($result->refresh_token, self::TOKEN_REFRESH);
            }    
            if ($grant_type == self::TOKEN_REFRESH){
                $this->saveCode($result->access_token, self::TOKEN_ACCESS);
                return true;
            }
        }
        
        return $this->exception($response);
    }    
}
