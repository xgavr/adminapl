<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Stock\Repository;

use Doctrine\ORM\EntityRepository;
use Stock\Entity\Register;
use Stock\Entity\RegisterVariable;
use Stock\Entity\Movement;
use Stock\Entity\Pt;
use Stock\Entity\Ptu;
use Stock\Entity\Vtp;
use Stock\Entity\Ot;
use Stock\Entity\St;
use Stock\Entity\Vt;
use Application\Entity\Order;
use Application\Entity\Goods;
use Stock\Entity\PtuGood;
use Company\Entity\Office;
use Company\Entity\Legal;
use User\Entity\User;
use Stock\Entity\Revise;


/**
 * Description of RegisterRepository
 *
 * @author Daddy
 */
class RegisterRepository extends EntityRepository
{
    
    /**
     * Обновить дату последовательности
     * 
     * @param date $dateVar
     * @param integer $varType
     * @param integer $varId
     * @param float $varStamp
     * @return null
     */
    private function updateVariable($dateVar, $varType, $varId, $varStamp)
    {
        $entityManager = $this->getEntityManager();
        $var = $entityManager->getRepository(RegisterVariable::class)
                ->findOneBy([]);
        
        if (!$var){
            $var = new RegisterVariable();
            $var->setDateVar($dateVar);
            $var->setVarId($varId);
            $var->setVarType($varType);
            $var->setVarStamp($varStamp);
            $entityManager->persist($var);
            $entityManager->flush($var);
        }
        
        if ($var->getVarStamp() > $varStamp){
            $var->setDateVar($dateVar);
            $var->setVarId($varId);
            $var->setVarType($varType);
            $var->setVarStamp($varStamp);
            $entityManager->persist($var);
            $entityManager->flush($var);            
        }        
        
        return;
    }
    
    /**
     * Найти наибольшую метку
     * @param date $dateOper
     * @return float
     */
    private function findMaxDocStamp($dateOper)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('r')
                ->from(Register::class, 'r')
                ->where('FLOOR(r.docStamp) = ?1')
                ->setParameter('1', strtotime($dateOper))
                ->orderBy('r.docStamp', 'DESC')
                ->setMaxResults(1)
                ;
        $reg = $queryBuilder->getQuery()->getOneOrNullResult();        
        if ($reg){
            return $reg->getDocStamp() + 0.001; 
        }
//        var_dump(strtotime($dateOper) + 0.001); exit;
        return strtotime($dateOper) + 0.001;
    }
    
    /**
     * Регистрация документа
     * 
     * @param date $dateOper
     * @param integer $docType
     * @param integer $docId
     */
    private function register($dateOper, $docType, $docId)
    {
        $entityManager = $this->getEntityManager();
        $reg = $entityManager->getRepository(Register::class)
                ->findOneBy(['docId' => $docId, 'docType' => $docType]);

        if ($reg){
            $docStamp = $reg->getDocStamp();
            
            if ($reg->getDateOper() != $dateOper){
                $newDocStamp = $this->findMaxDocStamp($dateOper);
                if ($newDocStamp != $docStamp){
                    $entityManager->getConnection()
                            ->update('register', ['date_oper' => $dateOper, 'doc_stamp' => $newDocStamp], ['id' => $reg->getId()]);
                }    
            }
        }            
        
        if (!$reg){    
            $docStamp = $this->findMaxDocStamp($dateOper);
            $entityManager->getConnection()
                    ->insert('register', ['doc_id' => $docId, 'doc_type' => $docType, 'date_oper' => $dateOper, 'doc_stamp' => $docStamp]);            
        }
        
        
        $this->updateVariable($dateOper, $docType, $docId, $docStamp);
        
        return $docStamp;        
    }
    
    /**
     * Найти документы для восстановления последовательности
     */
    public function findForActualize()
    {
        $var = $this->getEntityManager()->getRepository(RegisterVariable::class)
                ->findOneBy([]);
        
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('r')
                ->from(Register::class, 'r')
                ->orderBy('r.docStamp', 'ASC')
                ->setMaxResults(1)
                ;
        
        if ($var){
            $queryBuilder->where('r.docStamp > ?1')
                ->setParameter('1', $var->getVarStamp())
                    ;

        }
        return $queryBuilder->getQuery()->getOneOrNullResult();        
        
        return;
    }
    
    /**
     * Регистриция Pt
     * 
     * @param Pt $pt
     * @return float
     */
    public function ptRegister($pt)
    {
        $dateOper = date('Y-m-d 12:00:00', strtotime($pt->getDocDate()));        
        return $this->register($dateOper, Movement::DOC_PT, $pt->getId());
    }
    
    /**
     * Регистриция Ptu
     * 
     * @param Ptu $ptu
     * @return float
     */
    public function ptuRegister($ptu)
    {
        $dateOper = date('Y-m-d 00:01:00', strtotime($ptu->getDocDate()));
        return $this->register($dateOper, Movement::DOC_PTU, $ptu->getId());
    }    
    
    /**
     * Регистриция Vtp
     * 
     * @param Vtp $vtp
     * @return float
     */
    public function vtpRegister($vtp)
    {
        $dateOper = date('Y-m-d 22:01:00', strtotime($vtp->getDocDate()));
        return $this->register($dateOper, Movement::DOC_VTP, $vtp->getId());
    }    

    /**
     * Регистриция Ot
     * 
     * @param Ot $ot
     * @return float
     */
    public function otRegister($ot)
    {
        $dateOper = date('Y-m-d 00:01:00', strtotime($ot->getDocDate()));
        return $this->register($dateOper, Movement::DOC_OT, $ot->getId());
    }    

    /**
     * Регистриция St
     * 
     * @param St $st
     * @return float
     */
    public function stRegister($st)
    {
        $dateOper = date('Y-m-d 23:01:00', strtotime($st->getDocDate()));
        return $this->register($dateOper, Movement::DOC_ST, $st->getId());
    }    

    /**
     * Регистриция Vt
     * 
     * @param Vt $vt
     * @return float
     */
    public function vtRegister($vt)
    {
        $dateOper = date('Y-m-d 16:00:00', strtotime($vt->getDocDate()));
        return $this->register($dateOper, Movement::DOC_VT, $vt->getId());
    }    

    /**
     * Регистриция Order
     * 
     * @param Order $order
     * @return float
     */
    public function orderRegister($order)
    {
        $dateOper = date('Y-m-d 15:00:00', strtotime($order->getDocDate()));
        return $this->register($dateOper, Movement::DOC_ORDER, $order->getId());
    } 
    
    public function allRegister()
    {
        ini_set('memory_limit', '8192M');
        set_time_limit(0);
        
//        $ptus = $this->getEntityManager()->getRepository(Ptu::class)
//                ->findBy([]);
//        foreach ($ptus as $ptu){
//            $this->ptuRegister($ptu);
//        }

//        $ots = $this->getEntityManager()->getRepository(Ot::class)
//                ->findBy([]);
//        foreach ($ots as $ot){
//            $this->otRegister($ot);
//        }
//
//        $pts = $this->getEntityManager()->getRepository(Pt::class)
//                ->findBy([]);
//        foreach ($pts as $pt){
//            $this->ptRegister($pt);
//        }
//
        $orders = $this->getEntityManager()->getRepository(Order::class)
                ->findBy([]);
        foreach ($orders as $order){
            $this->orderRegister($order);
        }

        $vts = $this->getEntityManager()->getRepository(Vt::class)
                ->findBy([]);
        foreach ($vts as $vt){
            $this->vtRegister($vt);
        }

        $vtps = $this->getEntityManager()->getRepository(Vtp::class)
                ->findBy([]);
        foreach ($vtps as $vtp){
            $this->vtpRegister($vtp);
        }

        $sts = $this->getEntityManager()->getRepository(St::class)
                ->findBy([]);
        foreach ($sts as $st){
            $this->stRegister($st);
        }
    }
    
    /**
     * Найти ПТУ с ближайшей датой
     * @param Goods $good
     * @param date $docDate
     * @param Office $office
     * @retrun Ptu
     */
    public function findNearPtu($good, $docDate, $office)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
//        var_dump(date('Y-m-d 23:59:59', strtotime($docDate.' +10 days'))); exit;
        $queryBuilder->select('p')
                ->from(Ptu::class, 'p')
                ->where('p.docDate > ?1')
                ->andWhere('p.docDate <= ?2')
                ->setParameter('1', date('Y-m-d H:i:s', strtotime($docDate)))
                ->setParameter('2', date('Y-m-d 23:59:59', strtotime($docDate.' +10 days')))
                ->join('p.ptuGoods', 'pg')
                ->andWhere('pg.good = ?3')
                ->setParameter('3', $good->getId())
                ->andWhere('p.status = ?4')
                ->setParameter('4', Ptu::STATUS_ACTIVE)
                ->andWhere('p.office = ?5')
                ->setParameter('5', $office->getId())
//                ->andWhere('p.comment not like :comment')
//                ->setParameter('comment', '#Поправка%')
                ->orderBy('p.docDate', 'ASC')
                ->setMaxResults(1)
                ;
        return $queryBuilder->getQuery()->getOneOrNullResult();        
    }    
    
    /**
     * Найти ПТ с ближайшей датой
     * @param Goods $good
     * @param date $docDate
     * @param Office $office
     * @retrun Ptu
     */
    public function findNearPt($good, $docDate, $office)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
//        var_dump(date('Y-m-d 23:59:59', strtotime($docDate.' +10 days'))); exit;
        $queryBuilder->select('p')
                ->from(Pt::class, 'p')
                ->where('p.docDate > ?1')
                ->andWhere('p.docDate <= ?2')
                ->setParameter('1', date('Y-m-d H:i:s', strtotime($docDate)))
                ->setParameter('2', date('Y-m-d 23:59:59', strtotime($docDate.' +10 days')))
                ->join('p.ptGoods', 'pg')
                ->andWhere('pg.good = ?3')
                ->setParameter('3', $good->getId())
                ->andWhere('p.status = ?4')
                ->setParameter('4', Pt::STATUS_ACTIVE)
                ->andWhere('p.office2 = ?5')
                ->setParameter('5', $office->getId())
//                ->andWhere('p.comment not like :comment')
//                ->setParameter('comment', '#Поправка%')
                ->orderBy('p.docDate', 'ASC')
                ->setMaxResults(1)
                ;
        return $queryBuilder->getQuery()->getOneOrNullResult();        
    }    

    /**
     * Найти ПТУ с таким же артикулом
     * @param Goods $good
     * @param date $docDate
     * @param Office $office
     * @param bool $before
     * @retrun Ptu
     */
    public function correctCodePtu($good, $docDate, $office, $before = true)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('pg')
                ->from(PtuGood::class, 'pg')
                ->join('pg.ptu', 'p')
                ->join('pg.good', 'g')
                ->andWhere('g.code = ?3')
                ->andWhere('g.producer != ?4')
                ->setParameter('3', $good->getCode())
                ->setParameter('4', $good->getProducer()->getId())
                ->andWhere('p.status = ?5')
                ->setParameter('5', Ptu::STATUS_ACTIVE)
                ->andWhere('p.office = ?6')
                ->setParameter('6', $office->getId())
                ->andWhere('p.comment not like :comment')
                ->setParameter('comment', '#Поправка%')
                ->orderBy('p.docDate', 'ASC')
                ->setMaxResults(1)
                ;
        if ($before){
            $queryBuilder            
                ->andWhere('p.docDate >= ?1')
                ->andWhere('p.docDate <= ?2')
                ->setParameter('1', date('Y-m-d 23:59:59', strtotime($docDate.' -10 days')))
                ->setParameter('2', date('Y-m-d 23:59:59', strtotime($docDate)))
                ;
        } else {
            $queryBuilder            
                ->andWhere('p.docDate > ?1')
                ->andWhere('p.docDate <= ?2')
                ->setParameter('1', date('Y-m-d H:i:s', strtotime($docDate)))
                ->setParameter('2', date('Y-m-d 23:59:59', strtotime($docDate.' +10 days')))
                ;            
        }

        $ptuGood = $queryBuilder->getQuery()->getOneOrNullResult();
        if ($ptuGood){
            $ptuGood->setGood($good);
            $entityManager->persist($ptuGood);
            $entityManager->flush($ptuGood);
            return $ptuGood->getPtu();
        }
        
        return;
    }            
    
    /**
     * Запрос на журнал операция
     * @param type $params
     */
    public function transactions($params)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('r, p, o, pt, v, ord, s, vtp, rev')
            ->from(Register::class, 'r')
            ->leftJoin('r.ptu', 'p', 'WITH', 'r.docId = p.id and r.docType = 1')
            ->leftJoin('r.ot', 'o', 'WITH', 'r.docId = o.id and r.docType = 2')
            ->leftJoin('r.pt', 'pt', 'WITH', 'r.docId = pt.id and r.docType = 3')
            ->leftJoin('r.vt', 'v', 'WITH', 'r.docId = v.id and r.docType = 4')
            ->leftJoin('r.order', 'ord', 'WITH', 'r.docId = ord.id and r.docType = 5')
            ->leftJoin('r.st', 's', 'WITH', 'r.docId = s.id and r.docType = 6')
            ->leftJoin('r.vtp', 'vtp', 'WITH', 'r.docId = vtp.id and r.docType = 7')
            ->leftJoin('r.revise', 'rev', 'WITH', 'r.docId = rev.id and r.docType = 8')
            ; 

        if (is_array($params)){
            if (isset($params['sort'])){
                $queryBuilder->addOrderBy('r.'.$params['sort'], $params['order']);
            }        
            if (!empty($params['status'])){
                if (is_numeric($params['status'])){
                    $orX = $queryBuilder->expr()->orX();
                    switch ($params['status']){
                        case Register::STATUS_ACTIVE:
                            $orX->add($queryBuilder->expr()->eq('p.status', Ptu::STATUS_ACTIVE));
                            $orX->add($queryBuilder->expr()->eq('o.status', Ot::STATUS_ACTIVE));
                            $orX->add($queryBuilder->expr()->eq('pt.status', Pt::STATUS_ACTIVE));
                            $orX->add($queryBuilder->expr()->eq('v.status', Vt::STATUS_ACTIVE));
                            $orX->add($queryBuilder->expr()->eq('ord.status', Order::STATUS_SHIPPED));
                            $orX->add($queryBuilder->expr()->eq('s.status', St::STATUS_ACTIVE));
                            $orX->add($queryBuilder->expr()->eq('vtp.status', Vtp::STATUS_ACTIVE));
                            break;
                        case Register::STATUS_RETIRED:
                            $orX->add($queryBuilder->expr()->eq('p.status', Ptu::STATUS_RETIRED));
                            $orX->add($queryBuilder->expr()->eq('o.status', Ot::STATUS_RETIRED));
                            $orX->add($queryBuilder->expr()->eq('pt.status', Pt::STATUS_RETIRED));
                            $orX->add($queryBuilder->expr()->eq('v.status', Vt::STATUS_RETIRED));
                            $orX->add($queryBuilder->expr()->eq('ord.status', Order::STATUS_CANCELED));
                            $orX->add($queryBuilder->expr()->eq('s.status', St::STATUS_RETIRED));
                            $orX->add($queryBuilder->expr()->eq('vtp.status', Vtp::STATUS_RETIRED));
                            break;
                        case Register::STATUS_COMMISSION:
                            $orX->add($queryBuilder->expr()->eq('o.status', Ot::STATUS_COMMISSION));
                            $orX->add($queryBuilder->expr()->eq('vtp.status', Vtp::STATUS_COMMISSION));
                            $orX->add($queryBuilder->expr()->eq('v.status', Vt::STATUS_COMMISSION));
                            $orX->add($queryBuilder->expr()->eq('v.status', Vt::STATUS_DEFECT));
                            break;
                        case Register::STATUS_TAKE_NO:
                            $orX->add($queryBuilder->expr()->eq('pt.statusAccount', Pt::STATUS_TAKE_NO));
                            $orX->add($queryBuilder->expr()->eq('v.statusAccount', Vt::STATUS_TAKE_NO));
                            $orX->add($queryBuilder->expr()->eq('ord.statusAccount', Order::STATUS_TAKE_NO));
                            $orX->add($queryBuilder->expr()->eq('s.statusAccount', St::STATUS_TAKE_NO));
                            $orX->add($queryBuilder->expr()->eq('vtp.statusAccount', Vtp::STATUS_TAKE_NO));
                            break;
                    }
                    if ($orX->count()){
                        $queryBuilder->andWhere($orX);
                    }    
                }    
            }            
            if (!empty($params['doc'])){
                if (is_numeric($params['doc'])){
                    $queryBuilder->andWhere('r.docType = :doc')
                            ->setParameter('doc', $params['doc']);
                }    
            }
            if (!empty($params['year'])){
                if (is_numeric($params['year'])){
                    $queryBuilder->andWhere('YEAR(r.dateOper) = :year')
                            ->setParameter('year', $params['year']);
                }    
            }
            if (!empty($params['month'])){
                if (is_numeric($params['month'])){
                    $queryBuilder->andWhere('MONTH(r.dateOper) = :month')
                            ->setParameter('month', $params['month']);
                }    
            }
        }    
//                var_dump($queryBuilder->getQuery()->getSQL());

        return $queryBuilder->getQuery();
    }
    
    /**
     * Запрос на журнал операция
     * @param type $params
     */
    public function transactionsTotal($params)
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();

        $queryBuilder->select('count(r.id) as countRegister')
            ->from(Register::class, 'r')
            ->leftJoin('r.ptu', 'p', 'WITH', 'r.docId = p.id and r.docType = 1')
            ->leftJoin('r.ot', 'o', 'WITH', 'r.docId = o.id and r.docType = 2')
            ->leftJoin('r.pt', 'pt', 'WITH', 'r.docId = pt.id and r.docType = 3')
            ->leftJoin('r.vt', 'v', 'WITH', 'r.docId = v.id and r.docType = 4')
            ->leftJoin('r.order', 'ord', 'WITH', 'r.docId = ord.id and r.docType = 5')
            ->leftJoin('r.st', 's', 'WITH', 'r.docId = s.id and r.docType = 6')
            ->leftJoin('r.vtp', 'vtp', 'WITH', 'r.docId = vtp.id and r.docType = 7')
            ->leftJoin('r.revise', 'rev', 'WITH', 'r.docId = rev.id and r.docType = 8')
            ; 

        if (is_array($params)){
            if (!empty($params['status'])){
                if (is_numeric($params['status'])){
                    $orX = $queryBuilder->expr()->orX();
                    switch ($params['status']){
                        case Register::STATUS_ACTIVE:
                            $orX->add($queryBuilder->expr()->eq('p.status', Ptu::STATUS_ACTIVE));
                            $orX->add($queryBuilder->expr()->eq('o.status', Ot::STATUS_ACTIVE));
                            $orX->add($queryBuilder->expr()->eq('pt.status', Pt::STATUS_ACTIVE));
                            $orX->add($queryBuilder->expr()->eq('v.status', Vt::STATUS_ACTIVE));
                            $orX->add($queryBuilder->expr()->eq('ord.status', Order::STATUS_SHIPPED));
                            $orX->add($queryBuilder->expr()->eq('s.status', St::STATUS_ACTIVE));
                            $orX->add($queryBuilder->expr()->eq('vtp.status', Vtp::STATUS_ACTIVE));
                            break;
                        case Register::STATUS_RETIRED:
                            $orX->add($queryBuilder->expr()->eq('p.status', Ptu::STATUS_RETIRED));
                            $orX->add($queryBuilder->expr()->eq('o.status', Ot::STATUS_RETIRED));
                            $orX->add($queryBuilder->expr()->eq('pt.status', Pt::STATUS_RETIRED));
                            $orX->add($queryBuilder->expr()->eq('v.status', Vt::STATUS_RETIRED));
                            $orX->add($queryBuilder->expr()->eq('ord.status', Order::STATUS_CANCELED));
                            $orX->add($queryBuilder->expr()->eq('s.status', St::STATUS_RETIRED));
                            $orX->add($queryBuilder->expr()->eq('vtp.status', Vtp::STATUS_RETIRED));
                            break;
                        case Register::STATUS_COMMISSION:
                            $orX->add($queryBuilder->expr()->eq('o.status', Ot::STATUS_COMMISSION));
                            $orX->add($queryBuilder->expr()->eq('v.status', Vt::STATUS_COMMISSION));
                            $orX->add($queryBuilder->expr()->eq('v.status', Vt::STATUS_DEFECT));
                            break;
                        case Register::STATUS_TAKE_NO:
                            $orX->add($queryBuilder->expr()->eq('pt.statusAccount', Pt::STATUS_TAKE_NO));
                            $orX->add($queryBuilder->expr()->eq('v.statusAccount', Vt::STATUS_TAKE_NO));
                            $orX->add($queryBuilder->expr()->eq('ord.statusAccount', Order::STATUS_TAKE_NO));
                            $orX->add($queryBuilder->expr()->eq('s.statusAccount', St::STATUS_TAKE_NO));
                            $orX->add($queryBuilder->expr()->eq('vtp.statusAccount', Vtp::STATUS_TAKE_NO));
                            break;
                    }
                    if ($orX->count()){
                        $queryBuilder->andWhere($orX);
                    }    
                }    
            }            
            if (!empty($params['doc'])){
                if (is_numeric($params['doc'])){
                    $queryBuilder->andWhere('r.docType = :doc')
                            ->setParameter('doc', $params['doc']);
                }    
            }
            if (!empty($params['year'])){
                if (is_numeric($params['year'])){
                    $queryBuilder->andWhere('YEAR(r.dateOper) = :year')
                            ->setParameter('year', $params['year']);
                }    
            }
            if (!empty($params['month'])){
                if (is_numeric($params['month'])){
                    $queryBuilder->andWhere('MONTH(r.dateOper) = :month')
                            ->setParameter('month', $params['month']);
                }    
            }
        }    
        
        $result = $queryBuilder->getQuery()->getOneOrNullResult();

        return $result['countRegister'];
    }
}