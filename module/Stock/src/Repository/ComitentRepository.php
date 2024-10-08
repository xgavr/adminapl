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
use Application\Entity\Order;
use Company\Entity\Contract;
use Stock\Entity\Movement;
use Stock\Entity\Vt;
use ApiMarketPlace\Entity\MarketSaleReport;

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
            $connection->delete('comitent', ['id' => $cms->getId()]);
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
     * Добавить запись движения заказа
     * @param Order $order
     * @param type $data
     */
    public function insertOrderComitent($order, $data)
    {
        if ($order->getContract()){
            if ($order->getContract()->getKind() == Contract::KIND_COMITENT && $order->getStatus() == Order::STATUS_SHIPPED){
                $comitentData = [
                    'doc_key' => $order->getLogKey(),
                    'doc_type' => Movement::DOC_ORDER,
                    'doc_id' => $order->getId(),
                    'base_key' => $order->getLogKey(),
                    'base_type' => Movement::DOC_ORDER,
                    'base_id' => $order->getId(),
                    'doc_row_key' => $data['doc_row_key'],
                    'doc_row_no' => $data['doc_row_no'],
                    'date_oper' => date('Y-m-d 12:00:00', strtotime($order->getDocDate())),
                    'status' => Comitent::getStatusFromOrder($order),
                    'quantity' => -$data['quantity'], //минус озознанный
                    'amount' => -$data['amount'],
                    'base_amount' => -$data['base_amount'],
                    'good_id' => $data['good_id'],
                    'legal_id' => $order->getLegal()->getId(),
                    'company_id' => $order->getCompany()->getId(), //
                    'contract_id' => $order->getContract()->getId(), //
                    'doc_stamp' => $data['doc_stamp'],
                ];

                $this->insertComitent($comitentData);             
            }
        }
    }
    
    /**
     * Добавить запись движения возврата
     * @param Vt $vt
     * @param type $data
     */
    public function insertVtComitent($vt, $data)
    {
        if ($vt->getOrder()->getContract()){
            if ($vt->getOrder()->getContract()->getKind() == Contract::KIND_COMITENT && $vt->getStatus() == Vt::STATUS_ACTIVE){
                $comitentData = [
                    'doc_key' => $vt->getLogKey(),
                    'doc_type' => Movement::DOC_VT,
                    'doc_id' => $vt->getId(),
                    'base_key' => $vt->getOrder()->getLogKey(),
                    'base_type' => Movement::DOC_ORDER,
                    'base_id' => $vt->getOrder()->getId(),
                    'doc_row_key' => $data['doc_row_key'],
                    'doc_row_no' => $data['doc_row_no'],
                    'date_oper' => date('Y-m-d 22:00:00', strtotime($vt->getDocDate())),
                    'status' => Comitent::getStatusFromVt($vt),
                    'quantity' => -$data['quantity'], //минус озознанный
                    'amount' => -$data['amount'],
                    'base_amount' => -$data['base_amount'],
                    'good_id' => $data['good_id'],
                    'legal_id' => $vt->getOrder()->getLegal()->getId(),
                    'company_id' => $vt->getOrder()->getCompany()->getId(), //
                    'contract_id' => $vt->getOrder()->getContract()->getId(), //
                    'doc_stamp' => $data['doc_stamp'],
                ];

                $this->insertComitent($comitentData);             
            }
        }    
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
        $qb->select('sum(c.quantity) as rest, c.baseKey, c.baseType, c.baseId, c.docStamp, sum(c.amount)/sum(c.quantity) as price, '
                . 'sum(c.baseAmount)/sum(c.quantity) as basePrice')
                ->from(Comitent::class, 'c')
                ->distinct()
                ->where('c.good = ?1')
                ->andWhere('c.docStamp <= ?2')
                ->andWhere('c.docStamp > 0')
                ->andWhere('c.contract = ?3')
                ->andWhere('c.status != ?4')
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
     * Найти документ для возврата
     * 
     * @param integer $goodId
     * @param float $docStamp
     * @param integer $contractId
     * $param string $baseKey
     * 
     */
    public function findForReturn($goodId, $docStamp, $contractId, $baseKey = null)
    {
        $method = 'DESC';
        $entityManager = $this->getEntityManager();
        $qb = $entityManager->createQueryBuilder();
        $qb->select('c')
                ->from(Comitent::class, 'c')
                ->distinct()
                ->where('c.good = ?1')
                ->andWhere('c.docStamp <= ?2')
                ->andWhere('c.docStamp > 0')
                ->andWhere('c.contract = ?3')
                ->andWhere('c.status != ?4')
                ->andWhere('c.docType = ?5')
                ->setParameter('1', $goodId)
                ->setParameter('2', $docStamp)
                ->setParameter('3', $contractId)
                ->setParameter('4', Comitent::STATUS_RETIRED)
                ->setParameter('5', Movement::DOC_MSR)
//                ->groupBy('c.baseKey')
//                ->having('rest > 0')
                ;
        
        $qb->addOrderBy('c.docStamp', $method);
        
        if ($baseKey){
            $qb->andWhere('c.baseKey = ?6')
               ->setParameter('6', $baseKey);     
        }
        
        return $qb->getQuery()->getResult();
    }

    /**
     * Получить актуальный остаток
     * @param integer $goodId
     * @return array
     */
    private function goodComitentRest($goodId)
    {
        
        $entityManager = $this->getEntityManager();
        $qb = $entityManager->createQueryBuilder();
        $qb->select('identity(c.company) as companyId, identity(c.legal) as legalId, identity(c.contract) as contractId, sum(c.quantity) as rest, sum(c.amount) as amount')
                ->from(Comitent::class, 'c')
                ->where('c.good = ?1')
                ->setParameter('1', $goodId)
                ->groupBy('companyId')
                ->addGroupBy('legalId')
                ->addGroupBy('contractId')
                ;
            
        return $qb->getQuery()->getResult();            
    }
    
    /**
     * Обновить актуальные остатки
     * @param integer $goodId
     * @return null
     */
    public function updateComitentBalance($goodId) 
    {
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();
        
        $connection->update('comitent_balance', ['rest' => 0, 'price' => 0], ['good_id' => $goodId]);

        $rests = $this->goodComitentRest($goodId);
        
        foreach ($rests as $rest){
            $goodRest = $price = 0;        
            if (is_array($rest)){
                if (!empty($rest['rest'])){
                    $goodRest = $rest['rest'];
                    $price = abs($rest['amount']/$rest['rest']);
                }    
            }

            if ($goodRest){
                $upd = [
                    'rest' => $goodRest,
                    'price' => $price,
                ];

                $crit = array_filter([
                    'good' => $goodId,
                    'legal' => $rest['legalId'],
                    'company' => $rest['companyId'],
                    'contract' => $rest['contractId'],
                ]);

                $comitentBalance = $entityManager->getRepository(ComitentBalance::class)
                        ->findOneBy($crit);

                if ($comitentBalance){
                    $connection->update('comitent_balance', $upd, ['id' => $comitentBalance->getId()]);
                } else {
                    $connection->insert('comitent_balance', [
                        'good_id' => $goodId, 
                        'legal_id' => $rest['legalId'], 
                        'company_id' => $rest['companyId'],
                        'contract_id' => $rest['contractId'],
                        'rest' => $goodRest,
                        'price' => $price,
                    ]);
                }
            }    
        }    
                        
        return;
    }
    
    /**
     * Товары в торговых площадках
     * @param array $options
     */
    public function goodInComitent($options = null)
    {
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();
        $qb = $entityManager->createQueryBuilder();
        $qb->select('identity(c.good) as goodId')
                ->from(ComitentBalance::class, 'c')
                ->distinct()
                ->andWhere('c.rest != 0')
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
     * Движения товара
     * 
     * @param Goods $good
     * @param array $params
     * @return Query
     */
    public function movements($good, $params = null)
    {
        $entityManager = $this->getEntityManager();

        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('m, l, c, vt, vtOrder, msr')
            ->from(Comitent::class, 'm')
            ->join('m.legal', 'l')    
            ->join('m.company', 'c')
            ->leftJoin('m.vt', 'vt', 'WITH', 'm.baseType = '.Movement::DOC_VT) 
            ->leftJoin('vt.order', 'vtOrder')    
            ->leftJoin('m.marketSaleReport', 'msr', 'WITH', 'm.docType = '.Movement::DOC_MSR) 
            ->where('m.good = ?1')
            ->setParameter('1', $good->getId())
            ;
        
        if (is_array($params)){
            if (!empty($params['sort'])){
                $sort = $params['sort'];
                if ($sort == 'dateOper'){
                    $sort = 'docStamp';
                }
                $queryBuilder->addOrderBy('m.'.$sort, $params['order']);
            }
            if (!empty($params['legal'])){
                if (is_numeric($params['legal'])){
                    $queryBuilder->andWhere('m.legal = ?2')
                        ->setParameter('2', $params['office']);
                }    
            }
            if (!empty($params['startDate'])){
                $queryBuilder->andWhere('m.dateOper >= :startDate')
                        ->setParameter('startDate', $params['startDate']);
            }
            if (!empty($params['endDate'])){
                $queryBuilder->andWhere('m.dateOper <= :endDate')
                        ->setParameter('endDate', $params['endDate']);
            }
            if (!empty($params['month'])){
                if (is_numeric($params['month'])){
                    $queryBuilder->andWhere('MONTH(m.dateOper) = :month')
                            ->setParameter('month', $params['month']);
                }    
            }
            if (!empty($params['year'])){
                if (is_numeric($params['year'])){
                    $queryBuilder->andWhere('YEAR(m.dateOper) = :year')
                            ->setParameter('year', $params['year']);
                }    
            }
        }
//        var_dump($queryBuilder->getQuery()->getSQL());
        return $queryBuilder->getQuery();            
    }   
    
    /**
    * Остаток товара на момент времени
    * @param integer $goodId
     *@param integer $docType 
     *@param integer $docId 
     *@param integer $legalId 
     * @param integer $companyId
    * @return integer
    */
    public function stampRest($goodId, $docType, $docId, $legalId = null, $companyId = null)
    {
        $entityManager = $this->getEntityManager();
        
        $register = $entityManager->getRepository(Register::class)
                ->findOneBy(['docType' => $docType, 'docId' => $docId]);
                
        if ($register){
            $qb = $entityManager->createQueryBuilder();
            $qb->select('sum(m.quantity) as rSum')
                    ->from(Comitent::class, 'm')
                    ->where('m.good = ?1')
                    ->andWhere('m.docStamp <= ?2') 
                    ->andWhere('m.docStamp > 0')
                    ->setParameter('1', $goodId)
                    ->setParameter('2', $register->getDocStamp())
                    ;
            if (!empty($legalId)){
                if (is_numeric($legalId)){
                    $qb->andWhere('m.olegal = ?3');
                    $qb->setParameter('3', $legalId);
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
    
}