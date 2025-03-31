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
use Stock\Entity\Movement;
use Cash\Entity\Cash;

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
        
        $queryBuilder->select('identity(ct.company) as companyId, LAST_DAY(ct.dateOper) as period, sum(ct.amount) as amount')
            ->from(CashTransaction::class, 'ct')
            ->join('ct.cashDoc', 'cd')    
            ->join('ct.cash', 'c')    
            ->where('ct.status = :status')
            ->setParameter('status', CashTransaction::STATUS_ACTIVE)    
            ->andWhere('c.payment = :payment')
            ->setParameter('payment', Cash::PAYMENT_CASH)    
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
        
        $queryBuilder->select('identity(ut.company) as companyId, LAST_DAY(ut.dateOper) as period, sum(ut.amount) as amount')
            ->from(UserTransaction::class, 'ut')
            ->join('ut.cashDoc', 'cd')    
            ->where('ut.status = :status')
            ->setParameter('status', UserTransaction::STATUS_ACTIVE)    
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
     * Получить прочие движения в подотчете
     * @param date $startDate
     * @param date $endDate
     * @param array $docTypes
     * @return array
     */
    public function findOtherUserTransaction($startDate, $endDate, $docTypes)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $orX = $queryBuilder->expr()->orX();
        
        foreach ($docTypes as $docType){
            $orX->add($queryBuilder->expr()->eq('ut.docType', $docType));
        }    
        
        $queryBuilder->select('identity(ut.company) as companyId, LAST_DAY(ut.dateOper) as period, sum(ut.amount) as amount')
            ->from(UserTransaction::class, 'ut')
            ->where('ut.status = :status')
            ->setParameter('status', UserTransaction::STATUS_ACTIVE)    
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
//            ->andWhere($orX)
            ->andWhere('s.chargeDate >= :startDate')    
            ->setParameter('startDate', $startDate)    
            ->andWhere('s.chargeDate <= :endDate')    
            ->setParameter('endDate', $endDate) 
            ->groupBy('companyId')    
            ->addGroupBy('period')  
                ;
        
        if ($orX->count()){
            $queryBuilder->andWhere($orX);
        }
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
     * Получить остатки в банке организации
     * @param Legal $company
     * @param date $startDate
     * @return array
     */
    public function findCompanyBankBalance($company, $startDate)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('sum(b.balance) as amount')
            ->from(Balance::class, 'b')
            ->andWhere('b.dateBalance = :startDate')    
            ->setParameter('startDate', $startDate)    
            ->andWhere('b.company = :company')    
            ->setParameter('company', $company->getId()) 
            ->setMaxResults(1)    
 
                ;
//                var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getOneOrNullResult(2);       
    }
    
    /**
     * Получить остатки на депозите
     * @param date $startDate
     * @return array
     */
    public function findDepositBalance($startDate)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('identity(s.company) as companyId, sum(s.amount) as amount')
            ->from(Statement::class, 's')
            ->where('s.status = :status')
            ->setParameter('status', Statement::STATUS_ACTIVE)    
            ->andWhere('s.kind = :kindIn or s.kind = :kindOut')
            ->setParameter('kindIn', Statement::KIND_IN_DEPOSIT)    
            ->setParameter('kindOut', Statement::KIND_OUT_DEPO)    
            ->andWhere('s.chargeDate < :startDate')    
            ->setParameter('startDate', $startDate)    
            ->groupBy('companyId')    
            //->addGroupBy('period')  
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
        
        $queryBuilder->select('identity(ct.company) as companyId, sum(ct.amount) as amount')
            ->from(CashTransaction::class, 'ct')
            ->join('ct.cash', 'c')    
            ->where('ct.status = :status')
            ->setParameter('status', CashTransaction::STATUS_ACTIVE)    
            ->andWhere('c.payment = :payment')
            ->setParameter('payment', Cash::PAYMENT_CASH)    
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
        
        $queryBuilder->select('identity(ut.company) as companyId, sum(ut.amount) as amount')
            ->from(UserTransaction::class, 'ut')
            ->where('ut.status = :status')
            ->setParameter('status', UserTransaction::STATUS_ACTIVE)    
            ->andWhere('ut.dateOper < :startDate')    
            ->setParameter('startDate', $startDate)    
            ->groupBy('companyId')    
            //->addGroupBy('period')  
                ;
//                var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult(2);       
    }
        
    /**
     * Получить остатки товаров
     * @param date $startDate
     * @return array
     */
    public function findGoodBalance($startDate)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('identity(m.company) as companyId, identity(m.good) as goodId, sum(m.baseAmount) as amount, sum(m.quantity) as rest')
            ->from(Movement::class, 'm')
            ->andWhere('m.dateOper < :startDate')    
            ->setParameter('startDate', $startDate)    
            ->andWhere('m.status != :status')
            ->setParameter('status', Movement::STATUS_RETIRED)    
            ->groupBy('companyId')    
            ->addGroupBy('goodId')    
            ->having('rest > 0')    
                ;
        
//                var_dump($queryBuilder->getQuery()->getSQL()); exit;
        $result = [];
        $data = $queryBuilder->getQuery()->getResult(2);
        foreach ($data as $row){
            if (empty($result[$row['companyId']])){
               $result[$row['companyId']] = [
                    'companyId' => $row['companyId'],
                    'amount' => 0,
               ];                        
            }
            
            $result[$row['companyId']]['amount'] += $row['amount'];
        }
        return $result;       
    }
    
    /**
     * Получить движение товаров
     * @param date $startDate
     * @param date $endDate
     * @return array
     */
    public function findMovement($startDate, $endDate)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('identity(m.company) as companyId, LAST_DAY(m.dateOper) as period, '
                . 'sum(case when m.quantity > 0 then m.baseAmount else 0 end) as amountIn,'
                . 'sum(case when m.quantity < 0 then -m.baseAmount else 0 end) as amountOut')
            ->from(Movement::class, 'm')
            ->where('m.status != :status')
            ->setParameter('status', Movement::STATUS_RETIRED)    
            ->andWhere('m.dateOper >= :startDate')    
            ->setParameter('startDate', $startDate)    
            ->andWhere('m.dateOper <= :endDate')    
            ->setParameter('endDate', $endDate) 
            ->groupBy('companyId')    
            ->addGroupBy('period')  
                ;
        
        return $queryBuilder->getQuery()->getResult(2);       
    }        
}