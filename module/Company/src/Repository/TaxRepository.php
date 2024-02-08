<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Company\Repository;

use Doctrine\ORM\EntityRepository;
use Company\Entity\Tax;
/**
 * Description of TaxRepository
 *
 * @author Daddy
 */
class TaxRepository extends EntityRepository
{
    /**
     * Текщий размер налога
     * @param integer $taxKind
     * @param date $dateOper
     * @return Tax
     */
    public function currentTax($taxKind, $dateOper)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('t')
                ->from(Tax::class, 't')
                ->where('t.kind = :kind')
                ->setParameter('kind', $taxKind)
                ->andWhere('t.dateStart <= :dateStart')
                ->setParameter('dateStart', $dateOper)
                ->orderBy('t.dateStart', 'DESC')
                ->setMaxResults(1)
                ;

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

}