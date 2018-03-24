<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Service;

use Admin\Filter\AutoruOrderFilter;
/**
 * Description of AutoruManager
 *
 * @author Daddy
 */
class AutoruManager {

    /**
     * Doctrine entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Post manager.
     * @var Admin\Service\PostManager
     */
    private $postManager;
    
    /**
     * Telegramm manager.
     * @var Admin\Service\TelegrammManager
     */
    private $telegrammManager;
    
    public function __construct($entityManager, $postManager, $telegrammManager)
    {
        $this->entityManager = $entityManager;
        $this->postManager = $postManager;        
        $this->telegrammManager = $telegrammManager;        
    }
    
    public function postOrder()
    {
        $box = [
            'host' => 'imap.yandex.ru',
            'user' => 'autoru@autopartslist.ru',
            'password' => 'kjdrf4',
        ];
        
        $filter = new AutoruOrderFilter();
        
        $mail = $this->postManager->read($box);
        if (is_array($mail)){
            foreach($mail as $msg){
                if ($msg['subject'] == 'Заявка на новый товар с портала Авто.ру'&& $msg['content']){
                    $text = $filter->filter($msg['content']); 
                    $text = $msg['subject'].PHP_EOL.$text;
                    $this->telegrammManager->sendMessage(['chat_id' => '-1001128740501', 'text' => $text]);
                    //printf(nl2br($text));
                }
            }
        }
    }
}
