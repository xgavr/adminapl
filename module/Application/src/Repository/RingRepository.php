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

        $queryBuilder->select('r, hg')
            ->from(RingHelp::class, 'r')
            ->join('r.ringHelpGroup', 'hg')    
            ->orderBy('hg.sort')    
            ->addOrderBy('hg.id')    
            ->addOrderBy('r.sort')    
            ->addOrderBy('r.id')    
                ;
        
        if (is_array($params)){
            if (is_numeric($params['mode'])){
                $queryBuilder->andWhere('r.mode = ?1')
                        ->setParameter('1', $params['mode'])
                    ;
            }    
            if (!empty($params['helpGroup'])){
                $queryBuilder->andWhere('r.ringHelpGroup = ?2')
                        ->setParameter('2', $params['helpGroup'])
                    ;
            }    
        }

        return $queryBuilder->getQuery();
    }        
    
    /**
     * Подсказки для формы звонка
     * @param integer $mode
     * @param integer $helGroup
     * 
     * @return array
     */
    public function helpRingForm($mode, $helGroup = null)
    {
        $query = $this->findAllRingHelp(['mode' => $mode, 'helpGroup' => $helGroup]);
        return $query->getResult(2);
    }
}
