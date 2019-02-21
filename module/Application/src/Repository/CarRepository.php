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
     * @param Application\Entity\Make $make
     * @param Application\Entity\Model $model
     * @param Application\Entity\Car $car
     * 
     * @return null
     */
    public function updateAvailable($make = null, $model = null, $car = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('count(g.id) as goodCount')
            ->from(Car::class, 'c')
            ->leftJoin('c.goods', 'g')
            ;
        
        if ($car){
            $queryBuilder
                    ->addSelect('c.id as carId')
                    ->andWhere('c.id = ?1')
                    ->groupBy('c.id')    
                    ->setParameter('1', $car->getId())
                    ;
        } elseif ($model){
            $queryBuilder
                    ->addSelect('identity(c.model) as modelId')
                    ->join('c.model', 'm')
                    ->andWhere('m.id = ?2')
                    ->groupBy('modelId')    
                    ->setParameter('2', $model->getId())
                    ;
        } elseif ($make){
            $queryBuilder
                    ->addSelect('identity(c.make) as makeId')
                    ->join('c.model', 'm')
                    ->groupBy('makeId')    
                    ->andWhere('m.make = ?3')
                    ->setParameter('3', $make->getId())
                    ;
        }
        
        $data = $queryBuilder->getQuery()->getResult();
        
        foreach ($data as $row){
            switch ($row['goodCount']){
                case 0: $status = Car::STATUS_RETIRED; break;
                default: $status = Car::STATUS_ACTIVE;
            }
//            var_dump($status); exit;
            if (isset($row['carId'])){
                $this->getEntityManager()->getConnection()->update('car', ['status' => $status], ['id' => $row['carId']]);
            }    
            if (isset($row['modelId'])){
                $this->getEntityManager()->getConnection()->update('model', ['status' => $status], ['id' => $row['modelId']]);
            }    
            if (isset($row['makeId'])){
                $this->getEntityManager()->getConnection()->update('make', ['status' => $status], ['id' => $row['makeId']]);
            }    
        }      
        
        return;
    }    
}
