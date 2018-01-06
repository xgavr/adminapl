<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Repository;

use Doctrine\ORM\EntityRepository;
use Application\Entity\Cart;

/**
 * Description of CartRepository
 *
 * @author Daddy
 */
class CartRepository extends EntityRepository{

    /*
     * 
     */
    public function findAllCart()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('c')
            ->from(Cart::class, 'c')
            ->orderBy('c.good_id')
                ;

        return $queryBuilder->getQuery();
    }        

    /*
     * @var Apllication\Entity\Client
     */
    public function findClientCart($client)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('c')
            ->from(Cart::class, 'c')
            ->where('c.client = ?1')    
            ->orderBy('c.id')
            ->setParameter('1', $client->getId())    
                ;

        return $queryBuilder->getQuery();
    }   
    
    public function getClientNum($client)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('r')
            ->from(Cart::class, 'r')
            ->select('SUM(r.num) as num, SUM(r.num*r.price) as total')
            ->where('r.client = ?1')    
            ->groupBy('r.client')
            ->setParameter('1', $client->getId())
                ;
        return $queryBuilder->getQuery()->getResult();
        
    }
    
}
