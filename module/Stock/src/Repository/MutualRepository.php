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
        $qb->from(Mutual::class, 'm')
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
}