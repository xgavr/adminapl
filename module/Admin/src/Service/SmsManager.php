<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Service;

/**
 * Description of SmsManager
 * send sms from sms.ru
 * @author Daddy
 */
class SmsManager {
    
    const API_ID = ''; //не используется
    const SMS_API = ''; // не используется
    
    /**
     * Doctrine entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    private $adminManager;

    public function __construct($entityManager, $adminManager)
    {
        $this->entityManager = $entityManager;
        $this->adminManager = $adminManager;
    }
    
    /*
     * @var $options array
     * phone string
     * text string
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
    
    /*
     * @var $options array
     * phone string
     * text string
     */
    public function wamm($options)
    {
        $settings = $this->adminManager->getSettings();
        $result = false;
        if ($settings['wamm_url'] && $settings['wamm_api_id']){
//            var_dump($settings['wamm_url'].'/'.$settings['wamm_api_id'].'/'.$options['phone'].'/?text='. urlencode($options['text'])); exit;
            $result=file_get_contents($settings['wamm_url'].'/'.$settings['wamm_api_id'].'/'.$options['phone'].'/?text='. urlencode($options['text']));
        }    
        
        return $result;
    }
    
}
