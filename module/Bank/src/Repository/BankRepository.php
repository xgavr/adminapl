<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Bank\Repository;

use Doctrine\ORM\EntityRepository;
use Bank\Entity\Statement;
use Bank\Entity\Balance;
use Bank\Entity\Acquiring;
use Bank\Entity\AplPayment;

/**
 * Description of BankRepository
 *
 * @author Daddy
 */
class BankRepository extends EntityRepository
{
    /**
     * Получить выборку записей выписки
     * 
     * @param string $q поисковый запрос
     * @param string $rs счет
     * @return object
     */
    public function findStatement($q = null, $rs = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('s')
            ->from(Statement::class, 's')
            ->orderBy('s.chargeDate', 'DESC')
            ->addOrderBy('s.id', 'DESC')    
                ;
        
        if (is_array($rs)){
            $or = $queryBuilder->expr()->orX();
            foreach ($rs as $account){
                $or->add($queryBuilder->expr()->eq('s.account', trim($account)));
            }
            $queryBuilder->where($or);
        }
        
        if ($q){
            $or = $queryBuilder->expr()->orX();
            $or->add($queryBuilder->expr()->like('s.counterpartyInn', '?1'));
            $or->add($queryBuilder->expr()->like('s.counterpartyName', '?1'));
            $or->add($queryBuilder->expr()->like('s.purpose', '?1'));
            $queryBuilder->setParameter('1', '%' . $q . '%');

            if (is_numeric($q)){
                $or->add($queryBuilder->expr()->eq('FLOOR(s.amount)', floor($q)));
                $or->add($queryBuilder->expr()->eq('FLOOR(s.amount)', -floor($q)));
                
//                $i = -1;
//                while ($i > -10){
//                    if (round($q, $i)){
//                        $or->add($queryBuilder->expr()->eq('ROUND(s.amount, '.$i.')', round($q, $i)));                        
//                        $or->add($queryBuilder->expr()->eq('ROUND(s.amount, '.$i.')', -round($q, $i)));                        
//                    }
//                    $i--;
//                }
            }
            $queryBuilder->andWhere($or);
        }
        
        return $queryBuilder->getQuery();
    }    

    /**
     * Получить выборку записей остатков
     * 
     * @param string $q поисковый запрос
     * @param string $rs счет
     * @return object
     */
    public function findBalance($q = null, $rs = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('b')
            ->from(Balance::class, 'b')
            ->orderBy('b.dateBalance', 'DESC')
            ->addOrderBy('b.account', 'ASC')    
                ;
        
        if (is_array($rs)){
            $or = $queryBuilder->expr()->orX();
            foreach ($rs as $account){
                $or->add($queryBuilder->expr()->eq('b.account', trim($account)));
            }
            $queryBuilder->where($or);
        }
        
        if ($q){
        }
        
        return $queryBuilder->getQuery();
    }    

    /**
     * Получить текущий остаток по счету 
     * @param string $account
     * @return float
     */
    public function currentBalance($account)
    {
        $entityManger = $this->getEntityManager();
        $queryBuilder = $entityManger->createQueryBuilder();
        
        $queryBuilder->select('b')
                ->from(Balance::class, 'b')
                ->where('b.account = ?1')
                ->setParameter('1', $account)
                ->orderBy('b.dateBalance', 'DESC')
                ->setMaxResults(1)
                ;
        
        $balance = $queryBuilder->getQuery()->getOneOrNullResult();
        
        if ($balance){
            $queryBuilder = $entityManger->createQueryBuilder();
            $queryBuilder->select('SUM(s.amount) as amountSum')
                    ->from(Statement::class, 's')
                    ->where('s.account = ?1')
                    ->andWhere('s.chargeDate >= ?2')
                    ->setParameter('1', $balance->getAccount())
                    ->setParameter('2', $balance->getDateBalance())
                    ->groupBy('s.account')
                    ;
            
            $statement = $queryBuilder->getQuery()->getOneOrNullResult();
            
            if ($statement){
                return $balance->getBalance() + $statement['amountSum'];
            } else {
                return $balance->getBalance();
            }
        }
        
        return 0;                
    }
    
    /**
     * Получить выборку записей эквайринга
     * 
     * @param array $params поисковый запрос
     * @return object
     */
    public function findAcquiring($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('a.id, a.transDate, a.operDate, a.point, a.cartType, a.cart, a.acode, a.rrn, a.output, a.status, p.aplPaymentType, p.aplPaymentTypeId, p.aplPaymentDate, p.aplPaymentSum')
            ->from(Acquiring::class, 'a')
            ->leftJoin('a.aplPayments', 'p')
            ->orderBy('a.transDate', 'DESC')
            ->addOrderBy('a.point', 'ASC')    
                ;
                
        if (is_array($params)){
            if (isset($params['search'])){
                if (trim($params['search'])){
                    $or = $queryBuilder->expr()->orX();
                    $or->add($queryBuilder->expr()->like('a.cart', '?1'));
                    $or->add($queryBuilder->expr()->like('a.rrn', '?1'));
                    $or->add($queryBuilder->expr()->eq('a.aplPaymentTypeId', '?4'));

                    $queryBuilder->andWhere($or)
                            ->setParameter('1', '%' . trim($params['search']) . '%')
                            ->setParameter('4', $params['search'])
                            ;
                }    
            }
            if (isset($params['date'])){
                if ($params['date']){
                    $or = $queryBuilder->expr()->orX();
                    $or->add($queryBuilder->expr()->between('a.transDate', '?2', '?3'));
                    $or->add($queryBuilder->expr()->eq('a.operDate', '?2'));

                    $queryBuilder->andWhere($or)
                            ->setParameter('2', $params['date'])
                            ->setParameter('3', $params['date']. ' 23:59:59')
                            ;
                }    
            }
            if (isset($params['status'])){
                $queryBuilder->andWhere('a.status = ?5')
                        ->setParameter('5', $params['status'])
                        ;
            }
        }
        
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        
        return $queryBuilder->getQuery();
    }    
    
    /**
     * Получить выборку записей оплат по картам
     * 
     * @param array $params поисковый запрос
     * @return object
     */
    public function findAplPayment($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('a')
            ->from(AplPayment::class, 'a')
            ->orderBy('a.aplPaymentDate', 'DESC')
            ->addOrderBy('a.aplPaymentId', 'ASC')    
                ;
                
        if (is_array($params)){
            if (isset($params['status'])){
                $queryBuilder->andWhere('a.status = ?1')
                        ->setParameter('1', $params['status'])
                        ;
            }
        }
        
        return $queryBuilder->getQuery();
    }    
    
    /**
     * Удалить оплаты с отказами
     */
    public function compressAcquiring()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('a.rrn, sum(a.output) as outputSum')
                ->from(Acquiring::class, 'a')
                ->groupBy('a.rrn')
                ->where('a.status = ?1')
                ->having('outputSum = 0')
                ->setParameter('1', Acquiring::STATUS_NO_MATCH)
                ;
        
        return $queryBuilder->getQuery()->getResult();
    }
    
    /**
     * Удалить оплаты с отказами
     */
    public function compressAplPayment()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('a.aplPaymentType, a.aplPaymentTypeId, sum(a.aplPaymentSum) as outputSum')
                ->from(AplPayment::class, 'a')
                ->groupBy('a.aplPaymentType')
                ->addGroupBy('a.aplPaymentTypeId')
                ->where('a.status = ?1')
                ->having('outputSum = 0')
                ->setParameter('1', AplPayment::STATUS_NO_MATCH)
                ;
        
        return $queryBuilder->getQuery()->getResult();
    }
    
    /**
     * Поиск по сумме эквайринга
     * 
     * @param \Bank\Entity\Acquiring $acquiring
     * @return object
     */
    public function findAcquiringIntersect($acquiring)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('p')
            ->from(AplPayment::class, 'p')
            ->where('p.aplPaymentSum = ?1')
            ->andWhere('p.status = ?2')    
            ->andWhere('p.aplPaymentDate >= ?3')
            ->andWhere('p.aplPaymentDate <= ?4')
            ->orderBy('p.aplPaymentDate', 'DESC')
            ->setMaxResults(1)    
            ->setParameter('1', $acquiring->getOutput())
            ->setParameter('2', AplPayment::STATUS_NO_MATCH)    
            ->setParameter('3', date('Y-m-d', strtotime($acquiring->getTransDate())))
            ->setParameter('4', date('Y-m-d 23:59:59', strtotime($acquiring->getOperDate()) + 60*60*24*2)) //2 дня
             ;
        
        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

        /**
     * Поиск по сумме эквайринга
     * 
     * @param \Bank\Entity\Acquiring $acquiring
     * @return object
     */
    public function findAcquiringIntersectSum($acquiring)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('p.aplPaymentType, p.aplPaymentTypeId, sum(p.aplPaymentSum) as outputSum')
            ->from(AplPayment::class, 'p')
            ->andWhere('p.status = ?2')    
            ->andWhere('p.aplPaymentDate >= ?3')
            //->andWhere('p.aplPaymentDate <= ?4')
            ->groupBy('p.aplPaymentType')
            ->addGroupBy('p.aplPaymentTypeId')
            ->having('outputSum = ?1')
            ->setParameter('1', $acquiring->getOutput())
            ->setParameter('2', AplPayment::STATUS_NO_MATCH)    
            ->setParameter('3', date('Y-m-d', strtotime($acquiring->getTransDate())))
            //->setParameter('4', date('Y-m-d 23:59:59', strtotime($acquiring->getOperDate()) + 60*60*24*4)) //2 дня
             ;
        
        return $queryBuilder->getQuery()->getResult();
    }

}