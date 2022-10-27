<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ApiMarketPlace\Service;

use Gam6itko\OzonSeller\Service\V2\CategoryService as CategoryServiceV2;
use Gam6itko\OzonSeller\Service\V3\CategoryService as CategoryServiceV3;
use GuzzleHttp\Client as GuzzleClient;
use Http\Adapter\Guzzle6\Client as GuzzleAdapter;
use Symfony\Component\HttpClient\Psr18Client;


/**
 * Description of OzonService
 * 
 * @author Daddy
 */
class OzonService {
    
    /**
     * Raw request data (json) for webhook methods
     *
     * @var string
     */
    protected $input;
    
    
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Admin manager.
     * @var \Admin\Service\AdminManager
     */
    private $adminManager;
    
    /**
     * Request.
     * @var \ApiMarketPlace\Service\Request
     */
    private $request;
    
    /**
     * Update.
     * @var \ApiMarketPlace\Service\Update
     */
    private $updateManager;
    
//    private $ozon_host = 'http://cb-api.ozonru.me/'; //sandbox
    
    public function __construct($entityManager, $adminManager, $request, $updateManager)
    {
        $this->entityManager = $entityManager;
        $this->adminManager = $adminManager;
        $this->request = $request;
        $this->updateManager = $updateManager;
    }
    
    public function сategoryTree()
    {
        $settings = $this->adminManager->getApiMarketPlaces();
        
        $config = [
            'clientId' => $settings['ozon_client_id'],
            'apiKey' => $settings['ozon_api_key'],
//            'host' => $this->ozon_host,
        ];
        
        $client = new Psr18Client();
        $svc = new CategoryServiceV2($config, $client);
        
        $categoryTree = $svc->tree();
//        $attributes = $svc->attribute(17038826);
        var_dump($categoryTree); exit;
        
        return $categoryTree;
    }
}
