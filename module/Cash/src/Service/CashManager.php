<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cash\Service;

use Cash\Entity\Cash;
use Company\Entity\Office;

/**
 * Description of CashManager
 * 
 * @author Daddy
 */
class CashManager {
    
    
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
        
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    /**
     * Новая касса
     * 
     * @param Office $office
     * @param array $data
     * @return Cash
     */
    public function addCash($office, $data)
    {
        $cash = new Cash();
        $cash->setAplId($data['aplId']);
        $cash->setCheckStatus($data['checkStatus']);
        $cash->setComission($data['comission']);
        $cash->setDateCreated(date('Y-m-d H:i:s'));
        $cash->setName($data['name']);
        $cash->setRestStatus($data['restStatus']);
        $cash->setStatus($data['status']);
        $cash->setTillStatus($data['tillStatus']);
        
        $cash->setOffice($office);
        $this->entityManager->persist($cash);
        $this->entityManager->flush();
        
        return $cash;
    }
    
    /**
     * Обновить кассу
     * 
     * @param Cash $cash
     * @param array $data
     * @return Cash
     */
    public function updateCash($cash, $data)
    {
        $cash->setAplId($data['aplId']);
        $cash->setCheckStatus($data['checkStatus']);
        $cash->setComission($data['comission']);
        $cash->setName($data['name']);
        $cash->setRestStatus($data['restStatus']);
        $cash->setStatus($data['status']);
        $cash->setTillStatus($data['tillStatus']);
        
        $this->entityManager->persist($cash);
        $this->entityManager->flush();
        
        return $cash;
    }    
    
    /**
     * Удалить кассу
     * @param Cash $cash
     * @return null
     */
    public function removeCash($cash)
    {
        return;
    }
}
