<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Repository;
use Doctrine\ORM\EntityRepository;
use Application\Entity\Comment;
use Application\Entity\Order;

/**
 * Description of CommentRepository
 *
 * @author Daddy
 */
class CommentRepository  extends EntityRepository{

    /**
     * Запрос на все комментарии
     * @param array $params
     * @return Query
     * 
     */
    public function queryAllComments($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('c, client, o, u')
            ->from(Comment::class, 'c') 
            ->leftJoin('c.user', 'u')    
            ->leftJoin('c.client', 'client')
            ->leftJoin('c.order', 'o')    
            ->addOrderBy('c.id', 'DESC')    
                ;
        
        if (is_array($params)){
            if (!empty($params['clientId'])){
                if (is_numeric($params['clientId'])){
                    $queryBuilder->andWhere('c.client = :client')
                        ->setParameter('client', $params['clientId'])
                            ;
                }    
            }            
        }
        
        return $queryBuilder->getQuery();
    }       
    
    /**
     * Комментарии заказа запрос
     * @param Order $order
     * @return Query
     * 
     */
    public function orderComments($order)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('c')
            ->from(Comment::class, 'c')
            ->where('c.order = :orderId')
            ->setParameter('orderId', $order->getId())    
            ->addOrderBy('c.id', 'ASC')    
                ;
        
        return $queryBuilder->getQuery();
    }       
    
    /**
     * Получить последний комментарий
     * 
     * @param Order $order
     */
    public function lastComment($order)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('c')
            ->from(Comment::class, 'c')
            ->where('c.order = :orderId')
            ->setParameter('orderId', $order->getId())    
            ->setMaxResults(1)    
            ->addOrderBy('c.id', 'DESC')    
                ;
        
        return $queryBuilder->getQuery()->getOneOrNullResult();        
    }
}
