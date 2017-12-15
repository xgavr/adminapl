<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service;

use Zend\ServiceManager\ServiceManager;

/**
 * Description of ClientService
 *
 * @author Daddy
 */
class ClientManager
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
    
    public function addNewClient($data) 
    {
        // Создаем новую сущность.
        $customer = new Client();
        $client->setName($data['name']);
        
        
        // Добавляем сущность в менеджер сущностей.
        $this->entityManager->persist($customer);
        
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
    }   
    
    public function updateClient($customer, $data) 
    {
        $customer->setName($data['name']);

        $this->entityManager->persist($customer);
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
    }    
    
    public function removeClient($customer) 
    {   
        $this->entityManager->remove($customer);
        
        $this->entityManager->flush();
    }    

}
