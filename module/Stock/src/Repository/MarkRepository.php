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
                if ($params['markStatus'] == -22){
                    $queryBuilder->andWhere('m.markStatus != :markStatus')
                        ->setParameter('markStatus', Mark::MARK_RETIRED)
                     ;
                }    
                if ($params['markStatus'] > 0){
                    $queryBuilder->andWhere('m.markStatus = :markStatus')
                        ->setParameter('markStatus', $params['markStatus'])
                     ;
                }    
            }
            if (!empty($params['status'])){
                if ($params['status'] > 0){
                    $queryBuilder->andWhere('m.status = :status')
                        ->setParameter('status', $params['status'])
                     ;
                }    
            }
            
            if (!empty($params['search'])){                
                $codeFilter = new ArticleCode();
                $q = $codeFilter->filter($params['search']);

                if ($q){
                    
                    $queryBuilder->resetDQLPart('where');
                    $queryBuilder->getParameters()->clear();
                    
                    $orX = $queryBuilder->expr()->orX();
                    
                    $orX->add($queryBuilder->expr()->eq('g.code', ':query'));
                    $orX->add($queryBuilder->expr()->like('m.mark', ':mark'));
                    if (is_numeric($q)){
                        $orX->add($queryBuilder->expr()->eq('o.aplId', ':query'));
                    }
                    $queryBuilder->setParameter('query', $q);
                    $queryBuilder->setParameter('mark', "%{$params['search']}%");
                            
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
//        var_dump($queryBuilder->getQuery()->getDQL(), $queryBuilder->getQuery()->getParameters()); exit;
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
                        ->setMaxResults(1)    
                     ;
                }    
            }                        
            if (!empty($params['markStatus'])){
                if ($params['markStatus'] == -22){
                    $queryBuilder->andWhere('m.markStatus != :markStatus')
                        ->setParameter('markStatus', Mark::MARK_RETIRED)
                     ;
                }    
                if ($params['markStatus'] > 0){
                    $queryBuilder->andWhere('m.markStatus = :markStatus')
                        ->setParameter('markStatus', $params['markStatus'])
                     ;
                }    
            }      
            if (!empty($params['status'])){
                if ($params['status'] > 0){
                    $queryBuilder->andWhere('m.status = :status')
                        ->setParameter('status', $params['status'])
                     ;
                }    
            }
            
            if (!empty($params['search'])){                
                $codeFilter = new ArticleCode();
                $q = $codeFilter->filter($params['search']);

                if ($q){
                    
                    $queryBuilder->resetDQLPart('where');
                    $queryBuilder->getParameters()->clear();
                    
                    $orX = $queryBuilder->expr()->orX();
                    
                    $orX->add($queryBuilder->expr()->eq('g.code', ':query'));
                    $orX->add($queryBuilder->expr()->like('m.mark', ':mark'));
                    if (is_numeric($q)){
                        $orX->add($queryBuilder->expr()->eq('o.aplId', ':query'));
                    }
                    $queryBuilder->setParameter('query', $q);
                    $queryBuilder->setParameter('mark', "%{$params['search']}%");
                            
                    if ($orX->count()){
                        $queryBuilder->andWhere($orX);
                    }
                }   
            }
        }
        
        $result = $queryBuilder->getQuery()->getOneOrNullResult();

        return $result['countMark'];
    }    
    
    public function findForCheckMark31()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        
        $orX = $queryBuilder->expr()->orX();
        $orX->add($queryBuilder->expr()->eq(m.markStatus, Mark::MARK_UNKNOWN));
        $orX->add($queryBuilder->expr()->eq(m.markStatus, Mark::MARK_ACTIVE));

        $queryBuilder->select('SUBSTRING(m.mark, 1, 31) as mark31')
            ->from(Mark::class, 'm')
            ->where($orX)  
            ->andWhere('m.status = :status')
            ->setParameter('status', Mark::STATUS_ACTIVE)    
                ;
        
        return array_column($queryBuilder->getQuery()->getResult(), 'mark31');
    }
    
    /**
     * 
     * @param string $mark31
     * @return type
     */
    public function findMarkByMark31($mark31)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('m')
            ->from(Mark::class, 'm')
            ->where('m.mark like :mark31')
            ->setParameter('mark31', "$mark31%") 
            ->setMaxResults(1)
            ->orderBy('m.id', 'ASC')    
                ;
        
        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}