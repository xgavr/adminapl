<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Repository;
use Doctrine\ORM\EntityRepository;
use Application\Entity\Token;
use Application\Entity\Rawprice;
use Application\Entity\TokenGroup;
use Application\Entity\Goods;


/**
 * Description of TokenRepository
 *
 * @author Daddy
 */
class TokenRepository  extends EntityRepository
{
    
    /**
     * Запрос по токенам по разным параметрам
     * 
     * @param array $params
     * @return object
     */
    public function findAllToken($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('t')
            ->from(Token::class, 't')
            ->addOrderBy('t.lemma')                
                ;
        
        if (is_array($params)){
            if (isset($params['q'])){
                $queryBuilder->where('t.lemma like :search')
                    ->setParameter('search', '%' . $params['q'] . '%')
                        ;
            }
            if (isset($params['next1'])){
                $queryBuilder->where('t.lemma > ?1')
                    ->setParameter('1', $params['next1'])
                    ->setMaxResults(1)    
                 ;
            }
            if (isset($params['prev1'])){
                $queryBuilder->where('t.lemma < ?2')
                    ->setParameter('2', $params['prev1'])
                    ->orderBy('t.lemma', 'DESC')
                    ->setMaxResults(1)    
                 ;
            }
        }

        return $queryBuilder->getQuery();
    }            
    
    /**
     * Запрос по группам наименований по разным параметрам
     * 
     * @param array $params
     * @return object
     */
    public function findAllTokenGroup($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('t')
            ->from(TokenGroup::class, 't')
            ->addOrderBy('t.name')                
                ;
        
        if (is_array($params)){
            if (isset($params['q'])){
                $queryBuilder->where('t.lemms like :search')
                    ->setParameter('search', '%' . $params['q'] . '%')
                        ;
            }
            if (isset($params['next1'])){
                $queryBuilder->where('t.ids > ?1')
                    ->setParameter('1', $params['next1'])
                    ->setMaxResults(1)    
                 ;
            }
            if (isset($params['prev1'])){
                $queryBuilder->where('t.ids < ?2')
                    ->setParameter('2', $params['prev1'])
                    ->orderBy('t.ids', 'DESC')
                    ->setMaxResults(1)    
                 ;
            }
            if (isset($params['sort'])){
                $queryBuilder->orderBy('t.'.$params['sort'], $params['order']);                
            }            
        }

        return $queryBuilder->getQuery();
    }            
    
    /**
     * Найти строки прайсов токена
     * 
     * @param Application\Entity\Token $token
     * @return object
     */
    public function findTokenRawprice($token)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('r')
            ->from(Rawprice::class, 'r')
            ->join('r.tokens', 't')
            ->where('t.id = ?1')    
            ->andWhere('r.status = ?2')    
            ->setParameter('1', $token->getId())
            ->setParameter('2', Rawprice::STATUS_PARSED)
            ;
        
        return $queryBuilder->getQuery();            
    }

    /**
     * Найти токены товара по типу
     * 
     * @param Application\Entity\Goods $good
     * @param integer $tokenType Description
     * @return object
     */
    public function findTokenGoodsByStatus($good, $tokenType = Token::IS_DICT)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('rt.id, rt.lemma, rt.status')
            ->distinct()    
            ->from(Rawprice::class, 'r')
            ->join('r.tokens', 'rt', 'WITH')
            //->join(Token::class, 't', 'WITH')    
            ->where('r.good = ?1')   
            ->andWhere('rt.status = ?2')    
            ->setParameter('1', $good->getId())
            ->setParameter('2', $tokenType)
            ;
//            var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult();            
    }

    /**
     * Найти товары группы наименований
     * 
     * @param Application\Entity\TokenGroup $tokenGroup
     * @return object
     */
    public function findTokenGroupGoods($tokenGroup)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('g')
            ->from(Goods::class, 'g')
            ->where('g.tokenGroup = ?1')    
            ->setParameter('1', $tokenGroup->getId())
            ;
        
        return $queryBuilder->getQuery();            
    }

    /**
     * Найти токены для удаления
     * 
     * @return object
     */
    public function findTokenForDelete()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('o')
            ->addSelect('count(r.id) as rawpriceCount')    
            ->from(Token::class, 'o')
            ->leftJoin('o.rawprice', 'r')
            ->groupBy('o.id')
            ->having('rawpriceCount = 0')    
            //->setParameter('1', Rawprice::STATUS_PARSED)
                ;
        //var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult();            
    }

    /**
     * Найти близкий токен из словаря
     * 
     * @param Application\Entity\Token $token
     * @param integer $dict
     */
    public function findNearToken($token, $dict = Token::IS_DICT)
    {
        if (mb_strlen($token) < 3){
            return [];
        }
        
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('t')
                ->from(Token::class, 't')
                ->where('t.status = ?1')
                ->andWhere('t.lemma like ?2')
                ->orderBy('t.lemma')
                ->setParameter('1', $dict)
                ->setParameter('2', $token.'%')
                ->setMaxResults(1)
                ;
        //var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery()->getResult(2);            
    }
    
    
}
