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
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Post manager.
     * @var Admin\Service\PostManager
     */
    private $postManager;
    
    /**
     * Apl manager.
     * @var Admin\Service\AplService
     */
    private $aplService;

    /**
     * Telegramm manager.
     * @var Admin\Service\TelegrammManager
     */
    private $telegramManager;
    
    public function __construct($entityManager, $postManager, $telegramManager, $aplService)
    {
        $this->entityManager = $entityManager;
        $this->postManager = $postManager;        
        $this->telegramManager = $telegramManager;        
        $this->aplService = $aplService;        
    }
    
    public function postOrder()
    {
        $box = [
            'host' => 'imap.yandex.ru',
            'server' => '{imap.yandex.ru:993/imap/ssl}',
            'user' => 'autoru@autopartslist.ru',
            'password' => 'kjdrf4',
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
                    
//                    $this->telegramManager->sendMessage(['chat_id' => '-1001128740501', 'text' => $text]);
                    $this->telegramManager->addPostponeMesage([
                        'chat_id' => '-1001128740501',
                        'text' => $text,
                    ]);

                }
            }
        }
    }
}
