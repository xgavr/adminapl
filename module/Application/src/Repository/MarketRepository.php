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
use Application\Filter\Lemma;
use Application\Filter\Tokenizer;
use Application\Entity\TokenGroup;

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
        $rateSetting = $market->getRateSetting();
        
        if (count($rates)){
            if ($rateSetting == MarketPriceSetting::RATE_INCLUDE){
                $or = $queryBuilder->expr()->orX();            
                foreach ($rates as $rate){
                    if ($rate->getTokenGroup()){
                        $or->add($queryBuilder->expr()->eq($alias.'.tokenGroup', $rate->getTokenGroup()->getId()));
                    }
                    if ($rate->getGenericGroup()){
                        $or->add($queryBuilder->expr()->eq($alias.'.genericGroup', $rate->getGenericGroup()->getId()));
                    }
                    if ($rate->getProducer()){
                        $or->add($queryBuilder->expr()->eq($alias.'.producer', $rate->getProducer()->getId()));
                    }
                }
                $queryBuilder->andWhere($or);
            }    
            if ($rateSetting == MarketPriceSetting::RATE_EXCLUDE){
                $and = $queryBuilder->expr()->andX();            
                foreach ($rates as $rate){
                    if ($rate->getTokenGroup()){
                        $and->add($queryBuilder->expr()->neq($alias.'.tokenGroup', $rate->getTokenGroup()->getId()));
                    }
                    if ($rate->getGenericGroup()){
                        $and->add($queryBuilder->expr()->neq($alias.'.genericGroup', $rate->getGenericGroup()->getId()));
                    }
                    if ($rate->getProducer()){
                        $and->add($queryBuilder->expr()->neq($alias.'.producer', $rate->getProducer()->getId()));
                    }
                }
                $queryBuilder->andWhere($and);
            }    
        }            
    }
    
    /**
     * Найди товары токенов
     * 
     * @param MarketPriceSetting $market
     * 
     * @return Goods|null
     */
    public function findTokenGroupByTokens($market)
    {
        $result = [];
        $tokenFilterStr = $market->getTokenFilter();
        if (!empty($tokenFilterStr)){
            
            $phrases = array_filter(explode(PHP_EOL, $tokenFilterStr));
            
            if (count($phrases)){
                $entityManager = $this->getEntityManager();
                $lemmaFilter = new Lemma($entityManager);
                $tokenFilter = new Tokenizer();

                $queryBuilder = $entityManager->createQueryBuilder();
                $queryBuilder->select('tg.id, tg.name')
                    ->distinct()    
                    ->from(TokenGroup::class, 'tg')    
//                    ->join('tg.tokens', 't') 
                    ;
                $orX = $queryBuilder->expr()->orX();
                
                foreach ($phrases as $phrase){
                    $andX = $queryBuilder->expr()->andX();
                    $lemms = $lemmaFilter->filter($tokenFilter->filter($phrase));
                    if (count($lemms)){                                                
                        foreach ($lemms as $k => $words){
                            foreach ($words as $key => $word){
                                if ($word){
                                    $andX->add($queryBuilder->expr()->like('tg.lemms', '\'%'.$word.'%\''));
                                }    
                            }
                        }    
                    }    
                    if ($andX->count()){
                        $orX->add($andX);
                    }    
                }
                if ($orX->count()){
                    $queryBuilder->andWhere($orX);
                            var_dump($queryBuilder->getQuery()->getSQL()); exit;
                    $data = $queryBuilder->getQuery()->getResult();
                    foreach ($data as $row){
                        $result[] = $row['id'];
                    }                                
                }
            }    
        }
        
        return $result;
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
            $queryBuilder->select('g, p, gg')
                //->distinct()    
                ->from(Rawprice::class, 'r')
                ->join('r.code', 'a')    
                ->join(Goods::class, 'g', 'WITH', 'g.id = a.good')
                ->join('r.raw', 'raw')
                ->join('g.genericGroup', 'gg')
                ->where('r.status = ?1')  
                ->setParameter('1', Rawprice::STATUS_PARSED)    
                ->andWhere('r.comment = \'\'')
                ->andWhere('raw.supplier = ?2')
                ->setParameter('2', $market->getSupplier())    
                ->andWhere('g.price > 0')
                    ;            
        } else {
            $queryBuilder->select('g, p, gg')
                ->from(Goods::class, 'g')
                ->andWhere('g.price > 0')    
                ->join('g.genericGroup', 'gg')
                    ;
        }    
        
        $queryBuilder->andWhere('g.available = ?3')
            ->andWhere('g.statusPriceEx = ?4')    
            ->setParameter('3', Goods::AVAILABLE_TRUE)    
            ->setParameter('4', Goods::PRICE_EX_TRANSFERRED)    
            ->setMaxResults($market::MAX_BLOCK_ROW_COUNT*2)    
            ->join('g.producer', 'p')    
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
                            ->andWhere('p.movement > ?6')
                            ->setParameter('6', $market->getMovementLimit())
                    ;
        }
        
        if ($market->getGroupSetting() == MarketPriceSetting::GROUP_ACTIVE){
                    $queryBuilder
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
        if ($market->getTokenFilter()){
            $tg = $this->findTokenGroupByTokens($market);
            if (count($tg)){
                $inX = $queryBuilder->expr()->in('g.tokenGroup', $tg);
                $queryBuilder
                        ->andWhere($inX);                
            }        
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
        
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        $query = $queryBuilder->getQuery();
        
        return $query;
    }
    
    /**
     * Найти настройку для запуска выгрузки
     * @return MarketPriceSetting
     */
    public function findNext()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('m')
                ->from(MarketPriceSetting::class, 'm')
                ->where('m.dateUnload < ?1')
                ->andWhere('m.status = ?2')
                ->setParameter('1', date('Y-m-d'))
                ->setParameter('2', MarketPriceSetting::STATUS_ACTIVE)
                ->setMaxResults(1)
                ;
        return $queryBuilder->getQuery()->getResult();
    }
}
