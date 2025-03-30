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
use Fin\Entity\FinBalance;
use Stock\Entity\Movement;
use Cash\Entity\Cash;

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
    
}