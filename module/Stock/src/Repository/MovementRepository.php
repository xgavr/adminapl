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
use Stock\Entity\Movement;
use Application\Entity\Producer;
use Application\Entity\GenericGroup;
use Application\Entity\TokenGroup;

/**
 * Description of MovementRepository
 *
 * @author Daddy
 */
class MovementRepository extends EntityRepository{
    
    /**
     * Удаление записей движения документа
     * 
     * @param string $docKey
     */
    public function removeDocMovements($docKey)
    {
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();
        $qb = $entityManager->createQueryBuilder();
        $qb->select('m')
                ->from(Movement::class, 'm')
                ->where('m.docKey = ?1')
                ->setParameter('1', $docKey)
                ;
        $movements = $qb->getQuery()->getResult();
        
        foreach ($movements as $movement){
            $connection->delete('movement', ['id' => $movement->getId()]);
        }
        
        return;
    }

    /**
     * Добавление записей движения товара
     * 
     * @param array $data
     */
    public function insertMovement($data)
    {
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();
        $connection->insert('movement', $data);
        return;
    }
    
   /**
    * Количество движения у производителя
    * @param Producer $producer
    * @return integer
    */
    public function producerMovementCount($producer)
    {
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();

        $qb = $entityManager->createQueryBuilder();
        $qb->select('count(m.id) as mCount')
                ->from(Movement::class, 'm')
                ->join('m.good', 'g')
                ->where('g.producer = ?1')
                ->setParameter('1', $producer->getId())
                ;
        
        $result = $qb->getQuery()->getOneOrNullResult();
        $connection->update('producer', ['movement' => $result['mCount']],['id' => $producer->getId()]);
        
        return $result['mCount'];
    }
    
   /**
    * Количество движения у группы ТД
    * @param Group $group
    * @return integer
    */
    public function groupMovementCount($group)
    {
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();

        $qb = $entityManager->createQueryBuilder();
        $qb->select('count(m.id) as mCount')
                ->from(Movement::class, 'm')
                ->join('m.good', 'g')
                ->where('g.genericGroup = ?1')
                ->setParameter('1', $group->getId())
                ;
        
        $result = $qb->getQuery()->getOneOrNullResult();
        $connection->update('generic_group', ['movement' => $result['mCount']],['id' => $group->getId()]);
        
        return $result['mCount'];
    }    
    
   /**
    * Количество движения у группы наименований
    * @param TokenGroup $tokenGroup
    * @return integer
    */
    public function tokenGroupMovementCount($tokenGroup)
    {
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();

        $qb = $entityManager->createQueryBuilder();
        $qb->select('count(m.id) as mCount')
                ->from(Movement::class, 'm')
                ->join('m.good', 'g')
                ->where('g.tokenGroup = ?1')
                ->setParameter('1', $tokenGroup->getId())
                ;
        
        $result = $qb->getQuery()->getOneOrNullResult();
        $connection->update('token_group', ['movement' => $result['mCount']],['id' => $tokenGroup->getId()]);
        
        return $result['mCount'];
    }    
    
}