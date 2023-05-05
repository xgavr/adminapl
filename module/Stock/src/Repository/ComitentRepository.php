<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Stock\Repository;

use Doctrine\ORM\EntityRepository;
use Stock\Entity\Comitent;
use Stock\Entity\Register;
use Stock\Entity\ComitentBalance;

/**
 * Description of ComitentRepository
 *
 * @author Daddy
 */
class ComitentRepository extends EntityRepository{
    
    /**
     * Удаление записей движения документа
     * 
     * @param string $docKey
     */
    public function removeDocComitent($docKey)
    {
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();
        $qb = $entityManager->createQueryBuilder();
        $qb->select('c')
                ->from(Comitent::class, 'c')
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
    public function insertComitent($data)
    {
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();
        $connection->insert('comitent', $data);
        return;
    }
    
    /**
     * Найти документ для списания товара
     * 
     * @param integer $goodId
     * @param float $docStamp
     * @param integer $contractId
     * $param string $baseKey
     * 
     */
    public function findBases($goodId, $docStamp, $contractId, $baseKey = null)
    {
        $method = 'ASC';
        $entityManager = $this->getEntityManager();
        $qb = $entityManager->createQueryBuilder();
        $qb->select('sum(c.quantity) as rest, c.baseKey, c.baseType, c.baseId, c.docStamp, sum(c.amount)/sum(c.quantity) as price')
                ->from(Comitent::class, 'c')
                ->distinct()
                ->where('c.good = ?1')
                ->andWhere('c.docStamp <= ?2')
                ->andWhere('c.docStamp > 0')
                ->andWhere('c.contract = ?3')
                ->andWhere('m.status != ?4')
                ->setParameter('1', $goodId)
                ->setParameter('2', $docStamp)
                ->setParameter('3', $contractId)
                ->setParameter('4', Comitent::STATUS_RETIRED)
                ->groupBy('c.baseKey')
                ->having('rest > 0')
                ;
        
        $qb->addOrderBy('c.docStamp', $method);
        
        if ($baseKey){
            $qb->andWhere('c.baseKey = ?5')
               ->setParameter('5', $baseKey);     
        }
        
        return $qb->getQuery()->getResult();
    }
    
    /**
     * Получить актуальный остаток
     * @param integer $goodId
     * @param integer $legalId
     * @param integer $companyId
     * @param integer $contractId
     * @return array
     */
    private function goodBaseRest($goodId, $legalId, $companyId, $contractId)
    {
        
        $entityManager = $this->getEntityManager();
        $qb = $entityManager->createQueryBuilder();
        $qb->select('sum(c.quantity) as rest, sum(c.baseAmount) as amount')
                ->from(Comitent::class, 'c')
                ->where('c.good = ?1')
                ->andWhere('c.legal = ?2') 
                ->andWhere('c.company = ?3') 
                ->andWhere('c.contract = ?4') 
                ->setParameter('1', $goodId)
                ->setParameter('2', $legalId)
                ->setParameter('3', $companyId)
                ->setParameter('4', $contractId)
                ->setMaxResults(1)
                ;
            
        return $qb->getQuery()->getOneOrNullResult();            
    }
    
    /**
     * Обновить актуальные остатки
     * @param integer $goodId
     * @param integer $legalId
     * @param integer $companyId
     * @param integer $contractId
     * @param float $baseStamp
     * @return null
     */
    public function updateComitentBalance($goodId, $legalId, $companyId, $contractId) 
    {
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();

        $goodRest = $price = 0;
        
        $rest = $this->goodBaseRest($goodId, $legalId, $companyId, $contractId);
        
        if (is_array($rest)){
            if (!empty($rest['rest'])){
                $goodRest = $rest['rest'];
                $price = abs($rest['amount']/$rest['rest']);
            }    
        }

        $upd = [
            'rest' => $goodRest,
            'price' => $price,
        ];
        
        $crit = array_filter([
            'good' => $goodId,
            'legal' => $legalId,
            'company' => $companyId,
            'contract' => $contractId,
        ]);
        
        $comitentBalance = $entityManager->getRepository(ComitentBalance::class)
                ->findOneBy($crit);
        
        if ($comitentBalance){
            $connection->update('comitent_balance', $upd, ['id' => $comitentBalance->getId()]);
        } else {
            $connection->insert('comitent_balance', [
                'good_id' => $goodId, 
                'legal_id' => $legalId, 
                'company_id' => $companyId,
                'contract_id' => $contractId,
                'rest' => $goodRest,
                'price' => $price,
            ]);
        }
                        
        return;
    }
    
}