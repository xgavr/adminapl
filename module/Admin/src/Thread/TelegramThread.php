<?php

namespace Admin\Thread;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Aza\Components\Thread\Thread;

error_reporting(E_ALL & ~E_WARNING);

/**
 * Description of telegramThread
 *
 * @author Daddy
 */
class TelegramThread extends Thread {
    
    /**
     * TelegrammManager manager.
     * @var \Admin\Service\TelegrammManager
     */
    private $telegramManager;
    
    public function __construct($telegramManager)
    {
        parent::__construct();
        
        $this->telegramManager = $telegramManager;
    }
    
    
    function process() {
        $this->telegramManager->sendPostponeMessage();
    }
    
}
