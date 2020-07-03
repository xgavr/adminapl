<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Service;

/**
 * Description of LogManager
 * 
 * @author Daddy
 */
class LogManager {
    
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
    }        
}
