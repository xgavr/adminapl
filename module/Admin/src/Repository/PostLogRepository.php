<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Repository;

use Doctrine\ORM\EntityRepository;
use Admin\Entity\PostLog;
/**
 * Description of PostLogRepository
 *
 * @author Daddy
 */
class PostLogRepository extends EntityRepository
{
    /**
     * Запрос все записи
     * 
     * @return object
     */
    public function findLogs()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('p')
                ->from(PostLog::class, 'p')
                ->orderBy('p.id', 'DESC')
                ;
        return $queryBuilder->getQuery();            
        
    }
    
}
