<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Stock\Repository;

use Doctrine\ORM\EntityRepository;
use Stock\Entity\Comiss;

/**
 * Description of ComissRepository
 *
 * @author Daddy
 */
class ComissRepository extends EntityRepository{
    
    /**
     * Удаление записей движения документа
     * 
     * @param string $docKey
     */
    public function removeDocComiss($docKey)
    {
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();
        $qb = $entityManager->createQueryBuilder();
        $qb->select('c')
                ->from(Comiss::class, 'c')
                ->where('c.docKey = ?1')
                ->setParameter('1', $docKey)
                ;
        $comiss = $qb->getQuery()->getResult();
        
        foreach ($comiss as $cms){
            $connection->delete('comiss', ['id' => $cms->getId()]);
        }
        
        return;
    }

    /**
     * Найт остаток товара на комиссии
     * 
     * @param integer $goodId
     * @param date $dateOper
     * @param integer $officeId
     */
    public function findActiveComissioners($goodId, $dateOper, $officeId)
    {
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();
        $qb = $entityManager->createQueryBuilder();
        $qb->select('sum(c.quantity) as rest, sum(c.amount) as amount, identity(c.contact) as contactId')
                ->from(Comiss::class, 'c')
                ->where('c.good = ?1')
                ->andWhere('c.dateOper <= ?2')
                ->andWhere('c.office = ?3')
                ->setParameter('1', $goodId)
                ->setParameter('2', $dateOper)
                ->setParameter('3', $officeId)
                ->groupBy('contactId')
                ->having('rest > 0')
                ;
        
        return $qb->getQuery()->getResult();
    }


    /**
     * Добавление записей движения товара
     * 
     * @param array $data
     */
    public function insertComiss($data)
    {
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();
        $connection->insert('comiss', $data);
        return;
    }
}