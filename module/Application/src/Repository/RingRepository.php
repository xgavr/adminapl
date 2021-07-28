<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Repository;

use Doctrine\ORM\EntityRepository;
use Application\Entity\Ring;
use Application\Entity\RingHelpGroup;
use Application\Entity\RingHelp;

/**
 * Description of RingRepository
 *
 * @author Daddy
 */
class RingRepository extends EntityRepository{

    /*
     * 
     */
    public function findAllRing()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('r')
            ->from(Ring::class, 'r')
                ;

        return $queryBuilder->getQuery();
    }        
    
    /*
     * 
     */
    public function findAllRingHelpGroup($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('r')
            ->from(RingHelpGroup::class, 'r')
            ->orderBy('r.sort')    
                ;
        
        if (is_array($params)){
            if (is_numeric($params['mode'])){
                $queryBuilder->andWhere('r.mode = ?1')
                        ->setParameter('1', $params['mode'])
                    ;
            }    
        }

        return $queryBuilder->getQuery();
    }        
    
    /*
     * 
     */
    public function findAllRingHelp($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('r')
            ->from(RingHelp::class, 'r')
            ->orderBy('r.sort')    
                ;
        
        if (is_array($params)){
            if (is_numeric($params['mode'])){
                $queryBuilder->andWhere('r.mode = ?1')
                        ->setParameter('1', $params['mode'])
                    ;
            }    
            if (is_numeric($params['helpGroup'])){
                $queryBuilder->andWhere('r.ringHelpGroup = ?2')
                        ->setParameter('2', $params['helpGroup'])
                    ;
            }    
        }

        return $queryBuilder->getQuery();
    }        
    
}
