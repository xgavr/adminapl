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
use User\Entity\User;

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

    private $authService;
    
    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager, $contactManager, $userManager, $authService)
    {
        $this->entityManager = $entityManager;
        $this->contactManager = $contactManager;
        $this->userManager = $userManager;
        $this->authService = $authService;
    }
    
    public function addNewClient($data) 
    {
        // Создаем новую сущность.
        $client = new Client();
        $client->setName($data['name']);
        $client->setStatus($data['status']);
        
        $currentDate = date('Y-m-d H:i:s');
        $client->setDateCreated($currentDate);        
        
        $currentUser = $this->entityManager->getRepository(User::class)
                ->findOneByEmail($this->authService->getIdentity());
        
        $client->setManager($currentUser);
        
        
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
            $this->entityManager->remove($contact);
        }        
        
        $carts = $client->getCart();
        foreach ($carts as $cart) {
            $this->entityManager->remove($cart);
        }        
        
        $orders = $client->getOrder();
        foreach ($orders as $order) {
            $this->entityManager->remove($order);
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
