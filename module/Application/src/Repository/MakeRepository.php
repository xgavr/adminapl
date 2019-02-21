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

/**
 * Description of MakeRepository
 *
 * @author Daddy
 */
class MakeRepository extends EntityRepository{

    /**
     * Быстрая вставка Make
     * @param array $row 
     * @return integer
     */
    public function insertMake($row)
    {
        $inserted = $this->getEntityManager()->getConnection()->insert('make', $row);
        return $inserted;
    }    

    /**
     * Быстрая обновление Make
     * 
     * @param Application\Entity\Make
     * @param array $data 
     * @return integer
     */
    public function updateMake($make, $data)
    {
        $updated = $this->getEntityManager()->getConnection()->update('make', $data, ['id' => $make->getId()]);
        return $updated;
    }    

    /**
     * Быстрая вставка Model
     * @param array $row 
     * @return integer
     */
    public function insertModel($row)
    {
        $inserted = $this->getEntityManager()->getConnection()->insert('model', $row);
        return $inserted;
    }    

    /**
     * Быстрая обновление Model
     * 
     * @param Application\Entity\Model
     * @param array $data 
     * @return integer
     */
    public function updateModel($model, $data)
    {
        $updated = $this->getEntityManager()->getConnection()->update('model', $data, ['id' => $model->getId()]);
        return $updated;
    }    
    
    public function carMake($make)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('c.id')
            ->distinct()    
            ->from(Model::class, 'm')
            ->join('m.cars', 'c')
            ->where('m.make = ?1')
            ->setParameter('1', $make->getId())    
                ;
        
        return $queryBuilder->getQuery()->getResult();
        
    }

    /**
     * Запрос по машинам по разным параметрам
     * 
     * @param array $params
     * @return object
     */
    public function findAllMake($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('m')
            ->from(Make::class, 'm')
                ;
        
        if (is_array($params)){
            if (isset($params['q'])){
                $queryBuilder->where('m.name like :search')
                    ->setParameter('search', '%' . $params['q'] . '%')
                        ;
            }
            if (isset($params['next1'])){
                $queryBuilder->where('m.name > ?1')
                    ->setParameter('1', $params['next1'])
                    ->setMaxResults(1)    
                 ;
            }
            if (isset($params['prev1'])){
                $queryBuilder->where('m.name < ?2')
                    ->setParameter('2', $params['prev1'])
                    ->orderBy('m.name', 'DESC')
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
     * Найти товары бренда
     * 
     * @param Application\Entity\Make $make
     * @return object
     */
    public function findMakeGoods($make)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('g')
            ->distinct()    
            ->from(\Application\Entity\Goods::class, 'g')
            ->join('g.cars', 'c')
            ->join('c.model', 'm')    
            ->where('m.make = ?1')    
            ->setParameter('1', $make->getId())
            ;
        
        return $queryBuilder->getQuery()->getResult();            
    }

    /**
     * Запрос по моделям машин по разным параметрам
     * 
     * @param array $params
     * @return object
     */
    public function findAllModel($make, $params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('m')
            ->from(Model::class, 'm')
            ->where('m.make = ?0')
            ->setParameter('0', $make->getId())    
                ;
        
        if (is_array($params)){
            if (isset($params['q'])){
                $queryBuilder->andWhere('m.name like :search')
                    ->setParameter('search', '%' . $params['q'] . '%')
                        ;
            }
            if (isset($params['next1'])){
                $queryBuilder->andWhere('m.name > ?1')
                    ->setParameter('1', $params['next1'])
                    ->setMaxResults(1)    
                 ;
            }
            if (isset($params['prev1'])){
                $queryBuilder->andWhere('m.name < ?2')
                    ->setParameter('2', $params['prev1'])
                    ->orderBy('m.name', 'DESC')
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
     * Найти товары модели
     * 
     * @param Application\Entity\Model $model
     * @return object
     */
    public function findModelGoods($model)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('g')
            ->distinct()    
            ->from(\Application\Entity\Goods::class, 'g')
            ->join('g.cars', 'c')
            ->where('c.model = ?1')    
            ->setParameter('1', $model->getId())
            ;
        
        return $queryBuilder->getQuery()->getResult();            
    }
    
    /**
     * Найти товары всех машин
     * 
     * @return object
     */
    public function findGoods()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('gc.id')
            ->distinct()    
            ->from(\Application\Entity\Car::class, 'c')
            ->join('c.goods', 'gc')    
            ;

        return $queryBuilder->getQuery()->getResult();
    }    

}
