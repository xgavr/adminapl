<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Service;

use Laminas\Mail\Message;
use Laminas\Mime\Message as MimeMessage;
use Laminas\Mime\Mime;
use Laminas\Mime\Part as MimePart;
use Laminas\Mail\Transport\Smtp as SmtpTransport;
use Laminas\Mail\Transport\SmtpOptions;
use Laminas\Mail\Storage\Imap;
use Laminas\Mail\Exception;
use RecursiveIteratorIterator;
use Laminas\Log\Writer\Stream;
use Laminas\Log\Logger;
use Admin\Filter\HtmlFilter;
use Admin\Filter\EmailFromStr;
use Admin\Entity\PostLog;
use User\Entity\User;
use Application\Entity\Order;
use Application\Filter\OrderFromIdZ;

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
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Log manager.
     * @var \Admin\Service\LogManager
     */
    private $logManager;            
    
    /**
     * Print manager.
     * @var \Application\Service\PrintManager
     */
    private $printManager;            
    
    public function __construct($entityManager, $logManager, $printManager)
    {
        $this->entityManager = $entityManager;
        $this->logManager = $logManager;
        $this->printManager = $printManager;
        
        if (!is_dir($this::LOG_FOLDER)){
            mkdir($this::LOG_FOLDER);
        }
        
    }
    
    /**
     * Current user
     * @return User
     */
    public function currentUser()
    {
        return $this->logManager->currentUser();
    }
    
    /**
     * Отправить письмо
     * @param array $options
     * @return null
     */
    public function send($options)
    {
        //if ($_SERVER['SERVER_ADDR'] == '127.0.0.1') return; //если отладка на локальной машине, либо использовать sendmail
//        var_dump($options); exit;
        
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
        
        $parts = [$html];
        
//        $order = $this->entityManager->getRepository(Order::class)
//                ->find($options['orderId']);
        $orderFilter = new OrderFromIdZ($this->entityManager);
        $order = $orderFilter->filter($options['orderId']);
        
        if ($order){
            if (!empty($options['bill'])){
                $billFileName = $this->printManager->bill($order, 'Pdf', true, !empty($options['showCode']));
                if (file_exists($billFileName)){
                    $billFile              = new MimePart(fopen($billFileName, 'r'));
                    $billFile->type        = 'application/pdf';
                    $billFile->filename    = basename($billFileName);
                    $billFile->disposition = Mime::DISPOSITION_ATTACHMENT;
                    $billFile->encoding    = Mime::ENCODING_BASE64;      

                    $parts[] = $billFile;
                }
            }
        
            if (!empty($options['offer'])){
                $offerFileName = $this->printManager->offer($order, 'Pdf', true, !empty($options['showCode']));
                if (file_exists($offerFileName)){
                    $offerFile              = new MimePart(fopen($offerFileName, 'r'));
                    $offerFile->type        = 'application/pdf';
                    $offerFile->filename    = basename($offerFileName);
                    $offerFile->disposition = Mime::DISPOSITION_ATTACHMENT;
                    $offerFile->encoding    = Mime::ENCODING_BASE64;      

                    $parts[] = $offerFile;
                }
            }
            
            if (!empty($options['preorder'])){
                $preorderFileName = $this->printManager->preorder($order, 'Pdf', true, !empty($options['showCode']));
                if (file_exists($preorderFileName)){
                    $preorderFile              = new MimePart(fopen($preorderFileName, 'r'));
                    $preorderFile->type        = 'application/pdf';
                    $preorderFile->filename    = basename($preorderFileName);
                    $preorderFile->disposition = Mime::DISPOSITION_ATTACHMENT;
                    $preorderFile->encoding    = Mime::ENCODING_BASE64;      

                    $parts[] = $preorderFile;
                }
            }
        }    
        
        $body = new MimeMessage();
        $body->setParts($parts);
        
        $message = new Message();
        $message->setEncoding('UTF-8');
        $message->addTo($options['to']);
        $message->addFrom($options['from']);
        $message->setSubject($options['subject']);
        if ($options['copyMe']){
            $message->setCc($options['from']);
        }    

        $message->setBody($body);
        
        $contentTypeHeader = $message->getHeaders()->get('Content-Type');
        $contentTypeHeader->setType('multipart/related');

        $transport = new SmtpTransport();
        $smtpOptions   = new SmtpOptions([
//            'name'              => 'yandex',
//            'host'              => 'smtp.yandex.ru',
            'name'              => 'mail',
            'host'              => 'smtp.mail.ru',
            'port'              => 465,
            // Setup SMTP transport using LOGIN authentication
            'connection_class'  => 'login',
            'connection_config' => [
                'username' => $options['username'],
                'password' => $options['password'],
                'ssl'      => 'SSL',
            ],
        ]);
//        var_dump($options); exit;
        $transport->setOptions($smtpOptions);
        try {
            $transport->send($message);
        } catch (\Laminas\Mail\Protocol\Exception\RuntimeException $ex){
            return $ex->getMessage();
        }
        return 'ok';
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
        $emailFilter = new EmailFromStr();
        $fromEmail = $emailFilter->filter($data['from']);

        if ($fromEmail){
            $postLog = new PostLog();
            $postLog->setTo($data['to']);
            $postLog->setFrom($fromEmail);
            $postLog->setFromStr($data['from']);

            $postLog->setDateCreated(date('Y-m-d H:i:s', strtotime($data['date'])));
            $postLog->setStatus(PostLog::STATUS_ACTIVE);
            $postLog->setAct(PostLog::ACT_NO);

            if (isset($data['subject'])){
                 $postLog->setSubject($data['subject']);
            }

            $body = [];
            if (isset($data['content'])){
                if (is_array($data['content'])){
                    foreach ($data['content'] as $key => $value){
                        $txt = strip_tags($value);
                        if (strlen($txt) < 2048){
                            $body[$key] = $txt;
                        }            
                    }
                }    
                $postLog->setBody(\Laminas\Json\Json::encode($body));
            }    

            $fileNames = [];
            if (isset($data['attachment'])){
                $postLog->setAttachment(\Laminas\Json\Json::encode($data['attachment']));
            }

            $this->entityManager->persist($postLog);
            $this->entityManager->flush();
        }   
        
        return;
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
				$filename = urldecode($object->value);
			}
		}
	}

	if(!$filename && $part->ifparameters) {
		foreach($part->parameters as $object) {
			if(strtolower($object->attribute) == 'name') {
				$filename = urldecode($object->value);
			}
		}
	}
        
        $result = $filename;
        
        if (substr($filename, 0, 8) == '=?utf-8?'){
            $result = mb_decode_mimeheader($filename);
        }
        
        if (substr($filename, 0, 2) == '=?' || substr($result, 0, 2) == '=?'){
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
     * Список папок ящика
     * @param array $params
     */
    private function boxNames($params)
    {
        set_error_handler(function ($err_severity, $err_msg, $err_file, $err_line) {
//            $errMsg = $params['user'].PHP_EOL.$err_msg;
            throw new \ErrorException ($err_msg, 0, $err_severity, $err_file, $err_line);
        });

        $result = [];
        
        $mbox = imap_open($params['server'], $params['user'], $params['password'], OP_HALFOPEN);
        
        if ($mbox){
            $list = imap_list($mbox, $params['server'], "*");
            if (is_array($list)) {
                foreach ($list as $val) {
                    $result[] = preg_replace('/\{.*?\}/', '', mb_convert_encoding($val, 'UTF-8', 'UTF7-IMAP'));
                }
            } else {
                echo "вызов imap_list завершился с ошибкой: " . imap_last_error() . "\n";
            }
    //                $mailboxes = imap_list($connection, $params['server'], '*');
    //                var_dump($mailboxes); exit;
    //                array(6) {
    //                  [0]=>
    //                  string(35) "{imap.yandex.ru:993/imap/ssl}Drafts"
    //                  [1]=>
    //                  string(34) "{imap.yandex.ru:993/imap/ssl}INBOX"
    //                  [2]=>
    //                  string(35) "{imap.yandex.ru:993/imap/ssl}Outbox"
    //                  [3]=>
    //                  string(33) "{imap.yandex.ru:993/imap/ssl}Sent"
    //                  [4]=>
    //                  string(33) "{imap.yandex.ru:993/imap/ssl}Spam"
    //                  [5]=>
    //                  string(34) "{imap.yandex.ru:993/imap/ssl}Trash"
    //                }                
    //                array(6) {
    //                  [0]=>
    //                  string(53) "{imap.yandex.ru:993/imap/ssl}Отправленные"
    //                  [1]=>
    //                  string(47) "{imap.yandex.ru:993/imap/ssl}Исходящие"
    //                  [2]=>
    //                  string(37) "{imap.yandex.ru:993/imap/ssl}Спам"
    //                  [3]=>
    //                  string(47) "{imap.yandex.ru:993/imap/ssl}Удаленные"
    //                  [4]=>
    //                  string(47) "{imap.yandex.ru:993/imap/ssl}Черновики"
    //                  [5]=>
    //                  string(34) "{imap.yandex.ru:993/imap/ssl}INBOX"
    //                }
            imap_close($mbox);
        }    
        
        return $result;
    }
    
    /**
     * Чтение почтового ящика
     * @param array $params
     * server str {imap.yandex.ru:993/imap/ssl}
     * server str {imap.mail.ru:993/imap/ssl}
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
        ini_set('memory_limit', '512M');
        
//        $params['server'] = '{imap.yandex.ru:993/imap/ssl}';
        $params['server'] = '{imap.mail.ru:993/imap/ssl}';
        $nameDomain = explode("@", $params['user']);
        $domain = $nameDomain[1];
        if (strtolower($domain) == 'adminapl.ru'){
            $params['server'] = '{imap.mail.ru:993/imap/ssl}';            
        }
        
        $result = [];
        $imap_obj = $connection = null;
        
        if (!isset($params['folders'])){
            $params['folders'] = ['INBOX', 'Spam', 'Спам'];
        }            
        if (!isset($params['trash'])) $params['trash'] = ['Trash', 'Удаленные', 'Корзина'];
        
        $boxes = $this->boxNames($params);
//        var_dump($boxes); exit;
        
        if (is_array($params['folders'])){
            foreach ($params['folders'] as $foldername){
                
                if (!in_array($foldername, $boxes)){
                    continue;
                }

                $hostname = $params['server'].mb_convert_encoding($foldername, 'UTF7-IMAP', 'UTF-8');
                $connection = imap_open(
                        $hostname, 
                        $params['user'], 
                        $params['password']
                );
                
                if ($connection){
                    
                    $imap_obj = imap_check($connection);
                    

                    if ($imap_obj->Nmsgs){
    
//                        var_dump($imap_obj->Nmsgs); exit;
                        
                        $messageNumber = 1;
                        $msgNumbers = [];
                        
//                        var_dump($imap_obj->Nmsgs);
                        while ($messageNumber <= $imap_obj->Nmsgs){

                            $msgNumbers[] = $messageNumber;
                            
                            $structure = imap_fetchstructure($connection, $messageNumber);
                            $headers = imap_fetch_overview($connection, $messageNumber);

//                            var_dump($structure); exit;

                            $result[$messageNumber]['to'] = $params['user'];
                            
                            if (isset($headers[0])){
                                if (isset($headers[0]->to)){
                                    $result[$messageNumber]['to'] = iconv_mime_decode($headers[0]->to, ICONV_MIME_DECODE_CONTINUE_ON_ERROR);
                                }    
                                $result[$messageNumber]['from'] = iconv_mime_decode($headers[0]->from, ICONV_MIME_DECODE_CONTINUE_ON_ERROR);
                                $result[$messageNumber]['date'] = $headers[0]->date;
                                if (isset($headers[0]->subject)){
                                    $result[$messageNumber]['subject'] = iconv_mime_decode($headers[0]->subject, ICONV_MIME_DECODE_CONTINUE_ON_ERROR);                                    
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
                            
//                            var_dump($flattenedParts); exit;

                            if (count($flattenedParts)){

                                foreach($flattenedParts as $partNumber => $part) {
                                    
//                                    var_dump($part);
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
                                                if (!empty($part->subtype)){
                                                    $result[$messageNumber]['content'][$part->subtype] = $message;
                                                }    

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
//                                                            var_dump($attachment); exit;
                                                    // now do something with the attachment, e.g. save it somewhere

                                                    $temp_file = tempnam(sys_get_temp_dir(), 'Pst');
                                                    $fh = fopen($temp_file, 'w');
                                                    fwrite($fh, $attachment);
                                                    fclose($fh);                                

                                                    $result[$messageNumber]['attachment'][$partNumber] = [
                                                        'filename' => $filename,
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
                            }    

                            $messageNumber++;

                            if ($messageNumber > 150) {
                                break;
                            }
                        }
                        
                        foreach ($msgNumbers as $msgNumber){
                            if (!$params['leave_message']){
                                $move = false;
                                foreach ($params['trash'] as $trash){      
                                    if (!in_array($trash, $boxes)){
                                        continue;
                                    }                                        
                                    $move = imap_mail_move($connection, (string) $msgNumber, mb_convert_encoding($trash, 'UTF7-IMAP', 'UTF-8'));
                                }    
                                if (!$move){
                                    //imap_delete($connection, $msgNumber);                                
                                }    
                            }                                            
                        }
                    }    

                    imap_close($connection, CL_EXPUNGE);
                }    
            }    
        }    
        
        return $result;
    }
}
