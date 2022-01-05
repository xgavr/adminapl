<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Stock\Repository;

use Doctrine\ORM\EntityRepository;
use Stock\Entity\Mutual;
use Stock\Entity\Retail;
use Application\Entity\Supplier;
use Company\Entity\Legal;
use Stock\Entity\Ptu;
use Stock\Entity\Vtp;

/**
 * Description of MutualRepository
 *
 * @author Daddy
 */
class MutualRepository extends EntityRepository{
    
    /**
     * Удаление записей взаиморасчетов
     * 
     * @param string $docKey
     */
    public function removeDocMutuals($docKey)
    {
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();
        $qb = $entityManager->createQueryBuilder();
        $qb->select('m')
                ->from(Mutual::class, 'm')
                ->where('m.docKey = ?1')
                ->setParameter('1', $docKey)
                ;
        $mutuals = $qb->getQuery()->getResult();
        
        foreach ($mutuals as $mutual){
            $connection->delete('mutual', ['id' => $mutual->getId()]);
        }
        
        return;
    }

    /**
     * Удаление записей взаиморасчетов розницы
     * 
     * @param string $docKey
     */
    public function removeOrderRetails($docKey)
    {
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();
        $qb = $entityManager->createQueryBuilder();
        $qb->select('r')
                ->from(Retail::class, 'r')
                ->where('r.docKey = ?1')
                ->setParameter('1', $docKey)
                ;
        $retails = $qb->getQuery()->getResult();
        
        foreach ($retails as $retail){
            $connection->delete('retail', ['id' => $retail->getId()]);
        }
        
        return;
    }

    /**
     * Добавление записей взаиморасчетов
     * 
     * @param array $data
     */
    public function insertMutual($data)
    {
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();
        $connection->insert('mutual', $data);
        return;
    }
    
    /**
     * Добавление записей взаиморасчетов
     * 
     * @param array $data
     */
    public function insertRetail($data)
    {
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();
        $connection->insert('retail', $data);
        return;
    }
    
    /**
     * Сумма поставок юрлица
     * 
     * @param Legal $legal
     */
    public function legalAmount($legal)
    {
        $entityManager = $this->getEntityManager();
        $qb = $entityManager->createQueryBuilder();
        $qb->select('sum(m.amount) as amountSum')
                ->from(Mutual::class, 'm')
                ->where('m.legal = ?1')
                ->setParameter('1', $legal->getId())
                ;
        $data = $qb->getQuery()->getResult();
        foreach ($data as $row){
            return $row['amountSum'];
        }

        return 0;
    }
    
    /**
     * Сумма поставок юрлица
     * 
     * @param Legal $legal
     */
    public function ptuAmount($legal)
    {
        $entityManager = $this->getEntityManager();
        $qb = $entityManager->createQueryBuilder();
        $qb->select('sum(p.amount) as amountSum')
                ->from(Ptu::class, 'p')
                ->where('p.legal = ?1')
                ->setParameter('1', $legal->getId())
                ->andWhere('p.status = ?2')
                ->setParameter('2', Ptu::STATUS_ACTIVE)
                ;
        $data = $qb->getQuery()->getResult();
        foreach ($data as $row){
            return $row['amountSum'];
        }

        return 0;
    }
    
    /**
     * Сумма возвратов юрлица
     * 
     * @param Legal $legal
     */
    public function vtpAmount($legal)
    {
        $entityManager = $this->getEntityManager();
        $qb = $entityManager->createQueryBuilder();
        $qb->select('sum(v.amount) as amountSum')
                ->from(Vtp::class, 'v')
                ->join('v.ptu', 'p')
                ->where('p.legal = ?1')
                ->setParameter('1', $legal->getId())
                ->andWhere('v.status = ?2')
                ->setParameter('2', Vtp::STATUS_ACTIVE)
                ;
        $data = $qb->getQuery()->getResult();
        foreach ($data as $row){
            return $row['amountSum'];
        }

        return 0;
    }
    
    /**
     * Сумма поставок поставщика
     * 
     * @param Supplier $supplier
     */
    public function supplierAmount($supplier)
    {
        $result = 0;
        $legalContact = $supplier->getLegalContact();        
        foreach($legalContact->getLegals() as $legal){
            $result += $this->ptuAmount($legal);
            $result -= $this->vtpAmount($legal);
        }
        return $result;
    }
    
}