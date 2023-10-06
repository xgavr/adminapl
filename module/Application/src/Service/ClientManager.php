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
use Application\Entity\Phone;
use Application\Entity\Email;
use User\Filter\PhoneFilter;
use Application\Entity\Comment;

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
        $client->setAplId((empty($data['aplId'])) ? 0:$data['aplId']);
        $client->setName((empty($data['name'])) ? 'NaN':$data['name']);
        $client->setStatus($data['status']);
        $client->setPricecol(empty($data['pricecol']) ? Client::PRICE_0: $data['pricecol']);
        
        $currentDate = date('Y-m-d H:i:s');
        $client->setDateCreated($currentDate);    
        
        // Добавляем сущность в менеджер сущностей.
        $this->entityManager->persist($client);
        
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
        
        return $client;
    }   
    
    /**
     * Добавить клиента
     * @param array $data
     * @return Client
     */
    public function addClient($data) 
    {
        
        $currentDate = date('Y-m-d H:i:s');
        
        $add = [
            'apl_id' => (empty($data['aplId'])) ? 0:$data['aplId'],
            'name' => (empty($data['name'])) ? 'NaN':$data['name'],
            'status' => $data['status'],
            'date_created' => $currentDate,
            'pricecol' => empty($data['pricecol']) ? Client::PRICE_0: $data['pricecol'],
        ];
                
        $this->entityManager->getConnection()
                ->insert('client', $add);
        $client = $this->entityManager->getRepository(Client::class)
                ->findOneBy([], ['id'=>'DESC'],1,0);
        
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
        $client->setPricecol(empty($data['pricecol']) ? Client::PRICE_0: $data['pricecol']);

        $this->entityManager->persist($client);
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
    }    
    
    /**
     * Обновить клиента
     * @param Client $client
     * @param arrray $data
     */
    public function updClient($client, $data) 
    {
        $upd = [
            'apl_id' => $data['aplId'],
            'name' => empty($data['name']) ? 'Nan':$data['name'],
            'status' => $data['status'],
            'pricecol' => empty($data['pricecol']) ? Client::PRICE_0: $data['pricecol'],
        ];
        $this->entityManager->getConnection()
                ->update('client', $upd, ['id' => $client->getId()]);
    }    
    
    /**
     * Поиск клиента по телефону
     * @param string $phoneName
     * @return Client 
     */
    public function findByPhoneName($phoneName)
    {
        $phoneFilter = new PhoneFilter(['format' => PhoneFilter::PHONE_FORMAT_DB]);
        $phone = $this->entityManager->getRepository(Phone::class)
                ->findByName($phoneFilter->filter($phoneName));
        if ($phone){
            $contact = $phone->getContact();
        }
        return;        
    }
    
    /**
     * Возможность удаления
     * @param Client $client
     * @return boolean
     */
    public function isRemoveClient($client)
    {
        $contactCount = $this->entityManager->getRepository(Contact::class)
                ->count(['client' => $client->getId()]);
        if ($contactCount){
            return false;
        }

        $commentCount = $this->entityManager->getRepository(Comment::class)
                ->count(['client' => $client->getId()]);
        if ($commentCount){
            return false;
        }    
        
        return true;
    }
    
    /**
     * Удаление клиента
     * @param Client $client
     */
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

        $comments = $client->getComments();
        foreach ($comments as $comment) {
            $this->entityManager->remove($comment);
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
        return $this->contactManager->addNewContact($client, $data);
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
     * Объеденить клинтов
     * @param Client $client
     * @param Client $oldClient
     */
    public function union($client, $oldClient)
    {
        $comments = $this->entityManager->getRepository(Comment::class)
                ->findBy(['client' => $oldClient->getId()]);
        foreach ($comments as $comment){
            $comment->setClient($client);
            $this->entityManager->persist($comment);
        }        
        
        $this->entityManager->flush();
    }
    
    /**
     * Объеденить с одинаковым aplId
     * @param Client $client
     * @return 
     */
    public function aplUnion($client)
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(1800);
        
        if ($client->getAplId()){
            $clients = $this->entityManager->getRepository(Client::class)
                    ->findBy(['aplId' => $client->getAplId()]);
            if (count($clients) > 1){
                foreach ($clients as $oldClient){                    
                    if ($oldClient->getId() != $client->getId()){
                        $contact = $client->getLegalContact();
                        if ($contact){
                            foreach ($oldClient->getContacts() as $oldContact){
                                                                
                                $this->contactManager->union($contact, $oldContact);
                                
                                $this->entityManager->refresh($oldContact);
                                if ($this->contactManager->isRemoveContact($oldContact)){
                                    $this->contactManager->removeContact($oldContact);
                                }
                            }                            
                        }
                        
                        $this->union($client, $oldClient);

                        $this->entityManager->refresh($oldClient);

                        if ($this->isRemoveClient($oldClient)){
                           $this->removeClient($oldClient);
                        }            
                    }
                }
            }
        }
        return;
    }
    
    /**
     * Очистка дублей Апл
     */
    public function clearDoubleApl()
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(1800);
        $startTime = time();
        $finishTime = $startTime + 1740;

        $doubles = $this->entityManager->getRepository(Client::class)
                ->findDoubleApl();
        
        foreach ($doubles as $row){
            $client = $this->entityManager->getRepository(Client::class)
                    ->findOneBy(['aplId' => $row['aplId']], ['id' => 'DESC']);
            if ($client){
                $this->aplUnion($client);
            }
            
            if (time() >= $finishTime){
                break;
            }
        }
        
        return;
    }
}
