<?php

namespace Admin\Thread;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Aza\Components\Thread\Thread;
use Admin\Service\TelegrammManager;

/**
 * Description of telegramThread
 *
 * @author Daddy
 */
class TelegramThread extends Thread {
    
    function process() {
        TelegrammManager::sendPostponeMessage();        
    }
    
}
