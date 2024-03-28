<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Company\Repository;

use Doctrine\ORM\EntityRepository;
use Company\Entity\Cost;
use Company\Entity\CostMutual;
use Stock\Entity\Movement;

/**
 * Description of CostRepository
 *
 * @author Daddy
 */
class CostRepository extends EntityRepository
{

    /**
     * Выборка для формы
     * 
     * @param array params
     */
    public function formFind($params)
    {
        $cost = null;
        if (!empty($params['cost'])){
            $cost = $params['cost'];
        }

        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('c')
            ->from(Cost::class, 'c')
                ;
        
        if ($cost){
//            $queryBuilder->where('c.id = ?1')
//                    ->setParameter(1, $cost->getId())
//                    ;
        } else {
            $queryBuilder
                    ->andWhere('c.status = ?2')
                    ->setParameter('2', Cost::STATUS_ACTIVE)
                    ;
            
        }

        return $queryBuilder->getQuery()->getResult();       
    }
    
    /**
     * Получить операции
     * @param array $params
     * @return query
     */
    public function findMutuals($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('cm, c, cost, cd')
            ->from(CostMutual::class, 'cm')
            ->join('cm.company', 'c')    
            ->join('cm.cost', 'cost')    
            ->leftJoin('cm.cashDoc', 'cd', 'WITH', 'cm.docType = '.Movement::DOC_CASH) 
                ;
        
        if (is_array($params)){
            if (!empty($params['company'])){
                $queryBuilder->andWhere('cm.company = :company')
                        ->setParameter('company', $params['company'])
                        ;
            }            
            if (!empty($params['cost'])){
                if (is_numeric($params['cost'])){
                    $queryBuilder->andWhere('cm.cost = :cost')
                            ->setParameter('cost', $params['cost'])
                            ;
                }    
            }            
            if (!empty($params['startDate'])){
                $queryBuilder->andWhere("cm.dateOper >= :startDate")
                        ->setParameter('startDate', $params['startDate'])
                        ;
            }
            if (!empty($params['endDate'])){
                $queryBuilder->andWhere("cm.dateOper <= :endDate")
                        ->setParameter('endDate', $params['endDate'])
                        ;
            }
            if (isset($params['sort'])){
                $queryBuilder->orderBy('cm.'.$params['sort'], $params['order']);
            }            
        }    
//                var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();       
    }
    
    /**
     * Получить операции
     * @param array $params
     * @return query
     */
    public function findMutualsTotal($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('sum(cm.amount) as amount')
            ->from(CostMutual::class, 'cm')
            ->where('cm.status = :status')
            ->setParameter('status', CostMutual::STATUS_ACTIVE)    
                ;
        
        if (is_array($params)){
            if (!empty($params['company'])){
                $queryBuilder->andWhere('cm.company = :company')
                        ->setParameter('company', $params['company'])
                        ;
            }            
            if (!empty($params['cost'])){
                if (is_numeric($params['cost'])){
                    $queryBuilder->andWhere('cm.cost = :cost')
                            ->setParameter('cost', $params['cost'])
                            ;
                }    
            }            
            if (!empty($params['startDate'])){
                $queryBuilder->andWhere("cm.dateOper >= :startDate")
                        ->setParameter('startDate', $params['startDate'])
                        ;
            }
            if (!empty($params['endDate'])){
                $queryBuilder->andWhere("cm.dateOper <= :endDate")
                        ->setParameter('endDate', $params['endDate'])
                        ;
            }
        }    
        
        $queryBuilder->setMaxResults(1);
        
//                var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getOneOrNullResult();       
    }
}