<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Repository;

use Doctrine\ORM\EntityRepository;
use Application\Entity\Ring;

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
}
