<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Company\Repository;

use Doctrine\ORM\EntityRepository;
use Company\Entity\Office;
use Company\Entity\Legal;

/**
 * Description of OfficeRepository
 *
 * @author Daddy
 */
class OfficeRepository extends EntityRepository{
    
    /**
     * Найти компанию офиса по умолчанию
     * 
     * @param Office $office
     * @return Legal
     */
    public function findDefaultCompany($office, $dateDoc = null)
    {
        if (!$dateDoc){
            $dateDoc = date();
        }
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('l')
                ->from(Contact::class, 'c')
                ->join('c.legals', 'l')
                ->where('c.office = ?1')
                ->setParameter('1', $office->getId())
                ->andWhere('c.status = ?2')
                ->setParameter('2', Contact::STATUS_LEGAL)
                ->andWhere('l.date <= ?3')
                ->setParameter('3', $dateDoc)
                ->orderBy('l.dateStart', 'DESC')
                ;
        
        return $queryBuilder->getQuery()->getOneOrNullResult();
        
    }

}