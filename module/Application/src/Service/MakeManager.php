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
        $result = Decoder::decode(file_get_contents('https://cars-base.ru/api/cars/'.$make->getName()), Json::TYPE_ARRAY);
        if (!empty($result['error'])){
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
        foreach ($data as $row){
            try{
                $models = $this->entityManager->getRepository(Model::class)
                        ->findMakeModelByName($make, $row['name']);
            } catch(\Doctrine\ORM\Query\QueryException $e){
                $models = [];
            }    
//            var_dump($row['name'], count($models));
            foreach ($models as $model){
                $nameRu = str_replace(strtoupper($row['name']), $row['cyrillic-name'], $model->getName());
                $model->setNameRu($nameRu);
                $this->entityManager->persist($model);
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
}
