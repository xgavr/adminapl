<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Repository;
use Doctrine\ORM\EntityRepository;
use Application\Entity\Scale;
use Application\Entity\ScaleTreshold;
use Application\Entity\Rate;
use Application\Entity\Goods;
use Application\Entity\Rawprice;

/**
 * Description of RateRepository
 *
 * @author Daddy
 */
class RateRepository  extends EntityRepository
{

    /**
     * Найти расценку по умолчанию
     * 
     * @return Rate
     */
    public function findDefaultRate()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('r')
            ->from(Rate::class, 'r')
            ->where('r.status = ?1')
            ->setParameter('1', Rate::STATUS_ACTIVE)
            ->andWhere('r.producer is null')
            ->andWhere('r.genericGroup is null')
            ->andWhere('r.tokenGroup is null')
//            ->andWhere('r.supplier is null')    
            ->orderBy('r.id', 'ASC')
            ;
        
        $rates = $queryBuilder->getQuery()->getResult();
        foreach ($rates as $rate){
            return $rate;
        }
        
        return;
    }
    
    /**
     * Плучить специальную расценку
     * 
     * @param array $params
     * @return array
     */
    public function findParamRate($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('r')
            ->from(Rate::class, 'r')
            ->orderBy('r.id', 'ASC')
            ->where('r.status = ?1')
            ->setParameter('1', Rate::STATUS_ACTIVE)    
            ;
        
        if (is_array($params)){
            if (isset($params['supplier'])){
                $queryBuilder->andWhere('r.supplier = ?2')
                        ->setParameter('2', $params['supplier']);
            }
            if (isset($params['producer'])){
                $queryBuilder->andWhere('r.producer = ?3')
                        ->setParameter('3', $params['producer']);
            }
            if (isset($params['genericGroup'])){
                $queryBuilder->andWhere('r.genericGroup = ?4')
                        ->setParameter('4', $params['genericGroup']);
            }
            if (isset($params['tokenGroup'])){
                $queryBuilder->andWhere('r.tokenGroup = ?5')
                        ->setParameter('5', $params['tokenGroup']);
            }
        }

//        var_dump($queryBuilder->getQuery()->getSQL());
        $rates = $queryBuilder->getQuery()->getResult();
        foreach ($rates as $rate){
            return $rate;
        }
        
        return;
    }    

    /**
     * Плучить расценку
     * 
     * @param array $params
     * @return array
     */
    public function findRate($params = null)
    {
        $result = $this->findParamRate($params);
        if (!$result){
            return $this->findDefaultRate();
        }
        
        return $result;
    }    
    
    
    /**
     * Найти поставщиков товара
     * 
     * @param Goods $good
     * @return integer
     */
    public function findGoodSupplier($good)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('s.id')
                ->distinct()
                ->from(Goods::class, 'g')
                ->join('g.articles', 'a')
                ->join('a.rawprice', 'r')
                ->join('r.raw', 'raw')
                ->join('raw.supplier', 's')
                ->where('g.id = ?1')
                ->setParameter('1', $good->getId())
                ->andWhere('r.status = ?2')
                ->setParameter('2', Rawprice::STATUS_PARSED)
                ;
        
        $data = $queryBuilder->getQuery()->getResult();
        if (count($data) == 1){
            return $data[0]['id'];
        }
        return;
    }
    
    /**
     * Найти расценку
     * @param int $tokenGroupId
     * @param int $genericGroupId
     * @param int $producerId
     * @return Rate
     */
    public function getRate($tokenGroupId = null, $genericGroupId = null, $producerId = null)            
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $rate = null;
        if ($tokenGroupId){
            $queryBuilder->resetDQLParts();
            $queryBuilder->select('r')
                ->from(Rate::class, 'r')
                ->where('r.status = ?1')
                ->andWhere('r.tokenGroup = ?2')    
                ->setParameter('1', Rate::STATUS_ACTIVE)
                ->setParameter('2', $tokenGroupId)
                ->setMaxResults(1)    
                ;       
            $rate = $queryBuilder->getQuery()->getOneOrNullResult();
        }    
        
        if (!$rate && $genericGroupId){
            $queryBuilder->resetDQLParts();
            $queryBuilder->select('r')
                ->from(Rate::class, 'r')
                ->where('r.status = ?1')
                ->andWhere('r.genericGroup = ?2')    
                ->setParameter('1', Rate::STATUS_ACTIVE)
                ->setParameter('2', $genericGroupId)
                ->setMaxResults(1)    
                ;       
            $rate = $queryBuilder->getQuery()->getOneOrNullResult();
        }    
        
        if (!$rate && $producerId){
            $queryBuilder->resetDQLParts();
            $queryBuilder->select('r')
                ->from(Rate::class, 'r')
                ->where('r.status = ?1')
                ->andWhere('r.producer = ?2')    
                ->setParameter('1', Rate::STATUS_ACTIVE)
                ->setParameter('2', $producerId)
                ->setMaxResults(1)    
                ;       
            $rate = $queryBuilder->getQuery()->getOneOrNullResult();
        }    
        
        if (!$rate){
            $rate =  $this->findDefaultRate();
        }
        
        return $rate;        
    }
    
    /**
     * Найти расценку для товара
     * 
     * @param Goods $good
     * @return Rate 
     */
    public function findGoodRate($good)
    {
        $tokenGroupId = $genericGroupId = $producerId= null;
        if ($good->getTokenGroup()){
            $tokenGroupId = $good->getTokenGroup()->getId();
        }    
        
        if ($good->getGenericGroup()){
            $genericGroupId = $good->getGenericGroup()->getId();
        }    
        
        if ($good->getProducer()){
            $producerId = $good->getProducer()->getId();
        }    
        
        return $this->getRate($tokenGroupId, $genericGroupId, $producerId);
    }
    
    /**
     * Получить пороги шкалы
     * 
     * @param Scale $scale
     */
    public function tresholds($scale)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('t')
            ->from(ScaleTreshold::class, 't')
            ->where('t.scale = ?1')
            ->setParameter('1', $scale->getId())    
                ;

        return $queryBuilder->getQuery();
        
    }
    
    public function findFixPrice()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('g.id, g.aplId, g.code, g.name, p.id as producerId, p.name as producerName'
                . ', gg.name as genericGroupName, gg.id as genericGroupId, '
                . 'tg.id as tokenGroupId, tg.name as tokenGroupName,'
                . 'tg.lemms, g.fixPrice, g.meanPrice, g.minPrice, g.price')
            ->from(Goods::class, 'g')
            ->join('g.producer', 'p')    
            ->join('g.tokenGroup', 'tg')    
            ->join('g.genericGroup', 'gg')    
            ->where('g.fixPrice > 0')
                ;

        return $queryBuilder->getQuery();
        
    }
    
}
