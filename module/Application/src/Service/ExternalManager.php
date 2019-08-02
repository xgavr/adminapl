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
use Application\Entity\VehicleDetail;
use Application\Entity\VehicleDetailCar;
use Application\Entity\VehicleDetailValue;
use Application\Entity\Oem;
use Application\Entity\GenericGroup;
use Application\Entity\GoodAttributeValue;
use Application\Entity\CrossList;
use Application\Filter\ArticleCode;

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
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
  
    /**
     * Менеджер auto-db
     * 
     * @var \Application\Service\ExternalDB\AutodbManager 
     */
    private $autoDbManager;
    
    /**
     * Менеджер partsApi
     * 
     * @var \Application\Service\ExternalDB\PartsApiManager 
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
            case 'getSimilarInfo': $result = $this->autoDbManager->getSimilarDirectInfo($params['good']); break;
            case 'getLinked': $result = $this->autoDbManager->getGoodLinked($params['good']); break;
            case 'getImages': $result = $this->autoDbManager->getImages($params['good']); break;
            case 'getGenericArticles': $result = $this->autoDbManager->getGenericArticles(); break;
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
     * Обновить группы товаров из Текдок
     * 
     * @return null
     */
    public function updateGenericGroup()
    {
        // Пустая группа
        $this->entityManager->getRepository(GenericGroup::class)
                ->addGenericGroup([
                    'td_id' => 0,
                    'name' => 'Прочее',
                    'master_name' => 'Прочее',
                ]);
        
        $this->entityManager->getRepository(GenericGroup::class)
                ->updateZeroGroup();
        
        $data = $this->autoDb('getGenericArticles');
//        var_dump($data); exit;
        if (isset($data['data'])){
            if (isset($data['data']['array'])){
                foreach ($data['data']['array'] as $row){
                    $usageDesignation = null;
                    if (isset($row['usageDesignation'])){
                        $usageDesignation = $row['usageDesignation'];
                    }
                    $assemblyGroup = null;
                    if (isset($row['assemblyGroup'])){
                        $assemblyGroup = $row['assemblyGroup'];
                    }
                    
                    $this->entityManager->getRepository(GenericGroup::class)
                            ->addGenericGroup([
                                'td_id' => $row['genericArticleId'],
                                'name' => $row['designation'],
                                'assembly_group' => $assemblyGroup,
                                'master_name' => $row['masterDesignation'],
                                'usage_name' => $usageDesignation,
                            ]);
                }
            }    
        }    
        return;
    }
    /**
     * Добавление/обновление машины
     * 
     * @param array $data
     * @param array $group
     * @return \Application\Entity\Make
     */
    public function addMake($data, $group = null)
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
        } else {
            if ($data['name'] != $make->getName()){
                $this->entityManager->getRepository(Make::class)
                    ->updateMake($make, ['name' => $data['name']]);                
            }
        }    

        if ($make && is_array($group)){
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
     * @return \Application\Entity\Model
     */
    public function addModel($make, $data, $group = null)
    {
        $row = [
            'make_id' => $make->getId(),
            'td_id' => $data['tdId'],
            'apl_id' => $data['aplId'],
            'name' => $data['name'],
            'constructioninterval' => $data['constructioninterval'],
            'fullname' => '',
            'passenger' => Model::PASSENGER_NO,
            'commerc' => Model::COMMERC_NO,
            'moto' => Model::MOTO_NO,
            'status' => Model::STATUS_ACTIVE,
            'transfer_flag' => Model::TRANSFER_NO,
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
        } else {
            if ($data['name'] != $model->getName()){
                $this->entityManager->getRepository(Model::class)
                    ->updateModel($model, ['name' => $data['name']]);                
            }
        }   
            

        if ($model){
            $make->addModel($model);
            if (is_array($group)){
                $this->entityManager->getRepository(Model::class)
                    ->updateModel($model, $group);
            }    
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
        if (is_array($data)){
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
        }    
        
        return;
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
     * Полное наименование машины
     * 
     * @param Model $model
     * @param string name
     * 
     * @return string
     */
    public function carFullName($model, $name)
    {
        return $model->getMake()->getName().' '.$model->getName().' '.$name;
    }
    
    /**
     * Добавить машину
     * 
     * @param \Apllication\Entity\Model $model
     * @param array $data
     * @param integer $group
     * @return Car
     */
    public function addCar($model, $data, $group = null)
    {
        $car = $this->entityManager->getRepository(Car::class)
                ->findOneByTdId($data['tdId']);
        
        $fullName = $this->carFullName($model, $data['name']);
        
        if ($car == null){
            $car = new Car();
            $car->setAplId(0);
            $car->setName($data['name']);
            $car->setFullName($fullName);
            $car->setStatus(Car::STATUS_ACTIVE);
            $car->setTdId($data['tdId']);
            $car->setCommerc(Car::COMMERC_NO);
            $car->setMoto(Car::MOTO_NO);
            $car->setPassenger(Car::PASSENGER_NO); 
            $car->setUpdateFlag(0);
            $car->setTransferFlag(Car::TRANSFER_NO);
            
            $car->setModel($model);

            $this->entityManager->persist($car);
            $this->entityManager->flush();
        } else {
            if ($data['name'] != $car->getName()){
                $this->entityManager->getRepository(Car::class)
                    ->updateCar($car, ['name' => $data['name'], 'fullName' => $fullName, 'transfer_flag' => Car::TRANSFER_NO]);                            
            } else {
                if ($car->getFullName() != $fullName){
                    $this->entityManager->getRepository(Car::class)
                        ->updateCar($car, ['fullName' => $fullName, 'transfer_flag' => Car::TRANSFER_NO]);                                            
                }
            }    
        }
        
        if ($car && $group){
            
            $this->entityManager->getRepository(Car::class)
                ->updateCar($car, $group);            
        }
        
        return $car;
    }
    
    /**
     * Добавить значение атрибута
     * 
     * @param \Application\Entity\Car $car
     * @param \Application\Entity\CarAttributeType $carAttributeType
     * @param array $data
     */
    public function addCarAttributeValue($car, $carAttributeType, $data)
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
     * @param \Application\Entity\Model $model
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
            
            $this->addCarAttributeValue($car, $carAttributeType, 
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
     * НЕИСПОЛЬЗУЕТСЯ
     * Добавление машины к товару 
     * 
     * @param Application\Entity\Good $good
     * @param array $carData
     * @param bool $addCar
     */
    public function addCarToGood($good, $carData, $addCar = true)
    {
        if (isset($carData['carId'])){
            $car = $this->entityManager->getRepository(Car::class)
                    ->findOneByTdId($carData['carId']);
            if (!$car && $addCar){
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
                        $car = $this->entityManager->getRepository(Car::class)
                                ->findOneByTdId($carData['carId']);                        
                    }
                }    
            }   

            if ($car){
                
                $goodCar = $this->entityManager->getRepository(Car::class)
                        ->findGoodCar($good, $car);
                
                if (count($goodCar) == 0){
                    $this->entityManager->getRepository(Goods::class)
                            ->addGoodCar($good, $car);
                }    
            }    
        }
        return;
    }
    
    /**
     * Добавить наименование описания машины
     * @param string $name
     * @return VehicleDetail
     */
    public function addVehicleDetail($name)
    {
        $vehicleDetail = $this->entityManager->getRepository(VehicleDetail::class)
                ->findOneByName($name);
        
        if ($vehicleDetail == null){
            $vehicleDetail = new VehicleDetail();
            $vehicleDetail->setName($name);
            $vehicleDetail->setStatusEdit(VehicleDetail::CANNOT_VALUE_EDIT);
            $this->entityManager->persist($vehicleDetail);
            $this->entityManager->flush($vehicleDetail);
        }
        
        return $vehicleDetail;
    }
    
    /**
     * Добавить значение наименования описания машины
     * 
     * @param VehicleDetail $vehicleDetail
     * @param string $name
     * @return VehicleDetailValue
     */
    public function addVehicleDetailValue($vehicleDetail, $name)
    {
        $vehicleDetailValue = $this->entityManager->getRepository(VehicleDetailValue::class)
                ->findOneBy(['vehicleDetail' => $vehicleDetail->getId(), 'name' => $name]);
        
        if ($vehicleDetailValue == null){
            $vehicleDetailValue = new VehicleDetailValue();
            $vehicleDetailValue->setName($name);
            $vehicleDetailValue->setVehicleDetail($vehicleDetail);
            
            $this->entityManager->persist($vehicleDetailValue);
            $this->entityManager->flush($vehicleDetailValue);
        }
        
        return $vehicleDetailValue;
    }
    
    /**
     * Добавить описание машины
     * @param Car $car
     * @param string $key
     * @param string $value
     */
    public function addVehicleDetailCarKeyValue($car, $key, $value)
    {
        $vehicleDetail = $this->addVehicleDetail($key);
        if ($vehicleDetail){
            $vehicleDetailValue = $this->addVehicleDetailValue($vehicleDetail, $value);

            $vehicleDetailCar = $this->entityManager->getRepository(VehicleDetailCar::class)
                    ->findOneBy(['car' => $car->getId(), 'vehicleDetailValue' => $vehicleDetailValue->getId()]);

            if ($vehicleDetailCar == null){
                $vehicleDetailCar = new VehicleDetailCar();
                $vehicleDetailCar->setCar($car);
                $vehicleDetailCar->setVehicleDetailValue($vehicleDetailValue);

                $this->entityManager->persist($vehicleDetailCar);
                $this->entityManager->flush($vehicleDetailCar);
            }
        }    
        return;
    }
    
    /**
     * Добавить описания машины
     * @param Car $car
     * @param array $carData
     */
    public function addVehicleDetailCar($car, $carData)
    {
        if ($car->getUpdateFlag() != date('n')){
            
            foreach ($carData as $key => $value){
                $this->addVehicleDetailCarKeyValue($car, $key, $value);
            }

            $pcon = '';
            if (!empty($carData['yearOfConstrFrom'])){
                $pcon = substr($carData['yearOfConstrFrom'], -2).'.'.substr($carData['yearOfConstrFrom'], 0, 4).'-';

                $modelConstructionFrom = $car->getModel()->getConstructionFrom(); 

                $model = $car->getModel();
                $model->setTransferFlag(Model::TRANSFER_NO);

                if ($modelConstructionFrom > $carData['yearOfConstrFrom']){
                    $model->setConstructionFrom($carData['yearOfConstrFrom']);
                    $this->entityManager->persist($model);
                }

                if (!empty($carData['yearOfConstrTo'])){
                    $pcon .= substr($carData['yearOfConstrTo'], -2).'.'.substr($carData['yearOfConstrTo'], 0, 4);

                    $modelConstructionTo = $car->getModel()->getConstructionTo(); 
                    if ($modelConstructionTo < $carData['yearOfConstrTo'] || $modelConstructionTo == Model::COSTRUCTION_MAX_PERIOD){
                        $model->setConstructionTo($carData['yearOfConstrTo']);
                        $this->entityManager->persist($model);
                    }
                } else {
                    $model->setConstructionTo(date('Ym'));
                    $this->entityManager->persist($model);                    
                }
            }
            $this->addVehicleDetailCarKeyValue($car, 'PCON', $pcon);

            $car->setUpdateFlag(date('n'));
            $car->setTransferFlag(Car::TRANSFER_NO);
            $this->entityManager->persist($car);
            $this->entityManager->flush();
        }    
        
        return;
    }
    
    /**
     * Обновление машин товара
     * 
     * @param \Application\Entity\Goods $good
     * @param Model $model
     * @param array $carData
       
          ["manuId"] =>              int(183)
          ["manuName"] =>            string(7) "HYUNDAI"

     *    ["modId"] =>               int(4847)
          ["modelName"] =>           string(14) "SONATA VI (YF)"

     *    ["carId"] =>               int(7325)
          ["typeName"] =>            string(3) "2.4"
          ["typeNumber"] =>          int(7325)

     *    ["ccmTech"] =>             int(2359)
          ["constructionType"] =>    string(10) "седан"
          ["cylinder"] =>            int(4)
          ["cylinderCapacityCcm"] => int(2359)
          ["cylinderCapacityLiter"]=> int(240)
          ["fuelType"] =>            string(12) "бензин"
          ["fuelTypeProcess"] =>     string(45) "Непосредственный впрыск"
          ["impulsionType"] =>       string(47) "Привод на передние колеса"
          ["motorType"] =>           string(39) "Бензиновый двигатель"
          ["powerHpFrom"] =>         int(178)
          ["powerHpTo"] =>           int(178)
          ["powerKwFrom"] =>         int(131)
          ["powerKwTo"] =>           int(131)
          ["valves"] =>              int(4)
          ["yearOfConstrFrom"] =>    int(200901)
          ["yearOfConstrTo"] =>      int(201512)
          ["rmiTypeId"] =>          int(67586)
     * @param bool $addFlag
     */
    public function updateGoodCar($good, $model, $carData, $addFlag)
    {
        if ($model){
            $car = $this->addCar($model, ['tdId' => $carData['carId'], 'aplId' => 0, 'name' => $carData['typeName']]);
            if ($car){
                $this->addVehicleDetailCar($car, $carData);
                $goodCar = $this->entityManager->getRepository(Car::class)
                        ->findGoodCar($good, $car);
                if (count($goodCar) == 0){
                    $this->entityManager->getRepository(Goods::class)
                            ->addGoodCar($good, $car);
                }    
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
        ini_set('memory_limit', '512M');
        
        $this->entityManager->getConnection()->update('goods', ['status_car' => Goods::CAR_UPDATING], ['id' => $good->getId()]);
    
        $this->entityManager->getRepository(Goods::class)
                ->removeGoodCars($good);
        
        $tdId = $this->autoDbManager->getBestArticleId($good);
        if (!$tdId){
            $tdId = $this->getSimilarArticleId($good);
        }
        if (is_numeric($tdId)){
            $carsDataI = $this->autoDbManager->getLinked($tdId);
            if (is_array($carsDataI)){
                $addFlag = count($carsDataI)<=10;
                $makes = [];
                $models = [];
                foreach ($carsDataI as $carsData){
                    if (isset($carsData['data'])){
                        if (isset($carsData['data']['array'])){
                            foreach ($carsData['data']['array'] as $carData){
                                if (isset($carData['vehicleDetails'])){
                                    $vehicleDetails = $carData['vehicleDetails'];
                                    if (!isset($makes[$vehicleDetails['manuId']])){
                                        $makes[$vehicleDetails['manuId']] = $this->addMake([
                                            'tdId' => $vehicleDetails['manuId'], 
                                            'aplId' => 0, 
                                            'name' => $vehicleDetails['manuName']
                                        ]);
                                    }
                                    if (!isset($models[$vehicleDetails['modId']])){
                                        $models[$vehicleDetails['modId']] = $this->addModel(
                                                $makes[$vehicleDetails['manuId']],[
                                                    'tdId' => $vehicleDetails['modId'], 
                                                    'aplId' => 0, 
                                                    'name' => $vehicleDetails['modelName'],
                                                    'constructioninterval' => '',
                                                ]);
                                    }
                                    
                                    $this->updateGoodCar($good, $models[$vehicleDetails['modId']], $vehicleDetails, $addFlag);
                                }    
                            }
                        }
                    }
                }    
            }
        }  
        $this->entityManager->getConnection()->update('goods', ['status_car' => Goods::CAR_UPDATED, 'status_car_ex' => Goods::CAR_EX_NEW], ['id' => $good->getId()]);
        
        unset($makes);
        unset($models);
        unset($carsDataI);
        return;
    }
        
    /**
     * Добавление номеров к товару
     * 
     * @param Application\Entity\Goods $good
     */
    public function addOemsToGood($good)
    {
        $this->entityManager->getRepository(Goods::class)
                ->removeGoodOem($good);
        
        $info = $this->autoDbManager->getDirectInfo($good);
        if (!is_array($info)){
            $info = $this->autoDbManager->getSimilarDirectInfo($good);
        }
        
        if (is_array($info)){
            if (isset($info['data'])){
                if (isset($info['data']['array'])){
                    foreach ($info['data']['array'] as $infoArray){
                        if (isset($infoArray['oenNumbers'])){
                            if (isset($infoArray['oenNumbers']['array'])){
                                foreach ($infoArray['oenNumbers']['array'] as $oen){
                                    $this->entityManager->getRepository(Oem::class)
                                            ->addOemToGood($good, $oen, Oem::SOURCE_TD);
                                }
                            }    
                        }    
                    }    
                }
            }
        }
        
        $oemsRaw = $this->entityManager->getRepository(Goods::class)
                ->findOemRaw($good);        
        foreach ($oemsRaw as $oemRaw){
            if ($oemRaw->getCode()){
                $this->entityManager->getRepository(Oem::class)
                        ->addOemToGood($good, ['oe' => $oemRaw->getCode(), 'oeNumber' => $oemRaw->getFullCode()], Oem::SOURCE_SUP);            
            }    
        }
        
        $codeFilter = new ArticleCode();
        $crossList = $this->entityManager->getRepository(CrossList::class)
                ->findBy(['codeId' => $good->getId()]);        
        foreach ($crossList as $line){
            if ($codeFilter->filter($line->getOe())){
                $this->entityManager->getRepository(Oem::class)
                        ->addOemToGood($good, [
                            'oe' => $codeFilter->filter($line->getOe()),
                            'brandName' => $line->getOeBrand(), 
                            'oeNumber' => $line->getOe()
                         ], Oem::SOURCE_CROSS);
            }    
        }
        

        $this->entityManager->getConnection()->update('goods', ['status_oem' => Goods::OEM_UPDATED], ['id' => $good->getId()]);
        return;
    }

    /**
     * Добавление номеров к товару
     * 
     * @param \Application\Entity\Goods $good
     */
    public function updateGoodGenericGroup($good)
    {
        
        $genericArticleId = $this->autoDbManager->getGenericArticleId($good);
        
        $genericGroup = null;
        if (is_numeric($genericArticleId)){
            $genericGroup = $this->entityManager->getRepository(GenericGroup::class)
                    ->findOneByTdId($genericArticleId);

            if ($genericGroup == null){
                $this->updateGenericGroup();
                $genericGroup = $this->entityManager->getRepository(GenericGroup::class)
                        ->findOneByTdId($genericArticleId);
            }
        }   
        
        if (!$genericGroup){
            $genericGroup = $this->entityManager->getRepository(GenericGroup::class)
                    ->findGenericTokenGroup($good->getTokenGroup());
        }
        
        $statusData = ['status_group' => Goods::GROUP_UPDATED];
        if ($genericGroup){
            $data = ['generic_group_id' => $genericGroup->getId()];
            if ($genericGroup->getAplId()>0 && $good->getGroupApl() != $genericGroup->getAplId()){
                $data['group_apl'] = $genericGroup->getAplId();
                $statusData['status_group_ex'] = Goods::GROUP_EX_NEW;
            }
            $this->entityManager->getRepository(Goods::class)
                    ->updateGoodId($good->getId(), $data);            
        }
        
        $this->entityManager->getConnection()->update('goods', $statusData, ['id' => $good->getId()]);
        return;
    }
    
    /**
     * Добавление атрибутов к товару
     * 
     * @param \Application\Entity\Goods $good
     */
    public function addAttributesToGood($good)
    {
        $this->entityManager->getRepository(Goods::class)
                ->removeGoodAttributeValues($good);
        
        $info = $this->autoDbManager->getDirectInfo($good);
        if (is_array($info)){
            if (isset($info['data'])){
                if (isset($info['data']['array'])){
                    foreach ($info['data']['array'] as $infoArray){
                        if (isset($infoArray['articleAttributes'])){
                            if (isset($infoArray['articleAttributes']['array'])){
                                foreach ($infoArray['articleAttributes']['array'] as $attr){
                                    $this->entityManager->getRepository(GoodAttributeValue::class)
                                            ->addGoodAttributeValue($good, $attr);
                                }
                            }    
                        }    
                    }    
                }
            }
        }
        
        $this->entityManager->getConnection()->update('goods', ['status_description' => Goods::DESCRIPTION_UPDATED], ['id' => $good->getId()]);
        return;
        
    }

    /**
     * Добавление картинки к товару
     * 
     * @param Application\Entity\Good $good
     * @return type
     */
    public function addImageToGood($good)
    {
    
        $this->entityManager->getRepository(\Application\Entity\Images::class)
                ->removeGoodImages($good, \Application\Entity\Images::STATUS_TD);
        
        $this->autoDbManager->getImages($good);
        
        $this->entityManager->getConnection()->update('goods', ['status_image' => Goods::IMAGE_UPDATED], ['id' => $good->getId()]);
        return;
        
    }
}
