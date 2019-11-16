<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Repository;

use Doctrine\ORM\EntityRepository;
use Application\Entity\Make;
use Application\Entity\Model;
use Application\Entity\Car;
use Application\Entity\VehicleDetail;
use Application\Entity\VehicleDetailValue;
use Application\Entity\VehicleDetailCar;

/**
 * Description of CarRepository
 *
 * @author Daddy
 */
class CarRepository extends EntityRepository
{

    /**
     * Быстрая обновление Car
     * 
     * @param Application\Entity\Car
     * @param array $data 
     * @return integer
     */
    public function updateCar($car, $data)
    {
        $updated = $this->getEntityManager()->getConnection()->update('car', $data, ['id' => $car->getId()]);
        return $updated;
    }    

    public function findGoodCar($good, $car)
    {
        $entityManager = $this->getEntityManager();

        $sql = 'select gc.id '
                . ' from good_car as gc '
                . ' where gc.good_id = :good'
                . ' and gc.car_id = :car';

//        var_dump($sql); exit;

        $stmt = $entityManager->getConnection()->prepare($sql);
        $stmt->execute([
                'good' => $good->getId(),
                'car' => $car->getId(),
            ]);

        return $stmt->fetchAll();
        
    }
    
    /**
     * Удаление атрибутов Car
     * 
     * @param Application\Entity\Car
     * @return integer
     */
    public function deleteCarAttributeValue($car)
    {
        $deleted = $this->getEntityManager()->getConnection()->delete('car_attribute_value', ['car_id' => $car->getId()]);
        return $deleted;
    }    

    /**
     * Запрос по машин по разным моделям
     * 
     * @param array $params
     * @return object
     */
    public function findAllCar($model, $params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('m')
            ->from(Car::class, 'm')
            ->where('m.model = ?0')
            ->setParameter('0', $model->getId())    
                ;
        
        if (is_array($params)){
            if (isset($params['q'])){
                $queryBuilder->andWhere('m.name like :search')
                    ->setParameter('search', '%' . $params['q'] . '%')
                        ;
            }
            if (isset($params['next1'])){
                $queryBuilder->andWhere('m.tdId > ?1')
                    ->setParameter('1', $params['next1'])
                    ->orderBy('m.tdId')
                    ->setMaxResults(1)    
                 ;
            }
            if (isset($params['prev1'])){
                $queryBuilder->andWhere('m.tdId < ?2')
                    ->setParameter('2', $params['prev1'])
                    ->orderBy('m.tdId', 'DESC')
                    ->setMaxResults(1)    
                 ;
            }
            if (isset($params['sort'])){
                $queryBuilder->orderBy('m.'.$params['sort'], $params['order']);                
            }            
        }

        return $queryBuilder->getQuery();
    }           
    
    /**
     * Найти товары машины
     * 
     * @param Application\Entity\Car $car
     * @return object
     */
    public function findGoods($car)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('g')
            ->from(\Application\Entity\Goods::class, 'g')
            ->join('g.cars', 'c')
            ->where('c.id = ?1')    
            ->setParameter('1', $car->getId())
            ;
        
        return $queryBuilder->getQuery();            
    }
    
    /**
     * Обновить доступность машин
     * 
     * @param Application\Entity\Car $car
     * 
     * @return null
     */
    public function updateAvailable($car)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('count(g.id) as goodCount')
            ->from(Car::class, 'c')
            ->leftJoin('c.goods', 'g')
            ->groupBy('c.id')    
            ->where('c.id = ?1')
            ->setParameter('1', $car->getId())
            ;
                
        $data = $queryBuilder->getQuery()->getResult();
        
        $result = false;
        foreach ($data as $row){
            switch ($row['goodCount']){
                case 0: $result = true; $status = Car::STATUS_RETIRED; break;
                default: $status = Car::STATUS_ACTIVE; break;
            }
            
            $this->getEntityManager()->getConnection()->update('car', ['status' => $status, 'good_count' => $row['goodCount']], ['id' => $car->getId()]);
        }      
        
        return $result;
    }    
    
    /**
     * Обновить доступность моделей
     * 
     * @param Application\Entity\Model $model
     * 
     * @return null
     */
    public function updateAvailableModel($model)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('c')
            ->from(Car::class, 'c')
            ->where('c.model = ?1')
            ->setParameter('1', $model->getId())
            ;
                
        $cars = $queryBuilder->getQuery()->getResult();
        
        $modelResult = false;
        foreach ($cars as $car){
            $carResult = $this->updateAvailable($car);
            if ($carResult){
                $modelResult = true;
            }
        }      
        
        $result = false;
        switch ($modelResult){
            case true: $result = true; $status = Model::STATUS_RETIRED; break;
            default: $status = Model::STATUS_ACTIVE; break;
        }
            
        $this->getEntityManager()->getConnection()->update('model', ['status' => $status], ['id' => $model->getId()]);
        
        return $result;
    }    
    
    /**
     * Обновить доступность брендов
     * 
     * @param Application\Entity\Make $make
     * 
     * @return null
     */
    public function updateAvailableMake($make)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('m')
            ->from(Model::class, 'm')
            ->where('m.make = ?1')
            ->setParameter('1', $model->getId())
            ;
                
        $models = $queryBuilder->getQuery()->getResult();
        
        foreach ($models as $model){
            $this->updateAvailableModel($model);
        }      
        
        return;
    }    
    
    /**
     * Обновить статус машин, в зависимости от количества товара
     * @return null
     */
    public function updateAllCarStatus()
    {
        set_time_limit(600);
        
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('c.id, count(g.id) as goodCount')
            ->from(Car::class, 'c')
            ->leftJoin('c.goods', 'g')  
            ->groupBy('c.id')
            ;
                
        $carIds = $queryBuilder->getQuery()->getResult();
        
        foreach ($carIds as $row){
            switch ($row['goodCount']){
                case 0: $result = true; $status = Car::STATUS_RETIRED; break;
                default: $status = Car::STATUS_ACTIVE; break;
            }
            
            $this->getEntityManager()->getConnection()->update('car', ['status' => $status, 'good_count' => $row['goodCount']], ['id' => $row['id']]);
        }      
        
        return;        
    }

    /**
     * Обновить статус машин, в зависимости от количества товара
     * @return null
     */
    public function updateAllModelStatus()
    {
        set_time_limit(200);
        
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('m.id, sum(c.goodCount) as goodCount')
            ->from(Model::class, 'm')
            ->join('m.cars', 'c')  
            ->groupBy('m.id')
            ;
                
        $modelIds = $queryBuilder->getQuery()->getResult();
        
        foreach ($modelIds as $row){            
            switch ($row['goodCount']){
                case 0: $result = true; $status = Model::STATUS_RETIRED; break;
                default: $status = Model::STATUS_ACTIVE; break;
            }
            $this->getEntityManager()->getConnection()->update('model', ['status' => $status, 'good_count' => $row['goodCount']], ['id' => $row['id']]);
        }      
        
        return;        
    }

    /**
     * Обновить статус марок, в зависимости от количества товара
     * @return null
     */
    public function updateAllMakeStatus()
    {
        set_time_limit(100);
        
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('m.id, sum(mm.goodCount) as goodCount')
            ->from(Make::class, 'm')
            ->join('m.models', 'mm')  
            ->groupBy('m.id')    
            ;
                
        $makeIds = $queryBuilder->getQuery()->getResult();
        
        foreach ($makeIds as $row){            
            switch ($row['goodCount']){
                case 0: $result = true; $status = Model::STATUS_RETIRED; break;
                default: $status = Model::STATUS_ACTIVE; break;
            }
            $this->getEntityManager()->getConnection()->update('make', ['status' => $status, 'good_count' => $row['goodCount']], ['id' => $row['id']]);
        }      
        
        return;        
    }
    
    /**
     * Выборка аттрибутов
     * 
     * @return qyery
     */
    public function findAttributeTypes()
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('cat')
            ->from(\Application\Entity\CarAttributeType::class, 'cat')
            ;
        
        return $queryBuilder->getQuery();
    }
    
    /**
     * Выборка аттрибутов
     * 
     * @return qyery
     */
    public function findVehicleDetails()
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('vd')
            ->from(VehicleDetail::class, 'vd')
            ;
        
        return $queryBuilder->getQuery();
    } 
    
    /**
     * Получить значение атрибута машины
     * 
     * @param Car $car
     * @param string $detailName
     * 
     * @return string 
     */
    public function carDetailValue($car, $detailName)
    {

        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('vdv.name')
                ->from(VehicleDetailCar::class, 'vdc')
                ->join('vdc.vehicleDetailValue', 'vdv')
                ->join('vdv.vehicleDetail', 'vd')
                ->where('vdc.car = ?1')
                ->andWhere('vd.name = ?2')
                ->setParameter('1', $car->getId())
                ->setParameter('2', trim($detailName))
                ->setMaxResults(1)
            ;
        
        $row = $queryBuilder->getQuery()->getOneOrNullResult();
        return $row['name'];
    }
}
