<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Bankapi\Service;

/**
 * Description of Authenticate
 *
 * @author Daddy
 */
class Authenticate {
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
}
