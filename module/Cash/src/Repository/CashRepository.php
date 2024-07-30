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
use Stock\Entity\Movement;
use Stock\Entity\Register;
use Bank\Entity\Statement;

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
    public function findAllCashDoc($dateStart='2012-01-01', $dateEnd='2199-01-01', $params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('ct, cashDoc, c, cr, ur, cost, l, u, uc, cnt, clt, o, cdc')
            ->from(CashTransaction::class, 'ct')
            ->join('ct.cashDoc', 'cashDoc')
            ->join('ct.cash', 'c')
            ->leftJoin('cashDoc.cashRefill', 'cr')    
            ->leftJoin('cashDoc.userRefill', 'ur')    
            ->leftJoin('cashDoc.userCreator', 'uc')    
            ->leftJoin('cashDoc.cost', 'cost')    
            ->leftJoin('cashDoc.legal', 'l')
            ->leftJoin('cashDoc.cash', 'cdc')
            ->leftJoin('cashDoc.user', 'u')
            ->leftJoin('cashDoc.contact', 'cnt')
            ->leftJoin('cnt.client', 'clt')
            ->leftJoin('cashDoc.order', 'o')
            ->where('ct.dateOper >= ?1')
            ->setParameter('1', $dateStart)    
            ->andWhere('ct.dateOper <= ?2')
            ->setParameter('2', $dateEnd . ' 23:59:59')    
//            ->orderBy('cd.dateOper', 'DESC')                 
//            ->addOrderBy('cd.id', 'DESC')                 
                ;
        
        if (is_array($params)){
            if (!empty($params['cashId'])){
                if (is_numeric($params['cashId'])){
                    $queryBuilder->andWhere('ct.cash = ?3')
                        ->setParameter('3', $params['cashId'])
                            ;
                }    
            }            
            if (!empty($params['office'])){
                if (is_numeric($params['office'])){
                    $queryBuilder->andWhere('c.office = :office')
                        ->setParameter('office', $params['office'])
                            ;
                }    
            }            
            if (!empty($params['kind'])){
                if (is_numeric($params['kind'])){
                    $queryBuilder->andWhere('cashDoc.kind = ?4')
                        ->setParameter('4', $params['kind'])
                            ;
                }            
            }    
            if (!empty($params['cashDocId'])){
                if (is_numeric($params['cashDocId'])){
                    $queryBuilder->andWhere('cashDoc.id = :id')
                        ->setParameter('id', $params['cashDocId'])
                            ;
                }            
            }    
            if (isset($params['sort'])){
                //$queryBuilder->addOrderBy($params['sort'], $params['order']);
            }
            
            $queryBuilder->addOrderBy('ct.docStamp', isset($params['order']) ? $params['order']:'DESC');
        }

//        var_dump($queryBuilder->getQuery()->getSQL());
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
    public function findAllCashDocTotal($dateStart = '2012-01-01', $dateEnd = '2199-01-01', $params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('count(ct.id) as countCd,'
                . 'sum(CASE WHEN ct.amount >= 0 and ct.status = :status THEN ct.amount ELSE 0 END) as inTotal, '
                . 'sum(CASE WHEN ct.amount < 0 and ct.status = :status THEN ct.amount ELSE 0 END) as outTotal')
            ->from(CashTransaction::class, 'ct')
            ->join('ct.cashDoc', 'cd')
            ->join('cd.cash', 'c')
            ->where('ct.dateOper >= ?1')
            ->setParameter('1', $dateStart)    
            ->andWhere('ct.dateOper <= ?2')
            ->setParameter('2', $dateEnd . ' 23:59:59')    
            ->setParameter('status', CashTransaction::STATUS_ACTIVE)    
                ;
        
        if (is_array($params)){
            if (!empty($params['cashId'])){
                if (is_numeric($params['cashId'])){
                    $queryBuilder->andWhere('ct.cash = ?3')
                        ->setParameter('3', $params['cashId'])
                            ;
                }    
            }            
            if (!empty($params['office'])){
                if (is_numeric($params['office'])){
                    $queryBuilder->andWhere('c.office = :office')
                        ->setParameter('office', $params['office'])
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

        return $result;
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
    public function findAllUserDoc($dateStart='2012-01-01', $dateEnd='2199-01-01', $params = null)
    {
//        var_dump($dateStart, $dateEnd);
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('ut, cd, u, cr, ur, cost, l, c, uc, cnt, clt, o, cdu')
            ->from(UserTransaction::class, 'ut')
            ->join('ut.user', 'u')
            ->leftJoin('ut.cashDoc', 'cd')
            ->leftJoin('cd.cashRefill', 'cr')    
            ->leftJoin('cd.userRefill', 'ur')    
            ->leftJoin('cd.userCreator', 'uc')    
            ->leftJoin('cd.cost', 'cost')    
            ->leftJoin('cd.legal', 'l')
            ->leftJoin('cd.user', 'cdu')
            ->leftJoin('cd.contact', 'cnt')
            ->leftJoin('cnt.client', 'clt')
            ->leftJoin('cd.cash', 'c')
            ->leftJoin('cd.order', 'o')
            ->where('ut.dateOper >= ?1')
            ->setParameter('1', $dateStart)    
            ->andWhere('ut.dateOper <= ?2')
            ->setParameter('2', $dateEnd . ' 23:59:59')    
            ->orderBy('ut.docStamp', 'DESC')                 
//            ->addOrderBy('cd.id', 'DESC')                 
                ;
        
        if (is_array($params)){
            if (!empty($params['userId'])){
                if (is_numeric($params['userId'])){
                    $queryBuilder->andWhere('ut.user = ?3')
                        ->setParameter('3', $params['userId'])
                            ;
                }    
            }            
            if (!empty($params['officeId'])){
                if (is_numeric($params['officeId'])){
                    $queryBuilder->andWhere('u.office = :office')
                        ->setParameter('office', $params['officeId'])
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
            if (isset($params['order'])){                
                $queryBuilder->orderBy('ut.docStamp', $params['order']);
            }            
        }

//        var_dump($queryBuilder->getQuery()->getSQL());
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
    public function findAllUserDocTotal($dateStart = '2012-01-01', $dateEnd='2199-01-01', $params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('count(ut.id) as countCd, sum(CASE WHEN ut.amount >= 0 and ut.status = :status THEN ut.amount ELSE 0 END) as amountIn, sum(CASE WHEN ut.amount < 0 and ut.status = :status THEN ut.amount ELSE 0 END) as amountOut')
            ->from(UserTransaction::class, 'ut')
            ->join('ut.user', 'c')
            ->leftJoin('ut.cashDoc', 'cd')
            ->where('ut.dateOper >= ?1')
            ->setParameter('1', $dateStart)    
            ->andWhere('ut.dateOper <= ?2')
            ->setParameter('2', $dateEnd . ' 23:59:59')    
            ->setParameter('status', UserTransaction::STATUS_ACTIVE)    
                ;
        
        if (is_array($params)){
            if (!empty($params['userId'])){
                if (is_numeric($params['userId'])){
                    $queryBuilder->andWhere('ut.user = ?3')
                        ->setParameter('3', $params['userId'])
                            ;
                }    
            }            
            if (!empty($params['officeId'])){
                if (is_numeric($params['officeId'])){
                    $queryBuilder->andWhere('c.office = :office')
                        ->setParameter('office', $params['officeId'])
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
//var_dump($result);
        return $result;
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
     * @param CashDoc $cashDoc
     */
    public function findForUpdateApl($cashDoc = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('cd')
            ->from(CashDoc::class, 'cd')
            ->where('cd.statusEx = ?1')
            ->setParameter('1', CashDoc::STATUS_EX_NEW)    
            ->setMaxResults(1)   
                
//            ->andWhere('cd.aplId > 0')
                ;
                
            if ($cashDoc){
                $queryBuilder->where('cd.id = ?1')
                   ->setParameter('1', $cashDoc->getId())
                        ;
            }    
                
                
//            $orX = $queryBuilder->expr()->orX();
//            $orX->add($queryBuilder->expr()->eq('cd.kind', CashDoc::KIND_IN_PAYMENT_CLIENT));
//            $orX->add($queryBuilder->expr()->eq('cd.kind', CashDoc::KIND_OUT_RETURN_CLIENT));
//            $orX->add($queryBuilder->expr()->eq('cd.kind', CashDoc::KIND_IN_RETURN_SUPPLIER));
//            $orX->add($queryBuilder->expr()->eq('cd.kind', CashDoc::KIND_OUT_SUPPLIER));
//            $queryBuilder->andWhere($orX);
        
        return $queryBuilder->getQuery()->getOneOrNullResult();                
        
    }        
    
    /**
    * Остаток по if
    * @param integer $userId
    * @param float $docStamp 
    * @return integer
    */
    public function accountantRest($userId, $docStamp)
    {
        $entityManager = $this->getEntityManager();
        
        $qb = $entityManager->createQueryBuilder();
        $qb->select('sum(ut.amount) as utSum')
                ->from(UserTransaction::class, 'ut')
                ->where('ut.user = ?1')
                ->andWhere('ut.docStamp <= ?2') 
                ->andWhere('ut.docStamp > 0')
                ->andWhere('ut.status = :status')
                ->setParameter('1', $userId)
                ->setParameter('2', $docStamp)
                ->setParameter('status', UserTransaction::STATUS_ACTIVE)
                ;

        $result = $qb->getQuery()->getOneOrNullResult();
        if ($result){
            return $result['utSum'];
        }
            
        return 0;
    }    
    
    /**
    * Остаток на начало периода
    * @param date $dateStart
    * @param array $params
    */        
    public function balanceTransaction($dateStart, $params)
    {
        $entityManager = $this->getEntityManager();
        
        $qb = $entityManager->createQueryBuilder();
        $qb->select('sum(ut.amount) as bSum')
                ->from(UserTransaction::class, 'ut')
                ->andWhere('ut.dateOper < ?1')
                ->andWhere('ut.status = :status')
                ->setParameter('1', $dateStart)
                ->setParameter('status', UserTransaction::STATUS_ACTIVE)
                ;
        if (is_array($params)){
            if (!empty($params['user'])){
                if (is_numeric($params['user'])){
                    $qb->andWhere('ut.user = :user')
                        ->setParameter('user', $params['user'])
                            ;
                }    
            }            
        }

        $result = $qb->getQuery()->getOneOrNullResult();
        
        if (empty($result)){
            return 0;
        }

        if (empty($result['bSum'])){
            return 0;
        }
        
        return $result['bSum'];       
    }
    
    /**
    * Обороты за период
    * @param date $dateStart
    * @param date $dateEnd
    * @param int $period month, year
    * @param array $params
    */        
   public function periodTransaction($dateStart, $dateEnd, $period = 'month', $params = null)
   {
//       var_dump($dateEnd, $dateStart, $period, $params); exit;
        $entityManager = $this->getEntityManager();
        
        $qb = $entityManager->createQueryBuilder();
        $qb->select('sum(case when ut.amount > 0 then ut.amount else 0 end) as inSum, sum(case when ut.amount <= 0 then -ut.amount else 0 end) as outSum')
                ->from(UserTransaction::class, 'ut')
                ->andWhere('ut.dateOper <= ?2') 
                ->andWhere('ut.dateOper >= ?1')
                ->andWhere('ut.status = :status')
                ->setParameter('1', $dateStart)
                ->setParameter('2', $dateEnd)
                ->setParameter('status', UserTransaction::STATUS_ACTIVE)
                ->groupBy('period')
                ->orderBy('periodSort')
                ;
        
        $qbb = $entityManager->createQueryBuilder();
        $qbb->select('sum(utb.amount)')
                ->from(UserTransaction::class, 'utb')
                ->andWhere('utb.status = :status')
                ->setParameter('status', UserTransaction::STATUS_ACTIVE)
                ;

        switch ($period){
            case 'number': 
            case 'month': 
                $qb->addSelect('DATE_FORMAT(ut.dateOper, \'%m.%Y\') as period, DATE_FORMAT(ut.dateOper, \'%Y-%m\') as periodSort');
                $qbb->andWhere('utb.dateOper < DATE_FORMAT(ut.dateOper, \'%Y-%m-01\')');
                break;
            case 'year': 
                $qb->addSelect('DATE_FORMAT(ut.dateOper, \'%Y\') as period, Year(ut.dateOper) as periodSort'); 
                $qbb->andWhere('utb.dateOper < DATE_FORMAT(ut.dateOper, \'%Y-01-01\')');
                break;
            default: 
                $qb->addSelect('DATE_FORMAT(ut.dateOper, \'%d.%m.%Y\') as period, ut.dateOper as periodSort'); 
                $qbb->andWhere('utb.dateOper < ut.dateOper');
                break;    
        }
        
        if (is_array($params)){
            if (!empty($params['user'])){
                if (is_numeric($params['user'])){
                    $qb->andWhere('ut.user = :user')
                        ->setParameter('user', $params['user'])
                            ;
                    $qbb->andWhere('utb.user = :user')
                        ->setParameter('user', $params['user'])
                            ;
                }    
            }            
            if (!empty($params['order'])){
                $qb->orderBy('periodSort', $params['order']);
            }            
        }
        
        $qb->addSelect('('. $qbb->getQuery()->getDQL().') as bSum');
        
//        var_dump($qb->getQuery()->getSQL()); exit;
        $result = $qb->getQuery();
        return $result;       
   }
          
   /**
    * Данные для сопостовления торгового эквайринга
    * 
    * @return array
    */
   public function findForAsquiring()
   {
        $entityManager = $this->getEntityManager();
        $queryBuiler = $entityManager->createQueryBuilder();
        
        $orX = $queryBuiler->expr()->orX();
        $orX->add($queryBuiler->expr()->eq('c.payment',Cash::PAYMENT_CARD));
        $orX->add($queryBuiler->expr()->eq('c.payment',Cash::PAYMENT_QRCODE));
        
        $queryBuiler->select('cd')
            ->from(CashDoc::class, 'cd')
            ->innerJoin('cd.cash', 'c')    
            ->where('cd.dateOper > :dateOper')
            ->setParameter('dateOper', date('Y-m-d', strtotime('-1 month'))) 
//            ->andWhere('c.payment = :payment')
//            ->setParameter('payment', Cash::PAYMENT_CARD) 
            ->andWhere($orX)

            ->andWhere('cd.status = :status')
            ->setParameter('status', CashDoc::STATUS_ACTIVE)    
            ;        
        
//        var_dump($queryBuiler->getQuery()->getSQL()); exit;
        return $queryBuiler->getQuery()->getResult();
   }

   /**
    * Найти документ для выписки
    * @param Legal $legal
    * @param float $amount
    * @param date $paymentDate
    * @param Statement $statement
    * @return CashDoc
    */
   public function findCashDocForStatement($legal, $amount, $paymentDate, $statement)
   {
        $entityManager = $this->getEntityManager();
        $queryBuiler = $entityManager->createQueryBuilder();
        
        $orX = $queryBuiler->expr()->orX();
        $orX->add($queryBuiler->expr()->isNull('cd.statement'));
        $orX->add($queryBuiler->expr()->eq('cd.statement', $statement->getId()));
                
        
        $queryBuiler->select('cd')
            ->from(CashDoc::class, 'cd')
            ->innerJoin('cd.cash', 'c')    
            ->where('date(cd.dateOper) = :dateOper')
            ->setParameter('dateOper', date('Y-m-d', strtotime($paymentDate))) 
            ->andWhere('cd.amount = :amount')
            ->setParameter('amount', $amount) 
            ->andWhere('cd.legal = :legal')
            ->setParameter('legal', $legal->getId()) 
            ->andWhere('cd.status = :status')
            ->setParameter('status', CashDoc::STATUS_ACTIVE)
            ->andWhere($orX)                    
            ->setMaxResults(1)    
            ;        
//            var_dump($queryBuiler->getParameters());
//        var_dump($queryBuiler->getQuery()->getSQL()); exit;
        return $queryBuiler->getQuery()->getOneOrNullResult();
   }
   
   /**
    * Найти выписку для документа
    * @param CashDoc $cashDoc
    * @return Statement
    */
   public function findStatementForCashDoc($cashDoc)
   {
        $entityManager = $this->getEntityManager();
        $queryBuiler = $entityManager->createQueryBuilder();
        
        $orX = $queryBuiler->expr()->orX();

        $contact = $cashDoc->getContact();
        foreach ($contact->getLegals() as $legal){
            $orX->add($queryBuiler->expr()->eq('s.counterpartyInn', $legal->getInn()));
        }
                
        
        $queryBuiler->select('s')
            ->from(Statement::class, 's')
            ->where('date(s.chargeDate) = :docDate')
            ->setParameter('docDate', date('Y-m-d', strtotime($cashDoc->getDateOper()))) 
            ->andWhere('s.amount = :amount')
            ->setParameter('amount', $cashDoc->getAmount()) 
            ->andWhere('s.status = :status')
            ->setParameter('status', Statement::STATUS_ACTIVE)
            ->andWhere($orX)                    
            ->setMaxResults(1)    
            ;        
//            var_dump($queryBuiler->getParameters());
//        var_dump($queryBuiler->getQuery()->getSQL()); exit;
        return $queryBuiler->getQuery()->getOneOrNullResult();
   }
   
   /**
    * Исправить оплаты и отгрузки на сотрудников
    */
   public function findForUserRetailFix()
   {
        $entityManager = $this->getEntityManager();
        $queryBuiler = $entityManager->createQueryBuilder();
        
        $orX = $queryBuiler->expr()->orX();
//        $orX->add($queryBuiler->expr()->eq('r.docType', Movement::DOC_ORDER));
//        $orX->add($queryBuiler->expr()->eq('r.docType', Movement::DOC_VT));
//        $orX->add($queryBuiler->expr()->eq('r.docType', Movement::DOC_CASH));
        
        $queryBuiler->select('r')
                ->from(Register::class, 'r')
                ->join('r.vt', 'vt', 'WITH', 'r.docType = :docType')
                ->join('vt.order', 'o')
                ->join('o.contact', 'c')
                ->setParameter('docType', Movement::DOC_VT)
//                ->where('r.docType = :docType')
//                ->setParameter('docType', Movement::DOC_CASH)
                ->andWhere('c.user is not null')
//                ->andWhere($orX)
                ;
        
        return $queryBuiler->getQuery()->getResult();
   }
}
