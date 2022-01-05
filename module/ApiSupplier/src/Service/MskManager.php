<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ApiSupplier\Service;


/**
 * Description of MskManager
 * 
 * @author Daddy
 */
class MskManager {
    
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
    
    /**
     * Login
     * @param array $options
     * phone string
     * text string
     * 
     */
    public function login()
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
