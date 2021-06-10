<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service;

use Laminas\ServiceManager\ServiceManager;
use Application\Entity\ContactCar;
use Application\Entity\Contact;

/**
 * Description of ContactCarService
 *
 * @author Daddy
 */
class ContactCarManager
{
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
  
    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager, $ftpManager)
    {
        $this->entityManager = $entityManager;
    }
    
    /**
     * Добавить машину контакта
     * @param Contact $contact
     * @param array $data
     * @return ContactCar
     */
    public function Add($contact, $data)
    {
        $contactCar = new ContactCar();
        $contactCar->setAplId(!empty($data['aplId']) ? $data['aplId'] : null);
        $contactCar->setComment(!empty($data['comment']) ? $data['comment'] : null);
        $contactCar->setVin(!empty($data['vin']) ? $data['vin'] : null);
        $contactCar->setVin2(!empty($data['vin2']) ? $data['vin2'] : null);
        $contactCar->setStatus(!empty($data['status']) ? $data['status'] : $this::STATUS_ACTIVE);
        $contactCar->setYocm(!empty($data['yocm']) ? $data['yocm'] : null);
        $contactCar->setWheel(!empty($data['wheel']) ? $data['wheel'] : $this::WHEEL_LEFT);
        $contactCar->setTm(!empty($data['tm']) ? $data['tm'] : $this::TM_UNKNOWN);
        $contactCar->setAc(!empty($data['ac']) ? $data['ac'] : $this::AC_UNKNOWN);
        $contactCar->setMd(!empty($data['md']) ? $data['md'] : null);
        $contactCar->setEd(!empty($data['ed']) ? $data['ed'] : null);
        $contactCar->setEp(!empty($data['ep']) ? $data['ep'] : null);
        $contactCar->setMake(!empty($data['make']) ? $data['make'] : null);
        $contactCar->setModel(!empty($data['model']) ? $data['model'] : null);
        $contactCar->setCar(!empty($data['car']) ? $data['car'] : null);
        $currentDate = date('Y-m-d H:i:s');
        $contactCar->setDateCreated($currentDate);        
        
        $contactCar->setContact($contact);
        
        $this->entityManager->persist($contactCar);
        $this->entityManager->flush();
        
        return $contactCar;
    }
    
    /**
     * Обновить машину контакта
     * @param ContactCar $contactCar
     * @param array $data
     * @return ContactCar
     */
    public function update($contactCar, $data)
    {
        $contactCar->setAplId(!empty($data['aplId']) ? $data['aplId'] : null);
        $contactCar->setComment(!empty($data['comment']) ? $data['comment'] : null);
        $contactCar->setVin(!empty($data['vin']) ? $data['vin'] : null);
        $contactCar->setVin2(!empty($data['vin2']) ? $data['vin2'] : null);
        $contactCar->setStatus(!empty($data['status']) ? $data['status'] : $this::STATUS_ACTIVE);
        $contactCar->setYocm(!empty($data['yocm']) ? $data['yocm'] : null);
        $contactCar->setWheel(!empty($data['wheel']) ? $data['wheel'] : $this::WHEEL_LEFT);
        $contactCar->setTm(!empty($data['tm']) ? $data['tm'] : $this::TM_UNKNOWN);
        $contactCar->setAc(!empty($data['ac']) ? $data['ac'] : $this::AC_UNKNOWN);
        $contactCar->setMd(!empty($data['md']) ? $data['md'] : null);
        $contactCar->setEd(!empty($data['ed']) ? $data['ed'] : null);
        $contactCar->setEp(!empty($data['ep']) ? $data['ep'] : null);
        $contactCar->setMake(!empty($data['make']) ? $data['make'] : null);
        $contactCar->setModel(!empty($data['model']) ? $data['model'] : null);
        $contactCar->setCar(!empty($data['car']) ? $data['car'] : null);
        
        $this->entityManager->persist($contactCar);
        $this->entityManager->flush($contactCar);
        
        return $contactCar;
    }
    
    /**
     * Удалить машину контакта
     * @param ContactCar $contactCar
     */
    public function remove($contactCar)
    {
        if ($contactCar->getOrders()->count()){
            return false;
        }
        $this->entityManager->remove($contactCar);
        $this->entityManager->flush();
        return true;
    }
}
