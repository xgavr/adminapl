<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ApiMarketPlace\Service;

use ApiMarketPlace\Exception\ApiMarketPlaceException;

/**
 * Description of YandexMarket
 * 
 * @author Daddy
 */
class YandexMarket {
    
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
    
    public function __construct($entityManager, $adminManager, $request, $updateManager)
    {
        $this->entityManager = $entityManager;
        $this->adminManager = $adminManager;
        $this->request = $request;
        $this->updateManager = $updateManager;
    }
    
    /**
     * Handle marketplace request from webhook
     *
     * @return bool
     *
     * @throws ApiMarketPlaceException
     */
    public function handle()
    {

        $this->input = $this->request::getInput();

        if (empty($this->input)) {
            throw new ApiMarketPlaceException('Input is empty!');
        }

        $post = json_decode($this->input, true);
        if (empty($post)) {
            throw new ApiMarketPlaceException('Invalid JSON!');
        }

        $updId = $this->updateManager->add(['post_data' => $post]);
        
        return $updId;
    }
    
    public function newOrder()
    {
        
    }    
}
