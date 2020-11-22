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
use Stock\Entity\Unit;
use Stock\Entity\Ntd;
use Application\Entity\Supplier;

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

        $queryBuilder->select('p, l, o')
            ->from(Ptu::class, 'p')
            ->join('p.legal', 'l')
            ->join('p.office', 'o')    
                ;
        
        if (is_array($params)){
            if (isset($params['sort'])){
                $queryBuilder->orderBy('p.'.$params['sort'], $params['order']);
            }            
            if (!empty($params['supplierId'])){
                $supplier = $entityManager->getRepository(Supplier::class)
                        ->findOneById($params['supplierId']);
                if ($supplier){
                    $orX = $queryBuilder->expr()->orX();
                    foreach ($supplier->getLegalContact()->getLegals() as $legal){
                        $orX->add($queryBuilder->expr()->eq('p.legal', $legal->getId()));
                    }    
                    $queryBuilder->andWhere($orX);
                }    
            }            
        }

        return $queryBuilder->getQuery();
    }    
    
    
    /**
     * Запрос по все пту
     * 
     * @param array $params
     * @return query
     */
    public function queryAllPtu($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('p')
            ->from(Ptu::class, 'p')
                ;
        
        if (is_array($params)){
            if (!empty($params['supplierId'])){
                $supplier = $entityManager->getRepository(Supplier::class)
                        ->findOneById($params['supplierId']);
                if ($supplier){
                    $orX = $queryBuilder->expr()->orX();
                    foreach ($supplier->getLegalContact()->getLegals() as $legal){
                        $orX->add($queryBuilder->expr()->eq('p.legal', $legal->getId()));
                    }    
                    $queryBuilder->andWhere($orX);
                }    
            }            
        }
        
        return $queryBuilder->getQuery();
    }    
    
    /**
     * Запрос по  количество пту
     * 
     * @param array $params
     * @return query
     */
    public function findAllPtuTotal($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('count(p.id) as countPtu')
            ->from(Ptu::class, 'p')
                ;
        
        if (is_array($params)){
            if (!empty($params['supplierId'])){
                $supplier = $entityManager->getRepository(Supplier::class)
                        ->findOneById($params['supplierId']);
                if ($supplier){
                    $orX = $queryBuilder->expr()->orX();
                    foreach ($supplier->getLegalContact()->getLegals() as $legal){
                        $orX->add($queryBuilder->expr()->eq('p.legal', $legal->getId()));
                    }    
                    $queryBuilder->andWhere($orX);
                }    
            }            
        }
        
        $result = $queryBuilder->getQuery()->getOneOrNullResult();

        return $result['countPtu'];
    }    
    
    /**
     * Запрос товаров по пту
     * 
     * @param integer $ptuId
     * @param array $params
     * @return query
     */
    public function findPtuGoods($ptuId, $params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('pg, g, p, n, u, c')
            ->from(PtuGood::class, 'pg')
            ->join('pg.good', 'g')    
            ->join('g.producer', 'p')    
            ->join('pg.ntd', 'n')    
            ->join('pg.unit', 'u')    
            ->join('pg.country', 'c')    
            ->where('pg.ptu = ?1')
            ->setParameter('1', $ptuId)    
                ;
        
        if (is_array($params)){
            if (isset($params['sort'])){
            }            
        }
//        var_dump($queryBuilder->getQuery()->getSQL());
        return $queryBuilder->getQuery();
    }    
    
    /**
     * Запрос ЕИ для автозаполения
     * 
     * @param array $params
     * @return query
     */
    public function autocompeteUnit($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('u')
            ->from(Unit::class, 'u')
                ;
        
        if (is_array($params)){
            if (isset($params['search'])){
                $queryBuilder
                    ->where('u.name like ?1')                           
                    ->setParameter('1', $params['search'].'%')    
                        ;
            }            
        }
//        var_dump($queryBuilder->getQuery()->getSQL());
        return $queryBuilder->getQuery();
    }    

    /**
     * Запрос Ntd для автозаполения
     * 
     * @param array $params
     * @return query
     */
    public function autocompeteNtd($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('n')
            ->from(Ntd::class, 'n')
                ;
        
        if (is_array($params)){
            if (isset($params['search'])){
                $queryBuilder
                    ->where('n.ntd like ?1')                           
                    ->setParameter('1', $params['search'].'%')    
                        ;
            }            
        }
//        var_dump($queryBuilder->getQuery()->getSQL());
        return $queryBuilder->getQuery();
    }    
    
    
}