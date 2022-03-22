<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Repository;
use Doctrine\ORM\EntityRepository;
use Application\Entity\Idoc;
/**
 * Description of BillRepository
 *
 * @author Daddy
 */
class BillRepository  extends EntityRepository{

    /**
     * Запрос на все доки
     * @param array $params
     * @return Query
     * 
     */
    public function queryAllIdocs($params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('i, s')
            ->from(Idoc::class, 'i') 
            ->leftJoin('s.supplier', 's')    
            ->addOrderBy('i.id', 'DESC')    
                ;
        
        return $queryBuilder->getQuery();
    }       
    
}
