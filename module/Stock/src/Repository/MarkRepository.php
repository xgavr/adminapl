<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Stock\Repository;

use Doctrine\ORM\EntityRepository;
use Stock\Entity\Mark;
use Application\Filter\ArticleCode;

/**
 * Description of MarkRepository
 *
 * @author Daddy
 */
class MarkRepository extends EntityRepository{
    
    /**
     * Запрос по Mark
     * 
     * @param array $params
     * @return query
     */
    public function queryAllMark($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('m, o, g')
            ->from(Mark::class, 'm')
            ->join('m.order', 'o')    
            ->join('m.good', 'g')    
                ;
        
        if (is_array($params)){
            if (!empty($params['markId'])){
                if ($params['markId'] > 0){
                    $queryBuilder->andWhere('m.id = :markId')
                        ->setParameter('markId', $params['markId'])
                     ;
                }    
            }            
            if (!empty($params['markStatus'])){
                if ($params['markStatus'] > 0){
                    $queryBuilder->andWhere('m.markStatus = :markStatus')
                        ->setParameter('markStatus', $params['markStatus'])
                     ;
                }    
            }
            
            if (!empty($params['search'])){                
                $codeFilter = new ArticleCode();
                $q = $codeFilter->filter($params['search']);

                if ($q){
                    $orX = $queryBuilder->expr()->orX();
                    
                    $orX->add($queryBuilder->expr()->eq('g.code', ':query'));
                    if (is_numeric($q)){
                        $orX->add($queryBuilder->expr()->eq('o.aplId', ':query'));
                    }
                    $queryBuilder->setParameter('query', $q);
                            
                    if ($orX->count()){
                        $queryBuilder->andWhere($orX);
                    }
                }   
            }
            
            if (isset($params['sort'])){
                $queryBuilder->orderBy('m.'.$params['sort'], $params['order']);
            }                 
        }
//        var_dump($queryBuilder->getQuery()->getSQL()); exit;
        return $queryBuilder->getQuery();
    }  
    
    /**
     * Запрос по Mark
     * 
     * @param array $params
     * @return query
     */
    public function queryAllMarkTotal($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('count(m.id) as countMark')
            ->from(Mark::class, 'm')
            ->join('m.order', 'o')    
            ->join('m.good', 'g')    
                ;
        
        if (is_array($params)){
            if (!empty($params['markId'])){
                if ($params['markId'] > 0){
                    $queryBuilder->andWhere('m.id = :markId')
                        ->setParameter('markId', $params['markId'])
                     ;
                }    
            }                        
            if (!empty($params['markStatus'])){
                if ($params['markStatus'] > 0){
                    $queryBuilder->andWhere('m.markStatus = :markStatus')
                        ->setParameter('markStatus', $params['markStatus'])
                     ;
                }    
            }      
            
            if (!empty($params['search'])){                
                $codeFilter = new ArticleCode();
                $q = $codeFilter->filter($params['search']);

                if ($q){
                    $orX = $queryBuilder->expr()->orX();
                    
                    $orX->add($queryBuilder->expr()->eq('g.code', ':query'));
                    if (is_numeric($q)){
                        $orX->add($queryBuilder->expr()->eq('o.aplId', ':query'));
                    }
                    $queryBuilder->setParameter('query', $q);
                            
                    if ($orX->count()){
                        $queryBuilder->andWhere($orX);
                    }
                }   
            }
        }
        
        $result = $queryBuilder->getQuery()->getOneOrNullResult();

        return $result['countMark'];
    }    
}