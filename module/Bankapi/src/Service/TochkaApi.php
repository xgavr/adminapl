<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Bankapi\Service;

use Zend\Http\Client;
/**
 * Description of Tochka
 *
 * @author Администратор
 */
class TochkaApi {
    
    /**
     * Session manager.
     * @var Zend\Session\SessionManager
     */
    private $sessionManager;
    
    /*
     * @var string
     */
    private $client_id;
    
    public function __construct($sessionManager, $authParams) 
    {
        $this->sessionManager = $sessionManager;
        $this->client_id = $authParams['client_id'];
    }
    
    public function authorize()
    {
        $client = new Client();
        $client->setUri('https://enter.tochka.com/api/v1/authorize');
        $client->setAdapter('Zend\Http\Client\Adapter\Curl');
        $client->setParameterGet([
            'response_type' => 'code',
            'client_id' => $this->client_id,
        ]);
        
        $response = $client->send();
        
        if ($response->isSuccess()){
            $this->sessionManager->
            $response->getBody();
        }
        
        return;
    }
}
