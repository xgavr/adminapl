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
    
    /*
     * Id роли представителя клиента
     */
    const USER_ROLE_ID = 2;
    
    
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

    /**
     * User manager
     * @var Application\Service\UserManager
     */
    private $userManager;

    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager, $contactManager, $userManager)
    {
        $this->entityManager = $entityManager;
        $this->contactManager = $contactManager;
        $this->userManager = $userManager;
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
        
        return $client;
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
       if ($data['email'] && $data['password']){
           $data['full_name'] = $data['name'];
           $data['roles'][] = self::USER_ROLE_ID;
           $user = $this->userManager->addUser($data);
           foreach ($user->getContacts() as $contact){
               $contact->setClient($client);
               $this->entityManager->persist($contact);               
           }
            $this->entityManager->flush();           
       } else {
           $this->contactManager->addNewContact($client, $data);
       }    
    }   
    
}
