<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service;

use Laminas\ServiceManager\ServiceManager;
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
        
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Contact manager
     * @var \Application\Service\ContactManager
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
    
    /**
     * Добавить клиента
     * @param array $data
     * @return Client
     */
    public function addNewClient($data) 
    {
        // Создаем новую сущность.
        $client = new Client();
        $client->setAplId($data['aplId']);
        $client->setName($data['name']);
        $client->setStatus($data['status']);
        
        $currentDate = date('Y-m-d H:i:s');
        $client->setDateCreated($currentDate);        
        
//        $currentUser = $this->entityManager->getRepository(User::class)
//                ->findOneByEmail($this->authService->getIdentity());
//        
//        $client->setManager($currentUser);
        
        
        // Добавляем сущность в менеджер сущностей.
        $this->entityManager->persist($client);
        
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
        
        return $client;
    }   
    
    /**
     * Обновить клиента
     * @param Client $client
     * @param arrray $data
     */
    public function updateClient($client, $data) 
    {
        $client->setAplId($data['aplId']);
        $client->setName($data['name']);
        $client->setStatus($data['status']);

        $this->entityManager->persist($client);
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
    }    
    
    /**
     * Возможность удаления
     * @param Client $client
     * @return boolean
     */
    public function isRemoveClient($client)
    {
        $rows = $this->entityManager->getRepository(Contact::class)
                ->count(['client' => $client->getId()]);
        if ($rows){
            return false;
        }
        
        return true;
    }
    
    public function removeClient($client) 
    {   
        
        $contacts = $client->getContacts();
        foreach ($contacts as $contact) {
            $this->contactManager->removeContact($contact);
        }        
        
        $carts = $client->getCart();
        foreach ($carts as $cart) {
            $this->entityManager->remove($cart);
        }               
        
        $this->entityManager->remove($client);
        
        $this->entityManager->flush();
    }    

    /**
     * Очистка клиентов
     * @return null
     */
    public function cleanClients()
    {        
        ini_set('memory_limit', '2048M');
        set_time_limit(1800);
        $startTime = time();
        $finishTime = $startTime + 1740;
        
        $clientsForCleaninig = $this->entityManager->getRepository(Client::class)
                ->findAllClient([]);
        
        $iterable = $clientsForCleaninig->iterate();
        
        foreach ($iterable as $row){
            foreach ($row as $client){
                if ($this->isRemoveClient($client)){
                    $this->removeClient($client);
                }   
                $this->entityManager->detach($client);
            }    
            if (time() >= $finishTime){
                break;
            }
        }
                
//        $this->entityManager->getConnection()->delete('contact', ['status' => Contact::STATUS_RETIRED]);
        
        return;
    }    
    
     // Этот метод добавляет новый контакт.
    public function addContactToClient($client, $data) 
    {
        $this->contactManager->addNewContact($client, $data);
    }   
    
    /**
     * Передаем клиента/ов другому менеджеру
     * @array of Application\Entitty\Client $clients
     * @var Application\Entity\User $manager
     */
    
    public function transferToManager($clients, $manager)
    {
        if (count($clients)){
            foreach ($clients as $client){
                $client->setManager($manager);
                 $this->entityManager->persist($client);
            }
            $this->entityManager->flush();
        }
        
    }
    
    /**
     * Объеденить с одинаковым aplId
     * @param Client $client
     * @return 
     */
    public function aplUnion($client)
    {
        if ($client->getAplId()){
            $clients = $this->entityManager->getRepository(Client::class)
                    ->findBy(['aplId' => $client->getAplId()]);
            if (count($clients) > 1){
                foreach ($clients as $oldClient){                    
                    if ($oldClient->getId() != $client->getId()){
                        $contact = $client->getLegalContact();
                        if ($contact){
                            foreach ($oldClient->getContacts() as $oldContact){
                                foreach ($oldContact->getPhones() as $phone){
                                    $this->entityManager->getConnection()
                                            ->update('phone', ['contact_id' => $contact->getId()], ['id' => $phone->getId()]);
                                }
                                foreach ($oldContact->getEmails() as $email){
                                    $this->entityManager->getConnection()
                                            ->update('email', ['contact_id' => $contact->getId()], ['id' => $email->getId()]);
                                }
                                
                                if ($this->contactManager->isRemoveContact($oldContact)){
                                    $this->contactManager->removeContact($oldContact);
                                }
                            }
                            if ($this->isRemoveClient($oldClient)){
                               // $this->removeClient($oldClient);
                            }            
                        }
                    }
                }
            }
        }
        return;
    }
}
