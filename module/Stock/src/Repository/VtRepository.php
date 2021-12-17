<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Stock\Repository;

use Doctrine\ORM\EntityRepository;
use Application\Entity\Order;
use Application\Entity\Bid;
use Stock\Entity\Vt;
use Stock\Entity\VtGood;
use Stock\Entity\Unit;
use Stock\Entity\Ntd;
use Application\Entity\Supplier;
use Company\Entity\Office;

/**
 * Description of VtRepository
 *
 * @author Daddy
 */
class VtRepository extends EntityRepository{
    
    /**
     * Сумма возврата
     * 
     * @param Vt $vt
     * @return float
     */
    public function vtAmountTotal($vt)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('sum(vg.amount) as total')
                ->from(VtGood::class, 'vg')
                ->where('vg.vt = ?1')
                ->setParameter('1', $vt->getId())
                ->setMaxResults(1)
                ;
        
        $result = $queryBuilder->getQuery()->getOneOrNullResult();
        
        if (!empty($result['total'])){
            return $result['total'];
        }
        
        return 0;
    }

    /**
     * Запрос по возвратам
     * 
     * @param array $params
     * @return query
     */
    public function findAllVt($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('v, p, o')
            ->from(Vt::class, 'v')
            ->join('v.order', 'p')
            ->join('p.office', 'o')    
                ;
        
        if (is_array($params)){
            if (isset($params['orderId'])){
                $queryBuilder->andWhere('v.order = ?1')
                    ->setParameter('1', $params['orderId'])
                        ;
            }            
            if (isset($params['sort'])){
                $queryBuilder->orderBy('v.'.$params['sort'], $params['order']);
            }            
            if (is_numeric($params['officeId'])){
                $queryBuilder->andWhere('v.office = ?2')
                        ->setParameter('2', $params['officeId']);
            }
            if (!empty($params['year'])){
                if (is_numeric($params['year'])){
                    $queryBuilder->andWhere('YEAR(v.docDate) = :year')
                            ->setParameter('year', $params['year']);
                }    
            }
            if (!empty($params['month'])){
                if (is_numeric($params['month'])){
                    $queryBuilder->andWhere('MONTH(v.docDate) = :month')
                            ->setParameter('month', $params['month']);
                }    
            }
        }

        return $queryBuilder->getQuery();
    }    
    
    
    /**
     * Запрос по всем возвратам
     * 
     * @param array $params
     * @return query
     */
    public function queryAllVt($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('v')
            ->from(Vt::class, 'v')
                ;
        
        if (is_array($params)){
            if (is_numeric($params['officeId'])){
                $queryBuilder->andWhere('v.office = ?1')
                        ->setParameter('1', $params['officeId']);
            }
            if (!empty($params['year'])){
                if (is_numeric($params['year'])){
                    $queryBuilder->andWhere('YEAR(v.docDate) = :year')
                            ->setParameter('year', $params['year']);
                }    
            }            
            if (!empty($params['month'])){
                if (is_numeric($params['month'])){
                    $queryBuilder->andWhere('MONTH(v.docDate) = :month')
                            ->setParameter('month', $params['month']);
                }    
            }
        }
        
        return $queryBuilder->getQuery();
    }    
    
    /**
     * Запрос по  количество возвратов
     * 
     * @param array $params
     * @return query
     */
    public function findAllVtTotal($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('count(v.id) as countVt')
            ->from(Vt::class, 'v')
                ;
        
        if (is_array($params)){
            if (isset($params['orderId'])){
                $queryBuilder->andWhere('v.order = ?1')
                    ->setParameter('1', $params['orderId']);
                        ;
            }            
            if (is_numeric($params['officeId'])){
                $queryBuilder->andWhere('v.office = ?2')
                        ->setParameter('2', $params['officeId']);
            }
            if (!empty($params['year'])){
                if (is_numeric($params['year'])){
                    $queryBuilder->andWhere('YEAR(v.docDate) = :year')
                            ->setParameter('year', $params['year']);
                }    
            }
            if (!empty($params['month'])){
                if (is_numeric($params['month'])){
                    $queryBuilder->andWhere('MONTH(v.docDate) = :month')
                            ->setParameter('month', $params['month']);
                }    
            }
        }
        
        $result = $queryBuilder->getQuery()->getOneOrNullResult();

        return $result['countVt'];
    }    
    
    /**
     * Запрос товаров по возврату
     * 
     * @param integer $vtId
     * @param array $params
     * @return query
     */
    public function findVtGoods($vtId, $params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('vt, g, p')
            ->from(VtGood::class, 'vt')
            ->join('vt.good', 'g')    
            ->join('g.producer', 'p')    
            ->where('vt.vt = ?1')
            ->setParameter('1', $vtId)    
                ;
        
        if (is_array($params)){
            if (isset($params['sort'])){
            }            
        }
//        var_dump($queryBuilder->getQuery()->getSQL());
        return $queryBuilder->getQuery();
    }        
}