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
    const SETTINGS_FILE       = './data/settings/config.php'; // файл с настройками
    
    /**
     * Doctrine entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
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
        
        $writer = new PhpArray();
        
        $writer->toFile(self::SETTINGS_FILE, $config);
    }
}
