<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Fin\Repository;

use Doctrine\ORM\EntityRepository;
use Fin\Entity\FinDds;
use Company\Entity\Legal;
use Cash\Entity\CashTransaction;
use Cash\Entity\UserTransaction;
use Bank\Entity\Statement;
use Bank\Entity\Balance;

/**
 * Description of DdsRepository
 *
 * @author Daddy
 */
class DdsRepository extends EntityRepository
{

    /**
     * Получить ДДС
     * @param date $startDate
     * @param date $endDate
     * @param Legal $company
     * @return array
     */
    public function findDds($startDate, $endDate, $company)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('f')
            ->from(FinDds::class, 'f')
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
     * Запрос ДДС
     * @param date $startDate
     * @param date $endDate
     * @return array
     */
    public function queryDds($startDate, $endDate)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('f')
            ->from(FinDds::class, 'f')
            ->andWhere('f.period >= :startDate')    
            ->setParameter('startDate', $startDate)    
            ->andWhere('f.period <= :endDate')    
            ->setParameter('endDate', $endDate)
                ;
        
        return $queryBuilder->getQuery();   
    }
    
    
    
    /**
     * Получить движения в кассе
     * @param date $startDate
     * @param date $endDate
     * @param array $kinds
     * @return array
     */
    public function findCashTransaction($startDate, $endDate, $kinds)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $orX = $queryBuilder->expr()->orX();
        
        foreach ($kinds as $kind){
            $orX->add($queryBuilder->expr()->eq('cd.kind', $kind));
        }    
        
        $queryBuilder->select('identity(cd.company) as companyId, LAST_DAY(ct.dateOper) as period, sum(ct.amount) as amount')
            ->from(CashTransaction::class, 'ct')
            ->join('ct.cashDoc', 'cd')    
            ->where('ct.status = :status')
            ->setParameter('status', CashTransaction::STATUS_ACTIVE)    
            ->andWhere($orX)
            ->andWhere('ct.dateOper >= :startDate')    
            ->setParameter('startDate', $startDate)    
            ->andWhere('ct.dateOper <= :endDate')    
            ->setParameter('endDate', $endDate) 
            ->groupBy('companyId')    
            ->addGroupBy('period')  
                ;
//                var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult(2);       
    }
    
    /**
     * Получить движения в подотчете
     * @param date $startDate
     * @param date $endDate
     * @param array $kinds
     * @return array
     */
    public function findUserTransaction($startDate, $endDate, $kinds)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $orX = $queryBuilder->expr()->orX();
        
        foreach ($kinds as $kind){
            $orX->add($queryBuilder->expr()->eq('cd.kind', $kind));
        }    
        
        $queryBuilder->select('identity(cd.company) as companyId, LAST_DAY(ut.dateOper) as period, sum(ut.amount) as amount')
            ->from(UserTransaction::class, 'ut')
            ->join('ut.cashDoc', 'cd')    
            ->where('ut.status = :status')
            ->setParameter('status', CashTransaction::STATUS_ACTIVE)    
            ->andWhere($orX)
            ->andWhere('ut.dateOper >= :startDate')    
            ->setParameter('startDate', $startDate)    
            ->andWhere('ut.dateOper <= :endDate')    
            ->setParameter('endDate', $endDate) 
            ->groupBy('companyId')    
            ->addGroupBy('period')  
                ;
//                var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult(2);       
    }
    
    /**
     * Получить движения в банке
     * @param date $startDate
     * @param date $endDate
     * @param array $kinds
     * @return array
     */
    public function findStatement($startDate, $endDate, $kinds)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $orX = $queryBuilder->expr()->orX();
        
        foreach ($kinds as $kind){
            $orX->add($queryBuilder->expr()->eq('s.kind', $kind));
        }    
        
        $queryBuilder->select('identity(s.company) as companyId, LAST_DAY(s.chargeDate) as period, sum(s.amount) as amount')
            ->from(Statement::class, 's')
            ->where('s.status = :status')
            ->setParameter('status', Statement::STATUS_ACTIVE)    
            ->andWhere($orX)
            ->andWhere('s.chargeDate >= :startDate')    
            ->setParameter('startDate', $startDate)    
            ->andWhere('s.chargeDate <= :endDate')    
            ->setParameter('endDate', $endDate) 
            ->groupBy('companyId')    
            ->addGroupBy('period')  
                ;
//                var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult(2);       
    }
    
    /**
     * Получить остатки в банке
     * @param date $startDate
     * @return array
     */
    public function findBankBalance($startDate)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('identity(b.company) as companyId, LAST_DAY(b.dateBalance) as period, sum(b.balance) as amount')
            ->from(Balance::class, 'b')
            ->andWhere('b.dateBalance = :startDate')    
            ->setParameter('startDate', $startDate)    
            ->groupBy('companyId')    
            ->addGroupBy('period')  
                ;
//                var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult(2);       
    }
    
    /**
     * Получить остатки в кассе
     * @param date $startDate
     * @return array
     */
    public function findCashBalance($startDate)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('identity(cd.company) as companyId, sum(ct.amount) as amount')
            ->from(CashTransaction::class, 'ct')
            ->join('ct.cashDoc', 'cd')    
            ->andWhere('ct.dateOper < :startDate')    
            ->setParameter('startDate', $startDate)    
            ->groupBy('companyId')    
            //->addGroupBy('period')  
                ;
//                var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult(2);       
    }
    
    /**
     * Получить остатки в подотчете
     * @param date $startDate
     * @return array
     */
    public function findUserBalance($startDate)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('identity(cd.company) as companyId, sum(ut.amount) as amount')
            ->from(UserTransaction::class, 'ut')
            ->join('ut.cashDoc', 'cd')    
            ->andWhere('ut.dateOper < :startDate')    
            ->setParameter('startDate', $startDate)    
            ->groupBy('companyId')    
            //->addGroupBy('period')  
                ;
//                var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult(2);       
    }
    
}