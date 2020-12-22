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
use Application\Entity\Images;
use Application\Filter\ZetasoftCarKey;

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
     * Менеджер abcp
     * 
     * @var \Application\Service\ExternalDB\AbcpManager 
     */

    private $abcpManager;

    /**
     * Менеджер zetasoft
     * 
     * @var \Application\Service\ExternalDB\ZetasoftManager 
     */

    private $zetasoftManager;

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
    public function __construct($entityManager, $autoDbManager, $partsApiManager, $abcpManager, $zetasoftManager)
    {
        $this->entityManager = $entityManager;
        $this->autoDbManager = $autoDbManager;
        $this->partsApiManager = $partsApiManager;
        $this->abcpManager = $abcpManager;
        $this->zetasoftManager = $zetasoftManager;
    }
    
    /**
     * Подключение к abcp api
     * 
     * @param string $action
     * @param array $params
     * @return array|null;
     */
    public function abcp($action, $params = null)
    {
        switch($action){
            case 'manufacturers': $result = $this->abcpManager->getManufacturers($params); break;
            case 'models': $result = $this->abcpManager->getModels($params); break;
            case 'modifications': $result = $this->abcpManager->getModifications($params); break;
            case 'modification': $result = $this->abcpManager->getModification($params); break;
            case 'brands': $result = $this->abcpManager->getBrands($params); break;
            case 'getLinked': $result = $this->abcpManager->adaptabilityManufacturers($params['good']); break;
            default: break;
        }
        
//        var_dump($result);
        return $result;
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
     * Подключение к zetasoft api
     * 
     * @param string $action
     * @param array $params
     * @return array|null;
     */
    public function zetasoft($action, $params = null)
    {
        switch($action){
            case 'ping': $result = $this->zetasoftManager->ping(); break;
//            case 'token': $result = $this->zetasoftManager->token(); break;
//            case 'vendorCode': $result = $this->zetasoftManager->getVendorCode($params['good']); break;
            case 'vendorCode': $result = $this->zetasoftManager->getVendorCodeV2($params['good']); break;
            case 'getPartGroup': $result = $this->zetasoftManager->getPartGroups(); break;
            case 'getLinked': $result = $this->zetasoftManager->getGoodLinked($params['good']); break;
            case 'сriteria': $result = $this->zetasoftManager->getCriteria2(); break;
            case 'getBestArticle': $result = $this->zetasoftManager->getBestArticle($params['good']); break;
            case 'getInfo': $result = $this->zetasoftManager->getDirectInfo($params['good']); break;
            case 'getSimilarInfo': $result = $this->zetasoftManager->getSimilarDirectInfo($params['good']); break;
            case 'getImages': $result = $this->zetasoftManager->getImages($params['good']); break;
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
            case 'fillVolumes': $result = $this->partsApiManager->getFillVolumes($params['carId']);
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
        
        $data = $this->zetasoft('getPartGroup');
//        var_dump($data); exit;
        if (isset($data['data'])){
            foreach ($data['data'] as $row){
                $usageDesignation = null;
                if (isset($row['usageDescription'])){
                    $usageDesignation = $row['usageDescription'];
                }
                $assemblyGroup = null;
                if (isset($row['categoryName'])){
                    $assemblyGroup = $row['categoryName'];
                }

                $this->entityManager->getRepository(GenericGroup::class)
                        ->addGenericGroup([
                            'td_id' => $row['id'],
                            'name' => $row['partName'],
                            'assembly_group' => $assemblyGroup,
                            'master_name' => $row['name'],
                            'usage_name' => $usageDesignation,
                        ]);
            }
        }    
        return;
    }
    /**
     * Добавление/обновление машины
     * 
     * @param array $data
     * @param array $group
     * @return Make
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
//        $data1 = $this->partsApi('makes', ['group' => 'passenger']);
//        $this->fillMakesFromArray($data1,['passenger' => Make::PASSENGER_YES]);
//        $data2 = $this->partsApi('makes', ['group' => 'commercial']);
//        $this->fillMakesFromArray($data2,['commerc' => Make::COMMERC_YES]);
//        $data3 = $this->partsApi('makes', ['group' => 'moto']);
//        $this->fillMakesFromArray($data3,['moto' => Make::MOTO_YES]);
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
//        $data1 = $this->partsApi('models', ['makeId' => $make->getTdId(), 'group' => 'passenger']);
//        $this->fillModelFromArray($make, $data1,['passenger' => Model::PASSENGER_YES]);
//        $data2 = $this->partsApi('models', ['makeId' => $make->getTdId(), 'group' => 'commercial']);
//        $this->fillModelFromArray($make, $data2,['commerc' => Model::COMMERC_YES]);
//        $data3 = $this->partsApi('models', ['makeId' => $make->getTdId(), 'group' => 'moto']);
//        $this->fillModelFromArray($make, $data3,['moto' => Model::MOTO_YES]);
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
            $this->entityManager->flush($carAttributeGroup);
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
            $this->entityManager->flush($carAttributeType);
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
            $car->setGoodCount(0);
            
            $car->setModel($model);

            $this->entityManager->persist($car);
            $this->entityManager->flush($car);
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
        $this->entityManager->flush($carAttributeValue);
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
     * Добавить наименование описания машины
     * @param string $key
     * @return VehicleDetail
     */
    public function addVehicleDetail($key)
    {
        $filter = new ZetasoftCarKey();
        $name = $filter->filter($key);
        
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
        if ($vehicleDetail && $value){
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
            
            $carData['dateFrom'] = date('Ym', strtotime($carData['dateFrom']));
            if (isset($carData['dateTo'])){
                $carData['dateTo'] = date('Ym', strtotime($carData['dateTo']));
            }    
            
            foreach ($carData as $key => $value){
                if (!is_array($value)){
                    $this->addVehicleDetailCarKeyValue($car, $key, $value);
                }    
            }

            $pcon = '';
            if (!empty($carData['dateFrom'])){
                $pcon = substr($carData['dateFrom'], -2).'.'.substr($carData['dateFrom'], 0, 4).'-';

                $modelConstructionFrom = $car->getModel()->getConstructionFrom(); 

                $model = $car->getModel();
                $model->setTransferFlag(Model::TRANSFER_NO);

                if (!$modelConstructionFrom || $modelConstructionFrom > $carData['dateFrom']){
                    $model->setConstructionFrom($carData['dateFrom']);
                    $this->entityManager->persist($model);
                    $this->entityManager->flush($model);
                }

                if (!empty($carData['dateTo'])){
                    $pcon .= substr($carData['dateTo'], -2).'.'.substr($carData['dateTo'], 0, 4);

                    $modelConstructionTo = $car->getModel()->getConstructionTo(); 
                    if (!$modelConstructionTo || $modelConstructionTo < $carData['dateTo'] || $modelConstructionTo == Model::COSTRUCTION_MAX_PERIOD){
                        $model->setConstructionTo($carData['dateTo']);
                        $this->entityManager->persist($model);
                        $this->entityManager->flush($model);
                    }
                } else {
                    $model->setConstructionTo(date('Ym'));
                    $this->entityManager->persist($model);                    
                    $this->entityManager->flush($model);
                }
            }
            $this->addVehicleDetailCarKeyValue($car, 'PCON', $pcon);

            $car->setUpdateFlag(date('n'));
            $car->setTransferFlag(Car::TRANSFER_NO);
            $this->entityManager->persist($car);
            $this->entityManager->flush($car);
        }    
        
        return;
    }
    
    /**
     * Обновление машин товара
     * 
     * @param Goods $good
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
     * 
     * 
     */
    public function updateGoodCar($good, $model, $carData)
    {
        if ($model){
            $car = $this->addCar($model, ['tdId' => $carData['id'], 'aplId' => 0, 'name' => $carData['name']]);
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
     * @param Goods $good
     */
    public function addCarsToGood($good)
    {
        ini_set('memory_limit', '2048M');
        
        $this->entityManager->getConnection()->update('goods', ['status_car' => Goods::CAR_UPDATING], ['id' => $good->getId()]);
        $updateStatuses = ['status_car' => Goods::CAR_UPDATED];
    
        $cars = null;
        $carUpload = $good->getGenericGroup()->getCarUpload();
        if ($carUpload == GenericGroup::CAR_ACTIVE){
            try{
                $cars = $this->zetasoftManager->getGoodLinked($good);
            } catch (\Exception $ex){
                $cars = null;
            }    
        }    
        if (is_array($cars) || $carUpload == GenericGroup::CAR_RETIRED){
            $this->entityManager->getRepository(Goods::class)
                    ->removeGoodCars($good);                
            $updateStatuses['status_car_ex'] = Goods::CAR_EX_NEW;
            
            if (is_array($cars)){
                $makes = [];
                $models = [];
                foreach ($cars as $manufacturerId => $models){
                    foreach ($models as $modelId => $carData){
                        if (isset($carData['data'])){
                            foreach ($carData['data'] as $car){
                                $make = $this->addMake([
                                    'tdId' => $car['manufacturerId'],
                                    'aplId' => 0,
                                    'name' => $car['manufacturerName'],
                                ]);
                                $model = $this->addModel($make, [
                                    'tdId' => $car['modelId'], 
                                    'aplId' => 0, 
                                    'name' => $car['modelName'],
                                    'constructioninterval' => '',
                                ]);
                                $this->updateGoodCar($good, $model, $car);
                            }
                        }                    
                    }    
                }
            }    
        }  
        
        $this->entityManager->getConnection()->update('goods', $updateStatuses, ['id' => $good->getId()]);        
        return;
    }
        
    /**
     * Добавление номеров к товару
     * 
     * @param Goods $good
     */
    public function addOemsToGood($good)
    {
        $notSimilar = true;
        $change = false;
        try{
            $info = $this->zetasoftManager->getDirectInfo($good);
            if (!is_array($info)){
                $notSimilar = false;                
                $info = $this->zetasoftManager->getSimilarDirectInfo($good);
            }

            if (is_array($info)){
                $change = $info['change'];
            }

            if ($change || !$notSimilar){
                $this->entityManager->getRepository(Goods::class)
                        ->removeGoodSourceOem($good, Oem::SOURCE_TD);
                $this->entityManager->getRepository(Oem::class)
                        ->removeIntersectOem($good);            
            }
        } catch (\Exception $ex){
            $info = null;
        }    
        
        $this->entityManager->getRepository(Oem::class)
                ->addSupOem($good);
        $this->entityManager->getRepository(Oem::class)
                ->addCrossOem($good);                                
        
        if (is_array($info) && $notSimilar){
            if (!$change){
                $oemCount = $this->entityManager->getRepository(Oem::class)
                        ->count(['good' => $good->getId(), 'source' => Oem::SOURCE_TD]);
                $change = $oemCount === 0;
            }
            
            if ($change){
                if (isset($info['crossCodes'])){
                    foreach ($info['crossCodes'] as $oen){
                        $this->entityManager->getRepository(Oem::class)
                                ->addOemToGood($good, [
                                    'oeNumber' => $oen['vendorCode'],
                                    'brandName' => $oen['vendorName'],
                                ], Oem::SOURCE_TD);
                    }
                }    
            }
        }
        
        $this->entityManager->getConnection()->update('goods', ['status_oem' => Goods::OEM_UPDATED], ['id' => $good->getId()]);
        
        return;
    }

    /**
     * Добавление номеров к товару
     * 
     * @param Goods $good
     */
    public function updateGoodGenericGroup($good)
    {
        $statusData = ['td_direct' => Goods::TD_NO_DIRECT];
        $genericArticleId = null;
        
        try {
            $tdData = $this->zetasoftManager->getBestArticle($good);
            if (is_numeric($tdData['partGroupId'])){
                $genericArticleId = $tdData['partGroupId'];
                $statusData = ['td_direct' => Goods::TD_DIRECT];
            }

            if (!$genericArticleId){
                $tdData = $this->zetasoftManager->getSimilarArticle($good, true);
                if (is_numeric($tdData['partGroupId'])){
                    $genericArticleId = $tdData['partGroupId'];
                }            
            }
        } catch (\Exception $ex){
            if ($good->getGenericGroup()){
                if ($good->getGenericGroup()->getTdId()>0){
                    $statusData['status_group'] = Goods::GROUP_UPDATED;
                    goto upd; 
                }    
            }
        }    

        $genericGroup = null;
        if (is_numeric($genericArticleId)){
            $genericGroup = $this->entityManager->getRepository(GenericGroup::class)
                    ->findOneByTdId($genericArticleId);

            if ($genericGroup == null){
                $this->updateGenericGroup(); //обновить справочник групп из ТД
                $genericGroup = $this->entityManager->getRepository(GenericGroup::class)
                        ->findOneByTdId($genericArticleId);
            }
        }   
        
        if (!$genericGroup && $good->getTokenGroup()){
            $genericGroup = $this->entityManager->getRepository(GenericGroup::class)
                    ->findGenericTokenGroup($good->getTokenGroup(), $good);
        }
        
        $statusData['status_group'] = Goods::GROUP_UPDATED;
        if (!$genericGroup){
            if ($good->getGenericGroup()){
                $this->entityManager->getRepository(GenericGroup::class)
                        ->updateZeroGroupInGood($good);
            }    
            
            if ($good->getGroupApl()>0){
                $data['group_apl'] = 0;
                $statusData['status_group_ex'] = Goods::GROUP_EX_NEW;
                $this->entityManager->getRepository(Goods::class)
                        ->updateGoodId($good->getId(), $data);            
            }
        }
        
        if ($genericGroup){
            $data = ['generic_group_id' => $genericGroup->getId()];
            if (($genericGroup->getAplId()>0 && $good->getGroupApl() != $genericGroup->getAplId()) || ($genericGroup->getTdId()<0 && $good->getGroupApl() == 644)){
                $data['group_apl'] = $genericGroup->getAplId();
                $statusData['status_group_ex'] = Goods::GROUP_EX_NEW;
            }
            $this->entityManager->getRepository(Goods::class)
                    ->updateGoodId($good->getId(), $data);            
        }
        
        upd:
        $this->entityManager->getConnection()->update('goods', $statusData, ['id' => $good->getId()]);
        return;
    }
    
    /**
     * Добавление атрибутов к товару
     * 
     * @param Goods $good
     */
    public function addAttributesToGood($good)
    {
        $similarGood = false;
        
        try{
            $info = $this->zetasoftManager->getDirectInfo($good);        
            if (!is_array($info)){
                $info = $this->zetasoftManager->getSimilarDirectInfo($good, null, 'attr');
                $similarGood = true;
                if (!is_array($info)){
                    $this->entityManager->getRepository(Goods::class)
                            ->removeGoodAttributeValues($good);                
                }
            }
        } catch (\Exception $ex){
            $info = null;
        }    
        if (is_array($info)){
            $change = $info['change'];
            if (!$change){
                $attrCount = $this->entityManager->getRepository(GoodAttributeValue::class)
                        ->count(['good' => $good->getId()]);
                $change = $attrCount === 0;
            }
            if ($change){

                $this->entityManager->getRepository(Goods::class)
                        ->removeGoodAttributeValues($good);                

                if (isset($info['properties'])){
                    foreach ($info['properties'] as $attr){
                        $this->entityManager->getRepository(GoodAttributeValue::class)
                                ->addGoodAttributeValue($good, $attr, $similarGood);
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
     * @param Good $good
     * @return type
     */
    public function addImageToGood($good)
    {
    
        $mathImages = $this->entityManager->getRepository(Images::class)
                ->count(['good' => $good->getId(), 'similar' => Images::SIMILAR_SIMILAR]);
        
        if (!$mathImages){
            $this->entityManager->getRepository(Images::class)
                    ->saveImageFromGoodRawprice($good);
            try{
                $this->zetasoftManager->getImages($good);
            } catch (\Exception $ex){
                
            }    

        }    

        $this->entityManager->getConnection()->update('goods', ['status_image' => Goods::IMAGE_UPDATED], ['id' => $good->getId()]);
        return;
        
    }
    
    /**
     * Обновить автонормы
     * 
     * @param Car $car
     * @return type
     */
    public function updateFillVolumes($car)
    {
        return $this->partsApi('fillVolumes', ['carId' => $car->getTdId()]);
        
    }
        
}
