<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Service;

use Laminas\Http\Client;
use Laminas\Json\Json;

/**
 * Description of SoapManager
 * 
 * @author Daddy
 */
class SoapManager {
    
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

    public function __construct($entityManager, $adminManager)
    {
        $this->entityManager = $entityManager;
        $this->adminManager = $adminManager;
    }
    
    protected function api()
    {
        return 'https://autopartslist.ru/soap/';
        
    }
    
    /**
     * Транслятор с апл
     * 
     * @param string $uri
     * @param array $post
     * @return array
     */
    public function transapl($uri, $post)
    {
        $url = $uri;
        $client = new Client();
        $client->setUri($url);

        if ($post){     
            $client->setMethod('POST');
            $client->setParameterPost($post);            
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
            
        return $result;        
    }
}
