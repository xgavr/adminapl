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
        $code = $response->getStatusCode();
        switch ($code) {
            case 400: //Bad request format
            case 401: //The access token is invalid or has expired
            case 403: //Unauthorized
            case 500: //Internal Server Error
            default:
//                var_dump($response->getContent()); exit;
                $error_msg = $code.' '.$response->getContent();
//                throw new \Exception($error_msg);
                return ['message' => $error_msg];
        }
        
        throw new \Exception('Неопознаная ошибка');
    }    
    
    /**
     * токен доступа
     * 
     */    
    public function accessToken()
    {
        $aiSettings = $this->adminManager->getAiSettings();
        
        if (!empty($aiSettings['gigachat_expires_at']) && !empty($aiSettings['gigachat_access_token'])){
            var_dump(intval($aiSettings['gigachat_expires_at']), time()+60);
            if (intval($aiSettings['gigachat_expires_at']) > time()+60){
                return $aiSettings['gigachat_access_token'];
            }            
        }
        
        $postParameters = [
            'scope' => $aiSettings['gigachat_score'],
        ];
        
        $uuid4 = Uuid::uuid4();
        $uuid = $uuid4->toString();
        
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
            $result = Decoder::decode($response->getBody(), \Laminas\Json\Json::TYPE_ARRAY);
            
            $data = $aiSettings->toArray();
            $data['gigachat_access_token'] = $result['access_token'];
            $data['gigachat_expires_at'] = $result['expires_at'];
            
            $this->adminManager->setAiSettings($data);
            
            return $result['access_token'];
        }
        
        return $this->exception($response);
    }  
    
    /**
     * Возвращает массив объектов с данными доступных моделей
     * 
     */
    public function models()
    {

        $accessToken = $this->accessToken();
        
        if (empty($accessToken)){
            return [];
        }

//        var_dump($accessToken); exit;
        
        $client = new Client();
        $client->setUri('https://gigachat.devices.sberbank.ru/api/v1/models');
        $client->setAdapter($this::HTTPS_ADAPTER);
        $client->setMethod('GET');
        $client->setOptions(['timeout' => 30]);
        
        $headers = $client->getRequest()->getHeaders();
        $headers->addHeaders([
             'Authorization: Bearer '.$accessToken,
        ]);      
        
//        var_dump($headers); exit;
        
        $response = $client->send();
                
        if ($response->isOk()){
            $result = Decoder::decode($response->getBody(), \Laminas\Json\Json::TYPE_ARRAY);
            return $result;
        }
        
        return $this->exception($response);        
    }
    
    
    /**
     * Возвращает ответ модели с учетом переданных сообщений
     * 
     * @param string $messages
     * @param array $params
     * 
     * @return array
     */
    public function completions($messages = null, $params = null)
    {
        $accessToken = $this->accessToken();
        
        if (empty($accessToken)){
            return [];
        }
        
        $model = 'GigaChat:latest';
        $temperature = null;
        $xSessionIId = md5($messages[0]['content']);
        
        if (is_array($params)){
            if (!empty($params['model'])){
                $model = $params['model'];
            }
            if (!empty($params['temperature'])){
                $temperature = $params['temperature'];
            }
            if (!empty($params['xSessionIId'])){
                $xSessionIId = $params['xSessionIId'];
            }
        }
        
//        var_dump($accessToken); exit;
        $postParameters = [
            'model' => $model,
            'messages' => $messages,
        ];
        
        $client = new Client();
        $client->setUri('https://gigachat.devices.sberbank.ru/api/v1/chat/completions');
        $client->setAdapter($this::HTTPS_ADAPTER);
        $client->setMethod('POST');
        $client->setOptions(['timeout' => 30]);
        $client->setRawBody(Encoder::encode($postParameters));
        
        $headers = $client->getRequest()->getHeaders();
        $headers->addHeaders([
             'Content-Type: application/json',
             'X-Session-ID: '.$xSessionIId,
             'Authorization: Bearer '.$accessToken,
        ]);      
        
//        var_dump($headers); exit;
        
        $response = $client->send();
                
        if ($response->isOk()){
            $result = Decoder::decode($response->getBody(), \Laminas\Json\Json::TYPE_ARRAY);
            return $result;
        }
        
        return $this->exception($response);        
    }
}
