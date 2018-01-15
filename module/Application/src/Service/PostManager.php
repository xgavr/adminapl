<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Service;

use Zend\Mail\Message;
use Zend\Mail\Transport\Sendmail as SendmailTransport;
/**
 * Description of PostManager
 *
 * @author Daddy
 */
class PostManager {
    //put your code here
    /**
     * Doctrine entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    public function send($options)
    {
        $message = new Message();
        $message->addTo($options['to']);
        $message->addFrom($options['from']);
        $message->setSubject($options['subject']);
        $message->setBody($options['body']);

        $transport = new SendmailTransport();
        $transport->send($message);
    }
}
