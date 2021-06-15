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
use Application\Entity\Make;
use Application\Entity\Model;
use Application\Entity\Car;

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
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    /**
     * Добавить машину контакта
     * @param Contact $contact
     * @param array $data
     * @return ContactCar
     */
    public function add($contact, $data)
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
        
        $contactCar->setMake(null);
        if (isset($data['make'])){
            if (is_integer($data['make'])){
                $make = $this->entityManager->getRepository(Make::class)
                        ->find($data['make']);
                $contactCar->setMake($make);
            }
        }    
        $contactCar->setModel(null);
        if (isset($data['model'])){
            if (is_integer($data['model'])){
                $model = $this->entityManager->getRepository(Model::class)
                        ->find($data['model']);
                $contactCar->setModel($model);
            }
        }    
        $contactCar->setCar(null);
        if (isset($data['car'])){
            if (is_integer($data['car'])){
                $car = $this->entityManager->getRepository(Car::class)
                        ->find($data['car']);
                $contactCar->setCar($car);
            }
        }    

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

        $contactCar->setMake(null);
        if (isset($data['make'])){
            if (is_integer($data['make'])){
                $make = $this->entityManager->getRepository(Make::class)
                        ->find($data['make']);
                $contactCar->setMake($make);
            }
        }    
        $contactCar->setModel(null);
        if (isset($data['model'])){
            if (is_integer($data['model'])){
                $model = $this->entityManager->getRepository(Model::class)
                        ->find($data['model']);
                $contactCar->setModel($model);
            }
        }    
        $contactCar->setCar(null);
        if (isset($data['car'])){
            if (is_integer($data['car'])){
                $car = $this->entityManager->getRepository(Car::class)
                        ->find($data['car']);
                $contactCar->setCar($car);
            }
        }    
        
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
