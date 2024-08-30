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

namespace Bankapi\Service\Sber;

use Laminas\Http\Client;
use Laminas\Json\Decoder;
use Laminas\Json\Encoder;

/**
 * Description of Authenticate
 *
 * @author Daddy
 */
class Authenticate {
    
    //const URI_PRODUCTION = 'https://fintech.sberbank.ru:9443';
    const URI_TEST = 'https://iftfintech.testsbi.sberbank.ru:9443';
    const URI_PRODUCTION = 'https://iftfintech.testsbi.sberbank.ru:9443';
        
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

    public function __construct($authParams) 
    {
        $this->client_id = $authParams['client_id'];
        $this->client_secret = $authParams['client_secret'];
        $this->uri = self::URI_PRODUCTION;

        if (file_exists('./config/development.config.php')) {
            $this->client_id = $authParams['test_client_id'];
            $this->client_secret = $authParams['test_client_secret'];
            $this->uri = self::URI_TEST;
        }
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
     * Получить Client_id
     * @return bool
     */
    public function getClientId()
    {
        return $this->client_id;
    }

    /**
     * Получить Client_id
     * @return bool
     */
    public function getClientSecret()
    {
        return $this->client_secret;
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
                //$this->reAuth();
            default:
                try {
                    $error = Decoder::decode(utf8_decode($response->getContent()), \Laminas\Json\Json::TYPE_ARRAY);
                } catch (\Laminas\Json\Exception\RuntimeException $e){
                    $error['decode_error'] = $e->getMessage();
                }
                $error_msg = $response->getStatusCode().' '.$response->getReasonPhrase();
                if (isset($error['error'])){
                    $error_msg .= ' e('.$error['error'].')';
                }
                if (isset($error['decode_error'])){
                    $error_msg .= ' de('.$error['decode_error'].' c:'.$response->getContent().')';
                }
                if (isset($error['error_description'])){
                    $error_msg .= ' ed'.$error['error_description'];
                }
                if (isset($error['message'])){
                    $error_msg .= ' '.$error['message'];
                }
                if (isset($error['Errors'])){
                    foreach ($error['Errors'] as $error){
                        $error_msg .= PHP_EOL.' '.$error['errorCode'].' '.$error['message'].' '.$error['url'];
                        
                    }
                }
//                throw new \Exception($error_msg);
                return ['message' => $error_msg];
        }
        
        throw new \Exception('Неопознаная ошибка');
    }    
}
