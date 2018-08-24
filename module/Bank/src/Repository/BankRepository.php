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
 * Description of BankRepository
 *
 * @author Daddy
 */
class BankRepository extends EntityRepository
{
    public function findStatement($q = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('s')
            ->from(Statement::class, 's')
            ->orderBy('s.chargeDate', 'DESC')
                ;
        
        if ($q){
            $queryBuilder->where('s.counterpartyInn like ?1')
                    ->orWhere('s.counterpartyName like ?1')
                    ->orWhere('s.purpose like ?1')
                    ->setParameter('1', '%' . $q . '%')
                    ;
            if (is_numeric($q)){
                $queryBuilder->orWhere('FLOOR(s.amount) = ?2')
                    ->orWhere('FLOOR(s.amount) = ?3')
                    ->setParameter('2', floor($q))
                    ->setParameter('3', -floor($q))
                    ;                                     
            }
        }
        
        return $queryBuilder->getQuery();
    }        
}