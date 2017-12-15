<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service;

use Zend\ServiceManager\ServiceManager;

/**
 * Description of SupplierService
 *
 * @author Daddy
 */
class SupplierManager
{
    
    /**
     * Doctrine entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
  
    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    public function addNewSupplier($data) 
    {
        // Создаем новую сущность.
        $supplier = new Supplier();
        $supplier->setName($data['name']);
        $supplier->setName($data['info']);
        $supplier->setName($data['address']);        
        
        // Добавляем сущность в менеджер сущностей.
        $this->entityManager->persist($supplier);
        
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
    }   
    
    public function updateSupplier($supplier, $data) 
    {
        $supplier->setName($data['name']);
        $supplier->setName($data['info']);
        $supplier->setName($data['address']);

        $this->entityManager->persist($supplier);
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
    }    
    
    public function removeSupplier($supplier) 
    {   
        $this->entityManager->remove($supplier);
        
        $this->entityManager->flush();
    }    

}
