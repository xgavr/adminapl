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
use Cash\Entity\UserTransaction;
use Company\Entity\Office;

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
    public function cashDocQuery($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('cd.id, cd.dateOper')
            ->from(CashDoc::class, 'cd')
                ;
        
        if (is_array($params)){
            if (isset($params['cashId'])){
                $queryBuilder->andWhere('cd.cash = ?3')
                    ->setParameter('3', $params['cashId'])
                        ;
            }            
            if (is_numeric($params['kind'])){
                $queryBuilder->andWhere('cd.kind = ?4')
                    ->setParameter('4', $params['kind'])
                        ;
            }            
            if (isset($params['sort'])){
                $queryBuilder->addOrderBy('cd.'.$params['sort'], $params['order']);
            }            
        }

        return $queryBuilder->getQuery();
    }          
    
    /**
     * Запрос по кассовым документам
     * 
     * @param string $dateStart
     * @param string $dateEnd
     * @param array $params
     * @return query
     */
    public function findAllCashDoc($dateStart, $dateEnd, $params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('ct, cd, c, cr, ur, cost, l, u, uc, cnt, clt, o')
            ->from(CashTransaction::class, 'ct')
            ->join('ct.cashDoc', 'cd')
            ->leftJoin('cd.cashRefill', 'cr')    
            ->leftJoin('cd.userRefill', 'ur')    
            ->leftJoin('cd.userCreator', 'uc')    
            ->leftJoin('cd.cost', 'cost')    
            ->leftJoin('cd.legal', 'l')
            ->leftJoin('cd.cash', 'c')
            ->leftJoin('cd.user', 'u')
            ->leftJoin('cd.contact', 'cnt')
            ->leftJoin('cnt.client', 'clt')
            ->leftJoin('cd.order', 'o')
            ->where('ct.dateOper >= ?1')
            ->setParameter('1', $dateStart)    
            ->andWhere('ct.dateOper <= ?2')
            ->setParameter('2', $dateEnd . ' 23:59:59')    
            ->orderBy('cd.dateOper', 'DESC')                 
            ->addOrderBy('cd.id', 'DESC')                 
                ;
        
        if (is_array($params)){
            if (!empty($params['cashId'])){
                if (is_numeric($params['cashId'])){
                    $queryBuilder->andWhere('ct.cash = ?3')
                        ->setParameter('3', $params['cashId'])
                            ;
                }    
            }            
            if (!empty($params['kind'])){
                if (is_numeric($params['kind'])){
                    $queryBuilder->andWhere('cd.kind = ?4')
                        ->setParameter('4', $params['kind'])
                            ;
                }            
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
     * @param string $dateStart
     * @param string $dateEnd
     * @param array $params
     * @return query
     */
    public function findAllCashDocTotal($dateStart, $dateEnd, $params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('count(ct.id) as countCd')
            ->from(CashTransaction::class, 'ct')
            ->join('ct.cashDoc', 'cd')
            ->join('cd.cash', 'c')
            ->where('ct.dateOper >= ?1')
            ->setParameter('1', $dateStart)    
            ->andWhere('ct.dateOper <= ?2')
            ->setParameter('2', $dateEnd . ' 23:59:59')    
                ;
        
        if (is_array($params)){
            if (!empty($params['cashId'])){
                if (is_numeric($params['cashId'])){
                    $queryBuilder->andWhere('ct.cash = ?3')
                        ->setParameter('3', $params['cashId'])
                            ;
                }    
            }            
            if (!empty($params['kind'])){
                if (is_numeric($params['kind'])){
                    $queryBuilder->andWhere('cd.kind = ?4')
                        ->setParameter('4', $params['kind'])
                            ;
                }            
            }    
        }
        
        $result = $queryBuilder->getQuery()->getOneOrNullResult();

        return $result['countCd'];
    }    

    /**
     * Остаток в кассе
     * @param int $cashId
     * @param date $dateEnd
     */
    public function cashBalance($cashId, $dateEnd)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('sum(ct.amount) as balance')
            ->from(CashTransaction::class, 'ct')
            ->where('ct.cash = ?1')
            ->setParameter('1', $cashId)    
            ->andWhere('ct.dateOper <= ?2')
            ->setParameter('2', $dateEnd)    
            ->andWhere('ct.status = ?3')
            ->setParameter('3', CashTransaction::STATUS_ACTIVE)    
                ;
        
        $result = $queryBuilder->getQuery()->getOneOrNullResult();

        return $result['balance'];        
    }
    /**
     * Запрос по кассовым документам
     * 
     * @param string $dateStart
     * @param string $dateEnd
     * @param array $params
     * @return query
     */
    public function findAllUserDoc($dateStart, $dateEnd, $params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('ut, cd, u, cr, ur, cost, l, c, uc, cnt, clt, o')
            ->from(UserTransaction::class, 'ut')
            ->join('ut.cashDoc', 'cd')
            ->leftJoin('cd.cashRefill', 'cr')    
            ->leftJoin('cd.userRefill', 'ur')    
            ->leftJoin('cd.userCreator', 'uc')    
            ->leftJoin('cd.cost', 'cost')    
            ->leftJoin('cd.legal', 'l')
            ->leftJoin('cd.user', 'u')
            ->leftJoin('cd.contact', 'cnt')
            ->leftJoin('cnt.client', 'clt')
            ->leftJoin('cd.cash', 'c')
            ->leftJoin('cd.order', 'o')
            ->where('cd.dateOper >= ?1')
            ->setParameter('1', $dateStart)    
            ->andWhere('cd.dateOper <= ?2')
            ->setParameter('2', $dateEnd . ' 23:59:59')    
            ->orderBy('cd.dateOper', 'DESC')                 
            ->addOrderBy('cd.id', 'DESC')                 
                ;
        
        if (is_array($params)){
            if (!empty($params['userId'])){
                if (is_numeric($params['userId'])){
                    $queryBuilder->andWhere('ut.user = ?3')
                        ->setParameter('3', $params['userId'])
                            ;
                }    
            }            
            if (!empty($params['kind'])){
                if (is_numeric($params['kind'])){
                    $queryBuilder->andWhere('cd.kind = ?4')
                        ->setParameter('4', $params['kind'])
                            ;
                }    
            }            
            if (isset($params['sort'])){
                $queryBuilder->addOrderBy('ut.'.$params['sort'], $params['order']);
            }            
        }

        return $queryBuilder->getQuery();
    }      
    
    /**
     * Запрос по количеству записей
     * 
     * @param string $dateStart
     * @param string $dateEnd
     * @param array $params
     * @return query
     */
    public function findAllUserDocTotal($dateStart, $dateEnd, $params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('count(ut.id) as countCd')
            ->from(UserTransaction::class, 'ut')
            ->join('ut.cashDoc', 'cd')
            ->join('cd.user', 'c')
            ->where('cd.dateOper >= ?1')
            ->setParameter('1', $dateStart)    
            ->andWhere('cd.dateOper <= ?2')
            ->setParameter('2', $dateEnd . ' 23:59:59')    
                ;
        
        if (is_array($params)){
            if (!empty($params['userId'])){
                if (is_numeric($params['userId'])){
                    $queryBuilder->andWhere('ut.user = ?3')
                        ->setParameter('3', $params['userId'])
                            ;
                }    
            }            
            if (!empty($params['kind'])){
                if (is_numeric($params['kind'])){
                    $queryBuilder->andWhere('cd.kind = ?4')
                        ->setParameter('4', $params['kind'])
                            ;
                }    
            }            
        }
        
        $result = $queryBuilder->getQuery()->getOneOrNullResult();

        return $result['countCd'];
    }    

    /**
     * Остаток в подотчете
     * @param int $userId
     * @param date $dateEnd
     */
    public function userBalance($userId, $dateEnd)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('sum(ut.amount) as balance')
            ->from(UserTransaction::class, 'ut')
            ->where('ut.user = ?1')
            ->setParameter('1', $userId)    
            ->andWhere('ut.dateOper <= ?2')
            ->setParameter('2', $dateEnd) 
            ->andWhere('ut.status = ?3')
            ->setParameter('3', UserTransaction::STATUS_ACTIVE)    
                ;
        
        $result = $queryBuilder->getQuery()->getOneOrNullResult();

        return $result['balance'];        
    }
    
    
    /**
     * Касса по умолчанию
     * @param Office $office
     * @return Cash
     */
    public function defaultCash($office)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('c')
            ->from(Cash::class, 'c')
            ->where('c.office = ?1')
            ->setParameter('1', $office->getId())    
            ->andWhere('c.status = ?2')
            ->setParameter('2', Cash::STATUS_ACTIVE) 
            ->andWhere('c.tillStatus = ?3')
            ->setParameter('3', Cash::TILL_ACTIVE)    
            ->andWhere('c.restStatus = ?4')
            ->setParameter('4', Cash::REST_ACTIVE)
            ->setMaxResults(1)    
                ;
        
        return $queryBuilder->getQuery()->getOneOrNullResult();                
    }
    
    
    /**
     * Найти записи для отправки в АПЛ
     */
    public function findForUpdateApl()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('cd')
            ->from(CashDoc::class, 'cd')
            ->where('cd.statusEx = ?1')
            ->setParameter('1', CashDoc::STATUS_EX_NEW)    
            ->setMaxResults(1)    
                ;
            $orX = $queryBuilder->expr()->orX();
            $orX->add($queryBuilder->expr()->eq('cd.kind', CashDoc::KIND_IN_PAYMENT_CLIENT));
            $orX->add($queryBuilder->expr()->eq('cd.kind', CashDoc::KIND_OUT_RETURN_CLIENT));
            $orX->add($queryBuilder->expr()->eq('cd.kind', CashDoc::KIND_IN_RETURN_SUPPLIER));
            $orX->add($queryBuilder->expr()->eq('cd.kind', CashDoc::KIND_OUT_SUPPLIER));
            $queryBuilder->andWhere($orX);
        
        return $queryBuilder->getQuery()->getOneOrNullResult();                
        
    }        
}
