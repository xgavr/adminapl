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
        
        $settings = $this->adminManager->getTelegramSettings();
        if ($settings['telegram_api_key'] && $settings['telegram_bot_name'] && $settings['telegram_admin_chat_id']){
        
            $writer = new Stream($this::LOG_FILE);
            $logger = new Logger();
            $logger->addWriter($writer);
            Logger::registerErrorHandler($logger);

            try {
                $telegram = new Telegram($settings['telegram_api_key'], $settings['telegram_bot_name']);
                $telegram->addCommandsPaths($this::COMMANDS_PATH);
                $telegram->enableAdmins([$settings['telegram_admin_chat_id']]);

                $mysql_credentials = [
                    'host'     => 'localhost',
                    'user'     => 'telegramm',
                    'password' => 'Ghjnt3t',
                    'database' => 'telegramm',
                 ];
                $telegram->enableMySql($mysql_credentials, $this::USERNAME . '_');

    //            Logging (Error, Debug and Raw Updates)
                Longman\TelegramBot\TelegramLog::initErrorLog($this::LOG_FOLDER . "/".$this::USERNAME."_error.log");
                Longman\TelegramBot\TelegramLog::initDebugLog($this::LOG_FOLDER . "/".$this::USERNAME."_debug.log");
                Longman\TelegramBot\TelegramLog::initUpdateLog($this::LOG_FOLDER . "/".$this::USERNAME."_update.log");

                $telegram->enableLimiter();

                $telegram->handle();

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
        $settings = $this->adminManager->getTelegramSettings();
        if ($settings['telegram_api_key'] && $settings['telegram_bot_name'] && $settings['telegram_hook_url']){
        
            $writer = new Stream($this::LOG_FILE);
            $logger = new Logger();
            $logger->addWriter($writer);
            Logger::registerErrorHandler($logger);

            try {
                $telegram = new Telegram($settings['telegram_api_key'], $settings['telegram_bot_name']);
                $result = $telegram->setWebhook($settings['telegram_hook_url'], ['certificate' => '/var/www/apl/data/www/adminapl/adminapl.key']);
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
        $settings = $this->adminManager->getTelegramSettings();
        if ($settings['telegram_api_key'] && $settings['telegram_bot_name']){
            
            $writer = new Stream($this::LOG_FILE);
            $logger = new Logger();
            $logger->addWriter($writer);
            Logger::registerErrorHandler($logger);

            try {
                $telegram = new Telegram($settings['telegram_api_key'], $settings['telegram_bot_name']);
                $result = $telegram->deleteWebhook();
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
        $settings = $this->adminManager->getTelegramSettings();
        if ($settings['telegram_api_key'] && $settings['telegram_bot_name']){

            $writer = new Stream($this::LOG_FILE);
            $logger = new Logger();
            $logger->addWriter($writer);
            Logger::registerErrorHandler($logger);           
            \Longman\TelegramBot\TelegramLog::initDebugLog($this::LOG_FILE);

            try {
                $telegram = new Telegram($settings['telegram_api_key'], $settings['telegram_bot_name']);
                
                $proxy = $settings['telegram_proxy'];
                
                if ($proxy){
                    Request::setClient(new Client([
                        'proxy' => $proxy,
                        'base_uri' => 'https://api.telegram.org', 
                        'timeout' => 10.0,
                        'cookie' => true,
                    ]));
                }    
                
                if (isset($params['chat_id'])){
                    $chatId = $params['chat_id'];
                } else {
                    $chatId = $settings['telegram_admin_chat_id'];
                }    
                
                $result = Request::sendMessage(['chat_id' => $chatId, 'text' => $params['text']]);         
            } catch (Longman\TelegramBot\Exception\TelegramException $e){
                $logger->error($e->getMessage());
            } catch (Zend\Http\Client\Adapter\Exception $e){
                $logger->error($e->getMessage());
            }    

            $logger = null;

            return $result;
        }    
    }
    
    /**
     * Поверить доступность прокси
     * 
     * @param string $proxy
     * @return bool
     */
    public function checkProxy($proxy)
    {        
        $uri = 'https://api.telegram.org';
        try{
            $client = new Client();
            $response = $client->request('GET', $uri, ['proxy' => $proxy, 'timeout' => 5.0]);
            if ($response->getStatusCode() == 200){
                return true;
            } else {
//                var_dump($response->getStatusCode());
//                exit;
                return false;
            }
        } catch (\GuzzleHttp\Exception\ConnectException $e){
            return false;
        } catch (\GuzzleHttp\Exception\RequestException $e){
            return false;
        }    
        
        return false;
    }
    
    /**
     * Проверить текущий прокси
     * @return bool
     */
    public function checkCurrentProxy()
    {
        $settings = $this->adminManager->getTelegramSettings();
        return $this->checkProxy($settings['telegram_proxy']);
    }

    /**
     * Получить список прокси
     * @return array
     */
    public function proxyList()
    {
        $countryList = [
            'US',
            'FI', 
            'PL', 
            'UA', 
            'DE', 
            'IT', 
            'FR', 
            'GB', 
            'RO', 
            'SE', 
            'NO', 
            'LV', 
            'LT', 
        ];
        //https://www.nationsonline.org/oneworld/country_code_list.htm
        
//        $uri = 'https://www.proxy-list.download/api/v1/get?type=socks5&country=';
        
//        $result = [];
//        foreach ($countryList as $country){
//            $list = file_get_contents($uri.$country);
//            if ($list){
//                $result = array_merge($result, explode(PHP_EOL, $list));
//            }    
//        }    

        $uri = 'https://www.proxy-list.download/api/v1/get?type=socks5';
        $list = file_get_contents($uri);
        
        return array_filter(explode(PHP_EOL, $list));
    }
    
    /**
     * Получить прокси
     * 
     * @return string
     */
    public function getProxy()
    {
        set_time_limit(900);

        $proxyList = $this->proxyList();
        shuffle($proxyList);
//        var_dump($proxyList); exit;
        foreach ($proxyList as $proxy){
            if ($this->checkProxy('socks5://'.$proxy)){
                return $proxy;
            }
        }
        
        return;
    }
    
    /**
     * Проверить и заменить текущий прокси
     * @return null
     */
    public function checkEndChangeProxy()
    {
        if (!$this->checkCurrentProxy()){
            $newProxy = $this->getProxy();
            if ($newProxy){
                $data = $this->adminManager->getTelegramSettings()->toArray();
                //var_dump($data); exit;
                $data['telegram_proxy'] = 'socks5://'.$newProxy;
                $this->adminManager->setTelegramSettings($data);
            }
        }
        
        return;
    }
}