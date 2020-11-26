<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Service;

/**
 * Description of FtpManager
 *
 * @author Администратор
 */
class FtpManager {

    /**
     * Doctrine entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /*
     * @var Admin\Service\AdminManager
     */
    private $adminManager;
    
    public function __construct($entityManager, $adminManager)
    {
        $this->entityManager = $entityManager;
        $this->adminManager = $adminManager;
    }

    /*
     * Загрузка файла на фтп
     * @var array $params
     * $params['host'] - фтп сервер
     * $params['port'] - фтп порт
     * $params['login'] - логин
     * $params['password'] - пароль
     * $params['source_file'] - локальный файл
     * $params['dest_file'] - файл на фтп
     */
    public function put($params)
    {
        $ftp = ftp_connect($params['host']);
        
        ftp_login($ftp, $params['login'],$params['password']) or die("Cannot login");
        ftp_pasv($ftp, true) or die("Cannot switch to passive mode");
        
        $result = ftp_put($ftp, $params['dest_file'], $params['source_file'], FTP_BINARY);

        ftp_close($ftp);

        return $result;
    }

    /*
     * Перекинуть прайс в папку прайсов на АПЛ
     *  @var $params array
     * $params['source_file'] - локальный файл
     * $params['dest_file'] - файл на фтп
     */
    public function putPriceToApl($params)
    {
        $settings = $this->adminManager->getSettings();
        if ($settings['ftp_apl_suppliers_price']){
            $params['host'] = $settings['ftp_apl_suppliers_price'];
            $params['login'] = $settings['ftp_apl_suppliers_price_login'];
            $params['password'] = $settings['ftp_apl_suppliers_price_password'];
            
            if (file_exists($params['source_file']) && $params['dest_file']){
                return $this->put($params);
            }
        }
        
        return;        
    }
    
    /*
     * Перекинуть прайс в папку market на АПЛ
     *  @var $params array
     * $params['source_file'] - локальный файл
     * $params['dest_file'] - файл на фтп
     */
    public function putMarketPriceToApl($params)
    {
        $settings = $this->adminManager->getSettings();
        if ($settings['ftp_apl_suppliers_price']){
            $params['host'] = $settings['ftp_apl_suppliers_price'];
            $params['login'] = $settings['ftp_apl_market_price_login'];
            $params['password'] = $settings['ftp_apl_market_price_password'];
            
            if (file_exists($params['source_file']) && $params['dest_file']){
                return $this->put($params);
            }
        }
        
        return;        
    }    
}
