<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Service;

use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Mime;
use Zend\Mime\Part as MimePart;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;
use Zend\Mail\Storage\Imap;
use Zend\Mail\Exception;
use RecursiveIteratorIterator;
use Zend\Log\Writer\Stream;
use Zend\Log\Logger;
use Admin\Filter\HtmlFilter;

/**
 * Description of PostManager
 *
 * @author Daddy
 */
class PostManager {
    
    const LOG_FOLDER = './data/log/'; //папка логов
    const LOG_FILE = './data/log/mail.log'; //лог 
    
    //put your code here
    /**
     * Doctrine entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
        
        if (!is_dir($this::LOG_FOLDER)){
            mkdir($this::LOG_FOLDER);
        }
        
    }
    
    public function send($options)
    {
        if ($_SERVER['SERVER_ADDR'] == '127.0.0.1') return; //если отладка на локальной машине, либо использовать sendmail

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
    
    protected function readPart($iterator, $message, $logger = null)
    {
        
        if (isset($message->subject)){
            $subject = $message->subject;
        }    
        
        if (isset($message->contentType)){
            $type = $message->contentType;
        }    
    
        if (isset($message->received)){
            $receivedes = $message->getHeader('received');
            if (is_string($receivedes)) {
                $received = $receivedes;
            } else {
                $received = implode(';', $message->getHeader('received', 'array'));
            }
        }
        
        $headers = '';
        foreach ($message->getHeaders() as $name => $value) {
            if (is_string($value)) {
                $headers .= "$name: $value".PHP_EOL;
                continue;
            }            
            foreach ($value as $entry) {
                $headers .= "$name: $entry".PHP_EOL;
            }
        }  
        
        $content = $message->getContent();
        
        $htmlFilter = new HtmlFilter();
        
        if ($logger){
            $logger->info('Часть '.$iterator);
            $logger->debug('subject: '.$subject);
            $logger->debug('type: '.$type);
            $logger->debug('headers: '.$headers);
            $logger->debug('content: '.$htmlFilter->filter(base64_decode($content)));        
        }        
    }
    
    public function read($params)
    {
        $writer = new Stream($this::LOG_FILE);
        $logger = new Logger();
        $logger->addWriter($writer);
        Logger::registerErrorHandler($logger);

        $mail = new Imap([
            'host' => $params['host'],
            'user' => $params['user'],
            'password' => $params['password'],
            'ssl' => 'SSL',
        ]);
        
        $logger->info($params['user']);
        
        $maxMessage = count($mail);
        
        if ($maxMessage){
            $i = 0;
            foreach ($mail as $messageNum => $message) {
                $this->readPart($i, $message, $logger);
                foreach (new RecursiveIteratorIterator($message) as $part) {
                    $i++;
                   $this->readPart($i, $part, $logger);
                }                                
            }
        }
        
        $logger = null;
    }
}
