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
    const ADMIN_USERS = [189788583];
    const COMMANDS_PATH = './vendor/longman/src/Commnds/';

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
            $telegram->addCommandsPaths($this::COMMANDS_PATH);
            $telegram->enableAdmins($this::ADMIN_USERS);
            
            $mysql_credentials = [
                'host'     => 'localhost',
                'user'     => 'telegramm',
                'password' => 'Ghjnt3t',
                'database' => 'telegramm',
             ];
            $telegramm->enableMySql($mysql_credentials, $this::USERNAME . '_');
            
//            Logging (Error, Debug and Raw Updates)
            Longman\TelegramBot\TelegramLog::initErrorLog($this::LOG_FOLDER . "/".$this::USERNAME."_error.log");
            Longman\TelegramBot\TelegramLog::initDebugLog($this::LOG_FOLDER . "/".$this::USERNAME."_debug.log");
            Longman\TelegramBot\TelegramLog::initUpdateLog($this::LOG_FOLDER . "/".$this::USERNAME."_update.log");
            
            $telegram->enableLimiter();
            
            $telegramm->handle();
            
        } catch (Longman\TelegramBot\Exception\TelegramException $e){
            Longman\TelegramBot\TelegramLog::error($e);
        } catch (Longman\TelegramBot\Exception\TelegramLogException $e){
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
            $result = $telegramm->setWebhook($this::HOOK_URL, ['certificate' => '/var/www/apl/data/www/adminapl/adminapl.key']);
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
            $telegramm = new Telegram($this::API_KEY, $this::USERNAME);
            $result = Request::sendMessage(['chat_id' => $params['chat_id'], 'text' => $params['text']]);         
        } catch (Longman\TelegramBot\Exception\TelegramException $e){
            $logger->error($e->getMessage());
        }    
        
        $logger = null;
        
        return $result;
    }
}
