<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service;

use Zend\ServiceManager\ServiceManager;
use Application\Entity\Make;
use Application\Entity\Model;

/**
 * Description of MakeService
 *
 * @author Daddy
 */
class MakeManager
{
    
    /**
     * Doctrine entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
  
    /**
     * External manager.
     * @var Application\Entity\ExternalManager
     */
    private $externalManager;
  
    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager, $externalManager)
    {
        $this->entityManager = $entityManager;
        $this->externalManager = $externalManager;
    }
    
    /**
     * Добавление/обновление машины
     * 
     * @param array $data
     * @param array $group
     * @return type
     */
    public function addMake($data, $group)
    {
        $row = [
            'td_id' => $data['tdId'],
            'apl_id' => $data['aplId'],
            'name' => $data['name'],
            'fullname' => '',
            'passenger' => Make::PASSENGER_NO,
            'commerc' => Make::COMMERC_NO,
            'moto' => Make::MOTO_NO,
            'status' => Make::STATUS_ACTIVE,
        ];
        
        $make = $this->entityManager->getRepository(Make::class)
                ->findOneBy(['tdId' => $data['tdId']]);
        
        if (!$make){
            try{
                $this->entityManager->getRepository(Make::class)
                            ->insertMake($row);
            } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e){
                //дубликат
            }   

            $make = $this->entityManager->getRepository(Make::class)
                    ->findOneBy(['tdId' => $data['tdId']]);
        }    

        if ($make){
            $this->entityManager->getRepository(Make::class)
                ->updateMake($make, $group);
        }      
            

//            $this->entityManager->persist($make);
//            $this->entityManager-flush();
        
        return $make;        
    }
   
    /**
     * Заполнить машины из массива
     * 
     * @param array $data
     * @param array $group
     */
    private function fillMakesFromArray($data, $group)
    {
        foreach ($data as $row){
            $make = $this->addMake([
                'tdId' => $row['id'],
                'aplId' => 0,
                'name' => $row['name'],
                'fullName' => '',
            ], $group);
//            var_dump($make); exit;
        }
        
    }
    
    /**
     * Заполнить машины
     * 
     * @return null
     */
    public function fillMakes()
    {
        $data1 = $this->externalManager->partsApi('makes', ['group' => 'passenger']);
        $this->fillMakesFromArray($data1,['passenger' => Make::PASSENGER_YES]);
        $data2 = $this->externalManager->partsApi('makes', ['group' => 'commercial']);
        $this->fillMakesFromArray($data2,['commerc' => Make::COMMERC_YES]);
        $data3 = $this->externalManager->partsApi('makes', ['group' => 'moto']);
        $this->fillMakesFromArray($data3,['moto' => Make::MOTO_YES]);
        return;
    }

    /**
     * Добавление/обновление моделей машины
     * 
     * @param Application\Entity\Make $make
     * @param array $data
     * @param array $group
     * @return type
     */
    public function addModel($make, $data, $group)
    {
        $row = [
            'make_id' => $make->getId(),
            'td_id' => $data['tdId'],
            'apl_id' => $data['aplId'],
            'name' => $data['name'],
            'constructioninterval' => $data['constructioninterval'],
            'fullname' => '',
            'passenger' => Make::PASSENGER_NO,
            'commerc' => Make::COMMERC_NO,
            'moto' => Make::MOTO_NO,
            'status' => Make::STATUS_ACTIVE,
        ];
        
        $model = $this->entityManager->getRepository(Model::class)
                ->findOneBy(['tdId' => $data['tdId']]);
        
        if (!$model){
            try{
                $this->entityManager->getRepository(Model::class)
                            ->insertModel($row);
            } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e){
                //дубликат
            }   
            $model = $this->entityManager->getRepository(Model::class)
                    ->findOneBy(['tdId' => $data['tdId']]);
        }    
            

        if ($model){
            $this->entityManager->getRepository(Model::class)
                ->updateModel($model, $group);
                        
            $make->addModel($model);
        }      
            

//            $this->entityManager->persist($make);
//            $this->entityManager-flush();
        
        return $model;        
    }
   
    /**
     * Заполнить модели машины из массива
     * 
     * @param Application\Entity\Make $make
     * @param array $data
     * @param array $group
     */
    private function fillModelFromArray($make, $data, $group)
    {
        foreach ($data as $row){
            $model = $this->addModel($make, [
                'tdId' => $row['id'],
                'aplId' => 0,
                'name' => $row['name'],
                'fullName' => '',
                'constructioninterval' => $row['constructioninterval'],
            ], $group);
//            var_dump($model); exit;
        }
        
        return $model;
    }
    
    /**
     * Заполнить модели машины
     * 
     * @param Application\Entity\Make $make 
     * @return null
     */
    public function fillModels($make)
    {
        $data1 = $this->externalManager->partsApi('models', ['makeId' => $make->getTdId(), 'group' => 'passenger']);
        $this->fillModelFromArray($make, $data1,['passenger' => Model::PASSENGER_YES]);
        $data2 = $this->externalManager->partsApi('models', ['makeId' => $make->getTdId(), 'group' => 'commercial']);
        $this->fillModelFromArray($make, $data2,['commerc' => Model::COMMERC_YES]);
        $data3 = $this->externalManager->partsApi('models', ['makeId' => $make->getTdId(), 'group' => 'moto']);
        $this->fillModelFromArray($make, $data3,['moto' => Model::MOTO_YES]);
        return;
    }
    
    /**
     * Заполнить модели у всех производителей
     */
    public function fillAllModels()
    {
        set_time_limit(1800);
        
        $makes = $this->entityManager->getRepository(Make::class)
                ->findBy([]);
        foreach ($makes as $make){
            $this->fillModels($make);
        }
        
        return;
    }
}
