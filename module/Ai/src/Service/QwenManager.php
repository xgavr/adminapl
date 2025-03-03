<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Ai\Service;

use Ramsey\Uuid\Uuid;
use Laminas\Json\Decoder;
use Laminas\Json\Encoder;
use DeepSeek\DeepSeekClient;
use DeepSeek\Enums\Models;
use Laminas\Http\Client;

/**
 * Description of QwenManager
 * 
 * @author Daddy
 */
class QwenManager {
    
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
        
        if (!empty($aiSettings['qwen_api_key'])){
            return $aiSettings['qwen_api_key'];
        }
        
        return;
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
        
        $model = 'qwen-plus';
        $temperature = 1;
        
        if (is_array($params)){
            if (!empty($params['model'])){
                $model = $params['model'];
            }
            if (!empty($params['temperature'])){
                $temperature = $params['temperature'];
            }
        }
        
//        var_dump($accessToken); exit;
        $postParameters = [
            'model' => $model,
            'messages' => $messages,
            'frequency_penalty' => 0,
            'max_tokens' => 2048,
            'presence_penalty' => 0,
            'response_format' => ['type' => 'text'],
            'stop' => null,
            'stream' => false,
            'stream_options' => null,
            'temperature' => $temperature,
            'top_p' => 1,
            'tools' => null,
            'tool_choice' => 'none',
            'logprobs' => false,
            'top_logprobs' => null,
        ];
        
//        var_dump($postParameters); //exit;

        $client = new Client();
        $client->setUri('https://dashscope-intl.aliyuncs.com/compatible-mode/v1/chat/completions');
        $client->setAdapter($this::HTTPS_ADAPTER);
        $client->setMethod('POST');
        $client->setOptions(['timeout' => 30]);
        $client->setRawBody(Encoder::encode($postParameters));
        
        $headers = $client->getRequest()->getHeaders();
        $headers->addHeaders([
            'Content-Type: application/json',
            'Accept' => 'application/json',
            'Authorization: Bearer '.$accessToken,
        ]);      
        
//        var_dump($headers); //exit;
        
        $response = $client->send();
                
        if ($response->isOk()){
            $result = Decoder::decode($response->getBody(), \Laminas\Json\Json::TYPE_ARRAY);
            return $result;
        }
        
        return $this->exception($response);
    }    
}
