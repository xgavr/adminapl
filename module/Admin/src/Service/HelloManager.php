<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Service;

use Admin\Filter\AutoruOrderFilter;
use Admin\Filter\HtmlFilter;
use Admin\Filter\TurboOrderFilter;

/**
 * Description of HelloManager
 *
 * @author Daddy
 */
class HelloManager {

    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Post manager.
     * @var \Admin\Service\PostManager
     */
    private $postManager;
    
    /**
     * Apl manager.
     * @var \Admin\Service\AplService
     */
    private $aplService;

    /**
     * Telegramm manager.
     * @var \Admin\Service\TelegrammManager
     */
    private $telegramManager;
    
    /**
     * Менеджер админ
     * 
     * @var \Admin\Service\AdminManager
     */
    private $adminManager;   

    
    public function __construct($entityManager, $postManager, $telegramManager, $aplService, $adminManager)
    {
        $this->entityManager = $entityManager;
        $this->postManager = $postManager;        
        $this->telegramManager = $telegramManager;        
        $this->aplService = $aplService;        
        $this->adminManager = $adminManager;
    }
        
    public function checkingMail()
    {
        set_time_limit(300);
        $startTime = time();
        
        $settings = $this->adminManager->getSettings();
        
        $box = [
            'host' => 'imap.yandex.ru',
            'server' => '{imap.yandex.ru:993/imap/ssl}',
            'user' => $settings['hello_email'],
            'password' => $settings['hello_email_password'],
            'leave_message' => true,
        ];
        
        $mail = $this->postManager->readImap($box);
        
        return;
    }
}
