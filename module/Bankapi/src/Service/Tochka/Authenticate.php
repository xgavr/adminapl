<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Bankapi\Service\Tochka;

/**
 * Description of Authenticate
 *
 * @author Daddy
 */
class Authenticate {
    
    const URI_PRODUCTION = 'https://enter.tochka.com';
    const URI_DEBUGGING = 'https://private-anon-b91c8e0e22-tochka.apiary-proxy.com';
    const MODE_API = 'api';
    const MODE_SANDBOX = 'sandbox';
    
    const VERSION = 'v1';
    
    const TOKEN_AUTH = 'authorization_code';
    const TOKEN_ACCESS = 'access_token';
    const TOKEN_REFRESH = 'refresh_token';
    
    const TOKEN_FILENAME = 'bankapi_tochka.php'; //файл, где хранятся токены
    
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

    /**
     * @var string
     */
    public $token_dir;

    /**
     * @var string
     */
    public $token_filename;

    public function __construct($authParams) 
    {
        $this->client_id = $authParams['client_id'];
        $this->client_secret = $authParams['client_secret'];
        $this->token_dir = $authParams['token_dir'];

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
     * Хранение кодов в папке token_dir
     * @param string $code код
     * @param string $gran_type тип кода
     */
    public function saveCode($code, $gran_type)
    {
        if (file_exists($this->token_filename)){
            $config = new \Zend\Config\Config(include $this->token_filename, true);
        } else {
            $config = new \Zend\Config\Config([], true);
        }
        
        $config->$gran_type = $code;
        
        $writer = new \Zend\Config\Writer\PhpArray();
        $writer->toFile($this->token_filename, $config);
        
        return;
    }
    
    /**
     * Получить код доступа
     *@param string $gran_type тип кода
     */
    public function readCode($gran_type)
    {
        if (file_exists($this->token_filename)){
            $config = new \Zend\Config\Config(include $this->token_filename);
            return $config->$gran_type;
        }
        
        return;
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
}
