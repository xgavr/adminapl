<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Fin\Repository;

use Doctrine\ORM\EntityRepository;
use Company\Entity\Cost;
use Stock\Entity\Movement;
use ApiMarketPlace\Entity\MarketSaleReport;
use Fin\Entity\FinOpu;
use Company\Entity\Legal;
use Company\Entity\CostMutual;
use Zp\Entity\PersonalMutual;

/**
 * Description of FinRepository
 *
 * @author Daddy
 */
class FinRepository extends EntityRepository
{

    /**
     * Получить Опу
     * @param date $startDate
     * @param date $endDate
     * @param Legal $company
     * @return array
     */
    public function findOpu($startDate, $endDate, $company)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('f')
            ->from(FinOpu::class, 'f')
            ->andWhere('f.period >= :startDate')    
            ->setParameter('startDate', $startDate)    
            ->andWhere('f.period <= :endDate')    
            ->setParameter('endDate', $endDate)
            ->andWhere('f.company = :company')    
            ->setParameter('company', $company->getId())
            ->orderBy('f.period') 
            ->addOrderBy('f.status')    
                ;
//                var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult(2);       
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
    
    /**
     * Расходы
     * 
     * @param date $startDate
     * @param date $endDate
     * 
     * @return array 
     */
    public function costs($startDate, $endDate)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('identity(cm.company) as companyId, c.kind as kind, LAST_DAY(cm.dateOper) as period, sum(cm.amount) as amount')
            ->from(CostMutual::class, 'cm')
            ->join('cm.cost', 'c')    
            ->where('cm.status = :status')
            ->setParameter('status', CostMutual::STATUS_ACTIVE)    
            ->andWhere('cm.dateOper >= :startDate')    
            ->setParameter('startDate', $startDate)    
            ->andWhere('cm.dateOper <= :endDate')    
            ->setParameter('endDate', $endDate) 
            ->groupBy('companyId')    
            ->groupBy('kind')    
            ->addGroupBy('period')    
                ;
        
        return $queryBuilder->getQuery()->getResult();       
    }
    
    /**
     * Зарплата
     * 
     * @param date $startDate
     * @param date $endDate
     * 
     * @return array 
     */
    public function zp($startDate, $endDate)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('identity(pm.company) as companyId, pm.kind as kind, LAST_DAY(pm.dateOper) as period, sum(pm.amount) as amount')
            ->from(PersonalMutual::class, 'pm')
            ->where('pm.status = :status')
            ->setParameter('status', PersonalMutual::STATUS_ACTIVE)    
            ->andWhere('pm.kind = :kind')
            ->setParameter('kind', PersonalMutual::KIND_ACCRUAL)    
            ->andWhere('pm.dateOper >= :startDate')    
            ->setParameter('startDate', $startDate)    
            ->andWhere('pm.dateOper <= :endDate')    
            ->setParameter('endDate', $endDate) 
            ->groupBy('companyId')    
            ->groupBy('kind')    
            ->addGroupBy('period')    
                ;
        
        return $queryBuilder->getQuery()->getResult();       
    }
    

}