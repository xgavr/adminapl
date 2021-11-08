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
     * Запрос товаров по параметрам
     * @param MarketPriceSetting $market
     */
    public function marketQuery($market)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('g')
            ->from(Goods::class, 'g')
            ->where('g.available = ?1')
            ->setParameter('1', Goods::AVAILABLE_TRUE)    
                ;
        
        if ($market->getNameSetting() == MarketPriceSetting::NAME_GENERATED){
                    $queryBuilder->andWhere('i.name != i.description')
                    ;
        }
        
        if ($market->getProducerSetting() == MarketPriceSetting::PRODUCER_ACTIVE){
                    $queryBuilder
                            ->join('i.producer', 'p')
                            ->andWhere('p.movement > ?2')
                            ->setParameter('2', MarketPriceSetting::MOVEMENT_LIMIT)
                    ;
        }
        
        
        $query = $queryBuilder->getQuery();
        
        return $query;
    }
}
