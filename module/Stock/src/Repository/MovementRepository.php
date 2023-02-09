<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Stock\Repository;

use Doctrine\ORM\EntityRepository;
use Stock\Entity\Movement;
use Application\Entity\Producer;
use Application\Entity\TokenGroup;
use Stock\Entity\Register;
use Stock\Entity\GoodBalance;
use Application\Entity\Bid;
use Application\Entity\Order;
use Stock\Entity\VtpGood;
use Stock\Entity\Vtp;
use Stock\Entity\Reserve;
use Stock\Entity\Ptu;
use Stock\Entity\Pt;
use Stock\Entity\Vt;
use Stock\Entity\St;
use Application\Entity\Goods;
use Application\Filter\ArticleCode;

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
            $goodId = $movement->getGood()->getId();
            $officeId = $movement->getOffice()->getId();
            $companyId = $movement->getCompany()->getId();
            
            $connection->delete('movement', ['id' => $movement->getId()]);
            
            $this->updateGoodBalance($goodId, $officeId, $companyId);
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
        $qb = $entityManager->createQueryBuilder();
        $qb->select('sum(m.quantity) as rest, m.baseKey, m.docStamp, sum(m.amount)/sum(m.quantity) as price')
                ->from(Movement::class, 'm')
                ->distinct()
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
                ;
        
        if ($docStamp > 1641060060){
            $qb->addOrderBy('price', 'ASC');
        }
        $qb->addOrderBy('m.docStamp', $method);
        
        if ($baseKey){
            $qb->andWhere('m.baseKey = ?5')
               ->setParameter('5', $baseKey);     
        }
        
        return $qb->getQuery()->getResult();
    }
    
    /**
     * Найти партии с остатком
     * 
     * @param integer $goodId
     * @return array
     */
    public function availableBasePtu($goodId)
    {
        $entityManager = $this->getEntityManager();
        $qb = $entityManager->createQueryBuilder();
        $qb->select('m.baseId, sum(m.quantity) as rest, sum(m.baseAmount) as amount')
                ->from(Movement::class, 'm')
                ->where('m.good = ?1')
                ->andWhere('m.baseType = ?2')
                ->andWhere('m.status != ?4')
                ->setParameter('1', $goodId)
                ->setParameter('2', Movement::DOC_PTU)
                ->setParameter('4', Movement::STATUS_RETIRED)
                ->groupBy('m.baseKey')
                ->having('rest > 0')
                ->setMaxResults(1)
                ;
                
        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Найти партии с остатком
     * 
     * @param array $params
     * @return array
     */
    public function findPtuBases($params = null)
    {
        $entityManager = $this->getEntityManager();
        $qb = $entityManager->createQueryBuilder();
        $qb->select('p.id, p.aplId, p.docDate, p.docNo, '
                . 'g.id as goodId, g.code as code, '
                . 's.id as supplierId, s.name as supplierName, '
                . 'o.id as officeId, o.name as officeName, '
                . 'g.code, sum(m.quantity) as rest')
                ->from(Movement::class, 'm')
                ->join('m.good', 'g')
                ->join('m.ptu', 'p', 'WITH', 'm.docType = 1')
                ->join('p.supplier', 's')
                ->join('m.office', 'o')
                ->andWhere('m.baseType = ?2')
                ->andWhere('m.status != ?4')
                ->setParameter('2', Movement::DOC_PTU)
                ->setParameter('4', Movement::STATUS_RETIRED)
                ->groupBy('m.baseKey')
                ->addGroupBy('m.office')
                ->having('rest > 0')
                ;
        
        if (is_array($params)){

            $orX = $qb->expr()->orX();
            $orX->add($qb->expr()->eq('m.good', 0));                        

            if (isset($params['sort'])){
                $qb->addOrderBy('p.'.$params['sort'], $params['order']);
            }        
            if (!empty($params['code'])){
                $codeFilter = new ArticleCode();
                $orX->add($qb->expr()->eq('g.code', "{$codeFilter->filter($params['code'])}"));                        
            }
            if (!empty($params['orderId'])){
                if (is_numeric($params['orderId'])){
                    $bids = $entityManager->getRepository(Bid::class)
                            ->findBy(['order' => $params['orderId']]);
                    foreach ($bids as $bid){
                        $orX->add($qb->expr()->eq('m.good', $bid->getGood()->getId()));                        
                    }                    
                }
            }
            if ($orX->count()){
                $qb->andWhere($orX);
            }    
        }
//        var_dump($qb->getQuery()->getSQL());        
        return $qb->getQuery();
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
    * @param date $dateOper 
    * @param integer $officeId 
    * @param integer $companyId
    * @param integer $baseId
    * @return integer
    */
    public function goodRest($goodId, $dateOper, $officeId = null, $companyId = null, $baseId = null)
    {
        $entityManager = $this->getEntityManager();

        $qb = $entityManager->createQueryBuilder();
        $qb->select('sum(m.quantity) as rSum, sum(m.baseAmount) as rAmount')
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
        
        if (!empty($baseId)){
            if (is_numeric($baseId)){
                $qb->andWhere('m.baseId = ?5');
                $qb->setParameter('5', $baseId);
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
    
    /**
     * Удалить из резерва
     * @param string $docKey
     * @return null
     */
    private function deleteReserve($docKey)
    {
        $entityManager = $this->getEntityManager();
        $entityManager->getConnection()->delete('reserve', ['doc_key' => $docKey]);
        return;
    }
    
    /**
     * Добавить резерв
     * @param Order|Vtp $doc
     * @param integer $goodId
     * @param integer $status
     * @param float $rest
     */
    private function insertReserve($doc, $goodId, $rest)
    {        
        $userId = null;
        $docKey = $doc->getLogKey();
        if ($doc instanceof Order){
            $officeId = $doc->getOffice()->getId();
            $companyId = $doc->getCompany()->getId();
            $status = Reserve::STATUS_RESERVE;
            if ($doc->getStatus() == Order::STATUS_DELIVERY){
                $status = Reserve::STATUS_DELIVERY;
                if ($doc->getSkiper()){
                    $userId = $doc->getSkiper()->getId();
                }
            }
        } 
        if ($doc instanceof Vtp){
            $officeId = $doc->getPtu()->getOffice()->getId();
            $companyId = $doc->getPtu()->getContract()->getCompany()->getId();
            $status = Reserve::STATUS_VOZVRAT;
        } 
        
        $entityManager = $this->getEntityManager();
        $entityManager->getConnection()->insert('reserve', [
            'doc_key' => $docKey,
            'good_id' => $goodId,
            'office_id' => $officeId,
            'company_id' => $companyId,
            'user_id' => $userId,
            'rest' => $rest,
            'status' => $status,
        ]);
        return;
    }
    
    /**
     * Добавить резерв
     * @param Order|Vtp $doc
     */
    public function updateReserve($doc)
    {
        $docKey = $doc->getLogKey();
        $this->deleteReserve($docKey);
        
        $entityManager = $this->getEntityManager();
        if ($doc instanceof Order){
            if ($doc->getStatus() == Order::STATUS_CONFIRMED || $doc->getStatus() == Order::STATUS_DELIVERY){
                $bids = $entityManager->getRepository(Bid::class)
                        ->findBy(['order' => $doc->getId()]);
                foreach ($bids as $bid){
                    $this->insertReserve($doc, $bid->getGood()->getId(), $bid->getNum());
                }
            }    
        }
        if ($doc instanceof Vtp){
            if ($doc->getStatusDoc() != Vtp::STATUS_DOC_NOT_RECD && $doc->getStatus() == Vtp::STATUS_ACTIVE){
                $vtpGoods = $entityManager->getRepository(VtpGood::class)
                        ->findBy(['vtp' => $doc->getId()]);
                foreach ($vtpGoods as $vtpGood){
                    $this->insertReserve($doc, $vtpGood->getGood()->getId(), $vtpGood->getQuantity());
                }
            }    
        }
        
        return;
    }
    
    /**
     * Получить резервы
     * @param integer $goodId
     * @param integer $officeId
     * @param integer $companyId
     * @return array
     */
    private function reserveRests($goodId, $officeId, $companyId)
    {
        $entityManager = $this->getEntityManager();
        $qb = $entityManager->createQueryBuilder();
        $qb->select('r.status, sum(r.rest) as reserve')
                ->from(Reserve::class, 'r')
                ->where('r.good = ?1')
                ->andWhere('r.office = ?2') 
                ->andWhere('r.company = ?3') 
                ->setParameter('1', $goodId)
                ->setParameter('2', $officeId)
                ->setParameter('3', $companyId)
                ->groupBy('r.status')
                ;
            
        return $qb->getQuery()->getResult();                
    }
    
    /**
     * Получить актуальный остаток
     * @param integer $goodId
     * @param integer $officeId
     * @param integer $companyId
     * @return array
     */
    private function goodBaseRest($goodId, $officeId, $companyId)
    {
        
        $entityManager = $this->getEntityManager();
        $qb = $entityManager->createQueryBuilder();
        $qb->select('sum(m.quantity) as rest, sum(m.baseAmount) as amount')
                ->from(Movement::class, 'm')
                ->where('m.good = ?1')
                ->andWhere('m.office = ?2') 
                ->andWhere('m.company = ?3') 
                ->setParameter('1', $goodId)
                ->setParameter('2', $officeId)
                ->setParameter('3', $companyId)
                ->setMaxResults(1)
                ;
            
        return $qb->getQuery()->getOneOrNullResult();            
    }
        
    /**
     * Обновить актуальные остатки
     * @param integer $goodId
     * @param integer $officeId
     * @param integer $companyId
     * @param float $baseStamp
     * @return null
     */
    public function updateGoodBalance($goodId, $officeId, $companyId) 
    {
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();

        $goodRest = $price = $reserveRest = $deliveryRest = $vozvratRest = 0;
        
        $rest = $this->goodBaseRest($goodId,$officeId, $companyId);
        
        if (is_array($rest)){
            if (!empty($rest['rest'])){
                $goodRest = $rest['rest'];
                $price = abs($rest['amount']/$rest['rest']);
            }    
        }

        $reserves = $this->reserveRests($goodId, $officeId, $companyId);
        
        if (is_array($reserves)){
            foreach ($reserves as $reserve){
                switch ($reserve['status']){
                    case Reserve::STATUS_RESERVE: $reserveRest = $reserve['reserve']; break;
                    case Reserve::STATUS_DELIVERY: $deliveryRest = $reserve['reserve']; break;
                    case Reserve::STATUS_VOZVRAT: $vozvratRest = $reserve['reserve']; break;
                }
            }
        }    
        
        $upd = [
            'rest' => $goodRest,
            'price' => $price,
            'reserve' => $reserveRest,
            'delivery' => $deliveryRest,
            'vozvrat' => $vozvratRest,
        ];
        
        $crit = array_filter([
            'good' => $goodId,
            'office' => $officeId,
            'company' => $companyId,
        ]);
        
        $goodBalance = $entityManager->getRepository(GoodBalance::class)
                ->findOneBy($crit);
        if ($goodBalance){
            $connection->update('good_balance', $upd, ['id' => $goodBalance->getId()]);
        } else {
            $connection->insert('good_balance', [
                'good_id' => $goodId, 
                'office_id' => $officeId, 
                'company_id' => $companyId,
                'rest' => $goodRest,
                'price' => $price,
                'reserve' => $reserveRest,
                'delivery' => $deliveryRest,
                'vozvrat' => $vozvratRest,
            ]);
        }
                        
        return;
    }
    
    /**
     * Получить остаток товара текущий
     * @param Goods $good
     * @param array $params
     */
    public function goodBalance($good, $params = null)
    {
        $entityManager = $this->getEntityManager();
        $qb = $entityManager->createQueryBuilder();
        $qb->select('gb')
                ->from(GoodBalance::class, 'gb')
                ->where('gb.good = ?1')
                ->andWhere('gb.rest > 0') 
                ->setParameter('1', $good->getId())
                ->setMaxResults(1)
                ;
            
        return $qb->getQuery()->getOneOrNullResult();                    
    }
    
    /**
     * Получить ссылку на документ
     * @param string $logkey
     */
    public function docFromLogKey($logkey)
    {
        list($docType, $id) = \explode(':', $logkey);
        switch ($docType){
            case 'ord': return $this->getEntityManager()->getRepository(Order::class)
                    ->find($id);
            case 'pt': return $this->getEntityManager()->getRepository(Pt::class)
                    ->find($id);
            case 'ptu': return $this->getEntityManager()->getRepository(Ptu::class)
                    ->find($id);
            case 'vtp': return $this->getEntityManager()->getRepository(Vtp::class)
                    ->find($id);
            case 'vt': return $this->getEntityManager()->getRepository(Vt::class)
                    ->find($id);
            case 'st': return $this->getEntityManager()->getRepository(Vt::class)
                    ->find($id);
        }
        
        return;
    }
}