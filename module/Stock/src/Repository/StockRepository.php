<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Stock\Repository;

use Doctrine\ORM\EntityRepository;
use Stock\Entity\Ptu;
use Stock\Entity\PtuGood;

/**
 * Description of StockRepository
 *
 * @author Daddy
 */
class StockRepository extends EntityRepository{
    
    /**
     * Сумма ПТУ
     * 
     * @param Ptu $ptu
     * @return float
     */
    public function ptuAmountTotal($ptu)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('sum(pg.amount) as total')
                ->from(PtuGood::class, 'pg')
                ->where('pg.ptu = ?1')
                ->setParameter('1', $ptu->getId())
                ->setMaxResults(1)
                ;
        
        $result = $queryBuilder->getQuery()->getOneOrNullResult();
        
        if (!empty($result['total'])){
            return $result['total'];
        }
        
        return 0;
    }

    /**
     * Запрос по пту
     * 
     * @param array $params
     * @return query
     */
    public function findAllPtu($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('p')
            ->from(Ptu::class, 'p')
                ;
        
        if (is_array($params)){
            if (isset($params['sort'])){
//                if ($params['sort'] == 'unknownProducerCount'){
//                    $queryBuilder->orderBy('unknownProducerCount', $params['order']);
//                } elseif ($params['sort'] == 'supplierCount'){
//                    $queryBuilder->orderBy('supplierCount', $params['order']);
//                } elseif ($params['sort'] == 'rawpriceCount'){
//                    $queryBuilder->orderBy('rawpriceCount', $params['order']);
//                } else {
//                    $queryBuilder->orderBy('p.'.$params['sort'], $params['order']);                
//                }    
            }            
        }

        return $queryBuilder->getQuery();
    }    
    
}