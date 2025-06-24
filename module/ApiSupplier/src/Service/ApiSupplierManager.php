<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ApiSupplier\Service;

use Application\Entity\Supplier;
use Application\Entity\RequestSetting;
use Application\Entity\SupplierApiSetting;

/**
 * Description of ApiSupplierManager
 * 
 * @author Daddy
 */
class ApiSupplierManager {
    
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * 
     * @var \ApiSupplier\Service\MikadoManager
     */
    private $mikadoManager;

    public function __construct($entityManager, $mikadoManager)
    {
        $this->entityManager = $entityManager;
        $this->mikadoManager = $mikadoManager;
    }
    
    /**
     * Запустить все апи
     * 
     * @return null
     */
    public function reglament()
    {
        $this->mikadoManager->deliveriesToPtu(SupplierApiSetting::NAME_API_MIKADO);
        $this->mikadoManager->deliveriesToPtu(SupplierApiSetting::NAME_API_MIKADO_CL);
        
        return;
    }
}
