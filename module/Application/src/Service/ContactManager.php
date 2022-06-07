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
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
  
    /**
     * User manager.
     * @var \User\Service\User
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
        $phone = null;
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
                        $phone->setComment('');
                        if (!empty($data['comment'])){
                            $phone->setComment($data['comment']);                            
                        }

                        $currentDate = date('Y-m-d H:i:s');
                        $phone->setDateCreated($currentDate);

                        $this->entityManager->persist($phone);

                        $phone->setContact($contact);

                        if ($flushnow){
                            $this->entityManager->flush($phone);                
                        }
                    }    
                }    
            }
        }    
        return $phone;
    }
    
    public function addEmail($contact, $emailstr, $flushnow = false)
    {               
        $email = null;
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
                    $this->entityManager->flush($email);                
                }
            }    
        } 
        return $email;
    }
    
    public function addNewContact($parent, $data) 
    {
        // Создаем новую сущность.
        $contact = new Contact();
        $contact->setName((empty($data['name'])) ? 'NaN':$data['name']);
        
        $description = "";
        if (!empty($data['description'])){
            $description = $data['description'];
        }    
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
        
       if ($data['email'] && !empty($data['password'])){
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
    
    public function updateContact($contact, $data, $parent = null) 
    {
        $contact->setName($data['name']);
        $contact->setStatus($data['status']);
        $contact->setDescription(null);
        if (!empty($data['description'])){
            $contact->setDescription($data['description']);
        }    
        
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
        if ($parent){
            if ($parent instanceof \Application\Entity\Supplier ){
                $contact->setSupplier($parent);
            } elseif ($parent instanceof \Application\Entity\Client){
                $contact->setClient($parent);
            } elseif ($parent instanceof \Company\Entity\Office){
                $contact->setOffice($parent);
            } elseif ($parent instanceof \User\Entity\User) {
                $contact->setUser($parent); 
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
    
    /**
     * Можно ли удалить
     * @param Contact $contact
     * @return boolean
     */
    public function isRemoveContact($contact)
    {
        if ($contact->getSupplier()){
            return false;
        }
        if ($contact->getOffice()){
            return false;
        }
        if ($contact->getUser()){
            return false;
        }
        $rows = $this->entityManager->getRepository(Phone::class)
                ->count(['contact' => $contact->getId()]);
        if ($rows){
            return false;
        }
        $rows = $this->entityManager->getRepository(Email::class)
                ->count(['contact' => $contact->getId()]);
        if ($rows){
            return false;
        }
        $rows = $this->entityManager->getRepository(\Stock\Entity\Ot::class)
                ->count(['comiss' => $contact->getId()]);
        if ($rows){
            return false;
        }
        $rows = $this->entityManager->getRepository(\Application\Entity\Order::class)
                ->count(['contact' => $contact->getId()]);
        if ($rows){
            return false;
        }
        if (!$contact->getLegals()->isEmpty()){
            return false;
        }
        return true;
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
    
    
    /**
     * Очистка контактов
     * @return null
     */
    public function cleanContacts()
    {        
        ini_set('memory_limit', '2048M');
        set_time_limit(1800);
        $startTime = time();
        $finishTime = $startTime + 1740;
        
        $contactsForCleaninig = $this->entityManager->getRepository(Contact::class)
                ->findAllContact();
        
        $iterable = $contactsForCleaninig->iterate();
        
        foreach ($iterable as $row){
            foreach ($row as $contact){
                if ($this->isRemoveContact($contact)){
                    if ($contact->getStatus() != Contact::STATUS_RETIRED){
//                        $this->entityManager->getConnection()
//                                ->update('contact', ['status' => Contact::STATUS_RETIRED], ['id' => $contact->getId()]);
                        $this->removeContact($contact);
                    }                    
                } else {
                    if ($contact->getStatus() == Contact::STATUS_RETIRED){
                        $this->entityManager->getConnection()
                                ->update('contact', ['status' => Contact::STATUS_ACTIVE], ['id' => $contact->getId()]);
                    }                                        
                }   
                $this->entityManager->detach($contact);
            }    
            if (time() >= $finishTime){
                break;
            }
        }
                
//        $this->entityManager->getConnection()->delete('contact', ['status' => Contact::STATUS_RETIRED]);
        
        return;
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
