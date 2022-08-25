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
use Stock\Entity\Register;

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
     * Найти документ для списания товара
     * 
     * @param integer $goodId
     * @param float $docStamp
     * @param integer $officeId
     * $param string $baseKey
     * 
     */
    public function findBases($goodId, $docStamp, $officeId, $baseKey = null)
    {
        $method = 'ASC';
        if ($docStamp > 1567285260 && $docStamp < 1641060060){
            $method = 'DESC';
        }
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();
        $qb = $entityManager->createQueryBuilder();
        $qb->select('sum(m.quantity) as rest, m.baseKey, m.docStamp, sum(m.amount)/sum(m.quantity) as price')
                ->from(Movement::class, 'm')
                ->where('m.good = ?1')
                ->andWhere('m.docStamp < ?2')
                ->andWhere('m.docStamp > 0')
                ->andWhere('m.office = ?3')
                ->andWhere('m.status != ?4')
                ->setParameter('1', $goodId)
                ->setParameter('2', $docStamp)
                ->setParameter('3', $officeId)
                ->setParameter('4', Movement::STATUS_RETIRED)
                ->groupBy('m.baseKey')
                ->having('rest > 0')
                ->orderBy('price', 'ASC')
                ->addOrderBy('m.docStamp', $method)
                ;
        
        if ($baseKey){
            $qb->andWhere('m.baseKey = ?5')
               ->setParameter('5', $baseKey);     
        }
        
        return $qb->getQuery()->getResult();
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
    
    /**
    * Остаток товара
    * @param integer $goodId
     *@param date $dateOper 
     *@param integer $officeId 
     * @param integer $companyId
    * @return integer
    */
    public function goodRest($goodId, $dateOper, $officeId = null, $companyId = null)
    {
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();

        $qb = $entityManager->createQueryBuilder();
        $qb->select('sum(m.quantity) as rSum')
                ->from(Movement::class, 'm')
                ->where('m.good = ?1')
                ->andWhere('m.dateOper <= ?2') 
                ->setParameter('1', $goodId)
                ->setParameter('2', $dateOper)
                ;
        if (!empty($officeId)){
            if (is_numeric($officeId)){
                $qb->andWhere('m.office = ?3');
                $qb->setParameter('3', $officeId);
            }    
        }
        
        if (!empty($companyId)){
            if (is_numeric($companyId)){
                $qb->andWhere('m.company = ?4');
                $qb->setParameter('4', $companyId);
            }    
        }
        
        $result = $qb->getQuery()->getOneOrNullResult();
        
        return $result['rSum'];
    }            

    /**
    * Остаток товара на момент времени
    * @param integer $goodId
     *@param integer $docType 
     *@param integer $docId 
     *@param integer $officeId 
     * @param integer $companyId
    * @return integer
    */
    public function stampRest($goodId, $docType, $docId, $officeId = null, $companyId = null)
    {
        $entityManager = $this->getEntityManager();
        
        $register = $entityManager->getRepository(Register::class)
                ->findOneBy(['docType' => $docType, 'docId' => $docId]);
                
        if ($register){
            $qb = $entityManager->createQueryBuilder();
            $qb->select('sum(m.quantity) as rSum')
                    ->from(Movement::class, 'm')
                    ->where('m.good = ?1')
                    ->andWhere('m.docStamp <= ?2') 
                    ->andWhere('m.docStamp > 0')
                    ->setParameter('1', $goodId)
                    ->setParameter('2', $register->getDocStamp())
                    ;
            if (!empty($officeId)){
                if (is_numeric($officeId)){
                    $qb->andWhere('m.office = ?3');
                    $qb->setParameter('3', $officeId);
                }    
            }

            if (!empty($companyId)){
                if (is_numeric($companyId)){
                    $qb->andWhere('m.company = ?4');
                    $qb->setParameter('4', $companyId);
                }    
            }

            $result = $qb->getQuery()->getOneOrNullResult();

            return $result['rSum'];
        }
        return;
    }            


    /**
    * Количество продаж у товара
    * @param integer $goodId
    * @return integer
    */
    public function goodMovementRetail($goodId)
    {
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();

        $qb = $entityManager->createQueryBuilder();
        $qb->select('sum(m.quantity) as rSum')
                ->from(Movement::class, 'm')
                ->where('m.good = ?1')
                ->andWhere('m.docType = ?2 or m.docType = ?3')
                ->andWhere('m.status = ?4')
                ->setParameter('1', $goodId)
                ->setParameter('2', Movement::DOC_ORDER)
                ->setParameter('3', Movement::DOC_VT)
                ->setParameter('4', Movement::STATUS_ACTIVE)
                ;
        
        $result = $qb->getQuery()->getOneOrNullResult();
        //$connection->update('goods', ['movement' => -$result['rSum']],['id' => $good->getId()]);
        
        return intval($result['rSum']);
    }            
}