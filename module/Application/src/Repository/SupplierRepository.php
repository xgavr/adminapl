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

    public function findAllSupplier($status = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('c')
            ->from(Supplier::class, 'c')
            ->orderBy('c.status')
            ->addOrderBy('c.name')
                ;
        if ($status){
            $queryBuilder->andWhere('c.status = ?1')
                    ->setParameter('1', $status);
        }

        return $queryBuilder->getQuery();
    }     
    
    public function absentPriceDescriptions()
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('s, count(pd.id) as price_description_count')
                ->from(Supplier::class, 's')
                ->groupBy('s.id')
                ->where('s.status = ?1')
                ->setParameter('1', Supplier::STATUS_ACTIVE)
                ->leftJoin(\Application\Entity\PriceDescription::class, 'pd', 'WITH', 'pd.supplier = s.id')
                ->having('price_description_count = 0')
                ;
        
        return $queryBuilder->getQuery()->getResult();
        
    }
    
    /*
     * Получить статусы поставщиков
     * @var Apllication\Entity\Raw
     * 
     */
    public function statuses()
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('r.status as status, count(r.id) as status_count')
                ->from(Supplier::class, 'r')
                ->groupBy('r.status')
                ;
        
        return $queryBuilder->getQuery()->getResult();
    }
    
}
