<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service;

use Zend\ServiceManager\ServiceManager;
use Application\Entity\Tax;
use Application\Entity\Country;
use Application\Entity\Producer;
use Application\Entity\UnknownProducer;

/**
 * Description of RbService
 *
 * @author Daddy
 */
class ProducerManager
{
    
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
    
    public function addNewProducer($data) 
    {
        // Создаем новую сущность Producer.
        $producer = new Producer();
        $producer->setName($data['name']);

        $country = $this->entityManager->getRepository(Country::class)
                    ->findOneById($data['country']);
        if ($country == null){
            $country = new Country();
        }

        $producer->setCountry($country);
        
        // Добавляем сущность в менеджер сущностей.
        $this->entityManager->persist($producer);
        
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
    }   
    
    public function updateProducer($producer, $data) 
    {
        $producer->setName($data['name']);
        
        $country = $this->entityManager->getRepository(Country::class)
                    ->findOneById($data['country']);
        if ($country == null){
            $country = new Country();
        }
        
        $producer->setCountry($country);
               
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
    }    
    
    public function removeProducer($producer) 
    {   
        $newProducer = new Producer();
        foreach ($producer->getUnknownProducer() as $unknownProducer){
            $unknownProducer->setProducer($newProducer);
            $this->entityManager->persist($unknownProducer);
        }
        
        $this->entityManager->remove($producer);
        
        $this->entityManager->flush();
    }    
    
    /*
     * Создать производителя из неизвестного производителя
     *@var Application\Entity\UnknownProducer $unknownProducer
     *  
     */
    
    public function addProducerFromUnknownProducer($unknownProducer)
    {
       if ($unknownProducer->getName() && !$unknownProducer->getProducer()){
           
           $producer = new Producer();
           $producer->setName($unknownProducer->getName());
           
           $this->entityManager->persist($producer);
           
           $unknownProducer->setProducer($producer);
           $this->entityManager->persist($unknownProducer);
           
           $this->entityManager->flush();
       } 
    }
    
    /*
     * 
     */
    public function bindUnknownProducer($unknownProducer, $producer)
    {
        if ($unknownProducer->getName()){
            
           $unknownProducer->setProducer($producer);
           $this->entityManager->persist($unknownProducer);
           
           $this->entityManager->flush();            
        }
    }        
    
    /*
     * @string $name
     * @bool flushnow
     */
    public function addUnknownProducer($name, $flushnow = true)
    {
        $unknownProducer = $this->entityManager->getRepository(UnknownProducer::class)
                    ->findOneByName($name);

        if ($unknownProducer == null){

            // Создаем новую сущность UnknownProducer.
            $unknownProducer = new UnknownProducer();
            $unknownProducer->setName($name);

            $currentDate = date('Y-m-d H:i:s');
            $unknownProducer->setDateCreated($currentDate);

//            $producer = new Producer();
//            $unknownProducer->setProducer($producer);

            // Добавляем сущность в менеджер сущностей.
            $this->entityManager->persist($unknownProducer);

            // Применяем изменения к базе данных.
//            if ($flushnow){
                $this->entityManager->flush();
//            }    
        }  
        
        return $unknownProducer;
        
    }        
    
    
    /*
     * @var Application\Entity\Rawprice $rawprice
     */
    public function addNewUnknownProducerFromRawprice($rawprice) 
    {
        $producername = $rawprice->getProducer();
        
        if ($producername){
            $unknownProducer = $this->entityManager->getRepository(UnknownProducer::class)
                        ->findOneByName($producername);

            if ($unknownProducer == null){

                // Создаем новую сущность UnknownProducer.
                $unknownProducer = new UnknownProducer();
                $unknownProducer->setName($producername);

                $currentDate = date('Y-m-d H:i:s');
                $unknownProducer->setDateCreated($currentDate);

                $producer = new Producer();

                $unknownProducer->setProducer($producer);

                // Добавляем сущность в менеджер сущностей.
                $this->entityManager->persist($unknownProducer);

                // Применяем изменения к базе данных.
                $this->entityManager->flush();
            }    
        }    
    }   
    
    public function updateUnknownProducer($unknownProducer, $data) 
    {

        $producer = $this->entityManager->getRepository(Producer::class)
                    ->findOneById($data['producer']);
        if ($producer == null){
            $producer = new Producer();
        }
        
        $unknownProducer->setProducer($producer);
               
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
    }    
    
    public function removeUnknownProducer($unknownProducer) 
    {   
        $this->entityManager->remove($unknownProducer);
        
        $this->entityManager->flush();
    }    
    
}
