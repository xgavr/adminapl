<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Repository;

use Doctrine\ORM\EntityRepository;
use Application\Entity\Order;
/**
 * Description of OrderRepository
 *
 * @author Daddy
 */
class OrderRepository extends EntityRepository{

    public function findAllOrder()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('c')
            ->from(Order::class, 'c')
            ->orderBy('c.id')
                ;

        return $queryBuilder->getQuery();
    }        
}
