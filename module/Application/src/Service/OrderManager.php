<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service;

use Zend\ServiceManager\ServiceManager;

/**
 * Description of OrderService
 *
 * @author Daddy
 */
class OrderManager
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
    
    public function addNewOrder($data) 
    {
        // Создаем новую сущность.
        $customer = new Order();
        $order->setName($data['name']);
        
        
        // Добавляем сущность в менеджер сущностей.
        $this->entityManager->persist($customer);
        
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
    }   
    
    public function updateOrder($customer, $data) 
    {
        $customer->setName($data['name']);

        $this->entityManager->persist($customer);
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
    }    
    
    public function removeOrder($customer) 
    {   
        $this->entityManager->remove($customer);
        
        $this->entityManager->flush();
    }    

}
