<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace User\Repository;

use Doctrine\ORM\EntityRepository;
use User\Entity\User;
use User\Entity\Role;

class UserRepository  extends EntityRepository
{
    public function findUsersByRole($roleId)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('u')
            ->from(User::class, 'u')
            ->join("u.roles", 'r', 'WITH')    
            ->where('r.id = ?1')    
            ->setParameter('1', $roleId)
                ;
        
        return $queryBuilder->getQuery()->getResult();
    }    
    
    
}