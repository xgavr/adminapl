<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Stock\Repository;

use Doctrine\ORM\EntityRepository;
use Stock\Entity\Ptu;
use Stock\Entity\PtuGood;
use Stock\Entity\Mutual;
use Stock\Entity\Retail;
use Application\Entity\Supplier;
use Company\Entity\Legal;

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
     * Сумма поставок поставщика
     * 
     * @param Supplier $supplier
     */
    public function supplierAmount($supplier)
    {
        $result = 0;
        $legalContact = $supplier->getLegalContact();        
        foreach($legalContact->getLegals() as $legal){
            $result += $this->legalAmount($legal);
        }
        return $result;
    }
    
}