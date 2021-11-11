<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Repository;

use Doctrine\ORM\EntityRepository;
use Application\Entity\MarketPriceSetting;
use Application\Entity\Goods;
use Application\Entity\Images;
use Application\Entity\Rawprice;

/**
 * Description of MarketRepository
 *
 * @author Daddy
 */
class MarketRepository extends EntityRepository{

    /*
     * 
     */
    public function findAllMarket($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('m')
            ->from(MarketPriceSetting::class, 'm')
                ;

        if (is_array($params)){
            if (is_numeric($params['status'])){
                $queryBuilder->andWhere('m.status = ?1')
                        ->setParameter('1', $params['status'])
                    ;
            }    
            if (!empty($params['region'])){
                $queryBuilder->andWhere('m.region = ?2')
                        ->setParameter('2', $params['region'])
                    ;
            }    
        }

        return $queryBuilder->getQuery();
    }        
    
    /**
     * Параметры расценок
     * @param MarketPriceSetting $market
     * @param QueryBuilder $queryBuilder
     * @param string $alias 
     * @return QueryBuilder
     */
    
    public function rateParams($market, $queryBuilder, $alias)
    {
        $rates = $market->getRates();
        
        if (count($rates)){
            $or = $queryBuilder->expr()->orX();            
            foreach ($rates as $rate){
                if ($rate->getTokenGroup()){
                    $or->add($queryBuilder->expr()->eq($alias.'.tokenGroup', $rate->getTokenGroup()));
                }
                if ($rate->getGenericGroup()){
                    $or->add($queryBuilder->expr()->eq($alias.'.genericGroup', $rate->getGenericGroup()));
                }
                if ($rate->getProducer()){
                    $or->add($queryBuilder->expr()->eq($alias.'.producer', $rate->getProducer()));
                }
            }
            $queryBuilder->andWhere($or);
        }            
    }
    
    /**
     * Запрос товаров по параметрам
     * @param MarketPriceSetting $market
     */
    public function marketQuery($market)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        if ($market->getSupplier()){
            $queryBuilder->select('g, r')
                ->distinct()    
                ->from(Rawprice::class, 'r')
                ->join('r.code', 'a')    
                ->join('a.good', 'g')
                ->join('r.raw', 'raw')
                ->where('r.status', Rawprice::STATUS_PARSED)  
                ->andWhere('r.coomment == ""')
                ->andWhere('raw.supplier', $market->getSupplier())
                ->andWhere('g.price > 0')    
                    ;            
        } else {
            $queryBuilder->select('g')
                ->from(Goods::class, 'g')
                ->andWhere('g.price > 0')    
                    ;
        }    
        
        $queryBuilder->andWhere('g.available = ?1')
            ->andWhere('g.statusPriceEx = ?2')    
            ->setParameter('1', Goods::AVAILABLE_TRUE)    
            ->setParameter('2', Goods::PRICE_EX_TRANSFERRED)    
                ;
        
        $this->rateParams($market, $queryBuilder, 'g');
        
        if ($market->getNameSetting() == MarketPriceSetting::NAME_GENERATED){
                    $queryBuilder->andWhere('g.name != g.description')
                    ;
        }
        
        if ($market->getTdSetting() == MarketPriceSetting::TD_MATH){
                    $queryBuilder->andWhere('g.tdDirect = ?3')
                            ->setParameter('3', Goods::TD_DIRECT)
                    ;
        }
        if ($market->getProducerSetting() == MarketPriceSetting::PRODUCER_ACTIVE){
                    $queryBuilder
                            ->join('g.producer', 'p')
                            ->andWhere('p.movement > ?4')
                            ->setParameter('4', $market->getMovementLimit())
                    ;
        }
        
        if ($market->getGroupSetting() == MarketPriceSetting::GROUP_ACTIVE){
                    $queryBuilder
                            ->join('g.genericGroup', 'gg')
                            ->andWhere('gg.movement > ?5')
                            ->setParameter('5', $market->getMovementLimit())
                    ;
        }
        
        if ($market->getTokenGroupSetting() == MarketPriceSetting::TOKEN_GROUP_ACTIVE){
                    $queryBuilder
                            ->join('g.tokenGroup', 'tg')
                            ->andWhere('tg.movement > ?6')
                            ->setParameter('6', $market->getMovementLimit())
                    ;
        }
        
        
        if ($market->getMinPrice()){
            $queryBuilder->andWhere('g.price > ?7')
                        ->setParameter('7', $market->getMinPrice())
                    ;
        }

        if ($market->getMaxPrice()){
            $queryBuilder->andWhere('g.price < ?8')
                        ->setParameter('8', $market->getMaxPrice())
                    ;
        }
        
        $query = $queryBuilder->getQuery();
        
        return $query;
    }
}
