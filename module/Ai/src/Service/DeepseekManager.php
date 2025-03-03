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
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

/**
 * Description of DeepseekManager
 * 
 * @author Daddy
 */
class DeepseekManager {
    
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
        
        if (!empty($aiSettings['deepseek_api_key'])){
            return $aiSettings['deepseek_api_key'];
        }
        
        return;
    }  
    
    /**
     * Возвращает ответ модели с учетом переданных сообщений
     * 
     * @param string $message
     * 
     * 
     * @return array
     */
    public function completionsDsc($message = null)
    {
        $aiSettings = $this->adminManager->getAiSettings();
        
        $response = DeepSeekClient::build($aiSettings['deepseek_api_key'])
            ->query($message)
            ->run();

        return $response;        
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
        
        $model = 'deepseek-chat';
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
        $client = new Client();
        
        $headers = [
            'Content-Type: application/json',
            'Accept' => 'application/json',
            'Authorization: Bearer '.$accessToken,
        ];      
        
        $body = [
            'messages' => $messages,
            'model' => $model,
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

        $request = new Request('POST', 'https://api.deepseek.com/chat/completions', $headers, Encoder::encode($body));
        
        $response = $client->sendAsync($request)->wait();
                
        if ($response->isOk()){
            $result = Decoder::decode($response->getBody(), \Laminas\Json\Json::TYPE_ARRAY);
            return $result;
        }
        
        return $this->exception($response);        
    }    
}
