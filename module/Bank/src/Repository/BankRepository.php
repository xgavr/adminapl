<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Bank\Repository;

use Doctrine\ORM\EntityRepository;
use Bank\Entity\Statement;
/**
 * Description of SupplierRepository
 *
 * @author Daddy
 */
class BankRepository extends EntityRepository
{
    public function statement($q = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('s')
            ->from(Statement::class, 's')
           // ->orderBy('s.id')
                ;
        
        return $queryBuilder->getQuery();
    }        
}