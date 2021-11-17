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
     * @param integer $offset
     */
    public function marketQuery($market, $offset = 0)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        if ($market->getSupplier()){
            $queryBuilder->select('g')
                ->distinct()    
                ->from(Rawprice::class, 'r')
                ->join('r.code', 'a')    
                ->join(Goods::class, 'g', 'WITH', 'g.id = a.good')
                ->join('r.raw', 'raw')
                ->where('r.status = ?1')  
                ->setParameter('1', Rawprice::STATUS_PARSED)    
                ->andWhere('r.comment = \'\'')
                ->andWhere('raw.supplier = ?2')
                ->setParameter('2', $market->getSupplier())    
                ->andWhere('g.price > 0')    
                    ;            
        } else {
            $queryBuilder->select('g')
                ->from(Goods::class, 'g')
                ->andWhere('g.price > 0')    
                    ;
        }    
        
        $queryBuilder->andWhere('g.available = ?3')
            ->andWhere('g.statusPriceEx = ?4')    
            ->setParameter('3', Goods::AVAILABLE_TRUE)    
            ->setParameter('4', Goods::PRICE_EX_TRANSFERRED)    
                ;
        
        $this->rateParams($market, $queryBuilder, 'g');
        
        if ($market->getNameSetting() == MarketPriceSetting::NAME_GENERATED){
                    $queryBuilder->andWhere('g.name != g.description')
                    ;
        }
        
        if ($market->getTdSetting() == MarketPriceSetting::TD_MATH){
                    $queryBuilder->andWhere('g.tdDirect = ?5')
                            ->setParameter('5', Goods::TD_DIRECT)
                    ;
        }
        if ($market->getProducerSetting() == MarketPriceSetting::PRODUCER_ACTIVE){
                    $queryBuilder
                            ->join('g.producer', 'p')
                            ->andWhere('p.movement > ?6')
                            ->setParameter('6', $market->getMovementLimit())
                    ;
        }
        
        if ($market->getGroupSetting() == MarketPriceSetting::GROUP_ACTIVE){
                    $queryBuilder
                            ->join('g.genericGroup', 'gg')
                            ->andWhere('gg.movement > ?7')
                            ->setParameter('7', $market->getMovementLimit())
                    ;
        }
        
        if ($market->getTokenGroupSetting() == MarketPriceSetting::TOKEN_GROUP_ACTIVE){
                    $queryBuilder
                            ->join('g.tokenGroup', 'tg')
                            ->andWhere('tg.movement > ?8')
                            ->setParameter('8', $market->getMovementLimit())
                    ;
        }
        
        
        if ($market->getMinPrice()){
            $queryBuilder->andWhere('g.price > ?9')
                        ->setParameter('9', $market->getMinPrice())
                    ;
        }

        if ($market->getMaxPrice()){
            $queryBuilder->andWhere('g.price < ?10')
                        ->setParameter('10', $market->getMaxPrice())
                    ;
        }
        
        if ($offset){
            $queryBuilder->setFirstResult($offset);
        }
        
        $query = $queryBuilder->getQuery();
        
        return $query;
    }
}
