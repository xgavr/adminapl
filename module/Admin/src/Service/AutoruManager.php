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
     * Apl manager.
     * @var Admin\Service\AplService
     */
    private $aplService;

    /**
     * Telegramm manager.
     * @var Admin\Service\TelegrammManager
     */
    private $telegrammManager;
    
    public function __construct($entityManager, $postManager, $telegrammManager, $aplService)
    {
        $this->entityManager = $entityManager;
        $this->postManager = $postManager;        
        $this->telegrammManager = $telegrammManager;        
        $this->aplService = $aplService;        
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
                    $filtered = $filter->filter($msg['content']); 
                    $text = $msg['subject'].PHP_EOL.$filtered['text'];
                    
                    if ($phone = $filtered['phone']){
                        $data = [
                            'bo' => 1,
                            //'comment' => 'autoru',
                            'info2' => $text,
                            'phone' => $phone,
                            'address' => $filtered['address'],
                        ];
                        
                        $aplResponce = $this->aplService->checkout($data);
                        if (is_array($aplResponce)){
                            $orderData = (array) $aplResponce['order'];
                            if ($order = $orderData['id']){
                                $text .= PHP_EOL."https://autopartslist.ru/admin/orders/view/id/$order";
                            }
                        }    
                    }
                    
                    $this->telegrammManager->sendMessage(['chat_id' => '-1001128740501', 'text' => $text]);
                    //printf(nl2br($text));
                }
            }
        }
    }
}
