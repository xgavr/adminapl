<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service;

use Zend\ServiceManager\ServiceManager;
use Application\Entity\Client;
use Application\Entity\Contact;

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
    
    /**
     * Contact manager
     * @var Application\Service\ContactManager
     */
    private $contactManager;

  
    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager, $contactManager)
    {
        $this->entityManager = $entityManager;
        $this->contactManager = $contactManager;
    }
    
    public function addNewClient($data) 
    {
        // Создаем новую сущность.
        $client = new Client();
        $client->setName($data['name']);
        $client->setStatus($data['status']);
        
        $currentDate = date('Y-m-d H:i:s');
        $client->setDateCreated($currentDate);        
        
        
        
        // Добавляем сущность в менеджер сущностей.
        $this->entityManager->persist($client);
        
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
    }   
    
    public function updateClient($client, $data) 
    {
        $client->setName($data['name']);
        $client->setStatus($data['status']);

        $this->entityManager->persist($client);
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
    }    
    
    public function removeClient($client) 
    {   
        
        $contacts = $client->getContacts();
        foreach ($contacts as $contact) {
            $this->contactManager->remove($contact);
        }        
        
        $this->entityManager->remove($client);
        
        $this->entityManager->flush();
    }    

     // Этот метод добавляет новый контакт.
    public function addContactToClient($client, $data) 
    {
       $this->contactManager->addNewContact($client, $data);
    }   
    
}
