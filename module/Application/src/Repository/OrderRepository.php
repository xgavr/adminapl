<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Repository;

use Doctrine\ORM\EntityRepository;
use Application\Entity\Order;
use Application\Entity\Bid;
/**
 * Description of OrderRepository
 *
 * @author Daddy
 */
class OrderRepository extends EntityRepository{

    public function findAllOrder($user=null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('o')
            ->from(Order::class, 'o')
            ->orderBy('o.id')
                ;
        
        if ($user){
            $queryBuilder->join("o.client", 'c', 'WITH')
                    ->where('c.manager = ?1')
                    ->setParameter('1', $user)
                    ;
        }
        
        return $queryBuilder->getQuery();
    }       
    
    /*
     * @var Apllication\Entity\Client
     */
    public function findClientOrder($client)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('c')
            ->from(Order::class, 'c')
            ->where('c.client = ?1')    
            ->orderBy('c.id')
            ->setParameter('1', $client->getId())    
                ;

        return $queryBuilder->getQuery();
    }       
    

    /*
     * @var Apllication\Entity\Order
     */
    public function getOrderNum($order)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('r')
            ->from(Bid::class, 'r')
            ->select('SUM(r.num) as num, SUM(r.num*r.price) as total')
            ->where('r.order = ?1')    
            ->groupBy('r.order')
            ->setParameter('1', $order->getId())
                ;
        return $queryBuilder->getQuery()->getResult();
        
    }
    
    /*
     * @var Apllication\Entity\Order
     */
    public function findBidOrder($order)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('c')
            ->from(Bid::class, 'c')
            ->where('c.order = ?1')    
            ->orderBy('c.id')
            ->setParameter('1', $order->getId())    
                ;

        return $queryBuilder->getQuery();
    }        
    
    
}
