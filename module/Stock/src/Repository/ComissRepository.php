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