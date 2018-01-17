<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Repository;

use Doctrine\ORM\EntityRepository;
use Application\Entity\Client;
/**
 * Description of ClientRepository
 *
 * @author Daddy
 */
class ClientRepository extends EntityRepository{

    public function findAllClient($user = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('c')
            ->from(Client::class, 'c')
            ->orderBy('c.id')
                ;
        
        if ($user){
            $queryBuilder->where('c.manager = ?1')
                    ->setParameter('1', $user)
                    ;
        }

        return $queryBuilder->getQuery();
    }        
    
}
