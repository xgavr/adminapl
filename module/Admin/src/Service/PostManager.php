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
use Admin\Entity\PostLog;

/**
 * Description of PostManager
 *
 * @author Daddy
 */
class PostManager {
    
    const LOG_FOLDER = './data/log/'; //папка логов
    const LOG_FILE = './data/log/mail.log'; //лог 
    
    const CONTENT_SEPARATOR = '--==--';
    
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
    
    /**
     * Добавление сообщения в лог
     * 
     * @param array $data
     */
    private function addMessageToLog($data)
    {
        $filter = new HtmlFilter();
        
        $postLog = new PostLog();
        $postLog->setTo($data['to']);
        $postLog->setFrom($data['from']);
        $postLog->setSubject($data['subject']);
        $postLog->setDateCreated(date('Y-m-d H:i:s', strtotime($data['date'])));
        $postLog->setStatus(PostLog::STATUS_ACTIVE);
        
        foreach ($data['content'] as $content){
            $postLog->setBody($filter->filter($content));
            break;
        }
        
        $fileNames = [];
        if (is_array($data['attachment'])){
            foreach ($data['attachment'] as $attachment){
                $fileNames[] = $attachment['filename'];
            }
        }
        $postLog->setAttachment(implode(self::CONTENT_SEPARATOR, $fileNames));
        
        $this->entityManager->persist($postLog);
        $this->entityManager->flush($postLog);
    }


    /*
     * read Imap
     */
    
    protected function flattenParts($messageParts, $flattenedParts = array(), $prefix = '', $index = 1, $fullPrefix = true) 
    {

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
    
    protected function getPart($connection, $messageNumber, $partNumber, $encoding) 
    {
	
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
    
    protected function getBody($connection, $messageNumber, $encoding) 
    {
	
	$data = imap_body($connection, $messageNumber);
	switch($encoding) {
		case 0: return $data; // 7BIT
		case 1: return $data; // 8BIT
		case 2: return $data; // BINARY
		case 3: return base64_decode($data); // BASE64
		case 4: return quoted_printable_decode($data); // QUOTED_PRINTABLE
		case 5: return $data; // OTHER
	}
    }
    
    protected function getFilenameFromPart($part)
    {        

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
        
        if (substr($filename, 0, 2) == '=?'){
            $result = iconv_mime_decode($filename, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'utf-8');
        } else {    
            $filter = new \Application\Filter\ToUtf8();
            $result = $filter->filter($filename);
        }
        return $result;
//        return iconv_mime_decode($filename, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'utf-8');
//        return mb_decode_mimeheader($filename);
    }    
    
    /**
     * Чтение почтового ящика
     * @param array $params
     * server str {imap.yandex.ru:993/imap/ssl}
     * -foders array ['INBOX', Спам]
     * -trash str - папка "Удаленные"
     * -user str
     * -password str
     * -leave_message bool - не удалять сообщение если true
     * 
     * return array 
     */
    public function readImap($params)
    {
        $result = [];
        $imap_obj = $connection = null;
        
        if (!isset($params['folders'])){
            $params['folders'] = ['INBOX', 'Спам'];
        }            
        if (!isset($params['trash'])) $params['trash'] = 'Удаленные';
        
        if (is_array($params['folders'])){
            foreach ($params['folders'] as $foldername){

                $hostname = $params['server'].mb_convert_encoding($foldername, 'UTF7-IMAP', 'UTF-8');

                $connection = imap_open(
                        $hostname, 
                        $params['user'], 
                        $params['password']
                );

                if ($connection){
                      //Просмотр названий папок
    //                $list = imap_list($connection, '{imap.yandex.ru:993/imap/ssl}', '*');
    //                foreach ($list as $value) {
    //    
    //                    var_dump($value);
    //                    var_dump(mb_convert_encoding($value, 'UTF-8', 'UTF7-IMAP'));
    //    
    //                }            

//                    var_dump($connection);
                    
                    $imap_obj = imap_check($connection);

                    if ($imap_obj->Nmsgs){
    
//                        var_dump($imap_obj->Nmsgs);
                        
                        $messageNumber = 1;
                        while ($messageNumber <= $imap_obj->Nmsgs){

                            $structure = imap_fetchstructure($connection, $messageNumber);
                            $headers = imap_fetch_overview($connection, $messageNumber);

//                            var_dump($structure); exit;

                            $result[$messageNumber]['to'] = $params['user'];
                            
                            if (isset($headers[0])){
                                $result[$messageNumber]['from'] = iconv_mime_decode($headers[0]->from);
                                $result[$messageNumber]['date'] = $headers[0]->date;
                                if (isset($headers[0]->subject)){
                                    $result[$messageNumber]['subject'] = iconv_mime_decode($headers[0]->subject);                                    
                                }
                            }    

//                            var_dump($headers); exit;                            
//                            var_dump($structure->parts); exit;
//                            var_dump($this->flattenParts($structure)); exit;

                            $flattenedParts = [];
                            
                            if (isset($structure->parts)){
                                $flattenedParts = $this->flattenParts($structure->parts);
                            } else{
                                $flattenedParts[] = $structure;
                            }
                            
                            if (count($flattenedParts)){

                                foreach($flattenedParts as $partNumber => $part) {
                                    
//                                    var_dump($part); exit;
                                    if ($part){
                                        switch($part->type) {
                                            case 0:
                                                $charset = 'utf-8';
                                                $parameters = (array) $part->parameters;
                                                if (isset($parameters[0])){
                                                    if ($parameters[0]->attribute == 'charset'){
                                                        $charset = $parameters[0]->value;
                                                    }
                                                }    

                                                // the HTML or plain text part of the email
                                                if (isset($structure->parts)){
                                                    $message = $this->getPart($connection, $messageNumber, $partNumber, $part->encoding);
                                                } else {
                                                    $message = $this->getBody($connection, $messageNumber, $part->encoding);
                                                }    
                                                if (trim(strtoupper($charset)) != 'UTF-8'){
                                                    $message = iconv($charset, 'UTF-8//IGNORE', $message);
                                                }    
                                                // now do something with the message, e.g. render it
                                                $result[$messageNumber]['content'][$part->subtype] = $message;

                                                $filename = $this->getFilenameFromPart($part);
                                                if($filename) {
                                                        // it's an attachment
                                                        $attachment = $this->getPart($connection, $messageNumber, $partNumber, $part->encoding);
                                                        // now do something with the attachment, e.g. save it somewhere

                                                        $temp_file = tempnam(sys_get_temp_dir(), 'Pst');
                                                        $fh = fopen($temp_file, 'w');
                                                        fwrite($fh, $attachment);
                                                        fclose($fh);                                

                                                        $result[$messageNumber]['attachment'][$partNumber] = [
                                                            'filename' =>$filename,
                                                            'temp_file' => $temp_file,
                                                        ];
                                                } else {
                                                        // don't know what it is
                                                }

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
                                            case 8: // other
                                            case 9: // other
                                                    $filename = $this->getFilenameFromPart($part);
//var_dump($filename);
                                                    if($filename) {
                                                            // it's an attachment
                                                            if (isset($structure->parts)){
                                                                $attachment = $this->getPart($connection, $messageNumber, $partNumber, $part->encoding);
                                                            } else {
                                                                $attachment = $this->getBody($connection, $messageNumber, $part->encoding);
                                                            }    
    //                                                        var_dump($attachment); exit;
                                                            // now do something with the attachment, e.g. save it somewhere

                                                            $temp_file = tempnam(sys_get_temp_dir(), 'Pst');
                                                            $fh = fopen($temp_file, 'w');
                                                            fwrite($fh, $attachment);
                                                            fclose($fh);                                

                                                            $result[$messageNumber]['attachment'][$partNumber] = [
                                                                'filename' =>$filename,
                                                                'temp_file' => $temp_file,
                                                            ];

                                                    } else {
                                                            // don't know what it is
                                                    }

                                                    break;

                                        }
                                    }    

                                }

                                $this->addMessageToLog($result[$messageNumber]);
                            
                                if (!$params['leave_message']){
                                    $move = imap_mail_move($connection, (string) $messageNumber, mb_convert_encoding($params['trash'], 'UTF7-IMAP', 'UTF-8'));
                                    if (!$move){
                                        imap_delete($connection, $messageNumber);                                
                                    }    
                                }                
                            }    

                            $messageNumber++;

                            if ($messageNumber > 5) break;
                        }    
                    }    

                    imap_close($connection, CL_EXPUNGE);
                }    
            }    
        }    
        
        return $result;
    }
}
