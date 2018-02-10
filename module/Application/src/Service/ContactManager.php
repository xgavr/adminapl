<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service;

use Zend\ServiceManager\ServiceManager;
use Application\Entity\Contact;
use Application\Entity\Phone;
use Application\Entity\Email;
use Application\Entity\Supplier;
use Application\Entity\Client;
use User\Entity\User;

/**
 * Description of ContactService
 *
 * @author Daddy
 */
class ContactManager
{
    
     /*
     * Id роли клиента
     */
    const USER_ROLE_ID = 2;
    
   
    /**
     * Doctrine entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
  
    /**
     * User manager.
     * @var User\Service\User
     */
    private $userManager;
  
    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager, $userManager)
    {
        $this->entityManager = $entityManager;
        $this->userManager = $userManager;
    }
    
    public function getClientUserRoleId()
    {
        return self::USER_ROLE_ID;
    }


    public function addPhone($contact, $phonestr, $flushnow = false)
    {
                
        $phone = $this->entityManager->getRepository(Phone::class)
                ->findOneByName($phonestr);

        if ($phone == null){
            $phone = new Phone();            
            $phone->setContact($contact);
            $phone->setName($phonestr);            
    
            $currentDate = date('Y-m-d H:i:s');
            $phone->setDateCreated($currentDate);

            $this->entityManager->persist($phone);

            $contact->addPhone($phone);
            
            if ($flushnow){
                $this->entityManager->flush();                
            }
        }    
        
    }
    
    public function addNewContact($parent, $data) 
    {
        // Создаем новую сущность.
        $contact = new Contact();
        $contact->setName($data['name']);
        
        $description = $data['description'];
        if (!$description) $description = "";
        $contact->setDescription($description);
        $contact->setStatus($data['status']);
        
        $currentDate = date('Y-m-d H:i:s');
        $contact->setDateCreated($currentDate);

        if ($parent instanceof \Application\Entity\Supplier ){
            $contact->setSupplier($parent);
        } elseif ($parent instanceof \Application\Entity\Client){
            $contact->setClient($parent);
        } elseif ($parent instanceof \User\Entity\User) {
            $contact->setUser($parent);            
            $contact->setName($data['full_name']);
        } else {
            throw ('Неверный тип родительской сущности');
        }

        $this->addPhone($contact, $data['phone']);
        
        if ($data['email']){
            $email = $this->entityManager->getRepository(Email::class)
                    ->findOneByName($data['email']);
            if ($email == null){
                $email = new Email();            
                $email->setContact($contact);
                $email->setName($data['email']);            
                $email->setDateCreated($currentDate);

                $this->entityManager->persist($email);

                $contact->addEmail($email);
            }    
        }
        // Добавляем сущность в менеджер сущностей.
        $this->entityManager->persist($contact);
        
       if ($data['email'] && $data['password']){
            $user = $this->entityManager->getRepository(User::class)
                    ->findOneByEmail($data['email']);
            if ($user == null){
                $data['full_name'] = $data['name'];
                $data['roles'][] = self::USER_ROLE_ID;
                $user = $this->userManager->addUser($data);
                $contact->setUser($user);
            }   
       }
        // Добавляем сущность в менеджер сущностей.
        $this->entityManager->persist($contact);
        
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
    }   
    
    public function updateContact($contact, $data) 
    {
        $contact->setName($data['name']);
        $contact->setDescription($data['description']);
        $contact->setStatus($data['status']);
        
        $this->addPhone($contact, $data['phone']);
        
        if ($data['email']){
            $email = $this->entityManager->getRepository(Email::class)
                    ->findOneByName($data['email']);
            if ($email == null){
                $email = new Email();            
                $email->setContact($contact);
                $email->setName($data['email']);            
                $email->setDateCreated($currentDate);

                $this->entityManager->persist($email);

                $contact->addEmail($email);
            }
        }
        
       if ($data['email'] && $data['password']){
            $user = $this->entityManager->getRepository(User::class)
                    ->findOneByEmail($data['email']);
            if ($user == null){
                $data['full_name'] = $data['name'];
                $data['roles'][] = self::USER_ROLE_ID;
                $user = $this->userManager->addUser($data);
            }    
            $contact->setUser($user);
       }
        
        $this->entityManager->persist($contact);
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
    }    
    
    
    public function removePhone($phone)
    {
        $this->entityManager->remove($phone);
        $this->entityManager->flush();
        
    }
    
    public function removeContact($contact) 
    {   
        
        $phones = $contact->getPhones();
        foreach ($phones as $phone) {
            $this->entityManager->remove($phone);
        }        
        
        $emails = $contact->getEmails();
        foreach ($emails as $email) {
            $this->entityManager->remove($email);
        }        
        $this->entityManager->remove($contact);
        
        $this->entityManager->flush();
    }    
    
}
