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

/**
 * Description of SearchRepository
 *
 * @author Daddy
 */
class SearchRepository extends EntityRepository
{

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
        $lemmaFilter = new Lemma($entityManager);
        $tokenFilter = new Tokenizer();
        
        $lemms = $lemmaFilter->filter($tokenFilter->filter($searchStr));
//        var_dump($lemms);
        $lemmsIn = [];
        if (count($lemms)){                                                
            foreach ($lemms as $k => $words){
                foreach ($words as $key => $word){
                    if ($word){
                        $lemmsIn[] = $word;
                        $token = $entityManager->getRepository(Token::class)
                                ->findOneBy(['lemma' => $word]);
                        if ($token){
                            $lemmsIn[] = $token->getCorrect();
                        }
                    }    
                }
            }    
        }    

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('g.id, g.code, p.name as producerName, g.name, g.price, count(gt.id) as gtCount')
            ->from(GoodToken::class, 'gt')
            ->join('gt.good', 'g')    
            ->join('g.producer', 'p')
            ->groupBy('g.id')
            ->orderBy('gtCount', 'DESC')
            ->where('gt.id = 0')   
            ->having('gtCount > :lemmsCount')
            ->setParameter('lemmsCount', count($lemms)*2/3)    
            ;
        
        $orX = $queryBuilder->expr()->orX();
        if (count($lemmsIn)){
            $orX->add($queryBuilder->expr()->in('gt.lemma', $lemmsIn));
        }
                
        if ($orX->count()){
            $queryBuilder->where($orX);
        }
        
        if (is_array($params)){
            if (!empty($params['sort'])){
                $queryBuilder->orderBy('g.'.$params['sort'], $params['order']);                
            }            
        }
        
        //var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();
    }        
}