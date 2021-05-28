<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Company\Repository;

use Doctrine\ORM\EntityRepository;
use Company\Entity\Cost;
/**
 * Description of CostRepository
 *
 * @author Daddy
 */
class CostRepository extends EntityRepository
{

    /**
     * Выборка для формы
     * 
     * @param array params
     */
    public function formFind($params)
    {
        $cost = null;
        if (!empty($params['cost'])){
            $cost = $params['cost'];
        }

        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('c')
            ->from(Cost::class, 'c')
                ;
        
        if ($cost){
//            $queryBuilder->where('c.id = ?1')
//                    ->setParameter(1, $cost->getId())
//                    ;
        } else {
            $queryBuilder
                    ->andWhere('c.status = ?2')
                    ->setParameter('2', Cost::STATUS_ACTIVE)
                    ;
            
        }

        return $queryBuilder->getQuery()->getResult();       
    }
    

}