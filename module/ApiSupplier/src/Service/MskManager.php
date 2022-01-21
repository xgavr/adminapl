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
    
    const USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.110 Safari/537.36 OPR/82.0.4227.43';
    
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
            $client->request('GET', $uri1,
                [
                    'verify' => false,
                    'allow_redirects' => true,
                    'delay' => 100,
                    'query' => [
                        'come_from' => '/index.lmz',
                    ]
                ]
            );
            $uri2 = $requestSetting->getSiteNormalize().'set_agr.lmz';
            $client->request('GET', $uri2,
                [
                    'verify' => false,
                    'allow_redirects' => true,
                    'delay' => 100,
                    'query' => [
                        'agr_id' => 148183,
                        'come_from' => '/index.lmz',
                    ]
                ]
            );                
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
    
    /**
     * Читаем урл
     * @param string $url
     * @param string $poststr
     */
    public function readUrl($url, $poststr = null)
    {
        $userAgent = $this::USER_AGENT;
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
	   // откуда пришли на эту страницу
        curl_setopt($ch, CURLOPT_REFERER, $url);
        curl_setopt($ch, CURLOPT_HEADER, 1); 

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        if ($poststr){
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $poststr);
        } else {
           //запрещаем делать запрос с помощью POST и соответственно разрешаем с помощью GET
           curl_setopt($ch, CURLOPT_POST, 0);
        }	
	   
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	   //отсылаем серверу COOKIE полученные от него при авторизации
//        curl_setopt($ch, CURLOPT_COOKIEFILE, $_SERVER['DOCUMENT_ROOT'].'/cookie.txt');
        curl_setopt($ch, CURLOPT_USERAGENT, "$userAgent");
        curl_setopt($ch, CURLOPT_POSTREDIR, 3);

        $result = curl_exec($ch);

        curl_close($ch);

        if ($output === FALSE) {
            return "cURL Error: " . curl_error($ch);
        }    
        return $result;	           
    }
    
    /**
     * curl Логин
     */
    public function curlLogin()
    {
        $requestSetting = $this->entityManager->getRepository(RequestSetting::class)
                ->find($this::REQUEST_SETTING_ID);
        
        $uri = $requestSetting->getSiteNormalize().'login.lmz';
        $login = $requestSetting->getLogin();
        $password = $requestSetting->getPassword();
        $userAgent = $this::USER_AGENT;
        $postData = [
            'come_from' => '/index.lmz',
            'username' => $login,
            'password' => $password,
            'submit' => '%C2%F5%EE%E4',
        ];
        
//        var_dump($postData); exit;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $uri);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, 1); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
//        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "User-Agent: $userAgent",
                "Accept-Language: ru,en-US;q=0.9,en;q=0.8"
            ));     
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTREDIR, 3);
//        curl_setopt($ch, CURLOPT_USERPWD, "$login:$password");
//        curl_setopt($ch, CURLOPT_UNRESTRICTED_AUTH, 1);
                
        $output = curl_exec($ch);
        curl_close($ch);
        
        if ($output === FALSE) {
            echo "cURL Error: " . curl_error($ch);
        } else {  
            usleep(100);
            $output = $this->readUrl($requestSetting->getSiteNormalize().'set_agr.lmz?come_from=/index.lmz');
            usleep(100);
            $output = $this->readUrl($requestSetting->getSiteNormalize().'set_agr.lmz?agr_id=148183;come_from=/index.lmz');
            usleep(100);
            $output = $this->readUrl($requestSetting->getSiteNormalize().'/index.lmz');
            //$output = $this->readUrl($requestSetting->getSiteNormalize().'set_agr.lmz');
        }
        return $output;
    }
    
}
