<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Service;

use Laminas\Http\Client;
use Laminas\Json\Json;
use Laminas\Log\Writer\Stream;
use Laminas\Log\Logger;

/**
 * Description of SoapManager
 * 
 * @author Daddy
 */
class SoapManager {
    
    const LOG_FOLDER = './data/log/'; //папка логов
    
    /**
     * Doctrine entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Admin manager
     * @var \Admin\Service\Adminmanager
     */
    private $adminManager;
    
    private $logFilename;

    public function __construct($entityManager, $adminManager)
    {
        $this->entityManager = $entityManager;
        $this->adminManager = $adminManager;

        $this->logFilename = $this::LOG_FOLDER.'soap_'.date('Ymd').'.log';
    }
    
    protected function api()
    {
        return 'https://autopartslist.ru/soap/';
        
    }
    
    /**
     * Транслятор с апл
     * 
     * @param string $uri
     * @return array
     */
    public function transapl($uri)
    {
        $writer = new Stream($this->logFilename);
        $logger = new Logger();
        $logger->addWriter($writer);
        
        $url = $uri;
        $client = new Client();
        $client->setUri($url);

//        $logger->info($url);
        $post = file_get_contents('php://input');
        if (!empty($post)){    
            $client->setMethod('POST');
            $client->setRawBody(str_replace( 'http://adminapl.ru/soap/index', 'https://autopartslist.ru/soap/index', $post));
            $client->setEncType('text/xml');
        } else {
            $client->setMethod('GET');            
        }

        try{
            $response = $client->send();
            $result = str_replace('https://autopartslist.ru/soap/index', 'http://adminapl.ru/soap/index', $response->getBody());
//                var_dump($response->getHeaders()); exit;
        } catch (\Laminas\Http\Client\Adapter\Exception\RuntimeException $e){
            $ok = true;
        } catch (\Laminas\Http\Client\Adapter\Exception\TimeoutException $e){
            $ok = true;
        }    
            
        unset($logger);
        return $result;        
    }
}
