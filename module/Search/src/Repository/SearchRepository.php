<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Search\Repository;

use Doctrine\ORM\EntityRepository;
use Application\Filter\Lemma;
use Application\Filter\Tokenizer;
use Application\Entity\GoodToken;
use Application\Entity\Token;
use Application\Entity\Goods;
use Application\Entity\ArticleToken;

/**
 * Description of SearchRepository
 *
 * @author Daddy
 */
class SearchRepository extends EntityRepository
{
    
    /**
     * Токены из строки поиска
     * @param string $searchStr
     */
    private function lemmsFromSearchStr($searchStr)
    {
        $entityManager = $this->getEntityManager();

        $lemmaFilter = new Lemma($entityManager);
        $tokenFilter = new Tokenizer();
        
        $lemms = $lemmaFilter->filter($tokenFilter->filter($searchStr));
//        var_dump($lemms);
        $result = [];
        $i = 0;
        if (count($lemms)){                                                
            foreach ($lemms as $k => $words){
                foreach ($words as $key => $word){
                    if ($word){
                        $result[$i] = $word;
                        $token = $entityManager->getRepository(Token::class)
                                ->findOneBy(['lemma' => $word]);
                        if ($token){
                            $result[$i] = $token->getCorrect();
                        }
                        $i++;
                    }    
                }
            }    
        }    
        
        return $result;
    }

    /**
     * Найди товары по строке поиска
     * 
     * @param string $searchStr
     * @param array $params
     * 
     * @return Goods|null
     */
    public function queryGoodsBySearchStr($searchStr, $params = null)
    {
//        var_dump($phrase); exit;
        $entityManager = $this->getEntityManager();

        $lemms = $this->lemmsFromSearchStr($searchStr);
        
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('identity(gt.good) as goodId, g.id, g.code, p.name as producerName, g.name, g.price, count(gt.id) as gtCount')
            //->addSelect('min(replace(i.path, \'./public/\', \'http://adminapl.ru/\')) as image')    
            ->from(GoodToken::class, 'gt')
            ->join('gt.good', 'g')    
            ->join('g.producer', 'p')
            //->leftJoin('g.images', 'i')    
            ->groupBy('goodId')
            ->orderBy('gtCount', 'DESC')
            ->where('gt.id = 0')   
            ->having('gtCount > :lemmsCount')
            ->setParameter('lemmsCount', count($lemms)*2/3)    
            ;
        
        $orX = $queryBuilder->expr()->orX();
        if (count($lemms)){
            $orX->add($queryBuilder->expr()->in('gt.lemma', $lemms));
        }
                
        if ($orX->count()){
            $andX = $queryBuilder->expr()->andX();
    
            $andX->add($orX);
            
            $andX->add($queryBuilder->expr()->eq('g.available', Goods::AVAILABLE_TRUE));        

            $queryBuilder->where($andX);
        }
        
        
        if (is_array($params)){
            if (!empty($params['sort'])){
                $queryBuilder->addOrderBy('g.'.$params['sort'], $params['order']);                
            }            
            if (!empty($params['total'])){
                $queryBuilder->resetDQLPart('join')                        
                        ->join('gt.good', 'g')    
                        ->resetDQLPart('orderBy')                        
                        ->resetDQLPart('select')
                        ->addSelect('identity(gt.good) as goodId, count(gt.id) as gtCount')
                        ;
//                var_dump($queryBuilder->getQuery()->getSQL()); exit;
            }            
        }
        
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();
    }     
    
    
    /**
     * Найди товары по строке поиска
     * 
     * @param string $searchStr
     * @param array $params
     * 
     * @return Goods|null
     */
    public function queryArticlesBySearchStr($searchStr, $params = null)
    {
//        var_dump($phrase); exit;
        $entityManager = $this->getEntityManager();

        $lemms = $this->lemmsFromSearchStr($searchStr);
        
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('identity(a.good) as goodId, a.code, count(at.id) as atCount')
            //->addSelect('min(replace(i.path, \'./public/\', \'http://adminapl.ru/\')) as image')    
            ->from(ArticleToken::class, 'at')
            ->join('at.article', 'a')    
//            ->join('a.unknownProducer', 'up')
            //->leftJoin('g.images', 'i')    
            ->groupBy('goodId')
            ->orderBy('atCount', 'DESC')
            ->where('at.id = 0')   
            ->having('atCount > :lemmsCount')
            ->setParameter('lemmsCount', count($lemms)*2/3)    
            ;
        
        $orX = $queryBuilder->expr()->orX();
        if (count($lemms)){
            $orX->add($queryBuilder->expr()->in('at.lemma', $lemms));
        }
                
        if ($orX->count()){
            $andX = $queryBuilder->expr()->andX();
    
            $andX->add($orX);
            
//            $andX->add($queryBuilder->expr()->eq('g.available', Goods::AVAILABLE_TRUE));        

            $queryBuilder->where($andX);
        }
        
        
        if (is_array($params)){
            if (!empty($params['sort'])){
//                $queryBuilder->addOrderBy('g.'.$params['sort'], $params['order']);                
            }            
            if (!empty($params['total'])){
//                $queryBuilder->resetDQLPart('join')                        
//                        ->join('gt.good', 'g')    
//                        ->resetDQLPart('orderBy')                        
//                        ->resetDQLPart('select')
//                        ->addSelect('identity(gt.good) as goodId, count(gt.id) as gtCount')
//                        ;
//                var_dump($queryBuilder->getQuery()->getSQL()); exit;
            }            
        }
        
        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();
    }        
}