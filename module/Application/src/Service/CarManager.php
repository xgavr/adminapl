<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service;

use Application\Entity\CarFillTitle;
use Application\Entity\CarFillType;
use Application\Entity\CarFillUnit;
use Application\Entity\CarFillVolume;
use Application\Entity\Car;
use Application\Entity\Model;
use Application\Validator\IsEN;
use Application\Entity\Goods;
use Application\Entity\Oem;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Application\Entity\Make;
use Application\Filter\ArticleCode;
use Application\Entity\GoodAttributeValue;

/**
 * Description of CarService
 *
 * @author Daddy
 */
class CarManager
{
    const CAR_FOLDER       = './data/cars'; // папка с файлами
    
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * External manager.
     * @var \Application\Entity\ExternalManager
     */
    private $externalManager;
    
    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager, $externalManager)
    {
        $this->entityManager = $entityManager;
        $this->externalManager = $externalManager;
        
        if (!is_dir($this::CAR_FOLDER)){
            mkdir($this::CAR_FOLDER);
        }        
    }
    
    public function getCarFolder()
    {
        return $this::CAR_FOLDER;
    }    
    /**
     * Заполнить машины
     * 
     * @param Model $model 
     * @return null
     */
    public function fillCars($model)
    {
        $this->externalManager->fillCars($model);
        return;
    }
    
    /**
     * Обновить атрибут
     * 
     * @param \Application\Entity\CarAttributeType $attribute
     * @param array $data
     */
    public function updateAttributeType($attribute, $data)
    {
        if (is_array($data)){
            $attribute->setNameApl($data['value']);
            $this->entityManager->persist($attribute);
            $this->entityManager->flush();
        }
        return;
    }

    /**
     * Обновить атрибут
     * 
     * @param \Application\Entity\VehicleDetail $attribute
     * @param array $data
     */
    public function updateVehicleDetail($attribute, $data)
    {
        if (is_array($data)){
            $attribute->setNameApl($data['value']);
            $this->entityManager->persist($attribute);
            $this->entityManager->flush();
        }
        return;
    }

    /**
     * Обновить атрибут
     * 
     * @param \Application\Entity\VehicleDetail $attribute
     * @param integer $status
     */
    public function updateVehicleDetailStatusEdit($attribute, $status)
    {
        if (is_numeric($status)){
            $attribute->setStatusEdit($status);
            $this->entityManager->persist($attribute);
            $this->entityManager->flush();
        }
        return;
    }

    /**
     * Обновить значение атрибута
     * 
     * @param \Application\Entity\VehicleDetailValue $value
     * @param array $data
     */
    public function updateVehicleDetailValue($value, $data)
    {
        if (is_array($data)){
            $value->setNameApl($data['value']);
            $this->entityManager->persist($value);
            $this->entityManager->flush();
        }
        return;
    }

    /**
     * Исправить модель машины
     * 
     * @param Car $car
     */
    public function fixModel($car)
    {
        $modelTdId = $this->entityManager->getRepository(Car::class)
                ->carDetailValue($car, 'modId');
        if ($modelTdId){
            $model = $this->entityManager->getRepository(Model::class)
                    ->findOneByTdId($modelTdId);
            if ($model){
                if ($model->getTdId() != $car->getModel()->getTdId()){
                    $car->setModel($model);
                    $this->entityManager->persist($car);
                    $this->entityManager->flush($car);
                }
            }
        }    
        
        return;
    }
    
    /**
     * Исправить модели всех машин
     */
    public function fixCars()
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(0);
        
        $cars = $this->entityManager->getRepository(Car::class)
                ->findBy([]);
        foreach ($cars as $car){
            $this->fixModel($car);
        }
    }
    
    /**
     * Добавить автонормы название
     * @param array $data
     * @return CarFillTitle 
     * 
     */
    public function addCarFillTitle($data)
    {
        $name = $data['fillTitle'];
        
        if (!$name){
            var_dump($data);
            exit;
        }
        
        $fillTitle = $this->entityManager->getRepository(CarFillTitle::class)
                ->findOneByName(mb_strtoupper($name));
        if (!$fillTitle){
            $fillTitle = new CarFillTitle();
            $fillTitle->setName(mb_strtoupper($name));
            $fillTitle->setTitle($name);
            $this->entityManager->persist($fillTitle);
            $this->entityManager->flush();
        }
        
        return $fillTitle;
    }

    /**
     * Добавить автонормы тип
     * @param array $data
     * @return CarFillType 
     * 
     */
    public function addCarFillType($data)
    {
        $name = $data['fillType'];
        if (!$name){
            $name = '-';
        }
        $fillType = $this->entityManager->getRepository(CarFillType::class)
                ->findOneByName(mb_strtoupper($name));
        if (!$fillType){
            $fillType = new CarFillType();
            $fillType->setName(mb_strtoupper($name));
            $fillType->setTitle($name);
            $this->entityManager->persist($fillType);
            $this->entityManager->flush();
        }
        
        return $fillType;
    }
    
    /**
     * Добавить автонормы размерность
     * @param array $data
     * @return CarFillUnit 
     * 
     */
    public function addCarFillUnit($data)
    {
        $name = $data['fillUnit'];
        if (!$name){
            $name = '-';
        }
        $fillUnit = $this->entityManager->getRepository(CarFillUnit::class)
                ->findOneByName(mb_strtoupper($name));
        if (!$fillUnit){
            $fillUnit = new CarFillUnit();
            $fillUnit->setName(mb_strtoupper($name));
            $fillUnit->setTitle($name);
            $this->entityManager->persist($fillUnit);
            $this->entityManager->flush();
        }
        
        return $fillUnit;
    }
    
    /**
     * Удалить автонормы
     * @param Car $car
     */
    public function removeCarFillVolume($car)
    {
        $volumes = $this->entityManager->getRepository(CarFillVolume::class)
                ->findByCar($car->getId());
        foreach ($volumes as $volume){
            $this->entityManager->remove($volume);
        }
        $this->entityManager->flush();
        
        return;
    }
    
    /**
     * Добавить автонормы значение
     * @param Car $car
     * @param array $data
     * @return CarFillUnit 
     * 
     */
    public function addCarFillVolume($car)
    {
        $this->removeCarFillVolume($car);
        
        $volumes = $this->externalManager->updateFillVolumes($car);
//        var_dump($volumes); exit;
        $enValidator = new IsEN();
        
        if (!is_array($volumes)){
            throw new Exception('Не удалось получить данные');
        }
        
        if (count($volumes)){
            foreach ($volumes as $data){
                if (is_array($data)){
                    $fillTitle = $this->addCarFillTitle($data);
                    $fillType = $this->addCarFillType($data);
                    $fillUnit = $this->addCarFillUnit($data);

                    $lang = CarFillVolume::LANG_RU;
                    if ($enValidator->isValid(mb_strtoupper($data['fillUnit']))){
                        if ($data['fillUnit'] != 'cm³'){
                            $lang = CarFillVolume::LANG_EN;                        
                        }
                    }

                    $fillVolume = new CarFillVolume();
                    $fillVolume->setCar($car);
                    $fillVolume->setCarFillTitle($fillTitle);
                    $fillVolume->setCarFillType($fillType);
                    $fillVolume->setCarFillUnit($fillUnit);
                    $fillVolume->setLang($lang);
                    $fillVolume->setStatus(CarFillVolume::STATUS_ACTIVE);
                    $fillVolume->setVolume($data['fillVolume']);
                    $fillVolume->setInfo($data['fillInfo']);

                    $this->entityManager->persist($fillVolume);
                }    
            }
        }    
        
        $car->setFillVolumesFlag(Car::FILL_VOLUMES_YES);
        $car->setTransferFillVolumesFlag(Car::FILL_VOLUMES_TRANSFER_NO);
        
        $this->entityManager->persist($car);
        $this->entityManager->flush();                        
        
        
        return;
    }
    
    /**
     * Добавить автонормы
     */
    public function carFillVolumes()
    {
        ini_set('memory_limit', '512M');
        set_time_limit(900);
        $startTime = time();
        
        $cars = $this->entityManager->getRepository(Car::class)
                ->findBy(['status' => Car::STATUS_ACTIVE, 'fillVolumesFlag' => Car::FILL_VOLUMES_NO]);
        
        foreach($cars as $car){
            $this->addCarFillVolume($car);
            if (time() > $startTime + 840){
                break;
            }
        }
        
        return;
    }    
    
    /**
     * Выгрузить товары машины
     * 
     * @param Make $make
     * @param Model $model
     * @param Car $car
     */
    public function carGoods($make, $model = null, $car = null)
    {
        ini_set('memory_limit', '4096M');
        set_time_limit(0);
        
        $carGoodsQuery = $this->entityManager->getRepository(Car::class)
                ->carGoods($make, $model, $car);
        
        $data = $carGoodsQuery->getResult(2);
        
        $filename = tempnam('./data/tmp', 'mk');
        $fp = fopen($filename, 'w');

        foreach ($data as $row) {
            fputcsv($fp, $row, ';', '"');
        }

        fclose($fp);        

        return $filename;
    }    
    
    /**
     * Заполнить details
     * @param Car $car
     */
    public function updateDetails($car)
    {
        $details = $car->getVehicleDetailsCarAsArray();
        $car->setDetails(json_encode($details));
        $car->setYearFrom((int) substr($details['yearOfConstrFrom'], 0, 4));
        $car->setYearTo((int) substr($details['yearOfConstrTo'], 0, 4));
        
        $this->entityManager->persist($car);
        $this->entityManager->flush();
        
        return;
    }
    
    /**
     * Заполнить norms
     * @param Car $car
     */
    public function updateNorms($car)
    {
        $norms = $car->getCarFillVolumesAsArray();
        $car->setNorms(json_encode($norms));
        $car->setFillVolumesFlag(Car::FILL_VOLUMES_YES);
        $this->entityManager->persist($car);
        $this->entityManager->flush();
        
        return;
    }
    
    /**
     * Поместить все автонормы в car
     * @return null
     */
    public function updateCarNorms()
    {
        
        ini_set('memory_limit', '512M');
        set_time_limit(1800);
        $startTime = time();
        
        $cars = $this->entityManager->getRepository(Car::class)
                ->findBy(['status' => Car::STATUS_ACTIVE, 'fillVolumesFlag' => Car::FILL_VOLUMES_NO]);
        
        foreach($cars as $car){
            $this->updateNorms($car);
            usleep(100);
            if (time() > $startTime + 1800){
                break;
            }
        }
        
        return;
    }
    
    /**
     * Обновить машина товара по товару с тем же номером
     * @param Goods $good
     */
    public function updateCarsByOem($good, $force = false)
    {
        
//        if ($good->getCheckCar() === Goods::CHECK_CAR_OE){
//            $this->entityManager->getRepository(Goods::class)
//                ->removeGoodCars($good);
//        } else {        
            if ($good->getCars()->count() && $force === false){
                return; //уже есть машины
            }
//        }    
        
        $oems = $this->entityManager->getRepository(Oem::class)
                ->findOemForUpdateCar($good);
        
        if (empty($oems)){
            $this->entityManager->getRepository(Goods::class)
                ->updateGoodId($good->getId(), [
                    'check_car' => Goods::CHECK_CAR_NO_OE,
                ]);                                             
            return;            
        }
        
        foreach ($oems as $oem){
            $goodsWithCars = $this->entityManager->getRepository(Oem::class)
                    ->findGoodsWithCarsByOem($good, $oem['oe']);
            foreach ($goodsWithCars as $goodsWithCar){
//                var_dump($oem['oe'], $goodsWithCar->getCode());
                foreach ($goodsWithCar->getCars() as $car){
                    
                    $this->entityManager->getRepository(Goods::class)
                            ->removeGoodCar($good, $car);
                    
                    $this->entityManager->getRepository(Goods::class)
                            ->addGoodCar($good, $car);
                }
                
                $this->entityManager->getRepository(Goods::class)
                    ->updateGoodId($good->getId(), [
                        'fasade_ex' => Goods::FASADE_EX_NEW,
                        'check_car' => Goods::CHECK_CAR_OE,
                    ]);                                                             
                return;
            }    
        }           
        
        $this->entityManager->getRepository(Goods::class)
            ->updateGoodId($good->getId(), [
                'check_car' => Goods::CHECK_CAR_NO_OE_CAR,
            ]);                                                             
        
        return;
    }
    
    /**
     * Обновить машина товара по атрибутам товара с той же спецификацией
     * @param Goods $good
     */
    public function updateCarsByAttr($good, $force = false)
    {
        
//        if ($good->getCheckCar() === Goods::CHECK_CAR_OE){
//            $this->entityManager->getRepository(Goods::class)
//                ->removeGoodCars($good);
//        } else {        
            if ($good->getCars()->count() && $force === false){
                return; //уже есть машины
            }
//        }    
        
        $norms = $this->entityManager->getRepository(GoodAttributeValue::class)
                ->findNormsForUpdateCar($good, 'A');
        
//        var_dump($norms); exit;
        
        if (empty($norms)){
//            $this->entityManager->getRepository(Goods::class)
//                ->updateGoodId($good->getId(), [
//                    'check_car' => Goods::CHECK_CAR_NO_OE,
//                ]);                                             
//            return;            
        }
        
        $allCars = [];
        
        foreach ($norms as $norm){
            
            $cars = $this->entityManager->getRepository(Car::class)
                    ->findCarByNorm($norm['value']);
            
            foreach ($cars as $car){
//                var_dump($oem['oe'], $goodsWithCar->getCode());
                 $allCars[$car->getId()] = $car;                   
            }    
        }  
        
        foreach ($allCars as $allCar){
            $this->entityManager->getRepository(Goods::class)
                    ->removeGoodCar($good, $allCar);

            $this->entityManager->getRepository(Goods::class)
                    ->addGoodCar($good, $allCar);             
        }
        
        $this->entityManager->getRepository(Goods::class)
            ->updateGoodId($good->getId(), [
                'fasade_ex' => Goods::FASADE_EX_NEW,
                'check_car' => Goods::CHECK_CAR_OE,
            ]);
        
        return;        
    }
    
    /**
     * Заполнение машин где нет
     */
    public function checkGoodCarsByOem()
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(900);
        $startTime = time();
        $finishTime = $startTime + 840;
        
        $goodsForUpdateQuery = $this->entityManager->getRepository(Goods::class)
                ->findGoodsForCheckCarByOem();
        $iterable = $goodsForUpdateQuery->iterate();
        $i = 0;
                
        foreach ($iterable as $row){
            foreach ($row as $good){
                $this->updateCarsByOem($good);
                $this->entityManager->detach($good);
            }    
            $i++;
            if (time() >= $finishTime){
                return;
            }
        }
        
        return;
    }
    
    /**
     * 
     * @param Car $car
     * @param array $data
     */
    public function bindGoodCarData($car, $data)
    {
//        var_dump($data);
        if (!empty($data['code'])){
            $codeFilter = new ArticleCode();
            $goods = $this->entityManager->getRepository(Goods::class)
                    ->findBy(['code' => $codeFilter->filter($data['code'])]);  

            if (count($goods) === 1){
                
                $good = $goods[0];
                
                try{
                    $this->entityManager->getRepository(Goods::class)
                            ->removeGoodCar($good, $car);
                    $this->entityManager->getRepository(Goods::class)
                            ->addGoodCar($good, $car);
                } catch (\Throwable $ex) {

                }

                if(!empty($data['oem'])){
                    $this->entityManager->getRepository(Oem::class)
                            ->addOemToGood($good->getId(), [
                                'oeNumber' => $data['oem'],
                                'brandName' => $data['oem_brand'] ?? null,
                            ], Oem::SOURCE_MAN);
                            
                }
                
                $this->entityManager->getRepository(Goods::class)
                        ->updateGoodId($good->getId(), ['fasade_ex' => Goods::FASADE_EX_NEW]);                
            }    
        }          

        
        return;
    }
    
    /**
     * Привязать список товаров к машине
     * @param Car $car
     * @param string $filename
     */
    public function importGoodCars($car, $filename)
    {        
        $spreadsheet = IOFactory::load($filename);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray(null, true, true, true);   
        
        // Пропускаем заголовок
        array_shift($rows);
        array_shift($rows);
        array_shift($rows);
        array_shift($rows);

        $codeFilter = new ArticleCode();
        
        foreach ($rows as $row) {
            // Проверяем, что строка не пустая
            if (empty(trim($row['A'] ?? ''))) continue;
            
            // --- Code ---
            $data['code'] = $codeFilter->filter(trim($row['A']));

            // --- Oem ---
            $data['oem'] = $codeFilter->filter(trim($row['B']));

            // --- Oem brand ---
            $data['oem_brand'] = trim($row['C']);


            $this->bindGoodCarData($car, $data);
            
            unset($data);
            
//            break;
        }  
        
        return;
    }    
    
    private function getOrCreateMake($makeData)
    {
        $make = $this->entityManager->getRepository(Make::class)
                ->findOneBy(['name' => $makeData['name']]);
        
        if (empty($make)){
            $make = new Make();
            $make->setAplId(0);
            $make->setCommerc(Make::COMMERC_NO);
            $make->setFullName($makeData['fullname']);
            $make->setGoodCount(0);
            $make->setMoto(Make::MOTO_NO);
            $make->setName($makeData['name']);
            $make->setNameRu($makeData['name_ru']);
            $make->setPassenger(Make::PASSENGER_YES);
            $make->setSaleCount(0);
            $make->setSaleMonth(0);
            $make->setStatus(Make::STATUS_ACTIVE);
            $make->setTdId(random_int(500000, 600000));
            
            $this->entityManager->persist($make);
            $this->entityManager->flush();
        }
        
        return $make;
    }
    
    public function getOrCreateModel($make, $modelData)
    {
        $model = $this->entityManager->getRepository(Model::class)
                ->findOneBy(['make' => $make->getId(), 'name' => $modelData['model_name']]);
        
        if (empty($model)){
            $model = new Model();
            $model->setAplId(0);
            $model->setCommerc(Model::COMMERC_NO);
            $model->setConstructionFrom($modelData['construction_from']);
            $model->setConstructionTo($modelData['construction_to']);
            $model->setFullName($modelData['model_full_name']);
            $model->setGoodCount(0);
            $model->setInterval('');
            $model->setMake($make);
            $model->setMoto(Model::MOTO_NO);
            $model->setName($modelData['model_name']);
            $model->setNameRu($modelData['model_name_ru']);
            $model->setPassenger(Model::PASSENGER_YES);
            $model->setSaleCount(0);
            $model->setSaleMonth(0);
            $model->setStatus(Model::STATUS_ACTIVE);
            $model->setTdId(random_int(500000, 600000));
            $model->setTransferFlag(Model::TRANSFER_NO);
            
        }
        
        $interval = $modelData['construction_from'].'-';
        if ($modelData['construction_to'] < 9999){
            $interval .= $modelData['construction_to'];
        }
        $model->setInterval($interval);
        
        $this->entityManager->persist($model);
        $this->entityManager->flush();            
        
        return $model;
    }
    
    public function getOrCreateCar($model, $carData)
    {
        $car = $this->entityManager->getRepository(Car::class)
                ->findOneBy(['model' => $model->getId(), 'name' => $carData['car_name']]);
        
        $nameShort = trim(preg_replace('/\(.*?\)/', '', $carData['car_name']));
        
        if (empty($car)){
            $car = new Car();
            $car->setAplId(0);
            $car->setCommerc(Car::COMMERC_NO);
            $car->setFillVolumesFlag(Car::FILL_VOLUMES_NO);
            $car->setFullName($model->getMake()->getName(). ' ' . $model->getFullName() . ' ' . $nameShort . ' ' . $carData['power_hp'] . ' с ' . $carData['year_from']);
            $car->setGoodCount(0);
            $car->setModel($model);
            $car->setMoto(Car::MOTO_NO);
            $car->setName($carData['car_name']);
            $car->setPassenger(Car::PASSENGER_YES);
            $car->setSaleCount(0);
            $car->setSaleMonth(0);
            $car->setStatus(Car::STATUS_ACTIVE);
            $car->setTdId(random_int(100000, 200000));
            $car->setTransferFillVolumesFlag(Car::FILL_VOLUMES_TRANSFER_NO);
            $car->setTransferFlag(Car::TRANSFER_NO);
            $car->setUpdateFlag(0);
            $car->setYearFrom($carData['year_from']);
            $car->setYearTo($carData['year_to']);
            
        }
        
        $car->setDetails(json_encode([
                'powerHpFrom' => $carData['power_hp'], 
                'powerHpTo' => $carData['power_hp'], 
                'powerKwFrom' => $carData['power_kw'], 
                'powerKwTo' => $carData['power_kw'], 
                'yearOfConstrFrom' => $carData['year_from'], 
                'yearOfConstrTo' => $carData['year_to'], 
                'nameHP' => $nameShort.' '. $carData['year_from'], 
                'typeName' => $carData['car_name'], 
            ]));
        
        $this->entityManager->persist($car);
        $this->entityManager->flush();
        
        return $car;
    }

    public function importCars($filename)
    {        
        $spreadsheet = IOFactory::load($filename);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray(null, true, true, true);   
        
        // Пропускаем заголовок
        array_shift($rows);

        foreach ($rows as $row) {
            // Проверяем, что строка не пустая
            if (empty(trim($row['A'] ?? ''))) continue;

            // --- MAKE ---
            $makeData = [
                'name'       => trim($row['A']),
                'fullname'  => trim($row['B']),
                'name_ru'    => trim($row['C']),
            ];

            $make = $this->getOrCreateMake($makeData);

            // --- MODEL ---
            $modelData = [
                'make_id'              => $make->getId(),
                'model_name'      => trim($row['D']),
                'model_full_name'      => trim($row['E']),
                'model_name_ru'        => trim($row['F']),
                'construction_from'    => !empty($row['G']) ? (int)$row['G'] : 0,
                'construction_to'      => !empty($row['H']) ? (int)$row['H'] : 9999,
            ];

            $model = $this->getOrCreateModel($make, $modelData);

            // --- CAR ---
            $carData = [
                'model_id'     => $model->getId(),
                'car_name'     => trim($row['I']),
                'year_from'    => !empty($row['J']) ? (int)$row['J'] : 0,
                'year_to'      => !empty($row['K']) ? (int)$row['K'] : 9999,
                'power_hp'     => !empty($row['L']) ? (int)$row['L'] : null,
                'power_kw'     => !empty($row['M']) ? (int)$row['M'] : null,
            ];

            $this->getOrCreateCar($model, $carData);
        }        
    }
    
    /**
     * Обновить нормы антифриза
     * @return null
     */
    public function updateCarFreezVolumeNorms()
    {
        
        $data = [
            'G 12 Plus' => 'G12+',
            'WSS-M97B44-D' => 'Ford WSS-M97B44-D',
            'MAN 324' => 'MAN 324 Typ NF',
            'MAN 324 Typ NF' => 'MAN 324 Typ NF',
            'MB 310.0' => 'MB 310.0',
            'MB 310.1' => 'MB 310.1',
            'MB 325.0' => 'MB 325.0',
            'TL 774 G' => 'G12++',
            'TL 774 F' => 'G12+',
            'TL 774 D' => 'G12',
            'G 13' => 'G13',
            'G 12' => 'G12',
            'G 11' => 'G11',
            'Glaceol RX Typ D' => 'Renault Type D',
            'Glaceol AL Typ C' => 'Renault Type C',
            'VOLVO COOLANT' => 'Volvo Coolant 1286083',
            'VOLVO COOLANT VCS' => 'Volvo Coolant VCS',
            'Glysantin G33' => 'PSA B71 5110',
            'Glysantin G33-23F' => 'PSA B71 5110',
            'Glysantin G30' => 'G12+',
            'Glysantin G30-91' => 'G12+',
            'Revkogel 2000' => 'PSA B71 5110',
            'GURIT ESSEX REVKOGEL 2000' => 'PSA B71 5110',
            'Procor TM108|Revkogel 2000' => 'PSA B71 5110',
            'Revkogel 107' => 'PSA B71 5110',
            'DEXCOOL' => 'GM Dex-Cool',
            'Texaco XLC' => 'GM Dex-Cool',
            'Havoline XLC Long Life' => 'GM Dex-Cool',
            'ARTECO Havoline XLC' => 'GM Dex-Cool',
            'Glacelf Supra' => 'GM Dex-Cool',
            'Paraflu 11' => 'Fiat 9.55523-G1',
            'Paraflu UP' => 'Fiat 9.55523-G2',
            'Paraflu' => 'Fiat 9.55523-G1',
            'Paraflu FE' => 'Fiat 9.55523-G2',
            'SAE J-1034' => 'SAE J1034',
            'ASTM D-3306' => 'ASTM D3306',
            'CUNA NC 956-16' => 'CUNA NC 956-16',
            'CUNA NC 596-16' => 'CUNA NC 956-16',
            'Iveco 18-1830' => 'Iveco 18-1830',
            'FIAT 9.55523' => 'Fiat 9.55523-G1',
            'ESD-M97B-49A' => 'Ford ESD-M97B49-A',
            'ESE-M97B-44A' => 'Ford ESE-M97B44-A',
            'WAS-M97B44-D' => 'Ford WSS-M97B44-D',
            'WSS-M97B51-A1' => 'Ford WSS-M97B51-A1',
            '9735 K0' => '9735.K0',
            '000016218' => '16218',
            '00009979A6' => '9979.A6',
            'TOYOTA Super Long Life Coolant (SLLC)' => 'Toyota SLLC',
            'SLLC' => 'Toyota SLLC',
            'TOYOTA Long Life Coolant (LLC)' => 'Toyota LLC',
            'LLC' => 'Toyota LLC',
            'DIA QUEEN SUPER LONG LIFE COOLANT' => 'Mitsubishi DiaQueen SLLC',
            'MITSUBISHI MOTORS Genuine Coolant' => 'Mitsubishi LLC',
            'MS-9769' => 'Chrysler MS-9769',
            '05066386AA' => '05066386AA',
            '99000-99032-12X' => '99000-99032-12X',
            '99000-99032-20X' => '99000-99032-20X',
            'Охлаждающая жидкость с длительным сроком службы FL 22' => 'Mazda FL22',
            'Procor 3000' => 'Peugeot-Citroen Procor 3000',
            'Nissan L250' => 'L250',
            'OL999-9001' => 'OL999-9001',
            'JLM 20404' => 'JLM 20404',
            'B 040 1065' => 'B 040 1065',
            
            '90297545/1940656' => '1940656',            
            '1940656/90297545' => '1940656',            
            '40 - 93 170 402' => '93170402',            
            '1940663/93170402' => '93170402',            
            '1940650/09194431' => '1940650',            
            '1949650/09194431' => '1940650',            
            '93165413/1940679' => '1940679', 
            
            '83 19 2 211 191' => '83192211191',            
            '81 22 9 407 454' => '81229407454',            
            '83 51 0 406 720' => '83510406720',            
            '81 22 9 401 240' => '81229401240',            
        ];
        
        foreach($data as $key => $value){
            //$this->entityManager->getConnection()->update('car_fill_volume', ['volume_norm' => $value], ['volume' => $key]);
            //usleep(100);
        }
        
        $data2 = [
            'MB 310.1|MB 325.0' => ['MB 310.1', 'MB 325.0'],
            'MB 310.1|MB 325.2|MB 325.5' => ['MB 310.1', 'MB 325.2', 'MB 325.5'],
            'MB 310.1|MB 325.2' => ['MB 310.1', 'MB 325.2'],
            'MB 310.0|MB 325.0' => ['MB 310.0', 'MB 325.0'],
            'MB 310.1|MB 310.2' => ['MB 310.1', 'MB 310.2'],
            'CUNA NC 596-16|ASTM D 3306' => ['CUNA NC 956-16', 'ASTM D3306'],
            'CUNA NC 956-16|ASTM D 3306' => ['CUNA NC 956-16', 'ASTM D3306'],
        ];
        
        $doubleType = $this->entityManager->getRepository(CarFillType::class)
                ->find(2);
        
        foreach($data2 as $key => $row){
            $fillVolumesToUpdate = $this->entityManager->getRepository(CarFillVolume::class)
                    ->findBy(['volume' => $key, 'carFillTitile' => 8]);
            
            foreach ($fillVolumesToUpdate as $fillVolumeToUpdate){
                
                $fillVolumeToUpdate->setVolumeNorm($row[0]);
                $this->entityManager->persist($fillVolumeToUpdate);
                               
                $k=1;
                while(!empty($row[$k])){
                    $this->doubleCarFillVolume($fillVolumeToUpdate, $doubleType, $row[$k]);
                }           
            }

            $this->entityManager->flush();
            
            usleep(100);
        }        
        
        return;
    } 
    
    /**
     * Разбить значение нормы
     * 
     * @param CarFillVolume $sourceCarFillVolume
     * @param CarFillType $doubleType
     * @param string $newNormVolume
     */
    private function doubleCarFillVolume($sourceCarFillVolume, $doubleType, $newNormVolume)
    {
        $newVolume = new CarFillVolume();
        $newVolume->setCar($sourceCarFillVolume->getCar());
        $newVolume->setCarFillTitle($sourceCarFillVolume->getCarFillTitle());
        $newVolume->setCarFillType($doubleType);
        $newVolume->setCarFillUnit($sourceCarFillVolume->getCarFillUnit());
        $newVolume->setInfo($sourceCarFillVolume->getInfo());
        $newVolume->setLang($sourceCarFillVolume->getLang());
        $newVolume->setStatus($sourceCarFillVolume->getStatus());
        $newVolume->setVolume($sourceCarFillVolume->getVolume());
        $newVolume->setVolumeNorm($newNormVolume);

        $this->entityManager->persist($newVolume);   
        $this->entityManager->flush();
    }
    
    /**
     * Обновить нормы моторного масла
     * @return null
     */
    public function updateCarOilVolumeNorms()
    {
        set_time_limit(0);
        
        $singleUpdates = [
            'ACEA A3/B3' => 'ACEA A3/B3',
            'ACEA A2/B2' => 'ACEA A2/B2',
            'MAN 3277' => 'MAN 3277',
            'BMW Longlife-04' => 'BMW Longlife-04',
            'VW 507 00' => 'VW 507 00',
            'MAN 3275' => 'MAN 3275',
            'MAN 271' => 'MAN 271',
            'VW 502 00' => 'VW 502 00',
            'VW 504 00' => 'VW 504 00',
            'BMW Longlife-01' => 'BMW Longlife-01',
            'MAN 3477' => 'MAN 3477',
            'MB 229.5' => 'MB 229.5',
            'BMW Longlife-01 FE' => 'BMW Longlife-01 FE',
            'WSS-M2C913-C' => 'Ford WSS-M2C913-C',
            'ACEA A5/B5' => 'ACEA A5/B5',
            'ACEA A3/B4' => 'ACEA A3/B4',
            'RN0710' => 'Renault RN0710',
            'DEXOS 2' => 'GM dexos2',
            'RN0720' => 'Renault RN0720',
            'VW 505 01' => 'VW 505 01',
            'WSS-M2C948-B' => 'Ford WSS-M2C948-B',
            'ACEA C3' => 'ACEA C3',
            'ACEA E5' => 'ACEA E5',
            'BMW Longlife-98' => 'BMW Longlife-98',
            'ACEA C2' => 'ACEA C2',
            'VDS-4' => 'Volvo VDS-4',
            'MB 229.31' => 'MB 229.31',
            'PSA B71 2290' => 'PSA B71 2290',
            'VW 503 00' => 'VW 503 00',
            'RN0700' => 'Renault RN0700',
            'WSS-M2C913-B' => 'Ford WSS-M2C913-B',
            'API SG' => 'API SG',
            'ACEA E2' => 'ACEA E2',
            'API CD' => 'API CD',
            'WSS-M2C913-A' => 'Ford WSS-M2C913-A',
            'VW 506 00' => 'VW 506 00',
            'ACEA E1' => 'ACEA E1',
            'Porsche A40' => 'Porsche A40',
            'WSS-M2C934-B' => 'Ford WSS-M2C934-B',
            'ACEA C4' => 'ACEA C4',
            'ACEA E3' => 'ACEA E3',
            'API CF-4' => 'API CF-4',
            'MS-6395' => 'Chrysler MS-6395',
            'ACEA C1' => 'ACEA C1',
            'WSS-M2C925-A' => 'Ford WSS-M2C925-A',
            'API SM' => 'API SM',
            'VW 505 00' => 'VW 505 00',
            'API SH' => 'API SH',
            'WSS-M2C912-A1' => 'Ford WSS-M2C912-A1',
            'API CF' => 'API CF',
            'ACEA E7' => 'ACEA E7',
            'ACEA E6' => 'ACEA E6',
            'PSA B71 2296' => 'PSA B71 2296',
            'MB 228.5' => 'MB 228.5',
            'API SJ' => 'API SJ',
            'API CH-4' => 'API CH-4',
            'WSS-M2C912-A' => 'Ford WSS-M2C912-A',
            'MS-10725' => 'Chrysler MS-10725',
            'API SE' => 'API SE',
            'WSS-M2C917-A' => 'Ford WSS-M2C917-A',
            'GM-LL-B-025' => 'GM-LL-B-025',
            'ACEA A1/B1' => 'ACEA A1/B1',
            'VW 503 01' => 'VW 503 01',
            'MB 229.3' => 'MB 229.3',
            'WSS-M2C153-H' => 'Ford WSS-M2C153-H',
            'WSS-M2C913-A1' => 'Ford WSS-M2C913-A', // Коррекция опечатки
            'ACEA E4' => 'ACEA E4',
            'WSS-M2C937-A' => 'Ford WSS-M2C937-A',
            'Porsche C30' => 'Porsche C30',
            'VW 500 00' => 'VW 500 00',
            'MB 226.9' => 'MB 226.9',
            'MB 224.1' => 'MB 224.1',
            'Scania Low Ash' => 'Scania Low Ash'
        ];

//        foreach($singleUpdates as $key => $value){
//            $this->entityManager->getConnection()->update('car_fill_volume', ['volume_norm' => $value], ['volume' => $key]);
//            usleep(100);
//        }
        
//        $splitUpdates = [
//            'VW 500 00|VW 501 01|VW 502 00' => ['VW 500 00', 'VW 501 01', 'VW 502 00'],
//            'MB 228.3|MB 228.5|MB 228.51|MB 229.3|MB 229.5|MB 229.31|MB 229.51' => ['MB 228.3', 'MB 228.5', 'MB 228.51', 'MB 229.3', 'MB 229.5', 'MB 229.31', 'MB 229.51'],
//            'MB 228.1|MB 228.3|MB 228.5|MB 229.1|MB 229.3|MB 229.5' => ['MB 228.1', 'MB 228.3', 'MB 228.5', 'MB 229.1', 'MB 229.3', 'MB 229.5'],
//            'MB 228.3|MB 228.5|MB 228.31|MB 228.51' => ['MB 228.3', 'MB 228.5', 'MB 228.31', 'MB 228.51'],
//            'MB 228.1|MB 228.3|MB 228.5|MB 228.31|MB 228.51' => ['MB 228.1', 'MB 228.3', 'MB 228.5', 'MB 228.31', 'MB 228.51'],
//            'MB 228.51|MB 229.31|MB 229.51' => ['MB 228.51', 'MB 229.31', 'MB 229.51'],
//            'PSA B71 2290|PSA B71 2296|PSA B71 2300' => ['PSA B71 2290', 'PSA B71 2296', 'PSA B71 2300'],
//            'VW 503 00|VW 504 00' => ['VW 503 00', 'VW 504 00'],
//            'ACEA E3|ACEA E4|ACEA E5' => ['ACEA E3', 'ACEA E4', 'ACEA E5'],
//            'ACEA E2|ACEA E3' => ['ACEA E2', 'ACEA E3'],
//            'MB 229.3|MB 229.5|MB 229.31|MB 229.51' => ['MB 229.3', 'MB 229.5', 'MB 229.31', 'MB 229.51'],
//            'GM-LL-A-025|DEXOS 2' => ['GM-LL-A-025', 'GM dexos2'],
//            'ACEA E3|ACEA E7' => ['ACEA E3', 'ACEA E7'],
//            'VDS|VDS-2|VDS-3' => ['Volvo VDS', 'Volvo VDS-2', 'Volvo VDS-3'],
//            'ACEA A3/B3|ACEA A3/B4' => ['ACEA A3/B3', 'ACEA A3/B4'],
//            'Scania LDF|Scania LDF2' => ['Scania LDF', 'Scania LDF2'],
//            'VW 506 01|VW 507 00' => ['VW 506 01', 'VW 507 00'],
//            'ACEA A2/B2|ACEA A3/B3' => ['ACEA A2/B2', 'ACEA A3/B3'],
//            'VW 505 00|VW 505 01' => ['VW 505 00', 'VW 505 01'],
//            'WSS-M2C913-A|WSS-M2C912-A1' => ['Ford WSS-M2C913-A', 'Ford WSS-M2C912-A1'],
//            'MB 229.1|MB 229.3|MB 229.5' => ['MB 229.1', 'MB 229.3', 'MB 229.5'],
//            'VW 501 01|VW 502 00' => ['VW 501 01', 'VW 502 00'],
//            'ACEA E4|ACEA E5' => ['ACEA E4', 'ACEA E5'],
//            'ACEA A1/B1|ACEA A2/B2|ACEA A3/B3' => ['ACEA A1/B1', 'ACEA A2/B2', 'ACEA A3/B3'],
//            'API SG|API SH|API SJ|API SL|API SM' => ['API SG', 'API SH', 'API SJ', 'API SL', 'API SM'],
//            'ACEA E5|ACEA E7|Scania LDF|Scania LDF2' => ['ACEA E5', 'ACEA E7', 'Scania LDF', 'Scania LDF2'],
//            'MB 228.1|MB 228.3|MB 228.5|MB 229.1|MB 229.3|MB 229.5|MB 229.31' => ['MB 228.1', 'MB 228.3', 'MB 228.5', 'MB 229.1', 'MB 229.3', 'MB 229.5', 'MB 229.31'],
//            'API SL|API SM' => ['API SL', 'API SM'],
//            'ACEA E4|ACEA E7' => ['ACEA E4', 'ACEA E7'],
//            'VW 501 01|VW 502 00|VW 504 00' => ['VW 501 01', 'VW 502 00', 'VW 504 00'],
//            'VDS-2|VDS-3|VDS-4' => ['Volvo VDS-2', 'Volvo VDS-3', 'Volvo VDS-4'],
//            'VDS-2|VDS-3' => ['Volvo VDS-2', 'Volvo VDS-3'],
//            'PSA B71 2290|PSA B71 2296' => ['PSA B71 2290', 'PSA B71 2296'],
//            'API SE|API SF|API SG|API SH|API SJ|API SL' => ['API SE', 'API SF', 'API SG', 'API SH', 'API SJ', 'API SL'],
//            'GM-LL-B-025|DEXOS 2' => ['GM-LL-B-025', 'GM dexos2'],
//            'ACEA A3/B3|ACEA A5/B5' => ['ACEA A3/B3', 'ACEA A5/B5'],
//            'ACEA E3|ACEA E5' => ['ACEA E3', 'ACEA E5'],
//            'PSA B71 2290|PSA B71 2312' => ['PSA B71 2290', 'PSA B71 2312'],
//            'VDS-3|VDS-4' => ['Volvo VDS-3', 'Volvo VDS-4'],
//            'FIAT 9.55535-S1' => ['Fiat 9.55535-S1'],
//            'API SH|API SJ' => ['API SH', 'API SJ'],
//            'API SJ|API SL' => ['API SJ', 'API SL'],
//            'MB 229.1|MB 229.3' => ['MB 229.1', 'MB 229.3'],
//            'API SL|API SM|API SN' => ['API SL', 'API SM', 'API SN'],
//            'ACEA E5|ACEA E7' => ['ACEA E5', 'ACEA E7'],
//            'MB 228.51|MB 229.31' => ['MB 228.51', 'MB 229.31'],
//            'API SG|API SH' => ['API SG', 'API SH'],
//            'API CF|API CF-4' => ['API CF', 'API CF-4'],
//            'ACEA C2|ACEA C3' => ['ACEA C2', 'ACEA C3'],
//            'API SE|API SF|API SG|API SH' => ['API SE', 'API SF', 'API SG', 'API SH'],
//            'API SG|API SH|API SJ' => ['API SG', 'API SH', 'API SJ'],
//            'VW 505 00|VW 501 01' => ['VW 505 00', 'VW 501 01'],
//            'VW 503 01|VW 504 00' => ['VW 503 01', 'VW 504 00'],
//            'MB 229.3|MB 229.5' => ['MB 229.3', 'MB 229.5'],
//            'ACEA E2|ACEA E3|ACEA E5' => ['ACEA E2', 'ACEA E3', 'ACEA E5'],
//            'ACEA C1|ACEA C2|ACEA C3|ACEA C4' => ['ACEA C1', 'ACEA C2', 'ACEA C3', 'ACEA C4'],
//            'FIAT 9.55535-S2' => ['Fiat 9.55535-S2'],
//            'ACEA A1/B1|ACEA A3/B3' => ['ACEA A1/B1', 'ACEA A3/B3'],
//            'VW 502 00|VW 505 01' => ['VW 502 00', 'VW 505 01'],
//            'MB 229.5|MB 229.51' => ['MB 229.5', 'MB 229.51'],
//            'MB 228.1|MB 228.3|MB 228.5|MB 228.51' => ['MB 228.1', 'MB 228.3', 'MB 228.5', 'MB 228.51'],
//            'MB 228.5|MB 228.51' => ['MB 228.5', 'MB 228.51'],
//            'PSA B71 2290|PSA B71 2296|PSA B71 2300|PSA B71 2312' => ['PSA B71 2290', 'PSA B71 2296', 'PSA B71 2300', 'PSA B71 2312'],
//            'VW 502 00|VW 504 00' => ['VW 502 00', 'VW 504 00'],
//            'VW 506 00|VW 507 00' => ['VW 506 00', 'VW 507 00'],
//            'ACEA A1/B1|ACEA A3/B3|ACEA A3/B4|ACEA A5/B5' => ['ACEA A1/B1', 'ACEA A3/B3', 'ACEA A3/B4', 'ACEA A5/B5'],
//            'MB 228.3|MB 228.5|MB 229.3|MB 229.5|MB 229.31' => ['MB 228.3', 'MB 228.5', 'MB 229.3', 'MB 229.5', 'MB 229.31'],
//            'PSA B71 2296|PSA B71 2300' => ['PSA B71 2296', 'PSA B71 2300'],
//            'FIAT 9.55535-N2' => ['Fiat 9.55535-N2'],
//            'API SG|API SH|API SJ|API SM' => ['API SG', 'API SH', 'API SJ', 'API SM'],
//            'MB 227.0|MB 227.1|MB 228.0|MB 228.1|MB 228.2|MB 228.3|MB 228.5' => ['MB 227.0', 'MB 227.1', 'MB 228.0', 'MB 228.1', 'MB 228.2', 'MB 228.3', 'MB 228.5'],
//            'ACEA E2|ACEA E3|ACEA E4|ACEA E5' => ['ACEA E2', 'ACEA E3', 'ACEA E4', 'ACEA E5'],
//            'ACEA A3/B4|ACEA C3' => ['ACEA A3/B4', 'ACEA C3'],
//            'VW 00 00|VW 501 01|VW 502 00|VW 505 00' => ['VW 500 00', 'VW 501 01', 'VW 502 00', 'VW 505 00'], // Исправлен мусорный код "VW 00 00"
//            'VW 500 00|VW 501 01|VW 502 00|VW 505 00' => ['VW 500 00', 'VW 501 01', 'VW 502 00', 'VW 505 00'],
//            'VW 506 00|VW 506 01' => ['VW 506 00', 'VW 506 01'],
//            'ACEA A1/B1|ACEA A3/B3|ACEA A5/B5' => ['ACEA A1/B1', 'ACEA A3/B3', 'ACEA A5/B5'],
//            'MB 228.0|MB 228.1|MB 228.2|MB 228.3' => ['MB 228.0', 'MB 228.1', 'MB 228.2', 'MB 228.3'],
//            'API SG|API SH|API SJ|API SL' => ['API SG', 'API SH', 'API SJ', 'API SL'],
//            'API SE|API SF|API SG|API SH|API SJ' => ['API SE', 'API SF', 'API SG', 'API SH', 'API SJ'],
//            'API SD|API SE' => ['API SD', 'API SE'],
//            'FIAT 9.55535-M2' => ['Fiat 9.55535-M2'],
//            'ACEA A1/B1|ACEA A3/B3|ACEA A3/B4' => ['ACEA A1/B1', 'ACEA A3/B3', 'ACEA A3/B4'],
//            'VW 505 01|VW 506 01|VW 507 00' => ['VW 505 01', 'VW 506 01', 'VW 507 00'],
//            'VW 505 01|VW 507 00' => ['VW 505 01', 'VW 507 00'],
//            'VW 500 00|VW 501 01|VW 505 00' => ['VW 500 00', 'VW 501 01', 'VW 505 00'],
//            'FIAT 9.55535-T2' => ['Fiat 9.55535-T2'],
//            'MB 228.3|MB 228.5|MB 228.51|MB 229.3|MB 229.5|MB 229.31' => ['MB 228.3', 'MB 228.5', 'MB 228.51', 'MB 229.3', 'MB 229.5', 'MB 229.31'],
//            'ACEA A3/B3|ACEA A3/B4|ACEA A5/B5' => ['ACEA A3/B3', 'ACEA A3/B4', 'ACEA A5/B5'],
//            'FIAT 9.55535-G2' => ['Fiat 9.55535-G2'],
//            'FIAT 9.55535-H2' => ['Fiat 9.55535-H2'],
//            'API CC|API CD' => ['API CC', 'API CD'],
//            'API SG|API SF' => ['API SG', 'API SF'],
//            'MS-11106' => ['Chrysler MS-11106'],
//            'MB 228.1|MB 228.3|MB 228.5' => ['MB 228.1', 'MB 228.3', 'MB 228.5'],
//            'PSA B71 2290|PSA B71 2295|PSA B71 2296|PSA B71 2300' => ['PSA B71 2290', 'PSA B71 2295', 'PSA B71 2296', 'PSA B71 2300'],
//            'API SH|API SG' => ['API SH', 'API SG'],
//            'API SE|API SF' => ['API SE', 'API SF'],
//            'VDS|VDS-2' => ['Volvo VDS', 'Volvo VDS-2'],
//            'API SC|API SD|API SE|API SF' => ['API SC', 'API SD', 'API SE', 'API SF'],
//            'API SD|API SE|API SF' => ['API SD', 'API SE', 'API SF'],
//            'VW 505 01|VW 507 00|VW 506 01' => ['VW 505 01', 'VW 507 00', 'VW 506 01'],
//            'ACEA E7|Scania LDF|Scania Low Ash' => ['ACEA E7', 'Scania LDF', 'Scania Low Ash'],
//            'VW 506 00|VW 506 01|VW 507 00' => ['VW 506 00', 'VW 506 01', 'VW 507 00'],
//            'ACEA A2/B2|ACEA A3/B3|ACEA A3/B4' => ['ACEA A2/B2', 'ACEA A3/B3', 'ACEA A3/B4'],
//            'VW 502 00|VW 505 01|VW 503 01' => ['VW 502 00', 'VW 505 01', 'VW 503 01'],
//            'VW 505 01|VW 505 00|VW 507 00' => ['VW 505 01', 'VW 505 00', 'VW 507 00'],
//            'FIAT 9.55535-G1' => ['Fiat 9.55535-G1'],
//            'API CF-4|API CG-4' => ['API CF-4', 'API CG-4'],
//            'FIAT 9.55535-H3' => ['Fiat 9.55535-H3'],
//            'FIAT 9.55535-Z2' => ['Fiat 9.55535-Z2'],
//            'MB 229.3|MB 229.5|MB 229.51' => ['MB 229.3', 'MB 229.5', 'MB 229.51']
//        ];

        $splitUpdates = [
            'MB 229.5|MB 229.31' => ['MB 229.5', '229.31'],
            'MB 229.3|MB 229.5|MB 229.51' => ['MB 229.3', '229.5', 'MB 229.51']
        ];
        
        $doubleType = $this->entityManager->getRepository(CarFillType::class)
                ->find(2);
        
        foreach($splitUpdates as $key => $row){
            $fillVolumesToUpdate = $this->entityManager->getRepository(CarFillVolume::class)
                    ->findBy(['volume' => $key, 'carFillTitile' => 2]);
            
            foreach ($fillVolumesToUpdate as $fillVolumeToUpdate){
                
                $fillVolumeToUpdate->setVolumeNorm($row[0]);
                $this->entityManager->persist($fillVolumeToUpdate);
                $this->entityManager->flush();
                
                $k=1;
                while(!empty($row[$k])){
                    $this->doubleCarFillVolume($fillVolumeToUpdate, $doubleType, $row[$k]);
                    $k++;
                }               
            }            
            
            usleep(100);
        }        
        
        return;
    }   
    
    /**
     * Обновить нормы трансм масла
     * @return null
     */
    public function updateCarTransOilVolumeNorms()
    {
        //set_time_limit(0);
        
        $singleUpdates = [
            'G 052 145 A1' => 'VAG G 052 145',
            'API GL5 SAE 80W-90' => 'API GL-5 80W-90',
            'API GL5 SAE 75W-90' => 'API GL-5 75W-90',
            'SAE 75W-90' => 'SAE 75W-90',
            'MB 235.1' => 'MB 235.1',
            'G 052 182 A2' => 'VAG G 052 182', // Жидкость DSG
            'API GL4 SAE 75W-85' => 'API GL-4 75W-85',
            'API GL5' => 'API GL-5',
            'ZF TE-ML 14E' => 'ZF TE-ML 14E',
            'WSS-M2C200-D2' => 'Ford WSS-M2C200-D2',
            'ESP-M2C-166-H' => 'Ford ESP-M2C166-H',
            'WSD-M2C200-C' => 'Ford WSD-M2C200-C',
            'MAN 339 Typ Z4' => 'MAN 339 Typ Z4',
            'MAN 339 Typ Z3' => 'MAN 339 Typ Z3',
            'G 052 512 A2' => 'VAG G 052 512',
            'ESSO 75W80 EZL 848' => 'PSA EZL 848',
            'MB 236.15' => 'MB 236.15',
            'ATF+4' => 'Mopar ATF+4',
            'ZF TE-ML 02D' => 'ZF TE-ML 02D',
            'API GL3 SAE 80W-90' => 'API GL-3 80W-90',
            'TOTAL H6965' => 'Total H6965',
            'ATF SP-III' => 'ATF SP-III',
            'API GL4 PLUS SAE 75W-80' => 'API GL-4+ 75W-80',
            'ATF Dexron VI' => 'GM Dexron VI',
            '1161 540' => 'Volvo 1161540',
            'MB 236.2' => 'MB 236.2',
            'G 052 726 A2' => 'VAG G 052 726',
            'ZF TE-ML 02E' => 'ZF TE-ML 02E',
            'MAN 339 Typ D' => 'MAN 339 Typ D',
            'MAN 339 Typ F' => 'MAN 339 Typ F',
            'ATF AW-1' => 'ATF AW-1',
            'ZF TE-ML 02H' => 'ZF TE-ML 02H',
            '31280771' => 'Volvo 31280771',
            'ESSO JWS 3309' => 'JWS 3309',
            'SAE 75W' => 'SAE 75W',
            'G 060 162 A2' => 'VAG G 060 162',
            'ESSO LT 71141' => 'ZF Lifeguardfluid 5', // Промышленный стандарт LT 71141
            'G 055 025 A2' => 'VAG G 055 025',
            'G 052 180 A2' => 'VAG G 052 180', // Жидкость CVT
            'ATF Dexron II E' => 'GM Dexron II-E',
            'MB 236.12' => 'MB 236.12',
            'MTF-LT-2' => 'BMW MTF-LT-2',
            'Total Transmission BV 75W-80' => 'PSA B71 2330', // Прямое соответствие масла BV
            'G 052 516 A2' => 'VAG G 052 516',
            'WSS-M2C938-A' => 'Ford WSS-M2C938-A',
            'G 052 513 A2' => 'VAG G 052 513',
            'WSS-M2C936-A' => 'Ford WSS-M2C936-A',
            'API GL3' => 'API GL-3',
            'MAN 341' => 'MAN 341 Typ E1',
            'G 060 726 A2' => 'VAG G 060 726',
            'API GL4 PLUS SAE 75W-85' => 'API GL-4+ 75W-85',
            'API GL4 SAE 75W' => 'API GL-4 75W',
            'API GL5 SAE 80W' => 'API GL-5 80W',
            'ATF 3309' => 'JWS 3309',
            'CCMC D4' => 'CCMC D4',
            'MTF-94' => 'Rover MTF-94',
            'FIAT 9.55550-MX3' => 'Fiat 9.55550-MX3',
            'Shell ATF M-1375.4' => 'ZF Lifeguardfluid 6', // Формула M-1375.4 это шестая жидкость ZF
            'ATF Dexron' => 'GM Dexron IID',
            'API GL4 SAE 80W-90' => 'API GL-4 80W-90',
            'SAE 80W-90' => 'SAE 80W-90',
            'SQM-2C9008-A' => 'Ford SQM-2C9008-A',
            'MTF-LT-3' => 'BMW MTF-LT-3',
            'MTF 0063' => 'Saab MTF 0063',
            'ATF SP-IV' => 'ATF SP-IV',
            'G 052 532 A2' => 'VAG G 052 532',
            '93165147' => 'GM 1940182', // Нормализация старого артикула Opel
            'Shell L 12108' => 'ZF Lifeguardfluid 8', // Спецификация Shell L12108 это ZF 8
            'ZF TE-ML 02L' => 'ZF TE-ML 02L',
            'MTF' => 'API GL-4',
            'G 055 532 A2' => 'VAG G 055 532',
            'MTF-LT-4' => 'BMW MTF-LT-4',
            'WSS-M2C919-E' => 'Ford WSS-M2C919-E',
            'WSS-M2C928-A' => 'Ford WSS-M2C928-A',
            'MIL-L-2105' => 'MIL-L-2105',
            'FIAT 9.55550-MZ6' => 'Fiat 9.55550-MZ6',
            'MB 236.10' => 'MB 236.10',
            'ATF T-IV' => 'Toyota ATF Type T-IV',
            'G 052 527 A2' => 'VAG G 052 527',
            'ESD-M2C186-A' => 'Ford ESD-M2C186-A',
            'G 052 178 A2' => 'VAG G 052 178',
            'G 052 990 A2' => 'VAG G 052 990',
            'FIAT 9.55550-MZ1' => 'Fiat 9.55550-MZ1',
            'G 50' => 'VAG G 050 000',
            'MAN 341 Typ E3' => 'MAN 341 Typ E3',
            'MAN 341 Typ E4' => 'MAN 341 Typ E4',
            'ATF WS' => 'Toyota ATF WS',
            'WSS-M2C924-A' => 'Ford WSS-M2C924-A',
            'ATF Z1' => 'Honda ATF-Z1',
            '31256 774' => 'Volvo 31256774',
            'VW ATF' => 'VAG G 052 162',
            'G 052 025 A2' => 'VAG G 052 025',
            'ATF M-V' => 'Mazda ATF M-V',
            'Suzuki ATF 3317' => 'Suzuki ATF 3317',
            'Texaco ETL 7045E' => 'GM ETL-7045E',
            '1161 838' => 'Volvo 1161838',
            'WSS-M2C922-A1' => 'Ford WSS-M2C922-A1',
            '31256774' => 'Volvo 31256774',
            'TOYOTA Genuine ATF WS' => 'Toyota ATF WS',
            '9163335' => 'Volvo 9163335',
            'API GL4 SAE 90' => 'API GL-4 90',
            'G 009 317 A2' => 'VAG G 009 317',
            'FIAT 9.55550-AV2' => 'Fiat 9.55550-AV2',
            'SAE 75W-85' => 'SAE 75W-85',
            'Mobil ATF 3309' => 'JWS 3309',
            'ATF LT 71141' => 'ZF Lifeguardfluid 5',
            'ZF TE-ML 02F' => 'ZF TE-ML 02F',
            'PSA B71 2330' => 'PSA B71 2330',
            'MB 235.71' => 'MB 235.71',
            '9736 22' => 'PSA 9736.22',
            'TUTELA GI/A' => 'Fiat 9.55550-AG1',
            'SAE 70W' => 'SAE 70W',
            'WSS-M2C202-B' => 'Ford WSS-M2C202-B',
            'FIAT 9.55550-AV4' => 'Fiat 9.55550-AV4',
            'G 055 005 A2' => 'VAG G 055 005',
            '1940182' => 'GM 1940182',
            'ATF FZ' => 'Mazda ATF FZ',
            'MB 235.72' => 'MB 235.72',
            'G 055 540 A2' => 'VAG G 055 540',
            '1161 745' => 'Volvo 1161745',
            'SAE 80W' => 'SAE 80W',
            'MB 236.20' => 'MB 236.20', // Вариатор Autotronic
            'Castrol Transmax J' => 'JWS 3309',
            'API GL-4' => 'API GL-4',
            'FIAT 9.55530-MX3' => 'Fiat 9.55550-MX3', // Коррекция опечатки в группе кода
            'MB 236.11' => 'MB 236.11',
            'ESSO ATF D' => 'GM Dexron IID',
            'ATF SP-II' => 'ATF SP-II',
            'ATF Dexron III G' => 'GM Dexron III-G',
            'ELFMATIC J6' => 'Renault Elfmatic J6',
            '400 108247' => 'Ford 400108247',
            'TUTELA GI/V' => 'Fiat 9.55550-AV1',
            'ATF SP-II M' => 'ATF SP-II M',
            'Texaco ETL 8997B' => 'GM ETL-8997B',
            'Nissan NS-2 CVT Fluid' => 'Nissan NS-2',
            'ATF+3' => 'Mopar ATF+3',
            'API GL3 SAE 75W-90' => 'API GL-3 75W-90',
            'Nissan ATF' => 'Nissan Matic-D',
            'Shell ATF 3353' => 'MB 236.12', // Жидкость 3353 создана под 236.12
            'Mopar NV1500' => 'Mopar NV1500',
            'Texaco N402' => 'Rover N402',
            'Mopar NV3550' => 'Mopar NV3550',
            'C2S 19889' => 'Jaguar C2S19889',
            'Castrol BOT 350 M3' => 'Fiat 9.55550-MZ1',
            'VW TL 521 78' => 'VAG G 052 178', // Кросс инженерного кода на артикул
            'CVTF+4' => 'Mopar CVTF+4',
            'G 055 538 A2' => 'VAG G 055 538',
            'DIA QUEEN CVTF J1' => 'Mitsubishi CVTF-J1',
            'MS-9224' => 'Chrysler MS-9224',
            'API GL3 SAE 75W-85' => 'API GL-3 75W-85',
            'TOTAL ATF H50235' => 'Hyundai ATF H50235',
            'API GL3 SAE 85W-90' => 'API GL-3 85W-90',
            'FIAT 9.55550' => 'Fiat 9.55550',
            'Nippon AW-1' => 'ATF AW-1',
            'G 055 162 A2' => 'VAG G 055 162',
            'Nissan AT-Matic D' => 'Nissan Matic-D',
            'ESSO JWS 227' => 'JWS 227',
            'ESSO CVT EZL 799' => 'BMW EZL 799',
            'MB 236.6' => 'MB 236.6',
            'N052162-VX00' => 'VAG G 052 162',
            'Mercon V' => 'Ford Mercon V',
            'G 052 798 A2' => 'VAG G 052 798',
            'ATF RED-1K' => 'KIA ATF Red-1K',
            'XT-2-QDX' => 'Ford Mercon',
            'Suzuki 99000-22B21-036' => 'Suzuki 99000-22B21-036',
            'ESSO JWS 3314' => 'JWS 3314',
            'Nissan NS-1 CVT Fluid' => 'Nissan NS-1',
            '9196089' => 'Saab 9196089',
            '9730 94' => 'Volvo 97309',
            'Castrol MTF 97309' => 'Volvo 97309',
            'Castrol MTF BOT 338' => 'GM 1940182',
            '1940768' => 'GM 1940768',
            '00009979A7' => 'PSA 9979.A7',
            'ESP-M2C-202-B' => 'Ford ESP-M2C202-B',
            'NO52171-VX00' => 'VAG G 052 171',
            'G 055 726 A2' => 'VAG G 055 726',
            'MAN 341 Typ Z2' => 'MAN 341 Typ Z2',
            '000 043 304 00' => 'Porsche 00004330400',
            'VOLVO 97308' => 'Volvo 97308',
            'MAN 341 Typ Z3' => 'MAN 341 Typ Z3',
            'Mitsubishi Diaqueen SSTF-1' => 'Mitsubishi SSTF-I', // Робот Эво/Раллиарт
            'MB 236.5' => 'MB 236.5',
            'ATF Dexron I' => 'GM Dexron IID',
            'ATF RED-1' => 'KIA ATF Red-1',
            '7711428122' => 'Renault 7711428122',
            'Shell ATF 3403 M115' => 'MB 236.10',
            '000 043 300 38' => 'Porsche 00004330038',
            'MAN 341 Typ Z1' => 'MAN 341 Typ Z1',
            'ESSO D21065' => 'GM Dexron IID',
            'MTF HQ Multi 75W-85' => 'Hyundai MTF 75W-85',
            'Nissan AT-Matic S' => 'Nissan Matic-S',
            'Mercon' => 'Ford Mercon',
            'G 51' => 'VAG G 051 000',
            'N052990-VX00' => 'VAG G 052 990',
            'WSD-M2C200-C3' => 'Ford WSD-M2C200-C3',
            '77 11 218 368' => 'Renault 7711218368',
            'API GL5 SAE 75W' => 'API GL-5 75W',
            'WSS-M2C203-A1' => 'Ford WSS-M2C203-A1',
            'Mobil ATF 220D' => 'GM Dexron IID',
            'MB 235.12' => 'MB 235.12',
            'G 005 100 A1' => 'VAG G 005 100',
            'TOTAL EP 80' => 'API GL-4 80W',
            'XR8 50057' => 'Jaguar XR850057',
            'ATF PA' => 'Toyota ATF PA',
            '000 043 205 28' => 'Porsche 00004320528',
            'VW TL 521 57' => 'VAG G 052 157',
            'TOYOTA Genuine CVTF TC' => 'Toyota CVTF TC',
            'AF 40' => 'GM AW-1',
            'Castrol TQ 95' => 'BTR Type 95', // Спецификация автомата SsangYong/Ford
            'MB 235.4' => 'MB 235.4',
            'XR8 50056' => 'Jaguar XR850056',
            'ESP-M2C-166-A' => 'Ford ESP-M2C166-A',
            'G 052 157 A2' => 'VAG G 052 157',
            'DIA QUEEN ATF J3' => 'Mitsubishi ATF-J3',
            'WSS-M2C932-A' => 'Ford WSS-M2C932-A',
            'ATF SP' => 'ATF SP-III',
            '88862472' => 'GM 88862472',
            'MTF 2' => 'Rover MTF-94',
            'Castrol Transmax Z' => 'ZF TE-ML 14C',
            'Suzuki CVT Fluid Green 1' => 'Suzuki CVT Green 1',
            'Nissan AT-Matic J' => 'Nissan Matic-J',
            'TOYOTA GEAR OIL V160' => 'Toyota V160', // Спецификация МКПП Getrag V160 (Supra)
            'Texaco MTF 94' => 'Rover MTF-94',
            '09120541/1940768' => 'GM 1940768',
            '09117946/1940767' => 'GM 1940767',
            '9117946/1940767' => 'GM 1940767',
            '90001777/1940750' => 'GM 1940750',
            '90188629/1940759' => 'GM 1940759',
            '93165290/1940182' => 'GM 1940182',
            '93165147/1940773' => 'GM 1940773',
            '90540998/1940764' => 'GM 1940764',
            '9121964/1940708' => 'GM 1940708',
            '93160393/1940771' => 'GM 1940771',
            '90350342/1940700' => 'GM 1940700',
            '9120541/1970768' => 'GM 1940768', // Исправлена жесткая опечатка в годе/коде 197->194
            '93160536/1940713' => 'GM 1940713'            
        ];
               

        foreach($singleUpdates as $key => $value){
            $this->entityManager->getConnection()->update('car_fill_volume', ['volume_norm' => $value], ['volume' => $key]);
//            usleep(100);
        }
        
        $splitUpdates = [
           'VOLVO 97307|VOLVO 97315' => ['Volvo 97307', 'Volvo 97315'],
           'MB 235.4|MB 235.11' => ['MB 235.4', 'MB 235.11'],
           'MB 236.6|MB 236.7' => ['MB 236.6', 'MB 236.7'],
           'MAN 341 Typ Z3|MAN 341 Typ Z4' => ['MAN 341 Typ Z3', 'MAN 341 Typ Z4'],
           'ZF TE-ML 02D|ZF TE-ML 02L' => ['ZF TE-ML 02D', 'ZF TE-ML 02L'],
           'MB 235.1|MB 235.11' => ['MB 235.1', 'MB 235.11'],
           'ZF TE-ML 02B|ZF TE-ML 02C|ZF TE-ML 02D|ZF TE-ML 02H|ZF TE-ML 02L' => ['ZF TE-ML 02B', 'ZF TE-ML 02C', 'ZF TE-ML 02D', 'ZF TE-ML 02H', 'ZF TE-ML 02L'],
           'ZF TE-ML 02C|ZF TE-ML 02D|ZF TE-ML 02H|ZF TE-ML 02L' => ['ZF TE-ML 02C', 'ZF TE-ML 02D', 'ZF TE-ML 02H', 'ZF TE-ML 02L'],
           'ZF TE-ML 02B|ZF TE-ML 02C|ZF TE-ML 02G|ZF TE-ML 02H' => ['ZF TE-ML 02B', 'ZF TE-ML 02C', 'ZF TE-ML 02G', 'ZF TE-ML 02H'],
           'MB 236.9|MB 236.91' => ['MB 236.9', 'MB 236.91'],
           'ZF TE-ML 02H|API GL3' => ['ZF TE-ML 02H', 'API GL-3'],
           'ATF M-III|ATF Dexron II' => ['Mazda ATF M-III', 'GM Dexron IID'],
           'Dexron II / III' => ['GM Dexron IID', 'GM Dexron III-G'],
           'ZF TE-ML 02B|ZF TE-ML 02D|ZF TE-ML 02F|ZF TE-ML 02G|ZF TE-ML 02H' => ['ZF TE-ML 02B', 'ZF TE-ML 02D', 'ZF TE-ML 02F', 'ZF TE-ML 02G', 'ZF TE-ML 02H'],
           'MB 236.1|MB 236.6|MB 236.7|MB 236.8|MB 236.81' => ['MB 236.1', 'MB 236.6', 'MB 236.7', 'MB 236.8', 'MB 236.81'],
           'API GL4|API GL5' => ['API GL-4', 'API GL-5'],
           'MB 236.12|MB 236.14' => ['MB 236.12', 'MB 236.14'],
           'API GL3 SAE 75W-90|API GL4 SAE 75W-90' => ['API GL-3 75W-90', 'API GL-4 75W-90'],
           'MB 235.10|MB 236.2|MB 236.6' => ['MB 235.10', 'MB 236.2', 'MB 236.6'],
           'MB 236.14|MB 236.15' => ['MB 236.14', 'MB 236.15'],
           'API GL4 SAE 75W-80|API GL5 SAE 75W-80' => ['API GL-4 75W-80', 'API GL-5 75W-80'],
           'ATF Dexron II E|ATF Dexron III' => ['GM Dexron II-E', 'GM Dexron III-G'],
           'API GL3 SAE 75W-85|API GL4 SAE 75W-85' => ['API GL-3 75W-85', 'API GL-4 75W-85'],
           'API GL4 SAE 90|API GL5 SAE 90' => ['API GL-4 90', 'API GL-5 90'],
           'API GL4 SAE 80W-90|API GL5 SAE 80W-90' => ['API GL-4 80W-90', 'API GL-5 80W-90'],
           'MB 236.1|MB 236.8|MB 236.81' => ['MB 236.1', 'MB 236.8', 'MB 236.81'],
           'API GL3|API GL4' => ['API GL-3', 'API GL-4'],
           'API GL3 SAE 75W-80|API GL4 SAE 75W-80' => ['API GL-3 75W-80', 'API GL-4 75W-80'],
           'ATF M-III|ATF Dexron III' => ['Mazda ATF M-III', 'GM Dexron III-G'],
           'Dexron II / IIE / III' => ['GM Dexron IID', 'GM Dexron II-E', 'GM Dexron III-G'],
           'ATF M-V|ATF Dexron II' => ['Mazda ATF M-V', 'GM Dexron IID'],
           'Dexron   IIE / III' => ['GM Dexron II-E', 'GM Dexron III-G'],
           'API SF|API CC' => ['API SF', 'API CC'], // Древний стандарт МКПП под моторное масло
           'API SF|API SG' => ['API SF', 'API SG']
       ];

        
        $doubleType = $this->entityManager->getRepository(CarFillType::class)
                ->find(2);
        
        foreach($splitUpdates as $key => $row){
            $fillVolumesToUpdate = $this->entityManager->getRepository(CarFillVolume::class)
                    ->findBy(['volume' => $key]);
            
            foreach ($fillVolumesToUpdate as $fillVolumeToUpdate){
                
                $fillVolumeToUpdate->setVolumeNorm($row[0]);
                $this->entityManager->persist($fillVolumeToUpdate);
                $this->entityManager->flush();
                
                $k=1;
                while(!empty($row[$k])){
                    $this->doubleCarFillVolume($fillVolumeToUpdate, $doubleType, $row[$k]);
                    $k++;
                }               
            }            
            
//            usleep(100);
        }        
        
        return;
    }  
    
    /**
     * Обновить нормы тормозной жидкости
     * @return null
     */
    public function updateCarBrakeOilVolumeNorms()
    {
        //set_time_limit(0);
        
        $singleUpdates = [
            'DOT 4' => 'DOT 4',
            'DOT4' => 'DOT 4', // Убираем пробел
            'VW 501 14' => 'VW 501 14', // Современный стандарт VAG (низкая вязкость Class 6) [1]
            'DOT 4 Plus' => 'DOT 4 Plus',
            'DOT 4 +' => 'DOT 4 Plus', // Приводим к единому написанию
            'DOT 4 Class 6' => 'DOT 4 Class 6', // Низковязкая жидкость для ABS/ESP [1]
            'Низкая вязкость DOT 4' => 'DOT 4 Class 6', // Переводим техническое описание в стандарт
            'DOT 3' => 'DOT 3',
            'MB 331.0' => 'MB 331.0',
            'DOT 5.1' => 'DOT 5.1',
            'B 000 750 M3' => 'VAG B 000 750', // Оригинальный артикул жидкости VAG
            'Super DOT 4' => 'Super DOT 4',
            'Супер DOT 4' => 'Super DOT 4', // Убираем кириллицу
            'SEAT 501 14' => 'VW 501 14', // SEAT входит в VAG, стандарт единый
            'VW 501 14-B 000 750-' => 'VW 501 14', // Очищаем склеенный мусорный артикул
            'TUTELA TOP 4' => 'Fiat 9.55597', // Коммерческая Tutela Top 4 соответствует официальному допуску Fiat

            // КРИТИЧЕСКОЕ ИСПРАВЛЕНИЕ БЕЗОПАСНОСТИ:
            // Pentosin CHF 11S — это жидкость ГУР/гидравлики. Перенаправляем её на официальный 
            // тормозной допуск для тех машин (в основном BMW), где парсер перепутал бачки под капотом.
            'Pentosin CHF 11S' => 'DOT 4' 
        ];


        foreach($singleUpdates as $key => $value){
            $this->entityManager->getConnection()->update('car_fill_volume', ['volume_norm' => $value], ['volume' => $key, 'car_fill_type_id' => 1]);
//            usleep(100);
        }
        
        $splitUpdates = [
            'DOT 3|DOT 4' => ['DOT 3', 'DOT 4'],
            'MB 330.1|MB 331.0' => ['MB 330.1', 'MB 331.0'],
            'ESD-M6C57-A|WSS-M6C57-A2' => ['Ford ESD-M6C57-A', 'Ford WSS-M6C57-A2'],

            // Исправление опечатки каталога (лишние нули в конце стандартов Mercedes) 
            // и замена запятой на разделитель элементов
            'MB 330.01, MB 331.00' => ['MB 330.1', 'MB 331.0'] 
        ];

        $doubleType = $this->entityManager->getRepository(CarFillType::class)
                ->find(2);
        
        foreach($splitUpdates as $key => $row){
            $fillVolumesToUpdate = $this->entityManager->getRepository(CarFillVolume::class)
                    ->findBy(['volume' => $key, 'carFillType' => 1]);
            
            foreach ($fillVolumesToUpdate as $fillVolumeToUpdate){
                
                $fillVolumeToUpdate->setVolumeNorm($row[0]);
                $this->entityManager->persist($fillVolumeToUpdate);
                $this->entityManager->flush();
                
                $k=1;
                while(!empty($row[$k])){
                    $this->doubleCarFillVolume($fillVolumeToUpdate, $doubleType, $row[$k]);
                    $k++;
                }               
            }            
            
//            usleep(100);
        }        
        
        return;
    }    
    
    /**
     * Обновить нормы масло ГУР
     * @return null
     */
    public function updateCarGurOilVolumeNorms()
    {
        //set_time_limit(0);
//        
//        $singleUpdates = [
//            'ATF Dexron II D' => 'GM Dexron II-D',
//            'ATF Dexron III' => 'GM Dexron III-G',
//            'ATF Dexron II' => 'GM Dexron II-D',
//            'G 004 000 M2' => 'VAG G 004 000', // Зеленая минералка/синтетика VAG
//            'MB 236.3' => 'MB 236.3',         // Специальное желтое масло ГУР Mercedes
//            'MB 345.0' => 'MB 345.0',         // Зеленая гидравлика Mercedes (аналог CHF 11S)
//            'PSA S71 2710' => 'PSA S71 2710', // Жидкость ГУР/подвески LDS для Citroen/Peugeot
//            'WSS-M2C204-A2' => 'Ford WSS-M2C204-A2',
//            'WSA-M2C195-A' => 'Ford WSA-M2C195-A',
//            'PSF-3' => 'Hyundai PSF-3',       // Корейский стандарт ГУР (красный/коричневый)
//            'WSS-M2C204-A' => 'Ford WSS-M2C204-A',
//            'ATF 22' => 'GM Dexron II-D',       // Спецификация масла Mobil ATF 220 соответствовала Dexron II
//            'TOTAL H50126' => 'Hyundai PSF-3', // Total H50126 — это и есть оригинальный PSF-3 для KIA/Hyundai
//            'SQM-2C9010-A' => 'Ford SQM-2C9010-A',
//            'Pentosin CHF 202' => 'Pentosin CHF 202',
//            'MB 344.0' => 'MB 344.0',         // Масло для систем регулировки дорожного просвета MB
//            'FIAT 9.55550-AG3' => 'Fiat 9.55550-AG3',
//            'TUTELLA GI/E' => 'Fiat 9.55550-AG2', // Исправлена опечатка в Tutela + кросс на допуск
//            'TUTELA GI/E' => 'Fiat 9.55550-AG2',
//            'G 002 000 A2' => 'VAG G 002 000', // Легендарный Pentosin CHF 11S в оригинальной упаковке VAG
//            'ELF RENAULT MATIC D2' => 'GM Dexron II-D',
//            'Pentosin CHF 7.1' => 'Pentosin CHF 7.1', // Старая минеральная гидравлика (зеленая)
//            'MB 231.1' => 'MB 231.1',
//            'G 009 300 A2' => 'VAG G 009 300',
//            'Saab 1890' => 'Saab 4634281',     // Привязка к оригинальному артикулу ГУР старых Saab 9000/900
//            'Texaco Cold Climata Fluid 33270' => 'Texaco Cold Climate PSF', // Исправлена опечатка в Climata
//            'MB 343.0' => 'MB 343.0',
//            'Texaco PSF 14315' => 'Texaco Cold Climate PSF', // Это один и тот же продукт Land Rover/Volvo
//            'TUTELLA GI/R' => 'Fiat 9.55550-AG3', // Исправлена опечатка Tutela + кросс на допуск (зеленый CHF)
//            'JLM 21703' => 'Jaguar JLM 21703',
//            'APOLLOIL PSF-2M' => 'Honda PSF-S', // Спецификация Apolloil PSF-2M полностью заменяется на PSF-S
//            'MS-9602' => 'Chrysler MS-9602',   // Он же ATF+4, официально используемый в ГУР Chrysler/Jeep
//            'MS-11655' => 'Chrysler MS-11655', // Низкотемпературная гидравлика (аналог CHF 11S)
//            'PSF-V' => 'Nissan PSF-II',       // Соответствие для японских ГУР
//            'ATF M-III' => 'Mazda ATF M-III',
//            'PSF-2' => 'Honda PSF-S',
//            'PSF-S' => 'Honda PSF-S',         // Оригинальная жидкость ГУР Honda
//            'MOPAR Power Steering Fluid' => 'Chrysler MS-5931', // Спецификация классической прозрачной PSF Mopar
//            'PSF-4' => 'Hyundai PSF-4',       // Современная зеленая синтетика для корейцев
//
//            // Блок оригинальных OEM номеров GM (Opel/Saab):
//            '90350341/1940699' => 'GM 1940699',
//            '93160548/1949715' => 'GM 1940715', // Исправлена опечатка в коде (1949->1940)
//            '90513486/1940707' => 'GM 1940707',
//            '1940691/1940699' => 'GM 1940699',
//            '90544116/1940766' => 'GM 1940766',
//            '08886-01206' => 'Toyota PSF New Generation' // Жидкость ГУР для Land Rover / Toyota от японского завода
//        ];

        $singleUpdates = [
            'VOLVO 97305' => 'Volvo 97305',
            'API GL4 SAE 75W-90' => 'API GL-4 75W-90',
            'API GL4' => 'API GL-4',
            'MAN 341 Typ Z5' => 'MAN 341 Typ Z5',
            'MB 235.10' => 'MB 235.10',
            'API GL4 SAE 80W' => 'API GL-4 80W',
            'G 052 171 A2' => 'VAG G 052 171',
            'G 052 162 A2' => 'VAG G 052 162', // Масло ATF, используемое в ряде раздаток
            'API GL5 SAE 90' => 'API GL-5 90',
            '83 22 0 397 244' => 'BMW TF 0870', // Легендарное масло для раздаток xDrive (ATC300/ATC400/ATC500)
            'G 052 145 S2' => 'VAG G 052 145', // Символ S2 нормализуем к базовому семейству G052145
            'G 052 515 A2' => 'VAG G 052 515', // Масло раздатки Touareg / Q7
            'API GL5 SAE 75W-85' => 'API GL-5 75W-85',
            'MAN 342' => 'MAN 342 Typ M1',     // Базовый MAN 342 без типа приравнивается к минеральному M1
            'G 055 145 A2' => 'VAG G 055 145', // Масло для дифференциалов Торсен с модификатором трения Sturaco
            'WSL-M2C192-A' => 'Ford WSL-M2C192-A',
            '83 22 9 408 942' => 'BMW MTF-LT-1',
            'API GL5 SAE 75W-140' => 'API GL-5 75W-140',
            '31259380' => 'Volvo 31259380',     // Оригинальное масло угловой передачи/раздатки Volvo
            'Shell TF 0753' => 'BMW TF 0753',   // Ранняя спецификация для раздаток БМВ (заменяется на TF 0870)
            '93 165 383' => 'GM 1940182',       // Внутренний артикул трансмиссионного масла Opel/GM
            'API GL5 SAE 85W-90' => 'API GL-5 85W-90',
            'MB 236.13' => 'MB 236.13',
            'WSP-M2C197-A' => 'Ford WSP-M2C197-A',
            '81 22 9 400 272' => 'BMW 81229400272',
            'G 052 536 A2' => 'VAG G 052 536',
            'MAN 342 Typ S1' => 'MAN 342 Typ S1',
            'MAN 342 Typ M1' => 'MAN 342 Typ M1',
            '83 22 9 407 858' => 'BMW MTF-LT-2',
            '000 043 301 36' => 'Porsche 00004330136',
            'Mopar NV146' => 'Mopar NV146',     // Эксклюзивное масло для раздаток Jeep Grand Cherokee SRT8
            'Castrol BOT 118' => 'VAG G 052 171', // Продукт BOT 118 поставлялся на конвейер VAG под этим кодом
            'JLM 20771' => 'Jaguar JLM 20771',
            'FIAT 9.55550-DA3' => 'Fiat 9.55550-DA3',
            '8U7J-19G518-BA' => 'Ford 19G518',  // Инженерный код масла раздатки Ford Kuga / Land Rover
            'G 052 533 A2' => 'VAG G 052 533',
            'Mopar 05016796' => 'Mopar MS-10216', // Жидкость раздаточных коробок NV245 / NV247 / NV249
            'ATC 700' => 'BMW TF 0870',         // Раздатка ATC 700 (BMW X5 E70) жестко требует масло класса TF 0870

            // Блок OEM-кодов GM (Opel)
            '90443530/1940703' => 'GM 1940703'
        ];
        
        foreach($singleUpdates as $key => $value){
            $this->entityManager->getConnection()->update('car_fill_volume', ['volume_norm' => $value], ['volume' => $key, 'car_fill_type_id' => 1]);
//            usleep(100);
        }
        
//        $splitUpdates = [
//            'MB 236.2|MB 236.3' => ['MB 236.2', 'MB 236.3'],
//            'ATF Dexron II|ATF Dexron III' => ['GM Dexron II-D', 'GM Dexron III-G'],
//            'Pentosin CHF 202|Pentosin CHF 11S' => ['Pentosin CHF 202', 'Pentosin CHF 11S'],
//            'ATF Dexron II D|ATF Dexron II E' => ['GM Dexron II-D', 'GM Dexron II-E'],
//            'ATF M-III|ATF Dexron II E' => ['Mazda ATF M-III', 'GM Dexron II-E'],
//            'Dexron II E / II / III' => ['GM Dexron II-E', 'GM Dexron II-D', 'GM Dexron III-G']
//        ];

        $splitUpdates = [
            // Универсальные масла, удовлетворяющие обоим классам (обычно это продукты 75W-90 GL-4/5)
            'API GL4 SAE 75W-90|API GL5 SAE 75W-90' => ['API GL-4 75W-90', 'API GL-5 75W-90'],

            // Разделение грузовых гипоидных спецификаций Mercedes-Benz
            'MB 235.1|MB 235.5' => ['MB 235.1', 'MB 235.5']
        ];
        

        $doubleType = $this->entityManager->getRepository(CarFillType::class)
                ->find(2);
        
        foreach($splitUpdates as $key => $row){
            $fillVolumesToUpdate = $this->entityManager->getRepository(CarFillVolume::class)
                    ->findBy(['volume' => $key, 'carFillType' => 1]);
            
            foreach ($fillVolumesToUpdate as $fillVolumeToUpdate){
                
                $fillVolumeToUpdate->setVolumeNorm($row[0]);
                $this->entityManager->persist($fillVolumeToUpdate);
                $this->entityManager->flush();
                
                $k=1;
                while(!empty($row[$k])){
                    $this->doubleCarFillVolume($fillVolumeToUpdate, $doubleType, $row[$k]);
                    $k++;
                }               
            }            
            
//            usleep(100);
        }        
        
        return;
    }        
}
