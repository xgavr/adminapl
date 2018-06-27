<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Service;

use Zend\Config\Config;
use Zend\Config\Writer\PhpArray;
/**
 * Description of SmsManager
 * send sms from sms.ru
 * @author Daddy
 */
class AdminManager {
    
    const SETTINGS_DIR       = './data/settings/'; // папка с настройками
    const SETTINGS_FILE       = './data/settings/config.php'; // файл с настройками общими
    const PRICE_SETTINGS_FILE       = './data/settings/price_config.php'; // файл с настройками загрузки прайсов
    
    /**
     * Doctrine entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    /*
     * Получить общие настройки загрузки
     * @return array 
     */    
    public function getSettings()
    {
        if (file_exists(self::SETTINGS_FILE)){
            $config = new Config(include self::SETTINGS_FILE);
        }  else {
            $config = new Config([], true);
            $config->admin = [];
        }   
        
        return $config->admin;
    }
    
    public function setSettings($data)
    {
        if (!is_dir(self::SETTINGS_DIR)){
            mkdir(self::SETTINGS_DIR);
        }        
        if (file_exists(self::SETTINGS_FILE)){
            $config = new Config(include self::SETTINGS_FILE, true);
        }  else {
            $config = new Config([], true);
            $config->admin = [];
        }
        
        if (!isset($config->admin)){
            $config->admin = [];
        }
        
        $config->admin->apl_secret_key = $data['apl_secret_key'];

        $config->admin->sms_ru_url = $data['sms_ru_url'];
        $config->admin->sms_ru_api_id = $data['sms_ru_api_id'];
        
        $config->admin->telegram_bot_name = $data['telegram_bot_name'];
        $config->admin->telegram_api_key = $data['telegram_api_key'];
        $config->admin->telegram_hook_url = $data['telegram_hook_url'];
        $config->admin->telegram_admin_chat_id = $data['telegram_admin_chat_id'];
        $config->admin->telegram_proxy = $data['telegram_proxy'];
        
        $config->admin->ftp_apl_suppliers_price = $data['ftp_apl_suppliers_price'];
        $config->admin->ftp_apl_suppliers_price_login = $data['ftp_apl_suppliers_price_login'];
        $config->admin->ftp_apl_suppliers_price_password = $data['ftp_apl_suppliers_price_password'];

//        $config->admin->tamtam_access_token = $data['tamtam_access_token'];
//        $config->admin->tamtam_chat_id = $data['tamtam_chat_id'];
        
        $writer = new PhpArray();
        
        $writer->toFile(self::SETTINGS_FILE, $config);
    }

    
    /*
     * Получить настройки загрузки прайсов
     * @return array 
     */
    public function getPriceSettings()
    {
        if (file_exists(self::PRICE_SETTINGS_FILE)){
            $config = new Config(include self::PRICE_SETTINGS_FILE);
        }  else {
            $config = new Config([], true);
            $config->price = [];
        }   
        
        return $config->price;
    }
        
    /*
     * Настройки загрузки прайсов
     * @array $data
     */
    public function setPriceSettings($data)
    {
        if (!is_dir(self::SETTINGS_DIR)){
            mkdir(self::SETTINGS_DIR);
        }        
        if (file_exists(self::PRICE_SETTINGS_FILE)){
            $config = new Config(include self::PRICE_SETTINGS_FILE, true);
        }  else {
            $config = new Config([], true);
            $config->price = [];
        }
        
        if (!isset($config->price)){
            $config->price = [];
        }
        
        $config->price->upload_raw = $data['upload_raw']; //загружать прайсы в базу
        $config->price->is_loading_raw = $data['is_loading_raw']; //идет загрузка прайса
        
        $writer = new PhpArray();
        
        $writer->toFile(self::PRICE_SETTINGS_FILE, $config);
    }
    
}
