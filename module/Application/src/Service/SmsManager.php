<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Service;

/**
 * Description of PostManager
 * send sms from sms.ru
 * @author Daddy
 */
class SmsManager {
    
    const API_ID = '066df1aa-8aae-cba4-51f9-83812a6c9d7f';
    const SMS_API = 'http://sms.ru/sms/send';
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
     * @var $options array
     * phone string
     * text string
     */
    public function send($options)
    {
        $result=file_get_contents(self::SMS_API.'?api_id='.self::API_ID.'&to='.$options['phone'].'&text='. urlencode($options['text']).'&from=APL');
        
        return $result;
    }
}
