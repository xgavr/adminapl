<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ApiSupplier\Service;

use Application\Entity\Supplier;
use Application\Entity\RequestSetting;
use GuzzleHttp;

/**
 * Description of MskManager
 * 
 * @author Daddy
 */
class MskManager {
    
    const MSK_ID = 9;

    const REQUEST_SETTING_ID = 8;
    
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    private $adminManager;

    public function __construct($entityManager, $adminManager)
    {
        $this->entityManager = $entityManager;
        $this->adminManager = $adminManager;
    }
    
    /**
     * Login
     * @param array $options
     * phone string
     * text string
     * 
     */
    public function login()
    {
        $requestSetting = $this->entityManager->getRepository(RequestSetting::class)
                ->find($this::REQUEST_SETTING_ID);
        
        $uri = $requestSetting->getSiteNormalize().'login.lmz';
        $login = $requestSetting->getLogin();
        $password = $requestSetting->getPassword();
        
//        var_dump($uri); exit;
        $client = new GuzzleHttp\Client();
        $response = $client->request('POST', $uri,
            [
                'verify' => false,
                'allow_redirects' => true,
                'form_params' => [
                    'come_from' => '/index.lmz',
                    'username' => $login,
                    'password' => $password,
                    'submit' => 'Вход',
                ]
            ]
        );
        
        if ($response->getStatusCode() == 200){
            $uri1 = $requestSetting->getSiteNormalize().'set_agr.lmz';
//            $client->request('GET', $uri1,
//                [
//                    'verify' => false,
//                    'allow_redirects' => true,
//                    'delay' => 100,
//                    'query' => [
//                        'come_from' => '/index.lmz',
//                    ]
//                ]
//            );
//            $uri2 = $requestSetting->getSiteNormalize().'set_agr.lmz';
//            $client->request('GET', $uri2,
//                [
//                    'verify' => false,
//                    'allow_redirects' => true,
//                    'delay' => 100,
//                    'query' => [
//                        'agr_id' => 148183,
//                        'come_from' => '/index.lmz',
//                    ]
//                ]
//            );                
            $uri3 = $requestSetting->getSiteNormalize().'index.lmz';
            $response = $client->request('GET', $uri3,
                [
                    'verify' => false,
                    'allow_redirects' => true,
                    'delay' => 100,
                ]
            );                
            $result = $response->getBody()->read($response->getBody()->getSize());
        }
        
        return $result;
    }
    
}
