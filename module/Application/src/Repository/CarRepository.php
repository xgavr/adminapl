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
}
