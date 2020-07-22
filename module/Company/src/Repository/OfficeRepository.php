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
use Application\Entity\Contact;
use Company\Entity\Contract;

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
     * @param date $dateDoc
     * @return Legal
     */
    public function findDefaultCompany($office, $dateDoc = null)
    {
        if (!$dateDoc){
            $dateDoc = date('Y-m-d');
        }
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('l')
                ->from(Legal::class, 'l')
                ->join('l.contacts', 'c')
                ->where('c.office = ?1')
                ->setParameter('1', $office->getId())
                ->andWhere('c.status = ?2')
                ->setParameter('2', Contact::STATUS_LEGAL)
                ->andWhere('l.dateStart <= ?3')
                ->setParameter('3', $dateDoc)
                ->orderBy('l.dateStart', 'DESC')
                ;
        
        return $queryBuilder->getQuery()->getOneOrNullResult();
        
    }

    /**
     * Найти договор по умолчанию
     * 
     * @param Office $office
     * @param Legal $legal
     * @param date $dateDoc
     * @return Contract
     */
    public function findDefaultContract($office, $legal, $dateDoc = null)
    {
        if (!$dateDoc){
            $dateDoc = date('Y-m-d');
        }
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        
        $queryBuilder->select('c')
                ->from(Contract::class, 'c')
                ->where('c.office = ?1')
                ->setParameter('1', $office->getId())
                ->andWhere('c.legal = ?2')
                ->setParameter('2', $legal->getId())
                ->andWhere('l.dateStart <= ?3')
                ->setParameter('3', $dateDoc)
                ->orderBy('l.dateStart', 'DESC')
                ;
        
        return $queryBuilder->getQuery()->getOneOrNullResult();
        
    }

}