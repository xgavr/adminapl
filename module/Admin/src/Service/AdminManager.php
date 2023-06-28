<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Service;

use Laminas\Config\Config;
use Laminas\Config\Writer\PhpArray;
use Stock\Entity\RegisterVariable;

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
    const APL_EXCHANGE_SETTINGS_FILE       = './data/settings/apl_exchange_config.php'; // файл с настройками обмена
    const TD_EXCHANGE_SETTINGS_FILE       = './data/settings/td_exchange_config.php'; // файл с настройками обмена по апи текдока
    const TELEGRAM_SETTINGS_FILE       = './data/settings/telegram_config.php'; // файл с настройками telegram
    const ABCP_SETTINGS_FILE                    = './data/settings/abcp_config.php'; //файл с настройками abcp
    const AVTOIT_SETTINGS_FILE                    = './data/settings/avtoit_config.php'; //файл с настройками abcp
    const ZETASOFT_SETTINGS_FILE                    = './data/settings/zetasoft_config.php'; //файл с настройками abcp
    const PARTS_API_SETTINGS_FILE      = './data/settings/parts_api_config.php'; //файл с настройками abcp
    const API_MARKET_PLACES      = './data/settings/api_market_places.php'; //файл с настройками апи тп
    const SBP_SETTINGS      = './data/settings/sbp_settings.php'; //файл с настройками сбп
    
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Дата запрета
     * @var string
     */
    private $allowDate;    
    
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;

        $setting = $this->getSettings();
        $this->allowDate = $setting['allow_date'];
    }
    
    /**
     * Получить дату запрета
     * @return date
     */
    public function getAllowDate()
    {
        return $this->allowDate; 
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
        
        $config->admin->sms_ru_url = $data['sms_ru_url'];
        $config->admin->sms_ru_api_id = $data['sms_ru_api_id'];
//        $config->admin->wamm_url = $data['wamm_url'];
        $config->admin->wamm_api_id = $data['wamm_api_id'];
        $config->admin->wamm_read = $data['wamm_read'];
                
        $config->admin->dadata_api_key = $data['dadata_api_key'];
        $config->admin->dadata_standart_key = $data['dadata_standart_key'];

        $config->admin->ftp_apl_suppliers_price = $data['ftp_apl_suppliers_price'];
        $config->admin->ftp_apl_suppliers_price_login = $data['ftp_apl_suppliers_price_login'];
        $config->admin->ftp_apl_suppliers_price_password = $data['ftp_apl_suppliers_price_password'];

        $config->admin->ftp_apl_market_price_login = $data['ftp_apl_market_price_login'];
        $config->admin->ftp_apl_market_price_password = $data['ftp_apl_market_price_password'];

        $config->admin->hello_check = $data['hello_check'];
        $config->admin->hello_email = $data['hello_email'];
        $config->admin->hello_email_password = $data['hello_email_password'];
        $config->admin->hello_app_password = $data['hello_app_password'];
        $config->admin->autoru_email = $data['autoru_email'];
        $config->admin->autoru_email_password = $data['autoru_email_password'];
        
        $config->admin->telefonistka_email = $data['telefonistka_email'];
        $config->admin->telefonistka_email_password = $data['telefonistka_email_password'];
        
        $config->admin->allow_date = $data['allow_date'];
        $config->admin->doc_actualize = $data['doc_actualize'];
        
        $config->admin->mail_token = $data['mail_token'];
        $config->admin->turbo_passphrase = $data['turbo_passphrase'];
        
        $config->admin->job = $data['job'];
        
        $writer = new PhpArray();
        
        $writer->toFile(self::SETTINGS_FILE, $config);
        
        $registerVariable = $this->entityManager->getRepository(RegisterVariable::class)
                ->findOneBy([]);
        if ($registerVariable){
            $this->entityManager->refresh($registerVariable);
            $registerVariable->setAllowDate($data['allow_date']);
            $this->entityManager->persist($registerVariable);
            $this->entityManager->flush();
        }
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
        $config->price->uploading_raw = $data['uploading_raw']; //идет загрузка прайса
        $config->price->parse_raw = $data['parse_raw']; //разбирать прайсы
        $config->price->parse_producer = $data['parse_producer']; //разбирать производителей из прайсов
        $config->price->parse_article = $data['parse_article']; //разбирать артикулы из прайсов
        $config->price->parse_oem = $data['parse_oem']; //разбирать номера из прайсов
        $config->price->parse_name = $data['parse_name']; //разбирать наименования из прайсов
        $config->price->assembly_producer = $data['assembly_producer']; //создавать производителей
        $config->price->assembly_good = $data['assembly_good']; //создавать товары
        $config->price->update_good_price = $data['update_good_price']; //рассчитать цены в товарах
//        $config->price->good_token = $data['good_token']; //токены товаров
        $config->price->assembly_group_name = $data['assembly_group_name']; //собирать группы наименований
        $config->price->update_good_name = $data['update_good_name']; // наименований
        $config->price->image_mail_box = $data['image_mail_box']; //ящик для сбора картинок
        $config->price->image_mail_box_password = $data['image_mail_box_password']; //ящик для сбора картинок пароль
        $config->price->image_mail_box_check = $data['image_mail_box_check']; //ящик для сбора картинок проверять
        $config->price->cross_mail_box = $data['cross_mail_box']; //ящик для сбора кроссов
        $config->price->cross_mail_box_password = $data['cross_mail_box_password']; //ящик для сбора кроссов пароль
        $config->price->cross_mail_app_password = $data['cross_mail_app_password']; //ящик для сбора кроссов пароль app
        $config->price->cross_mail_box_check = $data['cross_mail_box_check']; //ящик для сбора кроссов проверять
        
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
        $config->bank->statement_app_password = $data['statement_app_password']; //Пароль app на email для выписок
        
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
        $config->apl_exchange->get_group_apl = $data['get_group_apl']; //получать группу товара
        $config->apl_exchange->get_car_id = $data['get_car_id']; //получать id машин
        $config->apl_exchange->get_acquiring = $data['get_acquiring']; //скачивать эквайринг
        $config->apl_exchange->rawprice = $data['rawprice']; //обновлять строки прайсов
        $config->apl_exchange->oem = $data['oem']; //обновлять номера
        $config->apl_exchange->group = $data['group']; //обновлять группы
        $config->apl_exchange->image = $data['image']; //обновлять картинки
        $config->apl_exchange->attribute = $data['attribute']; //обновлять атрибуты
        $config->apl_exchange->car = $data['car']; //обновлять машины
        $config->apl_exchange->good_name = $data['good_name']; //обновлять наименования
        $config->apl_exchange->good_price = $data['good_price']; //обновлять цены
        $config->apl_exchange->ptu = $data['ptu']; //обновлять пту
        $config->apl_exchange->order = $data['order']; //обновлять заказы
        $config->apl_exchange->cash = $data['cash']; //обновлять платежи
        $config->apl_exchange->market = $data['market']; //выгружать прайс листы в торговые площадки
        
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
        $config->telegram_settings->telegram_group_chat_id = $data['telegram_group_chat_id'];
        $config->telegram_settings->telegram_proxy = $data['telegram_proxy'];
        $config->telegram_settings->send_pospone_msg = $data['send_pospone_msg'];
        $config->telegram_settings->sending_pospone_msg = $data['sending_pospone_msg'];
        $config->telegram_settings->auto_check_proxy = $data['auto_check_proxy'];
        $config->telegram_settings->db_user = $data['db_user'];
        $config->telegram_settings->db_pass = $data['db_pass'];
        
        $writer = new PhpArray();
        
        $writer->toFile(self::TELEGRAM_SETTINGS_FILE, $config);
    }
    
    /**
     * Получить настройки abcp
     * @return array 
     */
    public function getAbcpSettings()
    {
        if (file_exists(self::ABCP_SETTINGS_FILE)){
            $config = new Config(include self::ABCP_SETTINGS_FILE);
        }  else {
            $config = new Config([], true);
            $config->abcp_settings = [];
        }   
        
        return $config->abcp_settings;
    }

    /**
     * Настройки abcp
     * @param array $data
     */
    public function setAbcpSettings($data)
    {
        if (!is_dir(self::SETTINGS_DIR)){
            mkdir(self::SETTINGS_DIR);
        }        
        if (file_exists(self::ABCP_SETTINGS_FILE)){
            $config = new Config(include self::ABCP_SETTINGS_FILE, true);
        }  else {
            $config = new Config([], true);
            $config->abcp_settings = [];
        }
        
        if (!isset($config->abcp_settings)){
            $config->abcp_settings = [];
        }
        
        $config->abcp_settings->host = $data['host'];
        $config->abcp_settings->login = $data['login'];
        $config->abcp_settings->api_key = $data['api_key'];
        $config->abcp_settings->md5_key = $data['md5_key'];
        $config->abcp_settings->max_query = $data['max_query'];
        
        $writer = new PhpArray();
        
        $writer->toFile(self::ABCP_SETTINGS_FILE, $config);
    }

    public function getPartsApiSettings()
    {
        if (file_exists(self::PARTS_API_SETTINGS_FILE)){
            $config = new Config(include self::PARTS_API_SETTINGS_FILE);
        }  else {
            $config = new Config([], true);
            $config->parts_api_settings = [];
        }   
        
        return $config->parts_api_settings;
    }

    /**
     * Настройки abcp
     * @param array $data
     */
    public function setPartsApiSettings($data)
    {
        if (!is_dir(self::SETTINGS_DIR)){
            mkdir(self::SETTINGS_DIR);
        }        
        if (file_exists(self::PARTS_API_SETTINGS_FILE)){
            $config = new Config(include self::PARTS_API_SETTINGS_FILE, true);
        }  else {
            $config = new Config([], true);
            $config->parts_api_settings = [];
        }
        
        if (!isset($config->parts_api_settings)){
            $config->parts_api_settings = [];
        }
        
        $config->parts_api_settings->host = $data['host'];
        $config->parts_api_settings->login = $data['login'];
        $config->parts_api_settings->api_key = $data['api_key'];
        $config->parts_api_settings->md5_key = $data['md5_key'];
        $config->parts_api_settings->max_query = $data['max_query'];
        
        $writer = new PhpArray();
        
        $writer->toFile(self::PARTS_API_SETTINGS_FILE, $config);
    }

    public function getAvtoitSettings()
    {
        if (file_exists(self::AVTOIT_SETTINGS_FILE)){
            $config = new Config(include self::AVTOIT_SETTINGS_FILE);
        }  else {
            $config = new Config([], true);
            $config->avtoit_settings = [];
        }   
        
        return $config->avtoit_settings;
    }

    
    /**
     * Настройки avtoit
     * @param array $data
     */
    public function setAvtoitSettings($data)
    {
        if (!is_dir(self::SETTINGS_DIR)){
            mkdir(self::SETTINGS_DIR);
        }        
        if (file_exists(self::AVTOIT_SETTINGS_FILE)){
            $config = new Config(include self::AVTOIT_SETTINGS_FILE, true);
        }  else {
            $config = new Config([], true);
            $config->avtoit_settings = [];
        }
        
        if (!isset($config->avtoit_settings)){
            $config->avtoit_settings = [];
        }
        
        $config->avtoit_settings->host = $data['host'];
        $config->avtoit_settings->login = $data['login'];
        $config->avtoit_settings->api_key = $data['api_key'];
        $config->avtoit_settings->md5_key = $data['md5_key'];
        $config->avtoit_settings->max_query = $data['max_query'];
        
        $writer = new PhpArray();
        
        $writer->toFile(self::AVTOIT_SETTINGS_FILE, $config);
    }

    /**
     * Получить настройки Zetasoft
     * 
     * @return array
     */
    public function getZetasoftSettings()
    {
        if (file_exists(self::ZETASOFT_SETTINGS_FILE)){
            $config = new Config(include self::ZETASOFT_SETTINGS_FILE);
        }  else {
            $config = new Config([], true);
            $config->zetasoft_settings = [];
        }   
        
        return $config->zetasoft_settings;
    }

    
    /**
     * Настройки zetasoft
     * @param array $data
     */
    public function setZetasoftSettings($data)
    {
        if (!is_dir(self::SETTINGS_DIR)){
            mkdir(self::SETTINGS_DIR);
        }        
        if (file_exists(self::ZETASOFT_SETTINGS_FILE)){
            $config = new Config(include self::ZETASOFT_SETTINGS_FILE, true);
        }  else {
            $config = new Config([], true);
            $config->zetasoft_settings = [];
        }
        
        if (!isset($config->zetasoft_settings)){
            $config->zetasoft_settings = [];
        }
        
        $config->zetasoft_settings->host = $data['host'];
        $config->zetasoft_settings->login = $data['login'];
        $config->zetasoft_settings->md5_key = $data['md5_key'];
        $config->zetasoft_settings->api_key = $data['api_key'];
        $config->zetasoft_settings->max_query = $data['max_query'];
        
        $writer = new PhpArray();
        
        $writer->toFile(self::ZETASOFT_SETTINGS_FILE, $config);
    }
    
    /**
     * Получить настройки апи ТП
     * 
     * @return array
     */
    public function getApiMarketPlaces()
    {
        if (file_exists(self::API_MARKET_PLACES)){
            $config = new Config(include self::API_MARKET_PLACES);
        }  else {
            $config = new Config([], true);
            $config->api_market_places = [];
        }   
        
        return $config->api_market_places;
    }

    
    /**
     * Настройки апи тп
     * @param array $data
     */
    public function setApiMarketPlaces($data)
    {
        if (!is_dir(self::SETTINGS_DIR)){
            mkdir(self::SETTINGS_DIR);
        }        
        if (file_exists(self::API_MARKET_PLACES)){
            $config = new Config(include self::API_MARKET_PLACES, true);
        }  else {
            $config = new Config([], true);
            $config->api_market_places = [];
        }
        
        if (!isset($config->api_market_places)){
            $config->api_market_places = [];
        }
        
        $config->api_market_places->market_unload = $data['market_unload'];
        $config->api_market_places->ozon_client_id = $data['ozon_client_id'];
        $config->api_market_places->ozon_api_key = $data['ozon_api_key'];
        
        $writer = new PhpArray();
        
        $writer->toFile(self::API_MARKET_PLACES, $config);
    }

    /**
     * Получить настройки СБП
     * 
     * @return array
     */
    public function getSbpSettings()
    {
        if (file_exists(self::SBP_SETTINGS)){
            $config = new Config(include self::SBP_SETTINGS);
        }  else {
            $config = new Config([], true);
            $config->sbp_settings = [];
        }   
        
        return $config->sbp_settings;
    }

    
    /**
     * Настройки СБП
     * @param array $data
     */
    public function setSbpSettings($data)
    {
        if (!is_dir(self::SETTINGS_DIR)){
            mkdir(self::SETTINGS_DIR);
        }        
        if (file_exists(self::SBP_SETTINGS)){
            $config = new Config(include self::SBP_SETTINGS, true);
        }  else {
            $config = new Config([], true);
            $config->sbp_settings = [];
        }
        
        if (!isset($config->sbp_settings)){
            $config->sbp_settings = [];
        }
        
        $config->sbp_settings->legal_id = $data['legal_id'];
        $config->sbp_settings->merchant_id = $data['merchant_id'];
        $config->sbp_settings->account = $data['account'];
        
        $writer = new PhpArray();
        
        $writer->toFile(self::SBP_SETTINGS, $config);
        
        return;
    }
    
}
