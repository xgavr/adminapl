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
 * Description of AdminManager
 *
 * @author Daddy
 */
class AdminManager {
    
    const SETTINGS_DIR       = './data/settings/'; // папка с настройками
    const SETTINGS_FILE       = './data/settings/config.php'; // файл с настройками общими
    const PRICE_SETTINGS_FILE       = './data/settings/price_config.php'; // файл с настройками загрузки прайсов
    const BANK_SETTINGS_FILE       = './data/settings/bank_config.php'; // файл с настройками обмена с банком
    const APL_EXCHANGE_SETTINGS_FILE       = './data/settings/apl_exchange_config.php'; // файл с настройками обмена с банком
    const TD_EXCHANGE_SETTINGS_FILE       = './data/settings/td_exchange_config.php'; // файл с настройками обмена по апи текдока
    const TELEGRAM_SETTINGS_FILE       = './data/settings/telegram_config.php'; // файл с настройками telegram
    
    /**
     * Doctrine entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    /**
     * Определяем нагрузку на сервер
     * @return boolean
     */
    public function canRun()
    {
        $load = sys_getloadavg();
        if ($load[0] > 10.0) {
            return FALSE;
        }
        return TRUE;
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
                
        $config->admin->ftp_apl_suppliers_price = $data['ftp_apl_suppliers_price'];
        $config->admin->ftp_apl_suppliers_price_login = $data['ftp_apl_suppliers_price_login'];
        $config->admin->ftp_apl_suppliers_price_password = $data['ftp_apl_suppliers_price_password'];

//        $config->admin->tamtam_access_token = $data['tamtam_access_token'];
//        $config->admin->tamtam_chat_id = $data['tamtam_chat_id'];
        
        $writer = new PhpArray();
        
        $writer->toFile(self::SETTINGS_FILE, $config);
    }

    
    /**
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
        
    /**
     * Настройки загрузки прайсов
     * @param array $data
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
        
        $config->price->receiving_mail = $data['receiving_mail']; //получать прайсы по почте
        $config->price->receiving_link = $data['receiving_link']; //получать прайсы по ссылке
        $config->price->upload_raw = $data['upload_raw']; //загружать прайсы в базу
        $config->price->parse_raw = $data['parse_raw']; //разбирать прайсы
        $config->price->parse_producer = $data['parse_producer']; //разбирать производителей из прайсов
        $config->price->parse_article = $data['parse_article']; //разбирать артикулы из прайсов
        $config->price->parse_oem = $data['parse_oem']; //разбирать номера из прайсов
        $config->price->parse_name = $data['parse_name']; //разбирать наименования из прайсов
        $config->price->assembly_producer = $data['assembly_producer']; //создавать производителей
        $config->price->assembly_good = $data['assembly_good']; //создавать товары
        $config->price->assembly_group_name = $data['assembly_group_name']; //собирать группы наименований
        $config->price->image_mail_box = $data['image_mail_box']; //ящик для сбора картинок
        $config->price->image_mail_box_password = $data['image_mail_box_password']; //ящик для сбора картинок пароль
        $config->price->image_mail_box_check = $data['image_mail_box_check']; //ящик для сбора картинок проверять
        
        $writer = new PhpArray();
        
        $writer->toFile(self::PRICE_SETTINGS_FILE, $config);
    }
    
    /**
     * Получить настройки обмена с банком
     * @return array 
     */
    public function getBankTransferSettings()
    {
        if (file_exists(self::BANK_SETTINGS_FILE)){
            $config = new Config(include self::BANK_SETTINGS_FILE);
        }  else {
            $config = new Config([], true);
            $config->bank = [];
        }   
        
        return $config->bank;
    }
        
    /**
     * Настройки обмена с банком
     * @param array $data
     */
    public function setBankTransferSettings($data)
    {
        if (!is_dir(self::SETTINGS_DIR)){
            mkdir(self::SETTINGS_DIR);
        }        
        if (file_exists(self::BANK_SETTINGS_FILE)){
            $config = new Config(include self::BANK_SETTINGS_FILE, true);
        }  else {
            $config = new Config([], true);
            $config->bank = [];
        }
        
        if (!isset($config->bank)){
            $config->bank = [];
        }
        
        $config->bank->statement_by_api = $data['statement_by_api']; //получать выписки по апи
        $config->bank->statement_by_file = $data['statement_by_file']; //получать выписки из файла
        $config->bank->doc_by_api = $data['doc_by_api']; //отправлять платжки в банк по апи
        $config->bank->tarnsfer_apl = $data['tarnsfer_apl']; //обмен а АПЛ
        $config->bank->statement_email = $data['statement_email']; //Email для получения выписок
        $config->bank->statement_email_password = $data['statement_email_password']; //Пароль на email для выписок
        
        $writer = new PhpArray();
        
        $writer->toFile(self::BANK_SETTINGS_FILE, $config);
    }
    
    /**
     * Получить настройки обмена с APL
     * @return array 
     */
    public function getAplExchangeSettings()
    {
        if (file_exists(self::APL_EXCHANGE_SETTINGS_FILE)){
            $config = new Config(include self::APL_EXCHANGE_SETTINGS_FILE);
        }  else {
            $config = new Config([], true);
            $config->apl_exchange = [];
        }   
        
        return $config->apl_exchange;
    }

    /**
     * Настройки обмена с APL
     * @param array $data
     */
    public function setAplExchangeSettings($data)
    {
        if (!is_dir(self::SETTINGS_DIR)){
            mkdir(self::SETTINGS_DIR);
        }        
        if (file_exists(self::APL_EXCHANGE_SETTINGS_FILE)){
            $config = new Config(include self::APL_EXCHANGE_SETTINGS_FILE, true);
        }  else {
            $config = new Config([], true);
            $config->apl_exchange = [];
        }
        
        if (!isset($config->apl_exchange)){
            $config->apl_exchange = [];
        }
        
        $config->apl_exchange->apl_secret_key = $data['apl_secret_key'];
        $config->apl_exchange->get_producer_id = $data['get_producer_id']; //получать id производителя
        $config->apl_exchange->get_good_id = $data['get_good_id']; //получать id товара
        $config->apl_exchange->get_acquiring = $data['get_acquiring']; //скачивать эквайринг
        
        $writer = new PhpArray();
        
        $writer->toFile(self::APL_EXCHANGE_SETTINGS_FILE, $config);
    }
    
    /**
     * Получить настройки обмена по апи баз тд
     * @return array 
     */
    public function getTdExchangeSettings()
    {
        if (file_exists(self::TD_EXCHANGE_SETTINGS_FILE)){
            $config = new Config(include self::TD_EXCHANGE_SETTINGS_FILE);
        }  else {
            $config = new Config([], true);
            $config->td_exchange = [];
        }   
        
        return $config->td_exchange;
    }
    
    /**
     * Настройки обмена по апи данных из ТД
     * @param array $data
     */
    public function setTdExchangeSettings($data)
    {
        if (!is_dir(self::SETTINGS_DIR)){
            mkdir(self::SETTINGS_DIR);
        }        
        if (file_exists(self::TD_EXCHANGE_SETTINGS_FILE)){
            $config = new Config(include self::TD_EXCHANGE_SETTINGS_FILE, true);
        }  else {
            $config = new Config([], true);
            $config->td_exchange = [];
        }
        
        if (!isset($config->td_exchange)){
            $config->td_exchange = [];
        }
        
        $config->td_exchange->update_car = $data['update_car']; //обновлять машины
        $config->td_exchange->update_image = $data['update_image']; //обновлять картинки
        $config->td_exchange->update_description = $data['update_description']; //обновлять описание
        $config->td_exchange->update_group = $data['update_group']; //обновлять группы
        $config->td_exchange->update_oe = $data['update_oe']; //обновлять номера
        
        $writer = new PhpArray();
        
        $writer->toFile(self::TD_EXCHANGE_SETTINGS_FILE, $config);
    }
    
    /**
     * Получить настройки telegram
     * @return array 
     */
    public function getTelegramSettings()
    {
        if (file_exists(self::TELEGRAM_SETTINGS_FILE)){
            $config = new Config(include self::TELEGRAM_SETTINGS_FILE);
        }  else {
            $config = new Config([], true);
            $config->telegram_settings = [];
        }   
        
        return $config->telegram_settings;
    }
    
    /**
     * Настройки telegram
     * @param array $data
     */
    public function setTelegramSettings($data)
    {
        if (!is_dir(self::SETTINGS_DIR)){
            mkdir(self::SETTINGS_DIR);
        }        
        if (file_exists(self::TELEGRAM_SETTINGS_FILE)){
            $config = new Config(include self::TELEGRAM_SETTINGS_FILE, true);
        }  else {
            $config = new Config([], true);
            $config->telegram_settings = [];
        }
        
        if (!isset($config->telegram_settings)){
            $config->telegram_settings = [];
        }
        
        $config->telegram_settings->telegram_bot_name = $data['telegram_bot_name'];
        $config->telegram_settings->telegram_api_key = $data['telegram_api_key'];
        $config->telegram_settings->telegram_hook_url = $data['telegram_hook_url'];
        $config->telegram_settings->telegram_admin_chat_id = $data['telegram_admin_chat_id'];
        $config->telegram_settings->telegram_proxy = $data['telegram_proxy'];
        $config->telegram_settings->auto_check_proxy = $data['auto_check_proxy'];
        
        $writer = new PhpArray();
        
        $writer->toFile(self::TELEGRAM_SETTINGS_FILE, $config);
    }
    
}
