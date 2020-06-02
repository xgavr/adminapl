<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service;

use Laminas\ServiceManager\ServiceManager;
use Application\Entity\Contact;
use Application\Entity\Phone;
use Application\Entity\Email;
use Application\Entity\Address;
use Application\Entity\Messenger;
use Company\Entity\Office;
use User\Entity\User;
use User\Filter\PhoneFilter;

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

    public function getParent($contact)
    {
        $result = [];
        
        if ($user = $contact->getUser()){
            $result['headTitle'] = 'Пользователи'; 
            $result['pageTitle'] = $user->getName();
            $result['route'] = 'users';
            $result['id'] = $user->getId();
        } elseIf($client = $contact->getUser()){
            $result['headTitle'] = 'Покупатели'; 
            $result['pageTitle'] = $client->getName();
            $result['route'] = 'clients';
            $result['id'] = $client->getId();            
        } elseif($supplier = $contact->getSupplier()){
            $result['headTitle'] = 'Поставщики'; 
            $result['pageTitle'] = $supplier->getName();
            $result['route'] = 'supplier';
            $result['id'] = $supplier->getId();                        
        } elseIf($office = $contact->getOffice()){
            $result['headTitle'] = 'Офисы'; 
            $result['pageTitle'] = $office->getName();
            $result['route'] = 'offices';
            $result['id'] = $office->getId();            
        }
        
        return $result;
        
    }

    public function addPhone($contact, $data, $flushnow = false)
    {                
        if (is_array($data)){
            if ($data['phone']){
                $filter = new PhoneFilter();
                $filter->setFormat(PhoneFilter::PHONE_FORMAT_DB);
                $findstr = $filter->filter($data['phone']);

                if ($findstr){
                    $phone = $this->entityManager->getRepository(Phone::class)
                            ->findOneByName($findstr);

                    if ($phone == null){
                        $phone = new Phone();            
                        $phone->setName($data['phone']);
                        $phone->setComment($data['comment']);

                        $currentDate = date('Y-m-d H:i:s');
                        $phone->setDateCreated($currentDate);

                        $this->entityManager->persist($phone);

                        $phone->setContact($contact);

                        if ($flushnow){
                            $this->entityManager->flush();                
                        }
                    }    
                }    
            }
        }    
    }
    
    public function addEmail($contact, $emailstr, $flushnow = false)
    {                
        if ($emailstr){
            
            $email = $this->entityManager->getRepository(Email::class)
                    ->findOneByName($emailstr);

            if ($email == null){
                $email = new Email();            
                $email->setContact($contact);
                $email->setName($emailstr);            

                $currentDate = date('Y-m-d H:i:s');
                $email->setDateCreated($currentDate);

                $contact->addEmail($email);

                $this->entityManager->persist($email);

                if ($flushnow){
                    $this->entityManager->flush();                
                }
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
        } elseif ($parent instanceof \Company\Entity\Office){
            $contact->setOffice($parent);
        } elseif ($parent instanceof \User\Entity\User) {
            $contact->setUser($parent); 
            if (!$data['name']){
                $contact->setName($data['full_name']);
            }    
        } else {
            throw new \Exception('Неверный тип родительской сущности');
        }

        if (isset($data['phone'])){
            $this->addPhone($contact, ['phone' => $data['phone']]);
        }    
        
        if (isset($data['email'])){
            $this->addEmail($contact, $data['email']);
        }    
        
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
        
        return $contact;
    }   
    
    public function updateContact($contact, $data) 
    {
        $contact->setName($data['name']);
        $contact->setDescription($data['description']);
        $contact->setStatus($data['status']);
        
        if (isset($data['phone'])){
            $this->addPhone($contact, ['phone' => $data['phone']]);
        }    
        
        if (isset($data['email']) && isset($data['password'])){
            $this->addEmail($contact, $data['email']);

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
        }   
        
        $this->entityManager->persist($contact);
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
    }    
    
    public function updateUserOffice($contact, $data) 
    {
        $office = $this->entityManager->getRepository(Office::class)
                ->findOneById($data['office']);
        
        if ($office){
            $contact->setOffice($office);
                
            $this->entityManager->persist($contact);
            // Применяем изменения к базе данных.
            $this->entityManager->flush();
        }    
    }    

    public function updateMessengers($contact, $data) 
    {
        $contact->setIcq($data['icq']);
        $contact->setTelegramm($data['telegramm']);
                
        $this->entityManager->persist($contact);
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
    }    
    
    
    public function updateSignature($contact, $data) 
    {
        $contact->setSignature($data['signature']);
                
        $this->entityManager->persist($contact);
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
    }    
    
    public function updatePhone($phone, $data)
    {                
        $phone->setName($data['phone']);
        $phone->setComment($data['comment']);

        $this->entityManager->persist($phone);
        $this->entityManager->flush();                
    }
    
    public function removePhone($phone)
    {
        $this->entityManager->remove($phone);
        $this->entityManager->flush();
        
    }
    
    public function updateEmail($email, $data)
    {                
        $email->setName($data['email']);

        $this->entityManager->persist($email);
        $this->entityManager->flush();                
    }
    
    public function removeEmail($email)
    {
        $this->entityManager->remove($email);
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
        
        $addresses = $contact->getAddresses();
        foreach ($addresses as $address) {
            $this->entityManager->remove($address);
        }        
        
        $messengers = $contact->getMessengers();
        foreach ($messengers as $messenger) {
            $this->entityManager->remove($messenger);
        }        
        
        $legals = $contact->getLegals();
        foreach ($legals as $legal) {
            $this->entityManager->removeLegalAssociation($legal);
        }        
        
        $this->entityManager->remove($contact);
        
        $this->entityManager->flush();
    }    
    
    public function addNewAddress($contact, $data, $flushnow = false)
    {                
        $address = new Address();            
        $address->setName($data['name']);
        $address->setAddress($data['address']);
        $address->setAddressSms($data['addressSms']);

        $currentDate = date('Y-m-d H:i:s');
        $address->setDateCreated($currentDate);

        $this->entityManager->persist($address);

        $address->setContact($contact);

        if ($flushnow){
            $this->entityManager->flush();                
        }
    }
        
    public function updateAddress($address, $data) 
    {
        $address->setName($data['name']);
        $address->setAddress($data['address']);
        $address->setAddressSms($data['addressSms']);
                
        $this->entityManager->persist($address);
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
    }        
    
    public function removeAddress($address) 
    {   
        
        $this->entityManager->remove($address);
        
        $this->entityManager->flush();
    }    
    
    
    public function addNewMessenger($contact, $data, $flushnow = false)
    {                
        $messenger = new Messenger();            
        $messenger->setIdent($data['ident']);
        $messenger->setStatus($data['status']);
        $messenger->setType($data['type']);

        $this->entityManager->persist($messenger);

        $messenger->setContact($contact);

        if ($flushnow){
            $this->entityManager->flush();                
        }
    }
        
    public function updateMessenger($messenger, $data) 
    {
        $messenger->setType($data['type']);
        $messenger->setStatus($data['status']);
        $messenger->setIdent($data['ident']);
                
        $this->entityManager->persist($messenger);
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
    }        
    
    public function removeMessenger($messenger) 
    {   
        
        $this->entityManager->remove($messenger);
        
        $this->entityManager->flush();
    }    
    
    
}
