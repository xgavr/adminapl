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
    
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
        
    }

    /*
     * Загрузка файла на фтп
     * @var array $params
     * $params['host'] - фтп сервер
     * $params['port'] - фтп порт
     * $params['login'] - логин
     * $params['password'] - пароль
     * $params['dest_file'] - локальный файл
     * $params['source_file'] - файл на фтп
     */
    public function put($params)
    {
        $ftp = ftp_connect($params['host'], $params['port']);
        
        ftp_login($ftp, $params['login'],$params['password']);
        
        if (ftp_login){

            $result = ftp_put($ftp, $params['dest_file'], $params['source_file'], FTP_BINARY);

            ftp_close($ftp);

            return $result;
        }
        
        return false;
    }    
}
