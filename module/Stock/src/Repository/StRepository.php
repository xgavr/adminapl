<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Stock\Repository;

use Doctrine\ORM\EntityRepository;
use Company\Entity\Office;
use Stock\Entity\St;
use Stock\Entity\StGood;

/**
 * Description of StRepository
 *
 * @author Daddy
 */
class StRepository extends EntityRepository{
    
    /**
     * Сумма St
     * 
     * @param St $st 
     * @return float
     */
    public function stAmountTotal($st)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('sum(sg.amount) as total')
                ->from(StGood::class, 'sg')
                ->where('sg.st = ?1')
                ->setParameter('1', $st->getId())
                ->setMaxResults(1)
                ;
        
        $result = $queryBuilder->getQuery()->getOneOrNullResult();
        
        if (!empty($result['total'])){
            return $result['total'];
        }
        
        return 0;
    }

    /**
     * Запрос по СТ
     * 
     * @param array $params
     * @return query
     */
    public function findAllSt($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('s, oo, c, u, co')
            ->from(St::class, 's')
            ->join('s.office', 'oo')    
            ->join('s.company', 'c')    
            ->leftJoin('s.user', 'u')    
            ->leftJoin('s.cost', 'co')    
                ;
        
        if (is_array($params)){
            if (isset($params['sort'])){
                $queryBuilder->orderBy('s.'.$params['sort'], $params['order']);
            }            
            if (!empty($params['officeId'])){
                $office = $entityManager->getRepository(Office::class)
                        ->findOneById($params['officeId']);
                if ($office){
                    $queryBuilder->andWhere('s.office = ?2')
                            ->setParameter('2', $office->getId());
                }
            }
            if (!empty($params['year'])){
                if (is_numeric($params['year'])){
                    $queryBuilder->andWhere('YEAR(s.docDate) = :year')
                            ->setParameter('year', $params['year']);
                }    
            }
            if (!empty($params['month'])){
                if (is_numeric($params['month'])){
                    $queryBuilder->andWhere('MONTH(s.docDate) = :month')
                            ->setParameter('month', $params['month']);
                }    
            }
        }
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();
    }    
    
    
    /**
     * Запрос по все СТ
     * 
     * @param array $params
     * @return query
     */
    public function queryAllSt($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('s')
            ->from(St::class, 's')
                ;
        
        if (is_array($params)){
            if (!empty($params['officeId'])){
                $office = $entityManager->getRepository(Office::class)
                        ->findOneById($params['officeId']);
                if ($office){
                    $queryBuilder->andWhere('s.office = ?1')
                            ->setParameter('1', $office->getId());
                }
            }
            if (!empty($params['year'])){
                if (is_numeric($params['year'])){
                    $queryBuilder->andWhere('YEAR(s.docDate) = :year')
                            ->setParameter('year', $params['year']);
                }    
            }            
            if (!empty($params['month'])){
                if (is_numeric($params['month'])){
                    $queryBuilder->andWhere('MONTH(s.docDate) = :month')
                            ->setParameter('month', $params['month']);
                }    
            }
        }
        
        return $queryBuilder->getQuery();
    }    
    
    /**
     * Запрос по  количество СТ
     * 
     * @param array $params
     * @return query
     */
    public function findAllStTotal($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('count(s.id) as countSt')
            ->from(St::class, 's')
                ;
        
        if (is_array($params)){
            if (!empty($params['officeId'])){
                $office = $entityManager->getRepository(Office::class)
                        ->findOneById($params['officeId']);
                if ($office){
                    $queryBuilder->andWhere('s.office = ?2')
                            ->setParameter('2', $office->getId());
                }
            }
            if (!empty($params['year'])){
                if (is_numeric($params['year'])){
                    $queryBuilder->andWhere('YEAR(s.docDate) = :year')
                            ->setParameter('year', $params['year']);
                }    
            }
            if (!empty($params['month'])){
                if (is_numeric($params['month'])){
                    $queryBuilder->andWhere('MONTH(s.docDate) = :month')
                            ->setParameter('month', $params['month']);
                }    
            }
        }
        
        $result = $queryBuilder->getQuery()->getOneOrNullResult();

        return $result['countSt'];
    }    
    
    /**
     * Запрос товаров по СТ
     * 
     * @param integer $stId
     * @param array $params
     * @return query
     */
    public function findStGoods($stId, $params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('sg, g, p')
            ->from(StGood::class, 'sg')
            ->join('sg.good', 'g')    
            ->join('g.producer', 'p')    
            ->where('sg.st = ?1')
            ->setParameter('1', $stId)    
                ;
        
        if (is_array($params)){
            if (isset($params['sort'])){
            }            
        }
//        var_dump($queryBuilder->getQuery()->getSQL());
        return $queryBuilder->getQuery();
    }        
}