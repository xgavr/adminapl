<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Stock\Repository;

use Doctrine\ORM\EntityRepository;
use Stock\Entity\Comiss;
use Stock\Entity\Register;

/**
 * Description of ComissRepository
 *
 * @author Daddy
 */
class ComissRepository extends EntityRepository{
    
    /**
     * Товары на комиссии
     * @param array $options
     */
    public function goodInCommiss($options = null)
    {
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();
        $qb = $entityManager->createQueryBuilder();
        $qb->select('identity(c.good) as goodId')
                ->from(Comiss::class, 'c')
                ->distinct()
                ->groupBy('c.good')
                ->having('sum(c.quantity) > 0')
                ;
        $data = $qb->getQuery()->getResult();
        
        if (is_array($options)){
            if (isset($options['asArray'])){
                $result = [];
                foreach ($data as $row){
                    $result[] = $row['goodId'];
                }
                return $result;
            }
        }
        
        return $data;        
    }
    
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
    
    /**
    * Остаток на момент времени
    * @param integer $clientId
     *@param integer $docType 
     *@param integer $docId 
     * @param integer $companyId
    * @return integer
    */
    public function clientStampRest($clientId, $docType, $docId, $companyId = null)
    {
        $entityManager = $this->getEntityManager();
        
        $register = $entityManager->getRepository(Register::class)
                ->findOneBy(['docType' => $docType, 'docId' => $docId]);
                
        if ($register){
            $qb = $entityManager->createQueryBuilder();
            $qb->select('sum(c.amount) as amount, sum(c.quantity) as quantity')
                    ->from(Comiss::class, 'c')
                    ->join('c.contact', 'contact')
                    ->where('contact.client = ?1')
                    ->andWhere('c.docStamp <= ?2') 
                    ->andWhere('c.docStamp > 0')
                    ->setParameter('1', $clientId)
                    ->setParameter('2', $register->getDocStamp())
                    ;

            if (!empty($companyId)){
                if (is_numeric($companyId)){
                    $qb->andWhere('с.company = ?4');
                    $qb->setParameter('4', $companyId);
                }    
            }

            $result = $qb->getQuery()->getOneOrNullResult();

            return $result;
        }
        return;
    }                    
}