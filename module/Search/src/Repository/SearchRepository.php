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
     * 
     * @return Goods|null
     */
    public function findGoodsBySearchStr($searchStr)
    {
//        var_dump($phrase); exit;
        $entityManager = $this->getEntityManager();
        $lemmaFilter = new Lemma($entityManager);
        $tokenFilter = new Tokenizer();
        $result = [0];

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('g')
            ->distinct()    
            ->from(Goods::class, 'g')
            ->where('tg.movement > 0')
            ->orderBy('tg.movement', 'DESC')
//            ->setMaxResults(5)    
            ;
        $orX = $queryBuilder->expr()->orX();
                
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
        
        if ($orX->count()){
            $queryBuilder->andWhere($orX);
//                            var_dump($queryBuilder->getQuery()->getSQL()); exit;
            $data = $queryBuilder->getQuery()->getResult();
            foreach ($data as $row){
                $result[] = $row['id'];
            }                                
        }
        
        return $result;
    }        
}