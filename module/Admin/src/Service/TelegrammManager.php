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
use GuzzleHttp\Client;

/**
 * Description of AutoruManager
 *
 * @author Daddy
 */
class TelegrammManager {

    const COMMANDS_PATH = './vendor/longman/src/Commands/';

    const LOG_FOLDER = './data/log/'; //папка логов
    const LOG_FILE = './data/log/telegramm.log'; //лог 
    
    /**
     * Doctrine entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    private $adminManager;   
    
    public function __construct($entityManager, $adminManager)
    {
        $this->entityManager = $entityManager;
        $this->adminManager = $adminManager;

        if (!is_dir($this::LOG_FOLDER)){
            mkdir($this::LOG_FOLDER);
        }
    }
    
    public function hook()
    {
        
        $settings = $this->adminManager->getSettings();
        if ($settings['telegram_api_key'] && $settings['telegram_bot_name'] && $settings['telegram_admin_chat_id']){
        
            $writer = new Stream($this::LOG_FILE);
            $logger = new Logger();
            $logger->addWriter($writer);
            Logger::registerErrorHandler($logger);

            try {
                $telegramm = new Telegram($settings['telegram_api_key'], $settings['telegram_bot_name']);
                $telegram->addCommandsPaths($this::COMMANDS_PATH);
                $telegram->enableAdmins([$settings['telegram_admin_chat_id']]);

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
        
        return;
    }
    
    public function setHook()
    {
        $settings = $this->adminManager->getSettings();
        if ($settings['telegram_api_key'] && $settings['telegram_bot_name'] && $settings['telegram_hook_url']){
        
            $writer = new Stream($this::LOG_FILE);
            $logger = new Logger();
            $logger->addWriter($writer);
            Logger::registerErrorHandler($logger);

            try {
                $telegramm = new Telegram($settings['telegram_api_key'], $settings['telegram_bot_name']);
                $result = $telegramm->setWebhook($settings['telegram_hook_url'], ['certificate' => '/var/www/apl/data/www/adminapl/adminapl.key']);
                if ($result->isOk()) {
                    echo $result->getDescription();
                }                    
            } catch (Longman\TelegramBot\Exception\TelegramException $e){
                $logger->error($e->getMessage());
            }    

            $logger = null;                
        }    
    }
    
    public function unsetHook()
    {
        $settings = $this->adminManager->getSettings();
        if ($settings['telegram_api_key'] && $settings['telegram_bot_name']){
            
            $writer = new Stream($this::LOG_FILE);
            $logger = new Logger();
            $logger->addWriter($writer);
            Logger::registerErrorHandler($logger);

            try {
                $telegramm = new Telegram($settings['telegram_api_key'], $settings['telegram_bot_name']);
                $result = $telegramm->deleteWebhook();
                if ($result->isOk()) {
                    echo $result->getDescription();
                }             
            } catch (Longman\TelegramBot\Exception\TelegramException $e){
                $logger->error($e->getMessage());
            }    

            $logger = null;
        }    
        
        return;
    }
    
    public function sendMessage($params)
    {
        $settings = $this->adminManager->getSettings();
        if ($settings['telegram_api_key'] && $settings['telegram_bot_name']){

            $writer = new Stream($this::LOG_FILE);
            $logger = new Logger();
            $logger->addWriter($writer);
            Logger::registerErrorHandler($logger);
            //\Longman\TelegramBot\TelegramLog::initDebugLog($this::LOG_FILE);

            try {
                $telegramm = new Telegram($settings['telegram_api_key'], $settings['telegram_bot_name']);
                
                $proxy = $settings['telegram_proxy'];
                
                if ($proxy){
                    Request::setClient(new Client([
                        'proxy' => $proxy,
                        'base_uri' => 'https://api.telegram.org', 
                        'timeout' => 10.0,
                        'cookie' => true,
    //                    'proxy' => 'http://localhost:8118',
//                        'request.options' => [
//                            'proxy' => $proxy,
//                        ],
                    ]));
                }    
                
                $result = Request::sendMessage(['chat_id' => $params['chat_id'], 'text' => $params['text']]);         
            } catch (Longman\TelegramBot\Exception\TelegramException $e){
                $logger->error($e->getMessage());
            }    

            $logger = null;

            return $result;
        }    
    }
}