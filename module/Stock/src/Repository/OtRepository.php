<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Stock\Repository;

use Doctrine\ORM\EntityRepository;
use Company\Entity\Office;
use Stock\Entity\Ot;
use Stock\Entity\OtGood;

/**
 * Description of OtRepository
 *
 * @author Daddy
 */
class OtRepository extends EntityRepository{
    
    /**
     * Сумма Ot
     * 
     * @param Ot $ot 
     * @return float
     */
    public function otAmountTotal($ot)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('sum(og.amount) as total')
                ->from(OtGood::class, 'og')
                ->where('og.ot = ?1')
                ->setParameter('1', $ot->getId())
                ->setMaxResults(1)
                ;
        
        $result = $queryBuilder->getQuery()->getOneOrNullResult();
        
        if (!empty($result['total'])){
            return $result['total'];
        }
        
        return 0;
    }

    /**
     * Запрос по ОТ
     * 
     * @param array $params
     * @return query
     */
    public function findAllOt($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('o, oo, c, u')
            ->from(Ot::class, 'o')
            ->join('o.office', 'oo')    
            ->join('o.company', 'c')    
            ->leftJoin('o.comiss', 'u')    
                ;
        
        if (is_array($params)){
            if (isset($params['sort'])){
                $queryBuilder->orderBy('o.'.$params['sort'], $params['order']);
            }            
            if (!empty($params['officeId'])){
                $office = $entityManager->getRepository(Office::class)
                        ->findOneById($params['officeId']);
                if ($office){
                    $queryBuilder->andWhere('o.office = ?2')
                            ->setParameter('2', $office->getId());
                }
            }
            if (!empty($params['year'])){
                if (is_numeric($params['year'])){
                    $queryBuilder->andWhere('YEAR(o.docDate) = :year')
                            ->setParameter('year', $params['year']);
                }    
            }
            if (!empty($params['month'])){
                if (is_numeric($params['month'])){
                    $queryBuilder->andWhere('MONTH(o.docDate) = :month')
                            ->setParameter('month', $params['month']);
                }    
            }
        }
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();
    }    
    
    
    /**
     * Запрос по все ОТ
     * 
     * @param array $params
     * @return query
     */
    public function queryAllOt($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('o')
            ->from(Ot::class, 'o')
                ;
        
        if (is_array($params)){
            if (!empty($params['officeId'])){
                $office = $entityManager->getRepository(Office::class)
                        ->findOneById($params['officeId']);
                if ($office){
                    $queryBuilder->andWhere('o.office = ?1')
                            ->setParameter('1', $office->getId());
                }
            }
            if (!empty($params['year'])){
                if (is_numeric($params['year'])){
                    $queryBuilder->andWhere('YEAR(o.docDate) = :year')
                            ->setParameter('year', $params['year']);
                }    
            }            
            if (!empty($params['month'])){
                if (is_numeric($params['month'])){
                    $queryBuilder->andWhere('MONTH(o.docDate) = :month')
                            ->setParameter('month', $params['month']);
                }    
            }
        }
        
        return $queryBuilder->getQuery();
    }    
    
    /**
     * Запрос по  количество ОТ
     * 
     * @param array $params
     * @return query
     */
    public function findAllOtTotal($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('count(o.id) as countOt')
            ->from(Ot::class, 'o')
                ;
        
        if (is_array($params)){
            if (!empty($params['officeId'])){
                $office = $entityManager->getRepository(Office::class)
                        ->findOneById($params['officeId']);
                if ($office){
                    $queryBuilder->andWhere('o.office = ?2')
                            ->setParameter('2', $office->getId());
                }
            }
            if (!empty($params['year'])){
                if (is_numeric($params['year'])){
                    $queryBuilder->andWhere('YEAR(o.docDate) = :year')
                            ->setParameter('year', $params['year']);
                }    
            }
            if (!empty($params['month'])){
                if (is_numeric($params['month'])){
                    $queryBuilder->andWhere('MONTH(o.docDate) = :month')
                            ->setParameter('month', $params['month']);
                }    
            }
        }
        
        $result = $queryBuilder->getQuery()->getOneOrNullResult();

        return $result['countOt'];
    }    
    
    /**
     * Запрос товаров по ОТ
     * 
     * @param integer $otId
     * @param array $params
     * @return query
     */
    public function findOtGoods($otId, $params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('og, g, p')
            ->from(OtGood::class, 'og')
            ->join('og.good', 'g')    
            ->join('g.producer', 'p')    
            ->where('og.ot = ?1')
            ->setParameter('1', $otId)    
                ;
        
        if (is_array($params)){
            if (isset($params['sort'])){
            }            
        }
//        var_dump($queryBuilder->getQuery()->getSQL());
        return $queryBuilder->getQuery();
    }        
}