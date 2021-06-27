<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Repository;

use Doctrine\ORM\EntityRepository;
use Application\Entity\Courier;
use Company\Entity\Office;
use Application\Entity\Shipping;

/**
 * Description of CourierRepository
 *
 * @author Daddy
 */
class CourierRepository extends EntityRepository{

    public function findAllCourier()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('c')
            ->from(Courier::class, 'c')
            ->orderBy('c.id')
                ;

        return $queryBuilder->getQuery();
    }        
    
    /**
     * Доставка офиса по умолчанию
     * @param Office $office
     * @return Shipping
     */
    public function findDefaultShipping($office)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('s')
            ->from(Shipping::class, 's')
            ->where('s.office = ?1')
            ->setParameter('1', $office->getId())    
            ->orderBy('s.id')
                ;

        return $queryBuilder->getQuery()->getOneOrNullResult();        
    }
}
