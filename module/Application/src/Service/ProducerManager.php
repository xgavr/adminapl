<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service;

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
            if ($flushnow){
                $this->entityManager->flush();
            }    
        }  
        
        return $unknownProducer;
        
    }        
    
    /**
     * Добавление нового неизвестного производителя
     * 
     * @param Application\Entity\Rawprice $rawprice
     * @param bool $flush
     */
    public function addNewUnknownProducerFromRawprice($rawprice, $flush = true) 
    {
        $producerName = $rawprice->getProducer();
        
        if ($producerName){
            $unknownProducer = $this->entityManager->getRepository(UnknownProducer::class)
                        ->findOneByName($producerName);

            if ($unknownProducer == null){

                // Создаем новую сущность UnknownProducer.
                $unknownProducer = new UnknownProducer();
                $unknownProducer->setName($producerName);

                $currentDate = date('Y-m-d H:i:s');
                $unknownProducer->setDateCreated($currentDate);

                $this->entityManager->persist($unknownProducer);

            }    
            
            $rawprice->setUnknownProducer($unknownProducer);
            $this->entityManager->persist($rawprice);

            $this->entityManager->flush();
        }    
    }  
    
    /**
     * Выборка производителей из прайсов и добавление их в неизвестные производители
     */
    public function grabUnknownProducerFromRawprice()
    {
        //$startTime = time();
        $rawprices = $this->entityManager->getRepository(Producer::class)
                ->findRawpriceUnknownProducer();
        
        foreach ($rawprices as $rawprice){
            $this->addNewUnknownProducerFromRawprice($rawprice, false);
            //if (time() > $startTime + 25) break; //выйти через 20 сек
        }
        $this->entityManager->flush();
    }
    

    /**
     * Обновление неизвестного производителя
     * 
     * @param Application\Entity\UnknownProducer $unknownProducer
     * @param array $data
     */
    public function updateUnknownProducer($unknownProducer, $data) 
    {
        if ($data['producer']){    
            $producer = $this->entityManager->getRepository(Producer::class)
                        ->findOneById($data['producer']);
        } elseif ($data['producer_name']){
            $producer = $this->entityManager->getRepository(Producer::class)
                        ->findOneByName($data['producer_name']);            
        }    
        
        $unknownProducer->setProducer($producer);
               
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
    }    
    
    /**
     * Удаление неизвестного производителя
     * 
     * @param Application\Entity\UnknownProducer $unknownProducer
     */
    public function removeUnknownProducer($unknownProducer) 
    {   
        $this->entityManager->remove($unknownProducer);
        
        $this->entityManager->flush();
    }    
    
    /**
     * Случайная выборка из прайсов по id неизвестного производителя и id поставщика 
     * @param array $params
     * @return object      
     */
    public function randRawpriceBy($params)
    {
        return $this->entityManager->getRepository(UnknownProducer::class)
                ->randRawpriceBy($params);
    }
    
    /**
     * Поиск и удаление неизвестных производителей не привязаных к строкам прайсов
     */
    public function removeEmptyUnknownProducer()
    {
        $unknownProducersForDelete = $this->entityManager->getRepository(UnknownProducer::class)
                ->findUnknownProducerForDelete();

        foreach ($unknownProducersForDelete as $row){
            var_dump($row['rawpriceCount']);
            //$this->removeUnknownProducer($unknownProducer);
        }
        
        return count($unknownProducersForDelete);
    }
    
    public function searchProducerNameAssistant($search)
    {
        $result = [];    
        if (strlen($search) >= 1){
            $names = $this->entityManager->getRepository(Producer::class)
                    ->searchNameForSearchAssistant($search);

            foreach ($names as $name){
                $result[] = [
                    'value' => $name->getName(),
                    'text' => $name->getId(),
                ];
            }
        }
        
        return $result;
    }  
        
}
