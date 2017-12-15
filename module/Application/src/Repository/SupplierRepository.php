<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Repository;

use Doctrine\ORM\EntityRepository;
use Application\Entity\Supplier;
/**
 * Description of SupplierRepository
 *
 * @author Daddy
 */
class SupplierRepository extends EntityRepository{

    public function findAllSupplier()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('c')
            ->from(Supplier::class, 'c')
            ->orderBy('c.id')
                ;

        return $queryBuilder->getQuery();
    }        
}
