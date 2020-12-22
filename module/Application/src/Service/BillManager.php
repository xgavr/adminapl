<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service;

use Application\Entity\Supplier;
use Application\Entity\BillSetting;
use Application\Entity\Idoc;

/**
 * Description of CarService
 *
 * @author Daddy
 */
class BillManager
{
    
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    
    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    /**
     * Добавить документ
     * 
     * @param Supplier $supplier
     * @param array $data
     * @return Idoc
     */
    public function addIdoc($supplier, $data)
    {
        $idoc = new Idoc();
        $idoc->setName($data['name']);
        $idoc->setStatus($data['status']);
        $idoc->setDescription($data['description']);
        $idoc->setDateCreated(date('Y-m-d H:i:s'));
        $idoc->setDocKey(null);
        $idoc->setSupplier($supplier);
        
        $this->entityManager->persist();
        $this->entityManager->flush();
        
        return $idoc;
    }
    
    /**
     * Обновить документ
     * 
     * @param Idoc $idoc
     * @param array $data
     * @return idoc
     */
    public function updateIdoc($idoc, $data)
    {
        $idoc->setName($data['name']);
        $idoc->setStatus($data['status']);
        $idoc->setDescription($data['description']);
        $idoc->setDocKey($data['docKey']);
        
        $this->entityManager->persist();
        $this->entityManager->flush();
        
        return $idoc;
    }
    
    /**
     * Удалить документ
     * 
     * @param Idoc $idoc
     */
    public function removeIdoc($idoc)
    {
        
        $this->entityManager->remove($idoc);
        $this->entityManager->flush();
        
        return;
    }
    
}
