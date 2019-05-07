<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Service;
use Aza\Components\Thread\Thread;

error_reporting(E_ALL & ~E_WARNING);

/**
 * Description of SmsManager
 * send sms from sms.ru
 * @author Daddy
 */
class ThreadManager extends Thread
{
    /**
     *
     * @var \Admin\Service\TelegrammManager;
     */    
    private $telegramManager;

    public function __construct($telegramManager)
    {
        $this->telegramManager = $telegramManager;
    }
    
    public function process()
    {
        $this->telegramManager->sendPostponeMessage();
    }
}
