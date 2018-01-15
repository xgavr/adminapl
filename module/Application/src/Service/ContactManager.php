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
  
    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
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

        if ($data['phone']){
            $phone = new Phone();            
            $phone->setContact($contact);
            $phone->setName($data['phone']);            
            $phone->setDateCreated($currentDate);

            $this->entityManager->persist($phone);

            $contact->addPhone($phone);
        }

        if ($data['email']){
            $email = new Email();            
            $email->setContact($contact);
            $email->setName($data['email']);            
            $email->setDateCreated($currentDate);

            $this->entityManager->persist($email);

            $contact->addEmail($email);
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

        $this->entityManager->persist($contact);
        // Применяем изменения к базе данных.
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
