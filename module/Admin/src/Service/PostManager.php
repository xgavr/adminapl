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
    
    public static function splitMime($body, $boundary)
    {
        // TODO: we're ignoring \r for now - is this function fast enough and is it safe to assume noone needs \r?
        $body = str_replace("\r", '', $body);

        $start = 0;
        $res = [];
        // find every mime part limiter and cut out the
        // string before it.
        // the part before the first boundary string is discarded:
        $p = strpos($body, '--' . $boundary . "\n", $start);
        var_dump($boundary);
        if ($p === false) {
            // no parts found!
            return [];
        }

        // position after first boundary line
        $start = $p + 3 + strlen($boundary);

        while (($p = strpos($body, '--' . $boundary . "\n", $start)) !== false) {
            $res[] = substr($body, $start, $p - $start);
            $start = $p + 3 + strlen($boundary);
        }

        // no more parts, find end boundary
        $p = strpos($body, '--' . $boundary . '--', $start);
        if ($p === false) {
//            throw new Exception\RuntimeException('Not a valid Mime Message: End Missing');
        }

        // the remaining part also needs to be parsed:
        $res[] = substr($body, $start, $p - $start);
        return $res;
    }
    
    protected function readMimeMessage($mimeString, $boundary, $logger = null)
    {
        try{
//            $message = MimeMessage::createFromMessage($mimeString, $boundary);
           // $this->splitMime($mimeString, $boundary);
            $message = MimeMessage::createFromMessage($mimeString, $boundary);
        } catch (Exception $e){
            return '';
        }    
        
        if ($message){
            
            $logger->info('--mime--');
            $logger->debug('--messageClass: '.get_class($message));
            
            foreach ($message->getParts() as $partNum => $part){            

                if ($logger){
                    $logger->debug('--partNum: '.$partNum);
                    $logger->debug('--partClass: '.get_class($part));
                    $logger->debug('--headers: '.$part->getHeaders());
                    $logger->debug('--content: '.$part->getContent());
                }    
            }    
            
        }
        
        return '';
    }
    
    protected function readPart($iterator, $message, $logger = null)
    {
        $subject = null;
        
        if (isset($message->subject)){
            $subject = $message->subject;
        }    
        
        $boundary = '';
        if (isset($message->boundary)){
            $boundary = $message->boundary;
        }
        
        $type = '';
        if (isset($message->contentType)){
            $types = $message->getHeader('contentType', 'array');
            $type .= $message->contentType.PHP_EOL;
            foreach ($types as $value){
                if (stripos($value, 'multipart/mixed') !== false && stripos ($value, 'boundary') !== false){
                    $typeValues = explode(';', $value);
                    foreach ($typeValues as $typeValue){
                        if (stripos($typeValue, 'boundary') !== false){
                            $typeValuesBoundaries = explode('=', $typeValue);
                            if (trim($typeValuesBoundaries[0]) == 'boundary'){
                                $boundary = str_replace(['"', "'"], '', $typeValuesBoundaries[1]);
                            }
                        }    
                    }
                }
            }    
        }    
    
        $rawContent = $message->getContent();
        
        
//        $received = '';
//        if (isset($message->received)){
//            $receivedes = $message->getHeader('received');
//            if (is_string($receivedes)) {
//                $received = $receivedes;
//            } else {
//                $received = implode(';', $message->getHeader('received', 'array'));
//            }
//        }
        
        $description = '';
        if (isset($message->description)){
            $description = $message->description;
        }
        
        $disposition = '';
        if (isset($message->disposition)){
            $disposition = $message->disposition;
        }
        
        $filename = '';
        if (isset($message->filename)){
            $filename = $message->filename;
        }
        
        $headers = '';
        foreach ($message->getHeaders() as $name => $value) {
            if (is_string($value)) {
                $headers .= "+header: $name: $value".PHP_EOL;
                continue;
            }            
            foreach ($value as $entry) {
                $headers .= "+header: $name: $entry".PHP_EOL;
            }
        }  
        
        $htmlFilter = new HtmlFilter();
        $content = $htmlFilter->filter(base64_decode($message->getContent()));
        
        if ($logger){
            $logger->info('Часть '.$iterator);
            $logger->debug('subject: '.$subject);
            $logger->debug('type: '.$type);
//            $logger->debug('received: '.$received);
            $logger->debug('disposition: '.$disposition);
            $logger->debug('boundary: '.$boundary);
            $logger->debug('filename: '.$filename);
            $logger->debug('headers: '.$headers);
            //$logger->debug('content: '.$content);  
            $logger->debug('rawContent: '.$rawContent);
        }  

        if (trim($boundary) && trim($rawContent)){
            $this->readMimeMessage($rawContent, $boundary, $logger);            
        }
                
        $result = [
            'subject' => $subject,
            'content' => $content,
//            'rawContent' => $rawContent,
        ];
        
        return array_filter($result);
    }
    
    public function read($params)
    {
        $writer = new Stream($this::LOG_FILE);
        $logger = new Logger();
        $logger->addWriter($writer);
        Logger::registerErrorHandler($logger);

        if (!isset($params['leave_message'])) $params['leave_message'] = false;
        
        $mail = new Imap([
            'host' => $params['host'],
            'user' => $params['user'],
            'password' => $params['password'],
            'ssl' => 'SSL',
        ]);
        
        $maxMessage = count($mail);
        
        $result = [];
        if ($maxMessage){
    
            $logger->info('');
            $logger->info('');
            $logger->info($params['user']);
        
            foreach ($mail as $messageNum => $message) {
                $logger->info('');
                $logger->info('---------------------------------------------------');
                $part = $this->readPart(0, $message, $logger);
                $result[$messageNum] = $part;
                $i = 0;
                foreach (new RecursiveIteratorIterator($message) as $part) {
                    $i++;
                    $part = $this->readPart($i, $part, $logger);
                    
                    $result[$messageNum] += $part;
                }  
                
                if (!$params['leave_message']){
                    try{
                       $mail->removeMessage($messageNum);
                    } catch (Exception $e){
                        $logger->error($e->getMessage());
                    }    
                }    
            }
        }
        
        $logger = null;
        
        return $result;
    }    
    
    /*
     * read Imap
     */
    
    protected function flattenParts($messageParts, $flattenedParts = array(), $prefix = '', $index = 1, $fullPrefix = true) {

	foreach($messageParts as $part) {
		$flattenedParts[$prefix.$index] = $part;
		if(isset($part->parts)) {
			if($part->type == 2) {
				$flattenedParts = $this->flattenParts($part->parts, $flattenedParts, $prefix.$index.'.', 0, false);
			}
			elseif($fullPrefix) {
				$flattenedParts = $this->flattenParts($part->parts, $flattenedParts, $prefix.$index.'.');
			}
			else {
				$flattenedParts = $this->flattenParts($part->parts, $flattenedParts, $prefix);
			}
			unset($flattenedParts[$prefix.$index]->parts);
		}
		$index++;
	}

	return $flattenedParts;			
    }
    
    protected function getPart($connection, $messageNumber, $partNumber, $encoding) {
	
	$data = imap_fetchbody($connection, $messageNumber, $partNumber);
	switch($encoding) {
		case 0: return $data; // 7BIT
		case 1: return $data; // 8BIT
		case 2: return $data; // BINARY
		case 3: return base64_decode($data); // BASE64
		case 4: return quoted_printable_decode($data); // QUOTED_PRINTABLE
		case 5: return $data; // OTHER
	}
    }
    
    protected function getFilenameFromPart($part) {

	$filename = '';
	
	if($part->ifdparameters) {
		foreach($part->dparameters as $object) {
			if(strtolower($object->attribute) == 'filename') {
				$filename = $object->value;
			}
		}
	}

	if(!$filename && $part->ifparameters) {
		foreach($part->parameters as $object) {
			if(strtolower($object->attribute) == 'name') {
				$filename = $object->value;
			}
		}
	}
	
	return $filename;
    }
    
    public function readImap($params)
    {
        $connection = imap_open(
                $params['server'], 
                $params['user'], 
                $params['password']
        );
        $messageNumber = 1;
        $structure = imap_fetchstructure($connection, $messageNumber);
        
        $flattenedParts = $this->flattenParts($structure->parts);
        
        foreach($flattenedParts as $partNumber => $part) {

            switch($part->type) {
		
		case 0:
			// the HTML or plain text part of the email
			$message = $this->getPart($connection, $messageNumber, $partNumber, $part->encoding);
			// now do something with the message, e.g. render it
		break;
	
		case 1:
			// multi-part headers, can ignore
	
		break;
		case 2:
			// attached message headers, can ignore
		break;
	
		case 3: // application
		case 4: // audio
		case 5: // image
		case 6: // video
		case 7: // other
			$filename = $this->getFilenameFromPart($part);
			if($filename) {
				// it's an attachment
				$attachment = $this->getPart($connection, $messageNumber, $partNumber, $part->encoding);
				// now do something with the attachment, e.g. save it somewhere
			}
			else {
				// don't know what it is
			}
		break;
	
            }
	
        }       

        var_dump($filename);
    }
}
