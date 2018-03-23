<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Service;

use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\Request;
use Zend\Log\Writer\Stream;
use Zend\Log\Logger;

/**
 * Description of AutoruManager
 *
 * @author Daddy
 */
class TelegrammManager {

    const API_KEY = '460756366:AAHb7nDcYHQ1oCW7mjGSBCIPXlYDq2sY08s';
    const USERNAME = 'SlavaAplBot';
    const HOOK_URL = 'https://adminapl.ru/telegramm/hook';

    const LOG_FOLDER = './data/log/'; //папка логов
    const LOG_FILE = './data/log/telegramm.log'; //лог 
    
    /**
     * Doctrine entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;

        if (!is_dir($this::LOG_FOLDER)){
            mkdir($this::LOG_FOLDER);
        }
    }
    
    public function hook()
    {
        $writer = new Stream($this::LOG_FILE);
        $logger = new Logger();
        $logger->addWriter($writer);
        Logger::registerErrorHandler($logger);
        
        try {
            $telegramm = new Telegram($this::API_KEY, $this::USERNAME);
            
            $mysql_credentials = [
                'host'     => 'localhost',
                'user'     => 'telegramm',
                'password' => 'Ghjnt3t',
                'database' => 'telegramm',
             ];
            $telegramm->enableMySql($mysql_credentials, $this::USERNAME . '_');
            
            $telegramm->handle();
            
        } catch (Longman\TelegramBot\Exception\TelegramException $e){
            $logger->error($e->getMessage());
        }    
        
        $logger = null;
    }
    
    public function setHook()
    {
        $writer = new Stream($this::LOG_FILE);
        $logger = new Logger();
        $logger->addWriter($writer);
        Logger::registerErrorHandler($logger);
        
        try {
            $telegramm = new Telegram($this::API_KEY, $this::USERNAME);
            $result = $telegramm->setWebhook($this::HOOK_URL, ['certificate' => '/var/www/httpd-cert/apl/adminapl.ru.crt']);
            if ($result->isOk()) {
                echo $result->getDescription();
            }                    
        } catch (Longman\TelegramBot\Exception\TelegramException $e){
            $logger->error($e->getMessage());
        }    
        
        $logger = null;                
    }
    
    public function unsetHook()
    {
        $writer = new Stream($this::LOG_FILE);
        $logger = new Logger();
        $logger->addWriter($writer);
        Logger::registerErrorHandler($logger);
        
        try {
            $telegramm = new Telegram($this::API_KEY, $this::USERNAME);
            $result = $telegramm->deleteWebhook();
            if ($result->isOk()) {
                echo $result->getDescription();
            }             
        } catch (Longman\TelegramBot\Exception\TelegramException $e){
            $logger->error($e->getMessage());
        }    
        
        $logger = null;                        
    }
    
    public function sendMessage($params)
    {
        $writer = new Stream($this::LOG_FILE);
        $logger = new Logger();
        $logger->addWriter($writer);
        Logger::registerErrorHandler($logger);
        
        try {
            $result = Request::sendMessage(['chat_id' => $params['chat_id'], 'text' => $params['text']]);         
        } catch (Longman\TelegramBot\Exception\TelegramException $e){
            $logger->error($e->getMessage());
        }    
        
        $logger = null;
        
        return $result;
    }
}
