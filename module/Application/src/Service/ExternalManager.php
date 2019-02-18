<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service;

use Application\Entity\Goods;
use Application\Entity\Make;
use Application\Entity\Model;
use Application\Entity\Car;
use Application\Entity\CarAttributeGroup;
use Application\Entity\CarAttributeType;
use Application\Entity\CarAttributeValue;

/**
 * Description of ExternalManager
 * Внешние апи
 *
 * @author Daddy
 */
class ExternalManager
{
    
    /**
     * Doctrine entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
  
    /**
     * Менеджер auto-db
     * 
     * @var Application\Service\ExternalDB\AutodbManager 
     */
    private $autoDbManager;
    
    /**
     * Менеджер partsApi
     * 
     * @var Application\Service\ExternalDB\PartsApiManager 
     */
    private $partsApiManager;
    
    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager, $autoDbManager, $partsApiManager)
    {
        $this->entityManager = $entityManager;
        $this->autoDbManager = $autoDbManager;
        $this->partsApiManager = $partsApiManager;
    }
    
    /**
     * Подключение к auto-db api
     * 
     * @param string $action
     * @param array $params
     * @return array|null;
     */
    public function autoDb($action, $params = null)
    {
        switch($action){
            case 'version': $result = $this->autoDbManager->getPegasusVersionInfo2(); break;
            case 'countries': $result = $this->autoDbManager->getCountries(); break;
            case 'сriteria': $result = $this->autoDbManager->getCriteria2(); break;
            case 'getArticle': $result = $this->autoDbManager->getArticleDirectSearchAllNumbersWithState($params['good']); break;
            case 'getBestArticle': $result = $this->autoDbManager->getBestArticle($params['good']); break;
            case 'getInfo': $result = $this->autoDbManager->getDirectInfo($params['good']); break;
            case 'getLinked': $result = $this->autoDbManager->getGoodLinked($params['good']); break;
            case 'getImages': $result = $this->autoDbManager->getImages($params['good']); break;
            default: break;
        }
        
//        var_dump($result);
        return $result;
    }
    
    /**
     * Подключение к parts api
     * 
     * @param string $action
     * @param array $params
     * @return array|null;
     */
    public function partsApi($action, $params = null)
    {
        $result = [];
        switch($action){
            case 'makes': $result = $this->partsApiManager->getMakes($params['group']); break;
            case 'models': $result = $this->partsApiManager->getModels($params['makeId'], $params['group']); break;
            case 'cars': $result = $this->partsApiManager->getCars($params['makeId'], $params['modelId'], $params['group']); break;
            default: break;
        }
        
//        var_dump($result); exit;
        return $result;
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
        $data1 = $this->partsApi('makes', ['group' => 'passenger']);
        $this->fillMakesFromArray($data1,['passenger' => Make::PASSENGER_YES]);
        $data2 = $this->partsApi('makes', ['group' => 'commercial']);
        $this->fillMakesFromArray($data2,['commerc' => Make::COMMERC_YES]);
        $data3 = $this->partsApi('makes', ['group' => 'moto']);
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
        $data1 = $this->partsApi('models', ['makeId' => $make->getTdId(), 'group' => 'passenger']);
        $this->fillModelFromArray($make, $data1,['passenger' => Model::PASSENGER_YES]);
        $data2 = $this->partsApi('models', ['makeId' => $make->getTdId(), 'group' => 'commercial']);
        $this->fillModelFromArray($make, $data2,['commerc' => Model::COMMERC_YES]);
        $data3 = $this->partsApi('models', ['makeId' => $make->getTdId(), 'group' => 'moto']);
        $this->fillModelFromArray($make, $data3,['moto' => Model::MOTO_YES]);
        return;
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
    
    /**
     * Добавить машину
     * 
     * @param Apllication\Entity\Model $model
     * @param array $data
     * @param integer $group
     * @return Car
     */
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
    
    /**
     * Добавить значение атрибута
     * 
     * @param Application\Entity\Car $car
     * @param Application\Entity\CarAttributeType $carAttributeType
     * @param array $data
     */
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
        $data1 = $this->partsApi('cars', ['makeId' => $model->getMake()->getTdId(), 'modelId' => $model->getTdId(), 'group' => 'passenger']);
        if (is_array($data1)){
            $this->fillCarFromArray($model, $data1,['passenger' => Car::PASSENGER_YES]);
        }    
        $data2 = $this->partsApi('cars', ['makeId' => $model->getMake()->getTdId(), 'modelId' => $model->getTdId(), 'group' => 'commercial']);
        if (is_array($data2)){
            $this->fillCarFromArray($model, $data2,['commerc' => Car::COMMERC_YES]);
        }    
        $data3 = $this->partsApi('cars', ['makeId' => $model->getMake()->getTdId(), 'modelId' => $model->getTdId(), 'group' => 'moto']);
        if (is_array($data3)){
            $this->fillCarFromArray($model, $data3,['moto' => Car::MOTO_YES]);
        }    
        return;
    }
        
    /**
     * Добавление машины к товару
     * 
     * @param Application\Entity\Good $good
     * @param array $carData
     */
    public function addCarToGood($good, $carData)
    {
        if (isset($carData['carId'])){
            $car = $this->entityManager->getRepository(Make::class)
                    ->findOneByTdId($carData['carId']);
            if (!$car){
                if (isset($carData['modId'])){
                    $model = $this->entityManager->getRepository(Model::class)
                            ->findOneByTdId($carData['modId']);
                    if (!$model){
                        if (isset($carData['manuId'])){
                            $make = $this->entityManager->getRepository(Make::class)
                                    ->findOneByTdId($carData['manuId']);
                            if (!$make){
                                $this->fillMakes();
                                $make = $this->entityManager->getRepository(Make::class)
                                        ->findOneByTdId($carData['manuId']);
                            }
                        }
                        if ($make){
                            $this->fillModels($make);
                            $model = $this->entityManager->getRepository(Model::class)
                                    ->findOneByTdId($carData['modId']);
                        }    
                        
                    }
                    if ($model){
                        $this->fillCars($model);
                        $car = $this->entityManager->getRepository(Make::class)
                                ->findOneByTdId($carData['carId']);                        
                    }
                }    
            }   

            if ($car){
                $this->entityManager->getRepository(Goods::class)
                        ->addGoodCar($good, $car);
            }    
        }
        return;
    }
    
    /**
     * Добавление машин к товару
     * 
     * @param Application\Entity\Goods $good
     */
    public function addCarsToGood($good)
    {
        $this->entityManager->getRepository(Goods::class)
                ->removeGoodCars($good);
        
        $tdId = $this->autoDbManager->getBestArticleId($good);
        if (is_numeric($tdId)){
            $carsData = $this->autoDbManager->getLinked($tdId);
            if (is_array($carsData)){
                if (isset($carsData['data'])){
                    if (isset($carsData['data']['array'])){
                        foreach ($carsData['data']['array'] as $carData){
                            if (isset($carData['vehicleDetails'])){
                                $this->addCarToGood($good, $carData['vehicleDetails']);
                            }    
                        }
                    }
                }
            }
        }  
        return;
    }
}
