<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Fin\Repository;

use Doctrine\ORM\EntityRepository;
use Company\Entity\Legal;
use Fin\Entity\FinBalance;
use Stock\Entity\Retail;
use Zp\Entity\PersonalMutual;

/**
 * Description of BalanceRepository
 *
 * @author Daddy
 */
class BalanceRepository extends EntityRepository
{

    /**
     * Получить Баланс
     * @param date $startDate
     * @param date $endDate
     * @param Legal $company
     * @return array
     */
    public function findBalance($startDate, $endDate, $company)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('f')
            ->from(FinBalance::class, 'f')
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
     * Запрос Баланс
     * @param date $startDate
     * @param date $endDate
     * @return array
     */
    public function queryBalance($startDate, $endDate)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('f')
            ->from(FinBalance::class, 'f')
            ->andWhere('f.period >= :startDate')    
            ->setParameter('startDate', $startDate)    
            ->andWhere('f.period <= :endDate')    
            ->setParameter('endDate', $endDate)
                ;
        
        return $queryBuilder->getQuery();   
    }
    
    /**
     * Получить балансы покупателей
     * @param date $endDate
     * @param array $params
     * @return array
     */
    public function findRetails($endDate, $params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $orX = $queryBuilder->expr()->orX(); 
        
        $queryBuilder->select('identity(r.company) as companyId, identity(r.contact) as contactId, sum(r.amount) as amount')
            ->from(Retail::class, 'r')    
            ->where('r.status = :status')
            ->setParameter('status', Retail::STATUS_ACTIVE)    
//            ->andWhere('r.dateOper >= :startDate')    
//            ->setParameter('startDate', $startDate)    
            ->andWhere('r.dateOper < :endDate')    
            ->setParameter('endDate', $endDate) 
            ->groupBy('companyId')    
            ->addGroupBy('contactId') 
            ->andHaving('amount != 0')    
                ;
        
        if (is_array($params)){
            if (!empty($params['debtor'])){
                $queryBuilder
                        ->having("amount > 0")
                        ;
            }
            if (!empty($params['creditor'])){
                $queryBuilder
                        ->having("amount < 0")
                        ;
            }            
        }
//                var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult(2);       
    }
    
    /**
     * Получить zp
     * @param date $endDate
     * @return array
     */
    public function findZp($endDate)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
//        $orX = $queryBuilder->expr()->orX(); 
        
        $queryBuilder->select('identity(pm.company) as companyId, sum(pm.amount) as amount')
            ->from(PersonalMutual::class, 'pm')    
            ->where('pm.status = :status')
            ->setParameter('status', PersonalMutual::STATUS_ACTIVE)    
            ->andWhere('pm.dateOper < :endDate')    
            ->setParameter('endDate', $endDate) 
            ->groupBy('companyId')    
            ->andHaving('amount != 0')    
                ;
        
//                var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult(2);       
    }
}