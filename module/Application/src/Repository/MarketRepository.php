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
        
        if ($market->getGoodSetting() == MarketPriceSetting::IMAGE_MATH){
            $queryBuilder->join('g.images', 'i')
                    ->andWhere('i.similar = ?2')
                    ->setParameter('2', Images::SIMILAR_MATCH)
                    ;
        }
        
        if ($market->getGoodSetting() == MarketPriceSetting::IMAGE_SIMILAR){
            $queryBuilder->join('g.images', 'i')
                    ->andWhere('i.similar = ?2')
                    ->setParameter('2', Images::SIMILAR_MATCH)
                    ;
        }
        
        
        $query = $queryBuilder->getQuery();
        
        return $query;
    }
}
