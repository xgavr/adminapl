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
                ->setMaxResults(1)
                ;
        
        return $queryBuilder->getQuery()->getOneOrNullResult();
        
    }

    /**
     * Найти договор по умолчанию
     * 
     * @param Office $office
     * @param Legal $legal
     * @param date $dateDoc
     * @param integer $pay
     * 
     * @return Contract
     */
    public function findDefaultContract($office, $legal, $dateDoc = null, $pay = Contract::PAY_CASH)
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
                ->andWhere('c.dateStart <= ?3')
                ->setParameter('3', $dateDoc)
                ->andWhere('c.pay = ?4')
                ->setParameter('4', $pay)
                ->orderBy('c.dateStart', 'DESC')
                ->setMaxResults(1)
                ;
        
        return $queryBuilder->getQuery()->getOneOrNullResult();
        
    }

    /**
     * Офис по умолчанию
     * 
     * @return Office
     */
    public function findDefaultOffice()
    {
        $entityManager = $this->getEntityManager();
        $defaultOffice = $entityManager->getRepository(Office::class)
                ->find(1);
        return $defaultOffice;        
    }
}