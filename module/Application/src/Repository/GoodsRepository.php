<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Repository;

use Doctrine\ORM\EntityRepository;
use Application\Entity\Goods;
use Application\Entity\Rawprice;
use Application\Entity\Producer;
/**
 * Description of GoodsRepository
 *
 * @author Daddy
 */
class GoodsRepository extends EntityRepository{

    /**
     * Запрос по товарам по разным параметрам
     * 
     * @param array $params
     * @return object
     */
    public function findAllGoods($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('c', 'p')
            ->from(Goods::class, 'c')
            ->join('c.producer', 'p', 'WITH')    
                ;

        if (is_array($params)){
            if (isset($params['producer'])){
                $queryBuilder->where('c.producer = ?1')
                    ->setParameter('1', $params['producer']->getId())
                        ;
            }
            if (isset($params['unknownProducer'])){
                $queryBuilder
                    ->join('c.rawprice', 'r', 'WITH')
                    ->andWhere('r.unknownProducer = ?2')
                    ->setParameter('2', $params['unknownProducer']->getId())
                        ;
            }
            if (isset($params['q'])){
                $queryBuilder->andWhere('c.code like :search')
                    ->setParameter('search', '%' . $params['q'] . '%')
                        ;
            }
            if (isset($params['next1'])){
                $queryBuilder->andWhere('c.code > ?3')
                    ->setParameter('3', $params['next1'])
                    ->setMaxResults(1)    
                 ;
            }
            if (isset($params['prev1'])){
                $queryBuilder->andWhere('c.code < ?4')
                    ->setParameter('4', $params['prev1'])
                    ->orderBy('c.code', 'DESC')
                    ->setMaxResults(1)    
                 ;
            }
            if (isset($params['sort'])){
                $queryBuilder->orderBy('c.'.$params['sort'], $params['order']);                
            }            
        } else {
            $queryBuilder    
                ->orderBy('c.id', 'DESC')
                ->setMaxResults(10)            
        }
//var_dump($queryBuilder->getQuery()->getDQL()); exit;
        return $queryBuilder->getQuery();
    }
    
    /**
     * Количество записей в прайсах с этим товара
     * 
     * @param Application\Entity\Goods $goods
     * 
     * @return object
     */
    public function rawprices($goods)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('r')
            ->from(Rawprice::class, 'r')
            ->where('r.good = ?1')
            ->andWhere('r.status = ?2')
            ->setParameter('1', $goods->getId())    
            ->setParameter('2', Rawprice::STATUS_PARSED)    
                ;
        //var_dump($queryBuilder->getQuery()->getDQL());
        return $queryBuilder->getQuery()->getResult();    
    }
    
    /**
     * Выборка из прайсов по id товара и id поставщика 
     * @param array $params
     * @return object
     */
    public function randRawpriceBy($params)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('r')
            ->from(Rawprice::class, 'r')
            ->join('r.raw', 'w')    
            ->where('r.good = ?1')
            ->andWhere('w.supplier = ?2')
            ->andWhere('r.status = ?3')
            ->setParameter('1', $params['good'])    
            ->setParameter('2', $params['supplier'])    
            ->setParameter('3', Rawprice::STATUS_PARSED)
            ->setMaxResults(5)
            //->orderBy('rand()')    
                ;
        return $queryBuilder->getQuery()->getResult();    
        
    }    
    
    
    public function searchByName($search){

        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('g, p')
            ->from(Goods::class, 'g')
            ->join("g.producer", 'p', 'WITH') 
            ->where('g.name like :search')    
            ->orderBy('g.name')
            ->setParameter('search', '%' . $search . '%')
                ;
        return $queryBuilder->getQuery();
    }
    
    public function searchNameForSearchAssistant($search)
    {        
        return $this->searchByName($search)->getResult();
    }  
    
    /**
     * @param Apllication\Entity\Goods $good
     */
    public function findGoodRawprice($good)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('c')
            ->from(Rawprice::class, 'c')
            ->where('c.good = ?1')    
            ->orderBy('c.id')
            ->setParameter('1', $good->getId())    
                ;

        return $queryBuilder->getQuery();
    }
    
    
    public function getMaxPrice($good)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('r')
            ->from(Rawprice::class, 'r')
            ->select('MAX(r.price) as price')
            ->where('r.good = ?1')    
            ->groupBy('r.good')
            ->setParameter('1', $good->getId())
                ;
        return $queryBuilder->getQuery()->getResult();
        
    }
       
    /**
     * Найти товары для удаления
     * 
     * @return object
     */
    public function findGoodsForDelete()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('g')
            ->addSelect('count(r.id) as rawpriceCount')    
            ->from(Goods::class, 'g')
            ->leftJoin(Rawprice::class, 'r', 'WITH', 'r.good = g.id')
            ->groupBy('g.id')
            ->having('rawpriceCount = 0')
                ;
        //var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult();            
    }

    
}
