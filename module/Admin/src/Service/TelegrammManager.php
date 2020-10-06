<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Service;

use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\Request;
use Laminas\Log\Writer\Stream;
use Laminas\Log\Logger;
use GuzzleHttp\Client;

/**
 * Description of TelegrammManager
 *
 * @author Daddy
 */
class TelegrammManager
{

    const COMMANDS_PATH = './vendor/longman/src/Commands/';

    const LOG_FOLDER = './data/log/telegram/'; //папка логов
    const POSTPONE_MSG_FILE = './data/log/telegram/telegram_postpone_msg.log'; //сообщения для отправки
    const LOG_SUFFIX = '_tlg.log';
    
    private $logFilename;
    
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Менеджер админ
     * 
     * @var \Admin\Service\AdminManager
     */
    private $adminManager;   
    
    protected function removeOldLog()
    {    
        $check_time = 60*60*24*7; //Неделя
        
        $folderName = $this::LOG_FOLDER;
        if (is_dir($folderName)){
            foreach (new \DirectoryIterator($folderName) as $fileInfo) {
                if ($fileInfo->isDot()) continue;
                if ($fileInfo->isFile()){
                    if ((time() - $check_time) > $fileInfo->getMTime()){
                        unlink(realpath($fileInfo->getPathname()));
                    }    
                }
            }
        }
        return;
    } 
    
    public function __construct($entityManager, $adminManager)
    {
        $this->entityManager = $entityManager;
        $this->adminManager = $adminManager;

        if (!is_dir($this::LOG_FOLDER)){
            mkdir($this::LOG_FOLDER);
        }
        
        $this->logFilename = $this::LOG_FOLDER.date('Ymd').$this::LOG_SUFFIX;
        $this->removeOldLog();
    }
    
    public function hook()
    {
        
        $settings = $this->adminManager->getTelegramSettings();
        if ($settings['telegram_api_key'] && $settings['telegram_bot_name'] && $settings['telegram_admin_chat_id']){
        
            $writer = new Stream($this->logFilename);
            $logger = new Logger();
            $logger->addWriter($writer);
            Logger::registerErrorHandler($logger);

            try {
                $telegram = new Telegram($settings['telegram_api_key'], $settings['telegram_bot_name']);
                $telegram->addCommandsPaths([$this::COMMANDS_PATH]);
                $telegram->enableAdmins([$settings['telegram_admin_chat_id']]);

                $mysql_credentials = [
                    'host'     => 'localhost',
                    'user'     => $settings['db_user'],
                    'password' => $settings['db_pass'],
                    'database' => 'telegramm',
                 ];
                $telegram->enableMySql($mysql_credentials);

    //            Logging (Error, Debug and Raw Updates)
                \Longman\TelegramBot\TelegramLog::initErrorLog($this::LOG_FOLDER . "/".$settings['telegram_bot_name']."_error.log");
                \Longman\TelegramBot\TelegramLog::initDebugLog($this::LOG_FOLDER . "/".$settings['telegram_bot_name']."_debug.log");
//                \Longman\TelegramBot\TelegramLog::initUpdateLog($this::LOG_FOLDER . "/".$settings['telegram_bot_name']."_update.log");

                $telegram->enableLimiter();

                $telegram->handle();

            } catch (\Longman\TelegramBot\Exception\TelegramException $e){
                $logger->err($e->getMessage());
            } catch (\Longman\TelegramBot\Exception\TelegramLogException $e){
                $logger->err($e->getMessage());            
            }    

            $logger = null;
        }
        
        return;
    }
    
    public function setHook()
    {
        $settings = $this->adminManager->getTelegramSettings();
        if ($settings['telegram_api_key'] && $settings['telegram_bot_name'] && $settings['telegram_hook_url']){
        
            $writer = new Stream($this->logFilename);
            $logger = new Logger();
            $logger->addWriter($writer);
            Logger::registerErrorHandler($logger);

            try {
                $telegram = new Telegram($settings['telegram_api_key'], $settings['telegram_bot_name']);
                $result = $telegram->setWebhook($settings['telegram_hook_url']);
                if ($result->isOk()) {
                    echo $result->getDescription();
                }                    
            } catch (\Longman\TelegramBot\Exception\TelegramException $e){
                $logger->err($e->getMessage());
            }    

            $logger = null;                
        }    
    }
    
    public function unsetHook()
    {
        $settings = $this->adminManager->getTelegramSettings();
        if ($settings['telegram_api_key'] && $settings['telegram_bot_name']){
            
            $writer = new Stream($this->logFilename);
            $logger = new Logger();
            $logger->addWriter($writer);
            Logger::registerErrorHandler($logger);

            try {
                $telegram = new Telegram($settings['telegram_api_key'], $settings['telegram_bot_name']);
                $result = $telegram->deleteWebhook();
                if ($result->isOk()) {
                    echo $result->getDescription();
                }             
            } catch (\Longman\TelegramBot\Exception\TelegramException $e){
                $logger->err($e->getMessage());
            }    

            $logger = null;
        }    
        
        return;
    }    
    
    /**
     * Послать сообщение
     * 
     * @param array $params
     * @return integer
     */
    public function sendMessage($params)
    {
        $settings = $this->adminManager->getTelegramSettings();
        if ($settings['telegram_api_key'] && $settings['telegram_bot_name']){

            $writer = new Stream($this->logFilename);
            $logger = new Logger();
            $logger->addWriter($writer);
            Logger::registerErrorHandler($logger);           
            \Longman\TelegramBot\TelegramLog::initDebugLog($this->logFilename);

            try {
                $telegram = new Telegram($settings['telegram_api_key'], $settings['telegram_bot_name']);
                $clientParams = [
                        'base_uri' => 'https://api.telegram.org', 
                        'timeout' => 10.0,
                        'cookie' => true,
                    ];
                
                $proxy = $settings['telegram_proxy'];
                
                if ($proxy){
                    $clientParams['proxy'] = $proxy;
                }    
                
                Request::setClient(new Client($clientParams));

                if (isset($params['chat_id'])){
                    $chatId = $params['chat_id'];
                } else {
                    $chatId = $settings['telegram_admin_chat_id'];
                }    
                
                $result = Request::sendMessage(['chat_id' => $chatId, 'text' => $params['text']]);         
            } catch (\Longman\TelegramBot\Exception\TelegramException $e){
                $logger->err($e->getMessage());
                $result = false;
            } catch (\Laminas\Http\Client\Adapter\Exception $e){
                $logger->err($e->getMessage());
                $result = false;
            }    

            $logger = null;
            return $result;
        }    
    }
    
    /**
     * Послать отложенное сообщение
     * @param bool $sendingPosponeMsg
     * 
     * @return null
     */
    public function sendPostponeMessage($sendingPosponeMsg = false)
    {
        $data = $this->adminManager->getTelegramSettings()->toArray();
        
        if ($data['sending_pospone_msg'] == 1 || $sendingPosponeMsg){
            
            $data['sending_pospone_msg'] = 2; // идет отправка
            $this->adminManager->setTelegramSettings($data);

            if (file_exists(self::POSTPONE_MSG_FILE)){
                $file = file(self::POSTPONE_MSG_FILE);
                if (count($file)){
                    try {
                        $result = $this->sendMessage(\Laminas\Json\Json::decode(trim($file[0]), \Laminas\Json\Json::TYPE_ARRAY));
                        if ($result){
                            $fp = fopen(self::POSTPONE_MSG_FILE, 'w');
                            unset($file[0]);
                            fputs($fp, implode("", $file));
                            fclose($fp);
                        }
                    } catch (\Longman\TelegramBot\Exception\TelegramException $e){
                        $data['sending_pospone_msg'] = 1; // отправка закончилась
                        $this->adminManager->setTelegramSettings($data);
                        return;
                    }    
                }   

                if (count($file)){
                    sleep(1);
                    $this->sendPostponeMessage(true);
                }
            }

            $data['sending_pospone_msg'] = 1; // отправка закончилась
            $this->adminManager->setTelegramSettings($data);
        }    
        
        return;
        
    }
    
    /**
     * Добавить отложенное сообщение
     * 
     * @param array $params
     */
    public function addPostponeMesage($params)
    {
        if (!is_dir(self::LOG_FOLDER)){
            mkdir(self::LOG_FOLDER);
        }        
        
        $params['text'] = $params['text'].PHP_EOL.date('Y-m-d H:i:s');
        
        file_put_contents(self::POSTPONE_MSG_FILE, \Laminas\Json\Json::encode($params).PHP_EOL, FILE_APPEND | LOCK_EX);
        
//        $client = new Client();
//        $promise = $client->requestAsync('GET', 'http://'.$_SERVER['HTTP_HOST'].'/telegramm/postpone');
//        $promise->wait();
                
        return;
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

//        $uri = 'https://www.proxy-list.download/api/v1/get?type=socks5';
        $uri = 'https://api.proxyscrape.com?request=getproxies&proxytype=socks5&timeout=10000&country=all';
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