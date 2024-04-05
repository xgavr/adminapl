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
use Company\Entity\TaxMutual;
use Company\Entity\Tax;
use Stock\Entity\Retail;
use Application\Entity\Order;
use Stock\Entity\Vt;

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
     * Получить активные расходы
     * @param date $startDate
     * @param date $endDate
     * @param Legal $company
     * @return array
     */
    public function findActiveCosts($startDate, $endDate, $company)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('c.id as costId, c.name as costName, sum(cm.amount) as amount')
            ->from(CostMutual::class, 'cm')
            ->join('cm.cost', 'c')
            ->andWhere('cm.dateOper >= :startDate')    
            ->setParameter('startDate', $startDate)    
            ->andWhere('cm.dateOper <= :endDate')    
            ->setParameter('endDate', $endDate)
            ->andWhere('cm.company = :company')    
            ->setParameter('company', $company->getId())
            ->andWhere('c.kind != :excKind')    
            ->setParameter('excKind', Cost::KIND_MP) 
            ->andWhere('cm.status = :status')
            ->setParameter('status', CostMutual::STATUS_ACTIVE)    
            ->addGroupBy('costId')    
                ;
//                var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult(2);       
    }
    
    /**
     * Получить сводные расходы
     * @param date $startDate
     * @param date $endDate
     * @param Legal $company
     * @return array
     */
    public function findCosts($startDate, $endDate, $company)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('LAST_DAY(cm.dateOper) as period, c.id as costId, c.name as costName, sum(cm.amount) as amount')
            ->from(CostMutual::class, 'cm')
            ->join('cm.cost', 'c')
            ->andWhere('cm.dateOper >= :startDate')    
            ->setParameter('startDate', $startDate)    
            ->andWhere('cm.dateOper <= :endDate')    
            ->setParameter('endDate', $endDate)
            ->andWhere('cm.company = :company')    
            ->setParameter('company', $company->getId())
            ->andWhere('c.kind != :excKind')    
            ->setParameter('excKind', Cost::KIND_MP) 
            ->andWhere('cm.status = :status')
            ->setParameter('status', CostMutual::STATUS_ACTIVE)    
            ->groupBy('period')    
            ->addGroupBy('costId')    
            ->orderBy('period') 
                ;
//                var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult(2);       
    }
    
    /**
     * Получить активные zp
     * @param date $startDate
     * @param date $endDate
     * @param Legal $company
     * @return array
     */
    public function findActiveZp($startDate, $endDate, $company)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('u.id as userId, u.fullName as userName, sum(pm.amount) as amount')
            ->from(PersonalMutual::class, 'pm')
            ->join('pm.user', 'u')
            ->andWhere('pm.dateOper >= :startDate')    
            ->setParameter('startDate', $startDate)    
            ->andWhere('pm.dateOper <= :endDate')    
            ->setParameter('endDate', $endDate)
            ->andWhere('pm.company = :company')    
            ->setParameter('company', $company->getId())
            ->andWhere('pm.amount < 0')    
            ->andWhere('pm.status = :status')
            ->setParameter('status', PersonalMutual::STATUS_ACTIVE)    
            ->addGroupBy('userId')    
                ;
//                var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult(2);       
    }
    
    /**
     * Получить сводные zp
     * @param date $startDate
     * @param date $endDate
     * @param Legal $company
     * @return array
     */
    public function findZp($startDate, $endDate, $company)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('LAST_DAY(pm.dateOper) as period, u.id as userId, u.fullName as userName, sum(pm.amount) as amount')
            ->from(PersonalMutual::class, 'pm')
            ->join('pm.user', 'u')
            ->andWhere('pm.dateOper >= :startDate')    
            ->setParameter('startDate', $startDate)    
            ->andWhere('pm.dateOper <= :endDate')    
            ->setParameter('endDate', $endDate)
            ->andWhere('pm.company = :company')    
            ->setParameter('company', $company->getId())
            ->andWhere('pm.amount < 0')    
            ->andWhere('pm.status = :status')
            ->setParameter('status', PersonalMutual::STATUS_ACTIVE)    
            ->groupBy('period')    
            ->addGroupBy('userId')    
            ->orderBy('period') 
                ;
//                var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult(2);       
    }
    
    /**
     * Получить активные user
     * @param date $startDate
     * @param date $endDate
     * @param Legal $company
     * @return array
     */
    public function findActiveUser($startDate, $endDate, $company)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $orX = $queryBuilder->expr()->orX();
        $orX->add($queryBuilder->expr()->eq('r.docType', Movement::DOC_ORDER));
        $orX->add($queryBuilder->expr()->eq('r.docType', Movement::DOC_VT));
        
        $queryBuilder->select('u.id as userId, u.fullName as userName')
            ->from(Retail::class, 'r')
            ->join('r.user', 'u')
            ->andWhere('r.dateOper >= :startDate')    
            ->setParameter('startDate', $startDate)    
            ->andWhere('r.dateOper <= :endDate')    
            ->setParameter('endDate', $endDate)
            ->andWhere('r.company = :company')    
            ->setParameter('company', $company->getId())
            ->andWhere('r.status = :status')
            ->setParameter('status', Retail::STATUS_ACTIVE)
            ->andWhere($orX)    
            ->addGroupBy('userId')    
                ;
//                var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult(2);       
    }
    
    /**
     * Получить сводные выручки
     * @param date $startDate
     * @param date $endDate
     * @param Legal $company
     * @return array
     */
    public function findRetailRevenue($startDate, $endDate, $company)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $orX = $queryBuilder->expr()->orX();
        $orX->add($queryBuilder->expr()->eq('r.docType', Movement::DOC_ORDER));
        $orX->add($queryBuilder->expr()->eq('r.docType', Movement::DOC_VT));
        
        $queryBuilder->select('LAST_DAY(r.dateOper) as period, u.id as userId, u.fullName as userName, sum(r.amount) as amount')
            ->from(Retail::class, 'r')
            ->join('r.user', 'u')
            ->andWhere('r.dateOper >= :startDate')    
            ->setParameter('startDate', $startDate)    
            ->andWhere('r.dateOper <= :endDate')    
            ->setParameter('endDate', $endDate)
            ->andWhere('r.company = :company')    
            ->setParameter('company', $company->getId())
            ->andWhere('r.status = :status')
            ->setParameter('status', Retail::STATUS_ACTIVE)    
            ->andWhere($orX)    
            ->groupBy('period')    
            ->addGroupBy('userId')    
            ->orderBy('period') 
                ;
//                var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult(2);       
    } 
    
    /**
     * Получить количество заказов
     * @param date $startDate
     * @param date $endDate
     * @param Legal $company
     * @return array
     */
    public function findRetailOrderCount($startDate, $endDate, $company)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $orX = $queryBuilder->expr()->orX();
        $orX->add($queryBuilder->expr()->eq('r.docType', Movement::DOC_ORDER));
//        $orX->add($queryBuilder->expr()->eq('r.docType', Movement::DOC_VT));
        
        $queryBuilder->select('LAST_DAY(r.dateOper) as period, u.id as userId, u.fullName as userName, count(r.docId) as orderCount')
            ->from(Retail::class, 'r')
            ->join('r.user', 'u')
            ->andWhere('r.dateOper >= :startDate')    
            ->setParameter('startDate', $startDate)    
            ->andWhere('r.dateOper <= :endDate')    
            ->setParameter('endDate', $endDate)
            ->andWhere('r.company = :company')    
            ->setParameter('company', $company->getId())
            ->andWhere('r.status = :status')
            ->setParameter('status', Retail::STATUS_ACTIVE)    
            ->andWhere($orX)    
            ->groupBy('period')    
            ->addGroupBy('userId')    
            ->orderBy('period') 
                ;
//                var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult(2);       
    }   
    
    /**
     * Получить сводные закупки
     * @param date $startDate
     * @param date $endDate
     * @param Legal $company
     * @return array
     */
    public function findRetailPurchase($startDate, $endDate, $company)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $orX = $queryBuilder->expr()->orX();
        $orX->add($queryBuilder->expr()->eq('m.docType', Movement::DOC_ORDER));
        $orX->add($queryBuilder->expr()->eq('m.docType', Movement::DOC_VT));
        
        $queryBuilder->select('LAST_DAY(m.dateOper) as period, u.id as userId, u.fullName as userName, sum(m.amount) as revenue, sum(m.baseAmount) as purchase')
            ->from(Movement::class, 'm')
            ->join('m.user', 'u')
            ->andWhere('m.dateOper >= :startDate')    
            ->setParameter('startDate', $startDate)    
            ->andWhere('m.dateOper <= :endDate')    
            ->setParameter('endDate', $endDate)
            ->andWhere('m.company = :company')    
            ->setParameter('company', $company->getId())
            ->andWhere('m.status = :status')
            ->setParameter('status', Retail::STATUS_ACTIVE)    
            ->andWhere($orX)    
            ->groupBy('period')    
            ->addGroupBy('userId')    
            ->orderBy('period') 
                ;
//                var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult(2);       
    }    
    
    /**
     * Выручка розницы
     * 
     * @param date $startDate
     * @param date $endDate
     * 
     * @return array 
     */
    public function retailRevenue($startDate, $endDate)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $orX = $queryBuilder->expr()->orX();
        $orX->add($queryBuilder->expr()->eq('r.docType', Movement::DOC_ORDER));
        $orX->add($queryBuilder->expr()->eq('r.docType', Movement::DOC_VT));
        
        $queryBuilder->select('identity(r.company) as companyId, LAST_DAY(r.dateOper) as period, sum(r.amount) as revenue')
            ->from(Retail::class, 'r')
            ->where('r.status = :status')
            ->setParameter('status', Retail::STATUS_ACTIVE)    
            ->andWhere($orX)
            ->andWhere('r.dateOper >= :startDate')    
            ->setParameter('startDate', $startDate)    
            ->andWhere('r.dateOper <= :endDate')    
            ->setParameter('endDate', $endDate) 
            ->groupBy('companyId')    
            ->addGroupBy('period')  
                ;
        
        return $queryBuilder->getQuery()->getResult();       
    }
        
    /**
     * Количество заказов розницы
     * 
     * @param date $startDate
     * @param date $endDate
     * 
     * @return array 
     */
    public function retailOrderCount($startDate, $endDate)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $orX = $queryBuilder->expr()->orX();
        $orX->add($queryBuilder->expr()->eq('r.docType', Movement::DOC_ORDER));
//        $orX->add($queryBuilder->expr()->eq('r.docType', Movement::DOC_VT));
        
        $queryBuilder->select('identity(r.company) as companyId, LAST_DAY(r.dateOper) as period, count(r.docId) as orderCount')
            ->from(Retail::class, 'r')
            ->where('r.status = :status')
            ->setParameter('status', Retail::STATUS_ACTIVE)    
            ->andWhere($orX)
            ->andWhere('r.dateOper >= :startDate')    
            ->setParameter('startDate', $startDate)    
            ->andWhere('r.dateOper <= :endDate')    
            ->setParameter('endDate', $endDate) 
            ->groupBy('companyId')    
            ->addGroupBy('period')  
                ;
        
        return $queryBuilder->getQuery()->getResult();       
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
            ->having('abs(revenue - purchase) > 0')    
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
        
        $queryBuilder->select('identity(cm.company) as companyId, c.kindFin as kindFin, LAST_DAY(cm.dateOper) as period, sum(cm.amount) as amount')
            ->from(CostMutual::class, 'cm')
            ->join('cm.cost', 'c')    
            ->where('cm.status = :status')
            ->setParameter('status', CostMutual::STATUS_ACTIVE)    
            ->andWhere('cm.dateOper >= :startDate')    
            ->setParameter('startDate', $startDate)    
            ->andWhere('cm.dateOper <= :endDate')    
            ->setParameter('endDate', $endDate) 
            ->andWhere('c.kind != :excKind')    
            ->setParameter('excKind', Cost::KIND_MP) 
            ->groupBy('companyId')    
            ->groupBy('kindFin')    
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
            ->andWhere('pm.amount < 0')    
//            ->andWhere('pm.kind = :kind')
//            ->setParameter('kind', PersonalMutual::KIND_ACCRUAL)    
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
    
    /**
     * ЕСН
     * 
     * @param date $startDate
     * @param date $endDate
     * 
     * @return array 
     */
    public function esn($startDate, $endDate)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('identity(tm.company) as companyId, LAST_DAY(tm.dateOper) as period, sum(tm.amount) as amount')
            ->from(TaxMutual::class, 'tm')
            ->join('tm.tax', 't')    
            ->where('tm.status = :status')
            ->setParameter('status', TaxMutual::STATUS_ACTIVE)    
            ->andWhere('t.kind = :kind')
            ->setParameter('kind', Tax::KIND_ESN)    
            ->andWhere('tm.dateOper >= :startDate')    
            ->setParameter('startDate', $startDate)    
            ->andWhere('tm.dateOper <= :endDate')    
            ->setParameter('endDate', $endDate) 
            ->groupBy('companyId')    
            ->addGroupBy('period')    
                ;
        
        return $queryBuilder->getQuery()->getResult();       
    }
    

}