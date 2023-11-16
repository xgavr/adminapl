<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Ai\Service;

use Ramsey\Uuid\Uuid;
use Laminas\Http\Client;
use Laminas\Json\Decoder;
use Laminas\Json\Encoder;

/**
 * Description of GigaManager
 * 
 * @author Daddy
 */
class GigaManager {
    
    /**
     * Adapter
     */
    const HTTPS_ADAPTER = 'Laminas\Http\Client\Adapter\Curl';  
    
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Admin manager.
     * @var \Admin\Service\AdminManager
     */
    private $adminManager;
        
    public function __construct($entityManager, $adminManager)
    {
        $this->entityManager = $entityManager;
        $this->adminManager = $adminManager;
    }
    
    /**
     * Обработка ошибок
     * @param \Laminas\Http\Response $response
     */
    public function exception($response)
    {
        switch ($response->getStatusCode()) {
            case 400: //Bad request format
            case 401: //The access token is invalid or has expired
            case 403: //Unauthorized
            case 500: //Internal Server Error
            default:
                $error = Decoder::decode($response->getContent(), \Laminas\Json\Json::TYPE_ARRAY);
                $error_msg = $response->getStatusCode().' '.$response->getReasonPhrase();
                if (isset($error['code'])){
                    $error_msg .= ' ('.$error['code'].')';
                }
                if (isset($error['message'])){
                    $error_msg .= ' '.$error['message'];
                }
//                throw new \Exception($error_msg);
                return ['message' => $error_msg];
        }
        
        throw new \Exception('Неопознаная ошибка');
    }    
    
    /**
     * токен доступа
     * 
     * @param string $uuid
     */    
    public function accessToken($uuid = null)
    {
        $aiSettings = $this->adminManager->getAiSettings();
        
        $postParameters = [
            'scope' => $aiSettings['gigachat_score'],
        ];
        
        if (empty($uuid)){
            $uuid4 = Uuid::uuid4();
            $uuid = $uuid4->toString();
//            var_dump($uuid); exit;
        }
        
        $client = new Client();
        $client->setUri('https://ngw.devices.sberbank.ru:9443/api/v2/oauth');
        $client->setAdapter($this::HTTPS_ADAPTER);
        $client->setMethod('POST');
//        $client->setRawBody(Encoder::encode($postParameters));
        $client->setParameterPost($postParameters);
        $client->setOptions(['timeout' => 30]);
        
        $headers = $client->getRequest()->getHeaders();
        $headers->addHeaders([
             'Authorization: Bearer '.$aiSettings['gigachat_client_secret'],
             'RqUID: '.$uuid,
             'Content-Type: application/x-www-form-urlencoded',
        ]);
        
        $response = $client->send();
                
        if ($response->isOk()){
            $result = Decoder::decode($response->getBody());
            return $result;
        }
        
        return $this->exception($response);
    }  
    
    /**
     * Возвращает массив объектов с данными доступных моделей
     * 
     * @param string $uuid
     * @param array $accessToken
     * @param string $model
     */
    public function models($uuid = null, $accessToken = null, $model = null)
    {
        if (empty($accessToken)){
            $accessToken = $this->accessToken($uuid);
        }
        
        if (empty($accessToken['expires_at'])){
            return;
        }
        
        $expire = $accessToken['expires_at'];
        
        if ($expire <= time()){
            $accessToken = $this->accessToken($uuid);            
        }
        
        if (empty($accessToken['access_token'])){
            return;
        }

        $client = new Client();
        $client->setUri('https://gigachat.devices.sberbank.ru/api/v1/models'.($model) ? '/'.$model:'');
        $client->setAdapter($this::HTTPS_ADAPTER);
        $client->setMethod('GET');
        $client->setOptions(['timeout' => 30]);
        
        $headers = $client->getRequest()->getHeaders();
        $headers->addHeaders([
             'Authorization: Bearer '.$accessToken['access_token'],
        ]);
        
        $response = $client->send();
                
        if ($response->isOk()){
            $result = Decoder::decode($response->getBody());
            return $result;
        }
        
        return $this->exception($response);        
    }
}
