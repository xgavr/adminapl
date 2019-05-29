<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Service;

use Admin\Filter\AutoruOrderFilter;
use Admin\Filter\HtmlFilter;
/**
 * Description of AutoruManager
 *
 * @author Daddy
 */
class AutoruManager {

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
    
    public function postOrder()
    {
        $settings = $this->adminManager->getSettings();
        $telegramSettings = $this->adminManager->getTelegramSettings();
        
        $box = [
            'host' => 'imap.yandex.ru',
            'server' => '{imap.yandex.ru:993/imap/ssl}',
            'user' => $settings['autoru_email'],
            'password' => $settings['autoru_email_password'],
            'leave_message' => false,
        ];
        
        $filter = new AutoruOrderFilter();
        $htmlFilter = new HtmlFilter();
        
        $mail = $this->postManager->readImap($box);
        if (is_array($mail)){
            foreach($mail as $msg){
                
                if ($msg['subject'] == 'Заявка на новый товар с портала Авто.ру' && $msg['content']['HTML']){
                    $filtered = $filter->filter($htmlFilter->filter($msg['content']['HTML'])); 
                    $text = $msg['subject'].PHP_EOL.$filtered['text'];
                    
                    $address = '';
                    if (isset($filtered['address'])){
                        $address = $filtered['address'];
                    }
                    
                    $email = '';
                    if (isset($filtered['email'])){
                        $email = $filtered['email'];
                    }
                    
                    if ($phone = $filtered['phone']){
                        $data = [
                            'bo' => 1,
                            //'comment' => 'autoru',
                            'info2' => $text,
                            'phone' => $phone,
                            'email' => $email,
                            'address' => $address,
                        ];
                        
                
                        $aplResponce = $this->aplService->checkout($data);
                        if (is_array($aplResponce)){
                            $orderData = (array) $aplResponce['order'];
                            if ($order = $orderData['id']){
                                $text .= PHP_EOL."https://autopartslist.ru/admin/orders/view/id/$order";
                            }
                        }    
                    }
                    
                    $this->telegramManager->addPostponeMesage([
                        'chat_id' => $telegramSettings['telegram_group_chat_id'],
                        'text' => $text,
                    ]);

                }
            }
        }
    }
}
