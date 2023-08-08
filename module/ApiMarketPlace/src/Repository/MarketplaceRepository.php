<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ApiMarketPlace\Repository;

use Doctrine\ORM\EntityRepository;
use ApiMarketPlace\Entity\MarketplaceUpdate;
use ApiMarketPlace\Entity\MarketSaleReport;
use ApiMarketPlace\Entity\MarketSaleReportItem;
use Stock\Entity\Revise;

/**
 * Description of MarketplaceRepository
 *
 * @author Daddy
 */
class MarketplaceRepository extends EntityRepository
{
    /**
     * Запрос по отчетам
     * 
     * @param array $params
     * @return query
     */
    public function queryAllReport($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('m, mp, c')
            ->from(MarketSaleReport::class, 'm')
            ->join('m.marketplace', 'mp')
            ->join('m.contract', 'c')    
                ;
        
        if (is_array($params)){
            if (!empty($params['marketplaceId'])){
                if (is_numeric($params['marketplaceId'])){
                    $queryBuilder->andWhere('m.marketplace = ?1')
                        ->setParameter('1', $params['marketplaceId'])
                            ;
                }    
            }            
            if (!empty($params['marketSaleReportId'])){
                if (is_numeric($params['marketSaleReportId'])){
                    $queryBuilder->andWhere('m.id = :id')
                        ->setParameter('id', $params['marketSaleReportId'])
                            ;
                }    
            }            
            if (!empty($params['year'])){
                if (is_numeric($params['year'])){
                    $queryBuilder->andWhere('YEAR(m.docDate) = :year')
                            ->setParameter('year', $params['year']);
                }    
            }
            if (!empty($params['month'])){
                if (is_numeric($params['month'])){
                    $queryBuilder->andWhere('MONTH(m.docDate) = :month')
                            ->setParameter('month', $params['month']);
                }    
            }
            if (isset($params['sort'])){
                $queryBuilder->addOrderBy('m.'.$params['sort'], $params['order']);
            }        
            
        }

        return $queryBuilder->getQuery();
    }      
    
    /**
     * Запрос по отчетам
     * 
     * @param array $params
     * @return query
     */
    public function findAllReportTotal($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('count(m.id) as countMps')
            ->from(MarketSaleReport::class, 'm')
                ;
        
        if (is_array($params)){
            if (!empty($params['marketplaceId'])){
                if (is_numeric($params['marketplaceId'])){
                    $queryBuilder->andWhere('m.marketplace = ?1')
                        ->setParameter('1', $params['marketplaceId'])
                            ;
                }    
            }            
            if (!empty($params['year'])){
                if (is_numeric($params['year'])){
                    $queryBuilder->andWhere('YEAR(m.docDate) = :year')
                            ->setParameter('year', $params['year']);
                }    
            }
            if (!empty($params['month'])){
                if (is_numeric($params['month'])){
                    $queryBuilder->andWhere('MONTH(m.docDate) = :month')
                            ->setParameter('month', $params['month']);
                }    
            }            
        }

        $result = $queryBuilder->getQuery()->getOneOrNullResult();

        return $result['countMps'];
    }      
    
    /**
     * Запрос товаров по отчету
     * 
     * @param integer $mspId
     * @param array $params
     * @return query
     */
    public function findReportItems($mspId, $params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('i, g, p')
            ->from(MarketSaleReportItem::class, 'i')
            ->leftJoin('i.good', 'g')    
            ->leftJoin('g.producer', 'p')    
            ->where('i.marketSaleReport = ?1')
            ->setParameter('1', $mspId)    
                ;
        
        if (is_array($params)){
            if (isset($params['sort'])){
            }            
            if (!empty($params['itemId'])){
                if (is_numeric($params['itemId'])){
                    $queryBuilder->andWhere('i.id = :id')
                        ->setParameter('id', $params['itemId'])
                            ;
                }    
            }            
        }
//        var_dump($queryBuilder->getQuery()->getSQL());
        return $queryBuilder->getQuery();
    }    
    
    /**
     * Обновить расходы по отчету
     * @param MarketSaleReport $marketSaleReport
     */
    public function updateReportRevise($marketSaleReport)
    {
        $cost = 0;
        
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('sum(r.amount) as cost')
            ->from(Revise::class, 'r')
            ->where('r.contract = :contractId')
            ->setParameter('contractId', $marketSaleReport->getContract()->getId())    
            ->andWhere('r.docDate >= :date1')
            ->setParameter('date1', date('Y-m-d', strtotime("first day of month", strtotime($marketSaleReport->getDocDate()))))    
            ->andWhere('r.docDate <= :date2')
            ->setParameter('date1', date('Y-m-d', strtotime("last day of month", strtotime($marketSaleReport->getDocDate()))))
            ->setMaxResults(1)    
                ;
                
        $row = $queryBuilder->getQuery()->getOneOrNullResult();   
        
        if ($row){
            $cost = abs($row['cost']);
        }
        
        $entityManager->getConnection()->update('market_sale_report', ['cost_amount' => $cost], ['id' => $marketSaleReport->getId()]);
        
        return;
    }
}
