<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Repository;
use Doctrine\ORM\EntityRepository;
use Application\Entity\Comment;
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
        
        return $queryBuilder->getQuery();
    }       
    
}
