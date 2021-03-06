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
    
    /**
     * Заказ из автору
     * 
     * @param array $msg
     * @return type
     */
    protected function autoruMsg($msg)
    {
        $filter = new AutoruOrderFilter();
        $htmlFilter = new HtmlFilter();

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
            
//            $telegramSettings = $this->adminManager->getTelegramSettings();
//            $this->telegramManager->addPostponeMesage([
//                'chat_id' => $telegramSettings['telegram_group_chat_id'],
//                'text' => $text,
//            ]);
        }

        
        return;
    }
    
    /**
     * Заказ с яндекс турбо
     * 
     * @param type $msg
     * @return type
     */
    protected function turboMsg($msg)
    {
        $filter = new TurboOrderFilter();

        if (isset($msg['content']['HTML'])){
            $filtered = $filter->filter($msg['content']['HTML']); 
        } else {
            $filtered = $filter->filter($msg['content']['PLAIN'], false);             
        }    
//        var_dump($filtered); exit;
        $text = 'Заказ с Яндекс.Турбо'.PHP_EOL.$filtered['text'];

        $name = null;
        if (isset($filtered['name'])){
            $name = $filtered['name'];
        }

        $address = '';
        if (isset($filtered['address'])){
            $address = $filtered['address'];
        }

        $email = '';
        if (isset($filtered['email'])){
            $email = $filtered['email'];
        }

        $items = [];
        if (isset($filtered['items'])){
            $items = $filtered['items'];
        }

        if ($phone = $filtered['phone']){
            $data = [
                'bo' => 1,
                'name' => $name,
                'info2' => $text,
                'phone' => $phone,
                'email' => $email,
                'address' => $address,
                'items' => $items,
            ];
//            var_dump($data); exit;

            $aplResponce = $this->aplService->checkout($data);
            if (is_array($aplResponce)){
                $orderData = (array) $aplResponce['order'];
                if ($order = $orderData['id']){
                    $text .= PHP_EOL."https://autopartslist.ru/admin/orders/view/id/$order";
                }
            }    
            
//            $telegramSettings = $this->adminManager->getTelegramSettings();
//            $this->telegramManager->addPostponeMesage([
//                'chat_id' => $telegramSettings['telegram_group_chat_id'],
//                'text' => $text,
//            ]);
        }    
        
        return;
    }
    
    public function postOrder()
    {
        set_time_limit(300);
        $startTime = time();
        
        $settings = $this->adminManager->getSettings();
        
        $box = [
            'host' => 'imap.yandex.ru',
            'server' => '{imap.yandex.ru:993/imap/ssl}',
            'user' => $settings['autoru_email'],
            'password' => $settings['autoru_email_password'],
            'leave_message' => false,
        ];
        $mail = $this->postManager->readImap($box);                
        if (is_array($mail)){
            foreach($mail as $msg){
                if ($msg['subject'] == 'Заявка на новый товар с портала Авто.ру' && $msg['content']['HTML']){
                    $this->autoruMsg($msg);
                }
//        var_dump($msg['content']); exit;
                if (isset($msg['content']['HTML'])){
                    if (mb_strpos($msg['content']['HTML'], 'Турбо-страницы') !== false 
                            && mb_strpos($msg['subject'], 'Новый заказ') !== false){
//        var_dump($msg); exit;
                        $this->turboMsg($msg);
    //                    exit;
                    }
                }    
                
                if (time() > $startTime + 240){
                    return;
                }                
            }
        }        
        return;
    }
}
