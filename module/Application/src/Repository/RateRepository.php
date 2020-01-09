<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Repository;
use Doctrine\ORM\EntityRepository;
use Application\Entity\Scale;
use Application\Entity\ScaleTreshold;
use Application\Entity\Rate;

/**
 * Description of RateRepository
 *
 * @author Daddy
 */
class RateRepository  extends EntityRepository
{

    /**
     * Найти расценку по умолчанию
     * 
     * @return Rate
     */
    public function findDefaultRate()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('r')
            ->from(Rate::class, 'r')
            ->orderBy('r.id', 'ASC')
            ;
        
        $rates = $queryBuilder->getQuery()->getResult();
        foreach ($rates as $rate){
            return $rate;
        }
        
        return;
    }
    
    /**
     * Плучить расценку
     * 
     * @param array $params
     * @return array
     */
    public function findRate($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('r')
            ->from(Rate::class, 'r')
            ->orderBy('r.id', 'ASC')
            ;
        
        if (is_array($params)){
            if (isset($params['supplier'])){
                $queryBuilder->where('r.supplier = ?1')
                        ->setParameter('1', $params['supplier']);
            }
            if (isset($params['producer'])){
                $queryBuilder->where('r.producer = ?1')
                        ->setParameter('1', $params['producer']);
            }
            if (isset($params['genericGroup'])){
                $queryBuilder->where('r.genericGroup = ?1')
                        ->setParameter('1', $params['genericGroup']);
            }
        }

        $rates = $queryBuilder->getQuery()->getResult();
        foreach ($rates as $rate){
            return $rate;
        }
        
        return $this->findDefaultRate();
    }    
    
    /**
     * Получить пороги шкалы
     * 
     * @param Scale $scale
     */
    public function tresholds($scale)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('t')
            ->from(ScaleTreshold::class, 't')
            ->where('t.scale = ?1')
            ->setParameter('1', $scale->getId())    
                ;

        return $queryBuilder->getQuery();
        
    }
    
}
