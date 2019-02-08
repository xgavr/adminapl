<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service;

use Zend\ServiceManager\ServiceManager;
use Application\Entity\Make;

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
        
        try{
            $this->entityManager->getRepository(Make::class)
                        ->insertMake($row);
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e){
            //дубликат
        }   
            
        $make = $this->entityManager->getRepository(Make::class)
                ->findOneBy(['tdId' => $data['tdId']]);

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
}
