<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service;

use Application\Entity\CarAttributeGroup;
use Application\Entity\CarAttributeType;
use Application\Entity\CarAttributeValue;
use Application\Entity\Car;

/**
 * Description of CarService
 *
 * @author Daddy
 */
class CarManager
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
     * Добавить аттрибут
     * 
     * @param array $data
     * @return CarAttributeGroup
     */
    public function addCarAttributeGroup($data)
    {
        $carAttributeGroup = $this->entityManager->getRepository(CarAttributeGroup::class)
                ->findOneByName($data['name']);
        
        if ($carAttributeGroup == null){
            $carAttributeGroup = new CarAttributeGroup();
            $carAttributeGroup->setName($data['name']);

            $this->entityManager->persist($carAttributeGroup);
            $this->entityManager->flush();
        }
            
        return $carAttributeGroup;    
    }

    /**
     * Добавить тип аттрибутов
     * 
     * @param Application\Entity\CarAttributeGroup $carAttributeGroup
     * @param array $data
     * @return CarAttributeType
     */
    public function addCarAttributeType($carAttributeGroup, $data)
    {
        $carAttributeType = $this->entityManager->getRepository(CarAttributeType::class)
                ->findOneBy(['carAttributeGroup' => $carAttributeGroup->getId(), 'name' => $data['name']]);
        
        if ($carAttributeType == null){
            $carAttributeType = new CarAttributeType();
            $carAttributeType->setName($data['name']);
            $carAttributeType->setTitle($data['title']);
            $carAttributeType->setCarAttributeGroup($carAttributeGroup);
            
            $this->entityManager->persist($carAttributeType);
            $this->entityManager->flush();
        }
            
        return $carAttributeType;    
    }
    
    public function addCar($model, $data, $group)
    {
        $car = $this->entityManager->getRepository(Car::class)
                ->findOneByTdId($data['tdId']);
        
        if ($car == null){
            $car = new Car();
            $car->setAplId(0);
            $car->setName($data['name']);
            $car->setFullName('');
            $car->setStatus(Car::STATUS_ACTIVE);
            $car->setTdId($data['tdId']);
            $car->setCommerc(Car::COMMERC_NO);
            $car->setMoto(Car::MOTO_NO);
            $car->setPassenger(Car::PASSENGER_NO); 
            
            $car->setModel($model);

            $this->entityManager->persist($car);
            $this->entityManager->flush();
        }
        
        if ($car){
            
            $this->entityManager->getRepository(Car::class)
                ->updateCar($car, $group);            
        }
        
        return $car;
    }
    
    public function addAttributeValue($car, $carAttributeType, $data)
    {
        $carAttributeValue = new CarAttributeValue();
        $carAttributeValue->setValue($data['value']);
        $carAttributeValue->setCar($car);
        $carAttributeValue->setCarAttributeType($carAttributeType);

        $this->entityManager->persist($carAttributeValue);
        $this->entityManager->flush();
    }
    
    
    /**
     * Заполнить модели машины из массива
     * 
     * @param Application\Entity\Model $model
     * @param array $data
     * @param array $group
     */
    private function fillCarFromArray($model, $data, $group)
    {
        $carTdId = null;
        foreach ($data as $row){
            $car = $this->addCar($model, [
                'tdId' => $row['id'],
                'aplId' => 0,
                'name' => $row['name'],
                'fullName' => '',
            ], $group);

            if ($carTdId != $row['id']){
                $this->entityManager->getRepository(Car::class)
                        ->deleteCarAttributeValue($car);
                $carTdId = $row['id'];
            }
            
            $carAttributeGroup = $this->addCarAttributeGroup([
                'name' => $row['attributegroup'],
            ]);

            $carAttributeType = $this->addCarAttributeType($carAttributeGroup, [
                'name' => $row['attributetype'],
                'title' => $row['displaytitle'],
            ]);
            
            $this->addAttributeValue($car, $carAttributeType, 
                    [
                        'value' => $row['displayvalue'],
                    ]);
        }
        
        return;
    }

    /**
     * Заполнить машины
     * 
     * @param Application\Entity\Model $model 
     * @return null
     */
    public function fillCars($model)
    {
        $data1 = $this->externalManager->partsApi('cars', ['makeId' => $model->getMake()->getTdId(), 'modelId' => $model->getTdId(), 'group' => 'passenger']);
        if (is_array($data1)){
            $this->fillCarFromArray($model, $data1,['passenger' => Car::PASSENGER_YES]);
        }    
        $data2 = $this->externalManager->partsApi('cars', ['makeId' => $model->getMake()->getTdId(), 'modelId' => $model->getTdId(), 'group' => 'commercial']);
        if (is_array($data2)){
            $this->fillCarFromArray($model, $data2,['commerc' => Car::COMMERC_YES]);
        }    
        $data3 = $this->externalManager->partsApi('cars', ['makeId' => $model->getMake()->getTdId(), 'modelId' => $model->getTdId(), 'group' => 'moto']);
        if (is_array($data3)){
            $this->fillCarFromArray($model, $data3,['moto' => Car::MOTO_YES]);
        }    
        return;
    }
    
    
}
