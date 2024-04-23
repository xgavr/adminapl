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
use Zp\Entity\PersonalAccrual;
use Zp\Entity\OrderCalculator;
use ApiMarketPlace\Entity\MarketSaleReport;
use Zp\Entity\PersonalMutual;
use Zp\Entity\PersonalRevise;
use Application\Entity\Order;
        
/**
 * Description of ZpRepository
 *
 * @author Daddy
 */
class ZpRepository extends EntityRepository
{

    /**
     * Получить операции
     * @param array $params
     * @return query
     */
    public function findMutuals($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('pm, c, u, a')
            ->from(PersonalMutual::class, 'pm')
            ->join('pm.company', 'c')    
            ->join('pm.user', 'u')    
            ->join('pm.accrual', 'a')    
                ;
        
        if (is_array($params)){
            if (!empty($params['company'])){
                $queryBuilder->andWhere('pm.company = :company')
                        ->setParameter('company', $params['company'])
                        ;
            }            
            if (!empty($params['user'])){
                if (is_numeric($params['user'])){
                    $queryBuilder->andWhere('pm.user = :user')
                            ->setParameter('user', $params['user'])
                            ;
                }    
            }            
            if (!empty($params['accrual'])){
                if (is_numeric($params['accrual'])){
                    $queryBuilder->andWhere('pm.accrual = :accrual')
                            ->setParameter('accrual', $params['accrual'])
                            ;
                }    
            }            
            if (!empty($params['startDate'])){
                $queryBuilder->andWhere("pm.dateOper >= :startDate")
                        ->setParameter('startDate', $params['startDate'])
                        ;
            }
            if (!empty($params['endDate'])){
                $queryBuilder->andWhere("pm.dateOper <= :endDate")
                        ->setParameter('endDate', $params['endDate'])
                        ;
            }
            if (isset($params['sort'])){
                $queryBuilder->orderBy('pm.'.$params['sort'], $params['order']);
            }            
        }    
//                var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();       
    }
    
    /**
     * Получить итоги операций
     * @param array $params
     * @return query
     */
    public function findMutualsTotal($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('sum(pm.amount) as amount, count(pm.id) as totalCount')
            ->from(PersonalMutual::class, 'pm')
            ->setMaxResults(1)    
                ;
        
        if (is_array($params)){
            if (!empty($params['company'])){
                $queryBuilder->andWhere('pm.company = :company')
                        ->setParameter('company', $params['company'])
                        ;
            }            
            if (!empty($params['user'])){
                if (is_numeric($params['user'])){
                    $queryBuilder->andWhere('pm.user = :user')
                            ->setParameter('user', $params['user'])
                            ;
                }    
            }            
            if (!empty($params['accrual'])){
                if (is_numeric($params['accrual'])){
                    $queryBuilder->andWhere('pm.accrual = :accrual')
                            ->setParameter('accrual', $params['accrual'])
                            ;
                }    
            }            
            if (!empty($params['startDate'])){
                $queryBuilder->andWhere("pm.dateOper >= :startDate")
                        ->setParameter('startDate', $params['startDate'])
                        ;
            }
            if (!empty($params['endDate'])){
                $queryBuilder->andWhere("pm.dateOper <= :endDate")
                        ->setParameter('endDate', $params['endDate'])
                        ;
            }
        }    
//                var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getOneOrNullResult();       
    }
    
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
            ->orderBy('p.sort')    
            ->addOrderBy('p.id')    
                ;
        if (is_array($params)){
            if (!empty($params['company'])){
                $queryBuilder->andWhere('p.company = :company')
                        ->setParameter('company', $params['company'])
                        ;
            }            
            if (isset($params['sort'])){
                $queryBuilder->addOrderBy('p.'.$params['sort'], $params['order']);
            }            
        }    
//                var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();       
    }
    
    /**
     * Сортировка
     * @param array $params
     * @return int
     */
    public function findMaxSortPosition($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('max(p.sort) as maxSort')
            ->from(Position::class, 'p')
            ->orderBy('p.sort')                    
                ;
        if (is_array($params)){
            if (!empty($params['company'])){
                $queryBuilder->andWhere('p.company = :company')
                        ->setParameter('company', $params['company'])
                        ;
            }            
            if (!empty($params['parentPosition'])){
                $queryBuilder->andWhere('p.parentPosition = :parentPosition')
                        ->setParameter('parentPosition', $params['parentPosition'])
                        ;
            }            
        }    
        
        $result = $queryBuilder->getQuery()->getOneOrNullResult();
        
        if ($result){
            return $result['maxSort'];
        }
//                var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return 0;       
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
            if (!empty($params['company'])){
                $queryBuilder->andWhere('p.company = :company')
                        ->setParameter('company', $params['company'])
                        ;
            }            
//                var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult();       
    }

    /**
     * Обновить parentPositions num
     * @param Position $position
     * @return query
     */
    public function updateParentPositionNum($position)
    {
        $parentPosition = $position->getParentPosition();
        
        if ($parentPosition){
            $parentPosition->setNum(0);
                    
            $entityManager = $this->getEntityManager();

            $queryBuilder = $entityManager->createQueryBuilder();

            $queryBuilder->select('sum(p.num) as totalNum')
                ->from(Position::class, 'p')
                ->where('p.parentPosition = :parentPosition')    
                ->andWhere('p.status = :status')
                ->setParameter('parentPosition', $parentPosition)    
                ->setParameter('status', Position::STATUS_ACTIVE)    
                ->setMaxResults(1)    
                    ;
            $data = $queryBuilder->getQuery()->getOneOrNullResult();

            if (!empty($data['totalNum'])){
                $parentPosition->setNum($data['totalNum']);
            }
            
            $entityManager->persist($parentPosition);
            $entityManager->flush();
        }    
        
        return;       
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
        
        $queryBuilder->select('p, u, pos')
            ->from(Personal::class, 'p')
            ->join('p.user', 'u')
            ->join('p.position', 'pos')
                ;
        if (is_array($params)){
            if (!empty($params['company'])){
                if (is_numeric($params['company'])){
                    $queryBuilder->andWhere('p.company = :company')
                            ->setParameter('company', $params['company'])
                            ;
                }    
            }            
            if (!empty($params['user'])){
                if (is_numeric($params['user'])){
                    $queryBuilder->andWhere('p.user = :user')
                            ->setParameter('user', $params['user'])
                            ;
                }    
            }            
            if (!empty($params['status'])){
                if (is_numeric($params['status'])){
                    $queryBuilder->andWhere('p.status = :status')
                            ->setParameter('status', $params['status'])
                            ;
                }    
            }            
            if (!empty($params['position'])){
                if (is_numeric($params['position'])){
                    $queryBuilder->andWhere('p.position = :position')
                            ->setParameter('position', $params['position'])
                            ;
                }
            }            
            if (isset($params['sort'])){
                $queryBuilder->orderBy('p.'.$params['sort'], $params['order']);
            }            
        }    
//                var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();       
    }   

    /**
     * Получить корректировки
     * @param array $params
     * @return query
     */
    public function findPersonalRevise($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('pr, u')
            ->from(PersonalRevise::class, 'pr')
            ->join('pr.user', 'u')
                ;
        
        if (is_array($params)){
            if (!empty($params['company'])){
                if (is_numeric($params['company'])){
                    $queryBuilder->andWhere('pr.company = :company')
                            ->setParameter('company', $params['company'])
                            ;
                }    
            }            
            if (!empty($params['user'])){
                if (is_numeric($params['user'])){
                    $queryBuilder->andWhere('pr.user = :user')
                            ->setParameter('user', $params['user'])
                            ;
                }    
            }            
            if (!empty($params['status'])){
                if (is_numeric($params['status'])){
                    $queryBuilder->andWhere('p.status = :status')
                            ->setParameter('status', $params['status'])
                            ;
                }    
            }            
            if (!empty($params['kind'])){
                if (is_numeric($params['kind'])){
                    $queryBuilder->andWhere('p.kind = :kind')
                            ->setParameter('kind', $params['kind'])
                            ;
                }    
            }            
            if (!empty($params['accrual'])){
                if (is_numeric($params['accrual'])){
                    $queryBuilder->andWhere('pr.accrual = :accrual')
                            ->setParameter('accrual', $params['accrual'])
                            ;
                }
            }            
            if (!empty($params['year'])){
                if (is_numeric($params['year'])){
                    $queryBuilder->andWhere('YEAR(pr.docDate) = :year')
                            ->setParameter('year', $params['year']);
                }    
            }
            if (!empty($params['month'])){
                if (is_numeric($params['month'])){
                    $queryBuilder->andWhere('MONTH(pr.docDate) = :month')
                            ->setParameter('month', $params['month']);
                }    
            }
            if (isset($params['sort'])){
                $queryBuilder->orderBy('pr.'.$params['sort'], $params['order']);
            }            
        }    
//                var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();       
    }   

    /**
     * Запрос начислений по персоналу
     * 
     * @param integer $personalId
     * @param array $params
     * @return query
     */
    public function findPersonalAccruals($personalId, $params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('pa, u, a')
            ->from(PersonalAccrual::class, 'pa')
            ->join('pa.user', 'u')    
            ->join('pa.accrual', 'a')    
            ->where('pa.personal = ?1')
            ->setParameter('1', $personalId)    
                ;
        
        if (is_array($params)){
            if (isset($params['sort'])){
            }            
        }
//        var_dump($queryBuilder->getQuery()->getSQL());
        return $queryBuilder->getQuery();
    }      
    
    /**
     * Список для формы
     * @param array $params
     * @return array
     */
    public function accrualListForm($params)
    {
        $result = [];
        
        $data = $this->findAccrual($params)->getResult();
        
        foreach ($data as $row){
            $result[$row->getId()] = $row->getName();
        }
        
        return $result;
    }

    /**
     * Список для формы
     * @param array $params
     * @return array
     */
    public function positionListForm($params)
    {
        $result = [];
        if (!empty($params['all'])){
            $result[] = $params['all'];
        }
        
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('p.id, p.name as name, pp.name as groupName')
            ->from(Position::class, 'p')
            ->join('p.parentPosition', 'pp')
            ->orderBy('p.sort')    
            ->addOrderBy('p.id')    
                ;
        if (is_array($params)){
            if (!empty($params['company'])){
                $queryBuilder->andWhere('p.company = :company')
                        ->setParameter('company', $params['company'])
                        ;
            }            
            if (!empty($params['status'])){
                $queryBuilder->andWhere('p.status = :status')
                        ->setParameter('status', $params['status'])
                        ;
            }            
            if (isset($params['sort'])){
                $queryBuilder->addOrderBy('p.'.$params['sort'], $params['order']);
            }            
        }    
        
        $data = $queryBuilder->getQuery()->getResult();
        
        foreach ($data as $row){
            $result[$row['id']] = $row['name'].' ('.$row['groupName'].')';
        }
        
        return $result;
    }
    
    /**
     * Найти начисления
     * 
     * @param type $dateCalculation
     * 
     * @return type Description
     */    
    public function findUniquePersonalAccrual($dateCalculation)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('identity(pa.company) as company, identity(pa.personal) as personal, '
                . 'identity(pa.accrual) as accrual, identity(pa.user) as user, pa.taxedNdfl')
                ->distinct()
                ->from(PersonalAccrual::class, 'pa')
                ->where('pa.dateOper <= :dateOper')
                ->setParameter('dateOper', $dateCalculation)
                ;
        
        return $queryBuilder->getQuery()->getResult();        
    }

    /**
     * Найти актуальные начисления
     * 
     * @param type $dateCalculation
     * 
     * @return type Description
     */    
    public function findActualPersonalAccrual($dateCalculation)
    {
        $result = [];

        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $uniquePersonalAccrualData = $this->findUniquePersonalAccrual($dateCalculation);
        
        foreach ($uniquePersonalAccrualData as $personalAccrualRow){

            $queryBuilder->resetDQLParts();
            $queryBuilder->select('pa')
                    ->from(PersonalAccrual::class, 'pa')
                    ->where('pa.dateOper <= :dateOper')
                    ->setParameter('dateOper', $dateCalculation)
                    ->orderBy('pa.dateOper', 'Desc')
                    ->andWhere('pa.user = :user')
                    ->setParameter('user', $personalAccrualRow['user'])
                    ->andWhere('pa.accrual = :accrual')
                    ->setParameter('accrual', $personalAccrualRow['accrual'])
                    ->andWhere('pa.company = :company')
                    ->setParameter('company', $personalAccrualRow['company'])
                    ->andWhere('pa.personal = :personal')
                    ->setParameter('personal', $personalAccrualRow['personal'])
                    ->andWhere('pa.taxedNdfl = :taxedNdfl')
                    ->setParameter('taxedNdfl', $personalAccrualRow['taxedNdfl'])
                    ->setMaxResults(1)
                    ;
        
            $data = $queryBuilder->getQuery()->getOneOrNullResult();
            
            if (!empty($data)){
                $result[] = $data;
            }            
        }     

        return $result;
    }
    
    /**
     * Получить order calculators
     * @param array $params
     * @return query
     */
    public function findOrderCalculators($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('oc')
            ->from(OrderCalculator::class, 'oc')
                ;
        
        if (is_array($params)){
            if (!empty($params['status'])){
                $queryBuilder->andWhere('oc.status = :status')
                        ->setParameter('status', $params['status'])
                        ;
            }            
            if (!empty($params['company'])){
                $queryBuilder->andWhere('oc.company = :company')
                        ->setParameter('company', $params['company'])
                        ;
            }            
            if (!empty($params['user'])){
                if (is_numeric($params['user'])){
                    $queryBuilder->andWhere('oc.user = :user')
                            ->setParameter('user', $params['user'])
                            ;
                }    
            }            
            if (!empty($params['startDate'])){
                $queryBuilder->andWhere("oc.dateOper >= :startDate")
                        ->setParameter('startDate', $params['startDate'])
                        ;
            }
            if (!empty($params['endDate'])){
                $queryBuilder->andWhere("oc.dateOper <= :endDate")
                        ->setParameter('endDate', $params['endDate'])
                        ;
            }
            if (isset($params['sort'])){
                $queryBuilder->orderBy('oc.'.$params['sort'], $params['order']);
            }            
        }    
//                var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();       
    }
    
    /**
     * База Tp
     * @param date $dateCalculation
     * @param array $params
     * @return int
     */
    public function baseTp($dateCalculation, $params = null)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('sum(msr.docAmount-msr.baseAmount-msr.costAmount) as base')
                ->from(MarketSaleReport::class, 'msr')
                ->where('msr.docDate = :dateOper')
                ->setParameter('dateOper', $dateCalculation)
                ->andWhere('msr.status = :status')
                ->setParameter('status', MarketSaleReport::STATUS_ACTIVE)
                ->setMaxResults(1)
                ;
        
        if (is_array($params)){
            if (!empty($params['company'])){
                if (is_numeric($params['company'])){
                    $queryBuilder
                            ->join('msr.contract', 'c')
                            ->andWhere('c.company = :company')
                            ->setParameter('company', $params['company'])
                            ;
                }
            }
        }
        
        $data = $queryBuilder->getQuery()->getOneOrNullResult();
        if (!empty($data)){
            return $data['base'];
        }
        
        return 0;
    }
    
    /**
     * Считать сотрудника по заказам
     * @param Order $order
     * @return PersonalAccrual
     */
    public function findForOrderCalculate($order)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('pa')
                ->from(PersonalAccrual::class, 'pa')
                ->join('pa.accrual', 'a')
                ->where('pa.user = :user')
                ->setParameter('user', $order->getUserId())
                ->andWhere('a.basis = :basis')
                ->setParameter('basis', Accrual::BASE_INCOME_ORDER)
                ->andWhere('pa.dateOper <= :dateOper')
                ->setParameter('dateOper', $order->getDateOper())
                ->orderBy('pa.dateOper', 'DESC')
                 ->setMaxResults(1)
                ;

        $result = $queryBuilder->getQuery()->getOneOrNullResult();
        if ($result){
            if ($result->getStatus() == PersonalAccrual::STATUS_ACTIVE){
                return $result;
            }    
        }
        return false;
    }
    
    /**
     * База Retail
     * @param date $dateCalculation
     * @param array $params
     * @return int
     */
    public function baseRetail($dateCalculation, $params = null)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('sum(oc.amount-oc.baseAmount) as base')
                ->from(OrderCalculator::class, 'oc')
                ->where('oc.dateOper = :dateOper')
                ->setParameter('dateOper', $dateCalculation)
                ->andWhere('oc.status = :status')
                ->setParameter('status', OrderCalculator::STATUS_ACTIVE)
                ->setMaxResults(1)
                ;
        
        if (is_array($params)){
            if (!empty($params['company'])){
                if (is_numeric($params['company'])){
                    $queryBuilder->andWhere('oc.company = :company')
                            ->setParameter('company', $params['company'])
                            ;
                }
            }
            if (!empty($params['user'])){
                if (is_numeric($params['user'])){
                    $queryBuilder->andWhere('oc.user = :user')
                            ->setParameter('user', $params['user'])
                            ;
                }
            }
        }
        
        $data = $queryBuilder->getQuery()->getOneOrNullResult();
        if (!empty($data)){
            return $data['base'];
        }
        
        return 0;
    }
    
    /**
     * Расчетный лист
     * 
     * @param array $params
     */
    public function payslip($params = null)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('identity(pm.company) as company, identity(pm.user) as user, '
                . 'sum(pm.amount) as amount,'
                . 'sum(case when pm.amount > 0 then pm.amount else 0 end) as amountIn,'
                . 'sum(case when pm.amount < 0 then -pm.amount else 0 end) as amountOut')
                ->from(PersonalMutual::class, 'pm')
                ->andWhere('pm.status = :status')
                ->setParameter('status', PersonalMutual::STATUS_ACTIVE)
                ->groupBy('company')
                ->addGroupBy('user')
                ;    
        
        if (is_array($params)){
            if ($params['summary'] == false){
                $queryBuilder->addSelect('identity(pm.accrual) as accrual')
                        ->addGroupBy('accrual')
                        ;
            }            
            if (!empty($params['company'])){
                if (is_numeric($params['company'])){
                    $queryBuilder->andWhere('pm.company = :company')
                            ->setParameter('company', $params['company'])
                            ;
                }    
            }            
            if (!empty($params['user'])){
                if (is_numeric($params['user'])){
                    $queryBuilder->andWhere('pm.user = :user')
                            ->setParameter('user', $params['user'])
                            ;
                }    
            }            
            if (!empty($params['accrual'])){
                if (is_numeric($params['accrual'])){
                    $queryBuilder->andWhere('pm.accrual = :accrual')
                            ->setParameter('accrual', $params['accrual'])
                            ;
                }    
            }            
            if (!empty($params['startDate'])){
                $queryBuilder->andWhere("pm.dateOper >= :startDate")
                        ->setParameter('startDate', $params['startDate'])
                        ;
            }
            if (!empty($params['endDate'])){
                $queryBuilder->andWhere("pm.dateOper <= :endDate")
                        ->setParameter('endDate', $params['endDate'])
                        ;
            }
            if (isset($params['sort'])){
                //$queryBuilder->orderBy($params['sort'], $params['order']);
            }                        
            $queryBuilder->addOrderBy('amount');
        }    
        
        return $queryBuilder->getQuery();
    }
}