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
        $result = null;
                
        if (is_array($post)){     
            $url = $this->api().$uri;
            $client = new Client();
            $client->setUri($url);
            $client->setMethod('POST');
            $client->setParameterPost($post);

            try{
                $response = $client->send();
                $result = $response->getBody();
            } catch (\Laminas\Http\Client\Adapter\Exception\RuntimeException $e){
                $ok = true;
            } catch (\Laminas\Http\Client\Adapter\Exception\TimeoutException $e){
                $ok = true;
            }    
            
            if ($ok){
            }
        }    
        return $result;        
    }
}
