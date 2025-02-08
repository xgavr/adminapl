<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Ai\Service;

use Ramsey\Uuid\Uuid;
use Laminas\Http\Client;
use Laminas\Json\Decoder;
use Laminas\Json\Encoder;

/**
 * Description of DeepseekManager
 * 
 * @author Daddy
 */
class DeepseekManager {
    
    /**
     * Adapter
     */
    const HTTPS_ADAPTER = 'Laminas\Http\Client\Adapter\Curl';  
    
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
        
    public function __construct($entityManager, $adminManager)
    {
        $this->entityManager = $entityManager;
        $this->adminManager = $adminManager;
    }    
}
