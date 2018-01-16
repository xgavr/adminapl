<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Service;

use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Mime;
use Zend\Mime\Part as MimePart;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;
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
        
        $breaks = array("<br />","<br>","<br/>");  
        $text = strip_tags(str_ireplace($breaks, PHP_EOL, $options['body']));
        
        $text = new MimePart($text);
        $text->type = Mime::TYPE_TEXT;
        $text->charset = 'utf-8';
        $text->encoding = Mime::ENCODING_QUOTEDPRINTABLE;        
    
        $html = new MimePart($options['body']);
        $html->type = Mime::TYPE_HTML;
        $html->charset = 'utf-8';
        $html->encoding = Mime::ENCODING_QUOTEDPRINTABLE;        
        
        $message = new Message();
        $message->setEncoding('UTF-8');
        $message->addTo($options['to']);
        $message->addFrom($options['from']);
        $message->setSubject($options['subject']);
        
        $body = new MimeMessage();
        $body->setParts([$text, $html]);
        
        $message->setBody($body);
        
        $contentTypeHeader = $message->getHeaders()->get('Content-Type');
        $contentTypeHeader->setType('multipart/alternative');

        // Setup SMTP transport using LOGIN authentication
        $transport = new SmtpTransport();
        $options   = new SmtpOptions([
            'name'              => 'localhost.localdomain',
            'host'              => '127.0.0.1',
//            'connection_class'  => 'login',
//            'connection_config' => [
//                'username' => 'user',
//                'password' => 'pass',
//            ],
        ]);
        $transport->setOptions($options);
        $transport->send($message);

    }
}
