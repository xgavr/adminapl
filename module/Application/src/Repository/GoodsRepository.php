<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Repository;

use Doctrine\ORM\EntityRepository;
use Application\Entity\Goods;
/**
 * Description of GoodsRepository
 *
 * @author Daddy
 */
class GoodsRepository extends EntityRepository{

    public function findAllGoods()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('g')
            ->from(Goods::class, 'g')
            ->orderBy('g.id')
                ;

        return $queryBuilder->getQuery();
    }        
}
