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

/**
 * Description of CarService
 *
 * @author Daddy
 */
class CarManager
{
    
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
    
}
