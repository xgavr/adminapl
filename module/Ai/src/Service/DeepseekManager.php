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
use DeepSeek\DeepSeekClient;
use DeepSeek\Enums\Models;

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
     * Возвращает ответ модели с учетом переданных сообщений
     * 
     * @param string $message
     * 
     * 
     * @return array
     */
    public function completions($message = null)
    {
        $aiSettings = $this->adminManager->getAiSettings();
        
        $response = DeepSeekClient::build($aiSettings['deepseek_api_key'])
            ->query($message)
            ->run();

        return $response;        
    }
}
