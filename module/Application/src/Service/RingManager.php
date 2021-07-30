<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service;

use Application\Entity\Ring;
use Application\Entity\Contact;
use Application\Entity\Client;
use Application\Entity\ContactCar;
use User\Entity\User;
use Application\Entity\Order;
use Company\Entity\Office;
use Application\Entity\RingHelpGroup;
use Application\Entity\RingHelp;

/**
 * Description of RingManager
 *
 * @author Daddy
 */
class RingManager
{
    /**
     * User current
     * @var \Application\Entity\User
     */
    private $user;
    
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Authentication service.
     * @var \Laminas\Authentication\AuthenticationService 
     */
    private $authService;    
    
    /**
     * Client manager.
     * @var \Application\Service\ClientManager
     */
    private $clientManager;    

    /**
     * Contact manager.
     * @var \Application\Service\ContactManager
     */
    private $contactManager;    
    
    /**
     * ContactCar manager.
     * @var \Application\Service\ContactCarManager
     */
    private $contactCarManager;    

    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager, $authService, $contactManager,
            $clientManager, $contactCarManager)
    {
        $this->user = null;
        $this->entityManager = $entityManager;
        $this->authService = $authService;
        $this->contactManager = $contactManager;
        $this->clientManager = $clientManager;
        $this->contactCarManager = $contactCarManager;
    }
    
    /**
     * Получить текущего пользователя
     * 
     * @param bool $useCachedUser
     * @return User
     * @throws \Exception
     */
    private function currentUser($useCachedUser = true)
    {
        if ($useCachedUser && $this->user!==null){
            return $this->user;
        }    
        
        if ($this->authService->hasIdentity()) {
            
            $this->user = $this->entityManager->getRepository(User::class)
                    ->findOneByEmail($this->authService->getIdentity());
            if ($this->user==null) {
                throw new \Exception('Not found user with such email');
            }
            
            // Return found User.
            return $this->user;
        }
        
        return null;        
    }    
     
    /**
     * Добавить нового клиента и его контакт
     * 
     * @param array $data
     * @return Integer
     */
    private function ringContact($data)
    {
        $contactId = empty($data['contact']) ? null:$data['contact'];
        if ($contactId){
            $contact = $this->entityManager->getRepository(Contact::class)
                    ->find($contactId);        
            if ($contact){
                return $contact;
            }
        }    
        if ($data['mode'] == Ring::MODE_NEW_ORDER){
            $client = $this->clientManager->addClient([
                'name' => empty($data['name']) ? 'NaN':$data['name'],
                'status' => Client::STATUS_ACTIVE,
            ]);
            $contact = $this->contactManager->addNewContact($client, [
                'name' => $client->getName(),
                'status' => Contact::STATUS_ACTIVE,
                'phone' => empty($data['phone1']) ? null:$data['phone1'],
            ]);
            
            if (!empty($data['phone2'])){
                $this->contactManager->addPhone($contact, [
                    'phone' => $data['phone2'],
                ]);
            }
            
            return $contact;
        }
        
        return;
    }
    
    /**
     * Найти машину клиента
     * @param Contact $contact
     * @param data $data
     * 
     * @return ContactCar
     */
    private function ringContactCar($contact, $data)
    {
        if ($data['mode'] == Ring::MODE_NEW_ORDER && $contact){
            $contactCarId = empty($data['contactCar']) ? null:$data['contactCar'];
            if ($contactCarId){
                $contactCar = $this->entityManager->getRepository(ContactCar::class)
                        ->find($contactCarId);        
                if ($contactCar){
                    return $contactCar;
                }
            }
        
            $contactCar = $this->contactCarManager->add($contact, [
                'make' => empty($data['make']) ? null:$data['make'],
                'vin' => empty($data['vin']) ? null:$data['vin'],
            ]);
            
            return $contactCar;
        }
        
        return;
    }
    
    /**
     * Найти менеджера
     * @param data $data
     * 
     * @return User
     */
    private function ringManager($data)
    {
        $managerId = empty($data['manager']) ? null:$data['manager'];
        if ($managerId){
            $manager = $this->entityManager->getRepository(User::class)
                    ->find($managerId);
            return $manager;
        }
        return;
    }
    
    /**
     * Найти заказ
     * @param data $data
     * 
     * @return User
     */
    private function ringOrder($data)
    {
        $orderId = empty($data['order']) ? null:$data['order'];
        if ($orderId){
            $order = $this->entityManager->getRepository(Order::class)
                    ->find($orderId);

            return $order;
        }
        
        return;
    }
    
    /**
     * Найти офис
     * @param data $data
     * 
     * @return Office
     */
    private function ringOffice($data)
    {
        $officeId = empty($data['office']) ? null:$data['office'];
        if ($officeId){
            $office = $this->entityManager->getRepository(Office::class)
                    ->find($officeId);

            return $office;
        }
        
        $office = $this->entityManager->getRepository(Office::class)
                ->findDefaultOffice();
        
        return $office;
    }
    
    /**
     * Добавить новый звонок
     * 
     * @param array $data
     * @return Ring
     */
    public function addRing($data)
    {
        $ring = new Ring();
        
        $contact = $this->ringContact($data);
        $contactCar = $this->ringContactCar($contact, $data);
                
        $ring->setContact($contact);
        $ring->setContactCar($contactCar);
        $ring->setDateCreated(date('Y-m-d H:i:s'));
        $ring->setGds(empty($data['gds']) ? null:$data['gds']);
        $ring->setInfo(empty($data['info']) ? null:$data['info']);
        $ring->setManager($this->ringManager($data));
        $ring->setMode($data['mode']);
        $ring->setName(empty($data['name']) ? null:$data['name']);
        $ring->setOffice($this->ringOffice($data));
        $ring->setOrder($this->ringOrder($data));
        $ring->setPhone(empty($data['phone']) ? null:$data['phone']);
        $ring->setStatus(empty($data['status']) ? Ring::STATUS_ACTIVE:$data['status']);
        $ring->setUser($this->currentUser());
        $ring->setVin(empty($data['vin']) ? null:$data['vin']);
        
        $this->entityManager->persist($ring);
        $this->entityManager->flush($ring);
        
        return $ring;
    }
    
    /**
     * Добавить подсказку
     * 
     * @param array $data
     * @return RingHelp
     */
    public function addHelp($data)
    {
        $help = new RingHelp();
        $help->setInfo($data['info'] ?? null);
        $help->setMode($data['mode']);
        $help->setName($data['name']);
        $help->setSort($data['sort'] ?? 100);
        $help->setStatus($data['status'] ?? RingHelpGroup::STATUS_ACTIVE);
        
        $help->setRingHelpGroup($data['helpGroup']);
        
        $this->entityManager->persist($help);
        $this->entityManager->flush($help);
        
        return $help;
    }
    
    /**
     * Обновить подсказку
     * 
     * @param RingHelp $help
     * @param array $data
     * @return RingHelp
     */
    public function updateHelp($help, $data)
    {
        $help->setInfo($data['info'] ?? null);
        $help->setMode($data['mode']);
        $help->setName($data['name']);
        $help->setSort($data['sort']);
        $help->setStatus($data['status']);
        
        $help->setRingHelpGroup($data['helpGroup']);
        
        $this->entityManager->persist($help);
        $this->entityManager->flush($help);
        
        return $help;
    }
    
    /**
     * Удалить подсказку
     * 
     * @param RingHelp $help
     * @return type
     */
    public function removeHelp($help)
    {
        
        $this->entityManager->remove($help);
        $this->entityManager->flush();
        
        return;
    }
    
    /**
     * Добавить группу подсказок
     * @param array $data
     * @return RingHelpGroup
     */
    public function addHelpGroup($data)
    {
        $helpGroup = new RingHelpGroup();
        $helpGroup->setInfo($data['info'] ?? null);
        $helpGroup->setMode($data['mode']);
        $helpGroup->setName($data['name']);
        $helpGroup->setSort($data['sort'] ?? 100);
        $helpGroup->setStatus($data['status'] ?? RingHelpGroup::STATUS_ACTIVE);
        
        $this->entityManager->persist($helpGroup);
        $this->entityManager->flush($helpGroup);
        
        return $helpGroup;
    }
    
    /**
     * Обновить группу подсказок
     * 
     * @param RingHelpGroup $helpGroup
     * @param array $data
     * @return RingHelpGroup
     */
    public function updateHelpGroup($helpGroup, $data)
    {
        $helpGroup->setInfo($data['info'] ?? null);
        $helpGroup->setMode($data['mode']);
        $helpGroup->setName($data['name']);
        $helpGroup->setSort($data['sort']);
        $helpGroup->setStatus($data['status']);
        
        $this->entityManager->persist($helpGroup);
        $this->entityManager->flush($helpGroup);
        
        return $helpGroup;
    }
    
    /**
     * Удалить группу подсказок
     * 
     * @param RingHelpGroup $helpGroup
     * @return type
     */
    public function removeHelpGroup($helpGroup)
    {
        foreach ($helpGroup->getRingHelps() as $help){
            $this->removeHelp($help);
        }
        
        $this->entityManager->remove($helpGroup);
        $this->entityManager->flush();
        
        return;
    }
}
