<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Company\Repository;

use Doctrine\ORM\EntityRepository;
use Company\Entity\Legal;
/**
 * Description of SupplierRepository
 *
 * @author Daddy
 */
class LegalRepository extends EntityRepository
{

    public function findOneByInnKpp($inn, $kpp = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('r')
            ->from(Legal::class, 'r')
            ->where('r.inn = ?1')    
            ->setParameter('1', $inn)
                ;
        if ($kpp){
        $queryBuilder
            ->andWhere('r.kpp = ?2')    
            ->setParameter('2', $kpp)
                ;
            
        }        
                ;
        return $queryBuilder->getQuery()->getResult();
        
    }
}