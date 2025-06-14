<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service;

use Laminas\ServiceManager\ServiceManager;
use Application\Entity\Make;
use Application\Entity\Model;
use Laminas\Json\Decoder;
use Laminas\Json\Json;
use Admin\Filter\TransferName;
use Application\Entity\Car;

/**
 * Description of MakeService
 *
 * @author Daddy
 */
class MakeManager
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
     * @return null
     */
    public function fillMakes()
    {
        $this->externalManager->fillMakes();
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
        $this->externalManager->fillModels($make);
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
    
    /**
     * Обновить полное наименование
     * 
     * @param Make $make
     * @param string $fullName
     */
    public function updateFullName($make, $fullName)
    {
        $make->setFullName($fullName);
        $this->entityManager->persist($make);
        $this->entityManager->flush($make);
        
        return;
    }

    /**
     * Обновить наименование ru
     * 
     * @param Make $make
     * @param string $nameRu
     */
    public function updateRuName($make, $nameRu)
    {
        $make->setNameRu($nameRu);
        $this->entityManager->persist($make);
        $this->entityManager->flush();
        
        return;
    }
    
    /**
     * Обновить полное наименование модели
     * 
     * @param Model $model
     * @param string $fullName
     */
    public function updateModelFullName($model, $fullName)
    {
        $model->setFullName($fullName);
        $this->entityManager->persist($model);
        $this->entityManager->flush($model);
        
        return;
    }

    /**
     * Обновить наименование ru
     * 
     * @param Model $model
     * @param string $nameRu
     */
    public function updateModelRuName($model, $nameRu)
    {
        $model->setNameRu($nameRu);
        $this->entityManager->persist($model);
        $this->entityManager->flush();
        
        return;
    }
    
    /**
     * Получить данные с https://cars-base.ru/#api
     * @param Make $make
     */
    private function findMakeBase($make)
    {
//        var_dump($make->getTransferName(), strtoupper($make->getFullName()));
        
        $result = Decoder::decode(file_get_contents('https://cars-base.ru/api/cars/'.$make->getTransferName()), Json::TYPE_ARRAY);
        if (!empty($result['error']) && $make->getFullName()){
            $result = Decoder::decode(file_get_contents('https://cars-base.ru/api/cars/'. strtoupper($make->getFullName())), Json::TYPE_ARRAY);            
        }
        return $result;
    }
    
    /**
     * Заполнить наименование рус моделей марки
     * 
     * @param Make $make
     */
    public function fillMakeModelsNameRu($make)
    {
        $data = $this->findMakeBase($make);
        $transferFilter = new TransferName();
        foreach ($data as $row){
            
            if (!empty($row['name'])){
                $nameEn = $transferFilter->filter($row['name']);

                $models = $this->entityManager->getRepository(Model::class)
                        ->findMakeModelByName($make, $nameEn);

    //            var_dump($row['name'], count($models));
                foreach ($models as $model){
                    $nameRu = str_replace(strtoupper($nameEn), $row['cyrillic-name'], $model->getTransferName());
                    if ($nameRu != $model->getTransferName()){
                        $model->setNameRu($nameRu);
                        $this->entityManager->persist($model);
                        continue;
                    }    

                    $nameRu = str_replace(strtoupper($nameEn), $row['cyrillic-name'], $model->getFullName());
                    if ($nameRu != $model->getFullName()){
                        $model->setNameRu($nameRu);
                        $this->entityManager->persist($model);
                        continue;
                    }    
                }
            }    
        }
        
        $this->entityManager->flush();
        
        return;        
    }
    
    /**
     * Заполнить name Ru 
     */
    public function fillNameRu()
    {
        $makes = $this->entityManager->getRepository(Make::class)
                ->findPopularMakes();
        
        foreach ($makes as $make){
            $data = $this->findMakeBase($make);
            var_dump($data);
            exit;
        }
        
        return;
    }
    
    
    /**
     * Поправить имя модели
     * @param Model $model
     */
    public function fixModelFullName($model)
    {
//        $model->setFullName(preg_replace('/\s*\([^)]*\)\s*/', '', $model->getName()));
//        $this->entityManager->persist($model);
//        $this->entityManager->flush();
        $newName =  trim(preg_replace('/\s*\([^)]*\)\s*/', '', preg_replace('/наклонная\s+задняя\s+часть/iu', 'хэтчбек', trim($model->getName()))));
        $this->entityManager->getConnection()->update('model', [
            'fullname' => $newName,
            'name_ru' => $newName,
        ], ['id' => $model->getId()]);
    }
    
    /**
     * Исправить наименования всех моделей
     */
    public function fixModelFullNames() 
    {
        $models = $this->entityManager->getRepository(Model::class)
                ->findAll();
        foreach ($models as $model){
            $this->fixModelFullName($model);
        }
    }
    
    /**
     * Поправить имя модели
     * @param Car $car
     */
    public function fixCarFullName($car)
    {
        $details = $car->getVehicleDetailsCarAsArray();
        if (!empty($details)){
            $year_from = (int) substr($details['yearOfConstrFrom'], 0, 4);
            $year_to = (int) substr($details['yearOfConstrTo'], 0, 4);
        }    
        
        $newName =  $car->getModel()->getMake()->getName(). ' ' . $car->getModel()->getFullName() . ' ' . $car->getName();
        if (!empty($year_from)){
            $newName .= ' c '. $year_from;
        }
        $this->entityManager->getConnection()->update('car', [
            'details' => json_encode($details),
            'year_from' => $year_from,
            'year_to' => $year_to,
            'fullname' => $newName,
        ], ['id' => $car->getId()]);
    }
    
    /**
     * Исправить наименования всех машин
     */
    public function fixCarFullNames() 
    {
        ini_set('memory_limit', '1024M');
        
        $cars = $this->entityManager->getRepository(Car::class)
                ->findAll();
        foreach ($cars as $car){
            $this->fixCarFullName($car);
        }
    }    
}
