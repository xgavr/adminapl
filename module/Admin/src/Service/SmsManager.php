<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Service;

use Laminas\Json\Json;
use Admin\Entity\Wammchat;
use Application\Entity\Order;

/**
 * Description of SmsManager
 * send sms from sms.ru
 * @author Daddy
 */
class SmsManager {
    
    const API_ID = ''; //не используется
    const SMS_API = ''; // не используется
    const WAMM_API = 'https://wamm.chat/api2'; 
    
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Admin manager
     * @var \Admin\Service\AdminManager
     */
    private $adminManager;
    
    /**
     * Log manager
     * @var \Admin\Service\LogManager
     */
    private $logManager;

    /**
     * Print manager
     * @var \Application\Service\PrintManager
     */
    private $printManager;

    public function __construct($entityManager, $adminManager, $logManager, $printManager)
    {
        $this->entityManager = $entityManager;
        $this->adminManager = $adminManager;
        $this->logManager = $logManager;
        $this->printManager = $printManager;
    }
    
    public function currentUser()
    {
        return $this->logManager->currentUser();
    }
    
    /**
     * @param array $options
     * phone string
     * text string
     * 
     */
    public function send($options)
    {
        $settings = $this->adminManager->getSettings();
        $result = false;
        if ($settings['sms_ru_url'] && $settings['sms_ru_api_id']){
            $result=file_get_contents($settings['sms_ru_url'].'?api_id='.$settings['sms_ru_api_id'].'&to='.$options['phone'].'&text='. urlencode($options['text']).'&from=APL');
        }    
        
        return $result;
    }
        
    /**
     * Проверить наличие WhatsApp по номеру телефона
     * @param array $options
     * @return array
     */
    public function wammCheckPhone($options)
    {
        $settings = $this->adminManager->getSettings();
        $response = false;
        if (self::WAMM_API && $settings['wamm_api_id']){
            $response = file_get_contents(self::WAMM_API.'/check_phone/'.$settings['wamm_api_id'].'/?phone='.$options['phone']);
        } 
        if ($response){
            $data = Json::decode($response, Json::TYPE_ARRAY);
            if (!empty($data['err'])){
                return $data['err'] == 0 && $data['result']== 'exists';
            }
        }
        
        return false;        
    }

    /**
     * Получение статуса сообщения
     * @param array $options
     * @return array
     */
    public function wammMsgState($options)
    {
        $settings = $this->adminManager->getSettings();
        $response = false;
        if (self::WAMM_API && $settings['wamm_api_id']){
            $response = file_get_contents(self::WAMM_API.'/msg_state/'.$settings['wamm_api_id'].'/?msg_id='.$options['msg_id']);
        } 
        if ($response){
            $data = Json::decode($response, Json::TYPE_ARRAY);
            if (!empty($data['err'])){
                if ($data['err'] == 0){
                    return $data['state'];
                }    
            }
        }
        
        return false;        
    }

    /**
     * Получение сообщений
     * @param int $col
     * @return array
     */
    public function wammMsgGetLast($col = 100)
    {
        $settings = $this->adminManager->getSettings();
        $result = $response = false;
        if (self::WAMM_API && $settings['wamm_api_id']){
            $response = file_get_contents(self::WAMM_API.'/msg_get_last/'.$settings['wamm_api_id'].'/?col='.$col);
        } 
        if ($response){
            $result = Json::decode($response, Json::TYPE_ARRAY);
        }
        
        return $result;        
    }

    /**
     *  Получение сообщений по номеру телефона
     * @param string $phone
     * @param int $col
     * @return array
     */
    public function wammMsgGet($phone, $col = 100)
    {
        $settings = $this->adminManager->getSettings();
        $result = $response = false;
        if (self::WAMM_API && $settings['wamm_api_id']){
            $response = file_get_contents(self::WAMM_API.'/msg_get/'.$settings['wamm_api_id'].'/?phone='.$phone.'&col='.$col);
        } 
        if ($response){
            $result = Json::decode($response, Json::TYPE_ARRAY);
        }
        
        return $result;        
    }

    /**
     * Отправка сообщений
     * @param array $options
     */
    public function wammMsgTo($options)
    {
        $settings = $this->adminManager->getSettings();
        $response = false;
        if (self::WAMM_API && $settings['wamm_api_id']){
            $response = file_get_contents(self::WAMM_API.'/msg_to/'.$settings['wamm_api_id'].'/?phone='.$options['phone'].'&text='. urlencode($options['text']));
        } 
        if ($response){
            $data = Json::decode($response, Json::TYPE_ARRAY);
            if (!empty($data['err'])){
                return $data['err'] == 0;
            }
        }
        
        return false;        
    }
    
    /**
     * Отправка файлов
     * @param array $options
     */
    public function wammFileTo($options)
    {
        $settings = $this->adminManager->getSettings();
        $response = $url = false;
        if (self::WAMM_API && $settings['wamm_api_id'] && $options['attachment'] == 'preorder'){
            if (!empty($options['attachment'])){
                if ($options['attachment'] == 'preorder'){
                    $order = $this->entityManager->getRepository(Order::class)
                            ->findOneBy(['aplId' => $options['name']]);
                    if ($order){
                        $url = $this->printManager->preorder($order, 'Pdf', false, true);
                    }    
                }
                if ($url){
//                    var_dump('https://adminapl.ru/doc/'.$url);
                    $response = file_get_contents(self::WAMM_API.'/file_to/'.$settings['wamm_api_id'].'/?phone='.$options['phone'].'&url=https://adminapl.ru/doc/'.$url);
                }    
            }
        } 
        if ($response){
            $data = Json::decode($response, Json::TYPE_ARRAY);
            if (!empty($data['err'])){
                return $data['err'] == 0;
            }
        }
        
        return false;        
    }
    
    /**
     * Добавление и обновление контактов
     * @param array $options
     */
    public function wammContactTo($options)
    {
        $settings = $this->adminManager->getSettings();
        $response = false;
        if (self::WAMM_API && $settings['wamm_api_id']){
            $response = file_get_contents(self::WAMM_API.'/contact_to/'.$settings['wamm_api_id'].'/?phone='.$options['phone'].'&name='. urlencode($options['name']));
        } 
        if ($response){
            $data = Json::decode($response, Json::TYPE_ARRAY);
            if (!empty($data['err'])){
                return $data['err'] == 0;
            }
        }
        
        return false;        
    }
    
    /**
     * Удаление контактов
     * @param array $options
     */
    public function wammContactDelete($options)
    {
        $settings = $this->adminManager->getSettings();
        $response = false;
        if (self::WAMM_API && $settings['wamm_api_id']){
            $response = file_get_contents(self::WAMM_API.'/contact_delete/'.$settings['wamm_api_id'].'/?phone='.$options['phone']);
        } 
        if ($response){
            $data = Json::decode($response, Json::TYPE_ARRAY);
            if (!empty($data['err'])){
                return $data['err'] == 0;
            }
        }
        
        return false;        
    }
    
    /*
     * Отправить сообщение wammchat
     * @var $options array
     * phone string
     * text string
     */
    public function wamm($options)
    {
        return $this->wammContactTo($options) || $this->wammMsgTo($options) || $this->wammFileTo($options);
    }

    /**
     * Обновить/добавить записи чата
     * @param array $data
     */
    public function updateWammchat($data)
    {
        foreach ($data as $row){
            $chat = $this->entityManager->getRepository(Wammchat::class)
                    ->findOneBy(['msgId' => $row['msg_id']]);
            if (!$chat){
                $chat = new Wammchat();
                $chat->setMsgId($row['msg_id']);
                $chat->setStatus(Wammchat::STATUS_ACTIVE);
            }

            $chat->setChatName($row['chat_name']);
            $chat->setDateIns($row['date_ins']);
            $chat->setDateUpd($row['date_upd']);
            $chat->setFromMe($row['from_me']);
            $chat->setMsgLink($row['msg_link']);
            $chat->setMsgText($row['msg_text']);
            $chat->setPhone($row['phone']);
            $chat->setTipMsg($row['tip_msg']);
            $chat->setState($row['state']);
            
            if (is_numeric($row['chat_name'])){
                $order = $this->entityManager->getRepository(Order::class)
                        ->findOneBy(['aplId' => $row['chat_name']]);
                if ($order){
                    $chat->setOrder($order);
                }    
            }
            
            $this->entityManager->persist($chat);
            $this->entityManager->flush();
        }
        
        return;
    }
    
    /**
     * Получить и обновить чат
     * @param integer $col
     * @return type
     */
    public function getAndUpdateWammchat($col = 100)
    {
//        ini_set('memory_limit', '4096M');
        set_time_limit(300);
        
        $result = $this->wammMsgGetLast($col);
        if (is_array($result)){
            if (!empty($result['msg_data'])){
                $this->updateWammchat($result['msg_data']);
            }
        }
        
        return;
    }
}
