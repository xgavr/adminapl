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
     * Обновить машина товара по товару с тем же номером
     * @param Goods $good
     */
    public function updateCarsByOem($good)
    {
        
//        if ($good->getCheckCar() === Goods::CHECK_CAR_OE){
//            $this->entityManager->getRepository(Goods::class)
//                ->removeGoodCars($good);
//        } else {        
            if ($good->getCars()->count()){
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
            $data['good'] = $codeFilter->filter(trim($row['A']));

            // --- Oem ---
            $data['oem'] = $codeFilter->filter(trim($row['B']));

            // --- Oem brand ---
            $data['oem_brand'] = trim($row['C']);


            $this->bindGoodCarData($car, $data);
            
            unset($data);
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
        
        if (empty($car)){

            $nameShort = trim(preg_replace('/\(.*?\)/', '', $carData['car_name']));

            $car = new Car();
            $car->setAplId(0);
            $car->setCommerc(Car::COMMERC_NO);
            $car->setDetails(json_encode([
                    'powerHpFrom' => $carData['power_hp'], 
                    'powerHpTo' => $carData['power_hp'], 
                    'powerKwFrom' => $carData['power_kw'], 
                    'powerKwTo' => $carData['power_kw'], 
                    'yearOfConstrFrom' => $carData['year_from'], 
                    'yearOfConstrTo' => $carData['year_to'], 
                    'nameHP' => $nameShort.' '. $carData['year_from'], 
                ]));
            $car->setFillVolumesFlag(Car::FILL_VOLUMES_NO);
            $car->setFullName($model->getMake()->getName(). ' ' . $model->getFullName() . ' ' . $nameShort . ' ' . $carData['power_hp'] . ' с ' . $carData['year_from']);
            $car->setGoodCount(0);
            $car->setModel($model);
            $car->setMoto(Car::MOTO_NO);
            $car->setName($carData['car_name']);
            $car->setPassenger(Car::PASSENGER_NO);
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
}
