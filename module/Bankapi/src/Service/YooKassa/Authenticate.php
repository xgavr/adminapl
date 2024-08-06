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

namespace Bankapi\Service\YooKassa;

use Laminas\Http\Client;
use Laminas\Json\Decoder;
use Laminas\Json\Encoder;

/**
 * Description of Authenticate
 *
 * @author Daddy
 */
class Authenticate {
    
    const URI_PRODUCTION = 'https://api.yookassa.ru/v3/';
    
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
        

    public function __construct($authParams) 
    {
        $this->client_id = $authParams['client_id'];
        $this->client_secret = $authParams['client_secret'];
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
}
