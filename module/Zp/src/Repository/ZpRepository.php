<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Zp\Repository;

use Doctrine\ORM\EntityRepository;
use Zp\Entity\Accrual;
use Zp\Entity\Personal;
use Zp\Entity\Position;
        
/**
 * Description of ZpRepository
 *
 * @author Daddy
 */
class ZpRepository extends EntityRepository
{

    /**
     * Получить Наисления
     * @param array $params
     * @return query
     */
    public function findAccrual($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('a')
            ->from(Accrual::class, 'a')
                ;
        if (is_array($params)){
            if (isset($params['sort'])){
                $queryBuilder->orderBy('a.'.$params['sort'], $params['order']);
            }            
        }    
//                var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();       
    }
    
    /**
     * Получить Штат
     * @param array $params
     * @return query
     */
    public function findPosition($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('p, pp')
            ->from(Position::class, 'p')
            ->leftJoin('p.parentPosition', 'pp')
            ->orderBy('p.id')    
                ;
        if (is_array($params)){
            if (isset($params['sort'])){
                $queryBuilder->addOrderBy('a.'.$params['sort'], $params['order']);
            }            
        }    
//                var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();       
    }
    
    /**
     * Получить parentPositions
     * @param array $params
     * @return query
     */
    public function findParentPositions($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('p')
            ->from(Position::class, 'p')
            ->where('p.parentPosition is null')    
                ;
//                var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult();       
    }
    
    
    
    /**
     * Получить Штат
     * @param array $params
     * @return query
     */
    public function findPersonal($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('p')
            ->from(Personal::class, 'p')
                ;
        if (is_array($params)){
            if (isset($params['sort'])){
                $queryBuilder->orderBy('a.'.$params['sort'], $params['order']);
            }            
        }    
//                var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();       
    }
    
    
    /**
     * Обороты розницы
     * 
     * @param date $startDate
     * @param date $endDate
     * 
     * @return array 
     */
    public function retailIncome($startDate, $endDate)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $orX = $queryBuilder->expr()->orX();
        $orX->add($queryBuilder->expr()->eq('m.docType', Movement::DOC_ORDER));
        $orX->add($queryBuilder->expr()->eq('m.docType', Movement::DOC_VT));
        
        $queryBuilder->select('identity(m.company) as companyId, LAST_DAY(m.dateOper) as period, sum(m.amount) as revenue, sum(m.baseAmount) as purchase')
            ->from(Movement::class, 'm')
            ->where('m.status = :status')
            ->setParameter('status', Movement::STATUS_ACTIVE)    
            ->andWhere($orX)
            ->andWhere('m.dateOper >= :startDate')    
            ->setParameter('startDate', $startDate)    
            ->andWhere('m.dateOper <= :endDate')    
            ->setParameter('endDate', $endDate) 
            ->groupBy('companyId')    
            ->addGroupBy('period')    
                ;
        
        return $queryBuilder->getQuery()->getResult();       
    }
        
    /**
     * Обороты ТП
     * 
     * @param date $startDate
     * @param date $endDate
     * 
     * @return array 
     */
    public function tpIncome($startDate, $endDate)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('identity(c.company) as companyId, LAST_DAY(m.docDate) as period, sum(m.docAmount) as revenue, sum(m.baseAmount) as purchase, sum(m.costAmount) as cost')
            ->from(MarketSaleReport::class, 'm')
            ->join('m.contract', 'c')    
            ->where('m.status = :status')
            ->setParameter('status', MarketSaleReport::STATUS_ACTIVE)    
            ->andWhere('m.docDate >= :startDate')    
            ->setParameter('startDate', $startDate)    
            ->andWhere('m.docDate <= :endDate')    
            ->setParameter('endDate', $endDate) 
            ->groupBy('companyId')    
            ->addGroupBy('period')    
                ;
        
        return $queryBuilder->getQuery()->getResult();       
    }
    

}