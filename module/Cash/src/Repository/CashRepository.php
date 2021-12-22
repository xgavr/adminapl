<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cash\Repository;

use Doctrine\ORM\EntityRepository;
use Cash\Entity\CashDoc;
use Cash\Entity\Cash;
use Cash\Entity\CashTransaction;

/**
 * Description of CashRepository
 *
 * @author Daddy
 */
class CashRepository extends EntityRepository
{
    /**
     * Запрос по кассовым документам
     * 
     * @param array $params
     * @return query
     */
    public function findAllCashDoc($dateOper, $params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('ct, cd, c')
            ->from(CashTransaction::class, 'ct')
            ->join('ct.cashDoc', 'cd')
            ->join('cd.cash', 'c')
            ->where('ct.dateOper = ?1')
            ->setParameter('1', $dateOper)    
            ->orderBy('cd.dateOper', 'DESC')                 
            ->addOrderBy('cd.id', 'DESC')                 
                ;
        
        if (is_array($params)){
            if (isset($params['cashId'])){
                $queryBuilder->andWhere('ct.cash = ?2')
                    ->setParameter('2', $params['cashId'])
                        ;
            }            
            if (isset($params['sort'])){
                $queryBuilder->addOrderBy('ct.'.$params['sort'], $params['order']);
            }            
        }

        return $queryBuilder->getQuery();
    }      
    
    /**
     * Запрос по количеству записей
     * 
     * @param array $params
     * @return query
     */
    public function findAllCashDocTotal($dateOper, $params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('count(ct.id) as countCd')
            ->from(CashTransaction::class, 'ct')
            ->join('ct.cashDoc', 'cd')
            ->join('cd.cash', 'c')
            ->where('ct.dateOper = ?1')
            ->setParameter('1', $dateOper)    
                ;
        
        if (is_array($params)){
            if (isset($params['cashId'])){
                $queryBuilder->andWhere('ct.cash = ?2')
                    ->setParameter('2', $params['cashId']);
                        ;
            }            
        }
        
        $result = $queryBuilder->getQuery()->getOneOrNullResult();

        return $result['countCd'];
    }        
}
