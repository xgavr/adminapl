<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Fasade\Service;

use Search\Entity\SearchToken;
use Search\Entity\SearchTitle;
use Application\Filter\Lemma;
use Application\Filter\Tokenizer;
use Application\Entity\Bigram;
use Application\Entity\Token;
use Search\Entity\SearchLog;
use Application\Entity\Goods;
use Application\Entity\Oem;

/**
 * Description of FasadeManager
 * 
 * @author Daddy
 */
class FasadeManager {
    
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
