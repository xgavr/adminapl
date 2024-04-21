<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Zp\Service;

use Zp\Entity\Accrual;
use Company\Entity\Legal;
use Zp\Entity\Position;
use Zp\Entity\Personal;
use Zp\Entity\PersonalAccrual;
use User\Entity\User;
use Zp\Entity\OrderCalculator;
use Zp\Entity\DocCalculator;
use Application\Entity\Order;
use Stock\Entity\Movement;
use Stock\Entity\Vt;
use Zp\Entity\PersonalMutual;
use Company\Entity\Contract;
use Stock\Entity\Register;
use Cash\Entity\CashDoc;
use Stock\Entity\St;
use Company\Entity\TaxMutual;
use Zp\Entity\PersonalRevise;

/**
 * Description of ZpCalculator
 * 
 * @author Daddy
 */
class ZpCalculator {
    
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Admin Manager
     * @var \Admin\Service\AdminManager
     */
    private $adminManager;

    /**
     * Tax Manager
     * @var \Company\Service\TaxManager
     */
    private $taxManager;
    
    /**
     * 
     * @var Accrual
     */
    private $taxNdflAccrual;

    public function __construct($entityManager, $adminManager, $taxManager)
    {
        $this->entityManager = $entityManager;
        $this->adminManager = $adminManager;
        $this->taxManager = $taxManager;
        
        $this->taxNdflAccrual = $this->entityManager->getRepository(Accrual::class)
                ->findOneBy(['payment' => Accrual::PAYMENT_TAX]);
    }
    
    /**
     * Удалить расчет
     * @param integer $docType
     * @param integer $docId
     * @return null
     */
    public function removeOrderCalculator($docType, $docId)
    {
        $orderCalculators = $this->entityManager->getRepository(OrderCalculator::class)
                ->findBy(['docType' => $docType, 'docId' => $docId]);
        foreach ($orderCalculators as $orderCalculator){
            $this->entityManager->remove($orderCalculator);
        }
        
        $this->entityManager->flush();
        
        return;
    }
    
    
    /**
     * Добавить расчет заказа
     * @param Order $order
     * @return OrderCalculator
     */
    public function addOrderCalculator($order)
    {
        $orderCalculator = $this->entityManager->getRepository(OrderCalculator::class)
                ->findOneBy(['docType' => Movement::DOC_ORDER, 'docId' => $order->getId()]);
        
        if ($orderCalculator){
            $orderCalculator->setStatus(OrderCalculator::STATUS_RETIRED);
            $this->entityManager->persist($orderCalculator);
        }    
                
        if ($order->getStatus() == Order::STATUS_SHIPPED && !$order->isComitentContract()){
            
            $base = $this->entityManager->getRepository(Movement::class)
                    ->findBaseAmount(Movement::DOC_ORDER, $order->getId());
            
            if (!$orderCalculator){
                $orderCalculator = new OrderCalculator();
                $orderCalculator->setDateCreated(date('Y-m-d H:i:s'));
            }    
            
            $orderCalculator->setAmount(abs($base['amount']));
            $orderCalculator->setBaseAmount(abs($base['baseAmount']));
            $orderCalculator->setCompany($order->getCompany());
            $orderCalculator->setCourier($order->getSkiper());
            $orderCalculator->setDateOper($order->getDateOper());
            $orderCalculator->setDocType(Movement::DOC_ORDER);
            $orderCalculator->setDocId($order->getId());
            $orderCalculator->setDeliveryAmount($order->getShipmentTotal());
            $orderCalculator->setOffice($order->getOffice());
            $orderCalculator->setOrder($order);
            $orderCalculator->setPayAmount(0);
            $orderCalculator->setShipping($order->getShipping());
            $orderCalculator->setStatus(OrderCalculator::STATUS_ACTIVE);
            $orderCalculator->setUser($order->getUser());

            $this->entityManager->persist($orderCalculator);            
        }
        
        $this->entityManager->flush();
        
        return $orderCalculator;
    }
    
    /**
     * Добавить расчет возврата
     * @param Vt $vt
     * @return OrderCalculator
     */
    public function addVtCalculator($vt)
    {
        $orderCalculator = $this->entityManager->getRepository(OrderCalculator::class)
                ->findOneBy(['docType' => Movement::DOC_VT, 'docId' => $vt->getId()]);
        
        if ($orderCalculator){
            $orderCalculator->setStatus(OrderCalculator::STATUS_RETIRED);
            $this->entityManager->persist($orderCalculator);
        }    
        
        if ($vt->getStatus() == Vt::STATUS_ACTIVE && !$vt->getOrder()->isComitentContract()){
            
            $base = $this->entityManager->getRepository(Movement::class)
                    ->findBaseAmount(Movement::DOC_VT, $vt->getId());

            if (!$orderCalculator){
                $orderCalculator = new OrderCalculator();
                $orderCalculator->setDateCreated(date('Y-m-d H:i:s'));
            }    
            
            $orderCalculator->setAmount(-abs($base['amount']));
            $orderCalculator->setBaseAmount(-abs($base['baseAmount']));
            $orderCalculator->setCompany($vt->getOrder()->getCompany());
            $orderCalculator->setCourier(null);
            $orderCalculator->setDateOper($vt->getDocDate());
            $orderCalculator->setDocType(Movement::DOC_VT);
            $orderCalculator->setDocId($vt->getId());
            $orderCalculator->setDeliveryAmount(0);
            $orderCalculator->setOffice($vt->getOffice());
            $orderCalculator->setOrder($vt->getOrder());
            $orderCalculator->setPayAmount(0);
            $orderCalculator->setShipping(null);
            $orderCalculator->setStatus(OrderCalculator::STATUS_ACTIVE);
            $orderCalculator->setUser($vt->getOrder()->getUser());

            $this->entityManager->persist($orderCalculator);            
        }
        
        $this->entityManager->flush();
        
        return $orderCalculator;
    }
    
    /**
     * Удалить расчет
     * 
     * @param integer $docType
     * @param integer $docId
     */
    public function removePersonalMutual($docType, $docId)
    {
       $personalMutuals = $this->entityManager->getRepository(PersonalMutual::class)
               ->findBy(['docType' => $docType, 'docId' => $docId]);
       
       foreach ($personalMutuals as $personalMutual){
           $this->entityManager->remove($personalMutual);
       }
       
       $this->entityManager->flush();
       
       return;
    }
        
    /**
     * Провести расчет
     * @param DocCalculator $docCalculator
     * 
     * @return PersonalMutual
     */
    public function repostDocCalculator($docCalculator)
    {
        $this->removePersonalMutual(Movement::DOC_ZP, $docCalculator->getId());
        
        $docStamp = $this->entityManager->getRepository(Register::class)
                ->zpDocRegister($docCalculator);
        
        $personalMutual = new PersonalMutual();
        $personalMutual->setAmount(-$docCalculator->getAmount());
        $personalMutual->setCompany($docCalculator->getCompany());
        $personalMutual->setDateOper($docCalculator->getDateOper());
        $personalMutual->setDocId($docCalculator->getId());
        $personalMutual->setDocKey($docCalculator->getLogKey());
        $personalMutual->setDocStamp($docStamp);
        $personalMutual->setDocType(Movement::DOC_ZP);
        $personalMutual->setStatus(PersonalMutual::getStatusFromDocCalculator($docCalculator));
        $personalMutual->setUser($docCalculator->getUser());
        $personalMutual->setKind(PersonalMutual::getKindFromDocCalculator($docCalculator));
        $personalMutual->setAccrual($docCalculator->getAccrual());
        
        $this->entityManager->persist($personalMutual);
                
        $taxNdfl = $this->taxManager->repostDocCalculatorNdflTax($docCalculator, $docStamp);
        
        if ($taxNdfl){
            $personalMutual = new PersonalMutual();
            $personalMutual->setAmount(abs($taxNdfl->getAmount()));
            $personalMutual->setCompany($docCalculator->getCompany());
            $personalMutual->setDateOper($docCalculator->getDateOper());
            $personalMutual->setDocId($docCalculator->getId());
            $personalMutual->setDocKey($docCalculator->getLogKey());
            $personalMutual->setDocStamp($docStamp);
            $personalMutual->setDocType(Movement::DOC_ZP);
            $personalMutual->setStatus(PersonalMutual::getStatusFromDocCalculator($docCalculator));
            $personalMutual->setUser($docCalculator->getUser());
            $personalMutual->setKind(PersonalMutual::KIND_DEDUCTION);
            $personalMutual->setAccrual($this->taxNdflAccrual);

            $this->entityManager->persist($personalMutual);
            $this->entityManager->flush();
        }    

        $this->taxManager->repostDocCalculatorEsn($docCalculator, $docStamp);
        
        return $personalMutual;
    }
    
    /**
     * Провести расчет
     * @param CashDoc $cashDoc
     * @param float $docStamp
     * 
     * @return PersonalMutual
     */
    public function repostCashDoc($cashDoc, $docStamp)
    {
        $this->removePersonalMutual(Movement::DOC_CASH, $cashDoc->getId());
        
        $personalMutual = null;
        
        if ($cashDoc->getDateOper() >= date('2024-01-01')){
            switch ($cashDoc->getKind()){

                case CashDoc::KIND_OUT_COURIER:
                    // начисление за доставку
                    $personalMutual = new PersonalMutual();
                    $personalMutual->setAmount(-$cashDoc->getAmount());
                    $personalMutual->setCompany($cashDoc->getCompany());
                    $personalMutual->setDateOper($cashDoc->getDateOper());
                    $personalMutual->setDocId($cashDoc->getId());
                    $personalMutual->setDocKey($cashDoc->getLogKey());
                    $personalMutual->setDocStamp($docStamp);
                    $personalMutual->setDocType(Movement::DOC_CASH);
                    $personalMutual->setStatus(PersonalMutual::getStatusFromCashDoc($cashDoc));
                    $personalMutual->setUser($cashDoc->getUserRefill());
                    $personalMutual->setKind(PersonalMutual::KIND_ACCRUAL_RETAIL);
                    $personalMutual->setAccrual($this->entityManager->getRepository(Accrual::class)
                            ->findOneBy(['payment' => Accrual::PAYMENT_COURIER]));

                    $this->entityManager->persist($personalMutual);
                    
                    $taxNdfl = $this->taxManager->repostCashDocNdflTax($cashDoc, $docStamp);

                    if ($taxNdfl){
                        $personalMutual = new PersonalMutual();
                        $personalMutual->setAmount(abs($taxNdfl->getAmount()));
                        $personalMutual->setCompany($cashDoc->getCompany());
                        $personalMutual->setDateOper($cashDoc->getDateOper());
                        $personalMutual->setDocId($cashDoc->getId());
                        $personalMutual->setDocKey($cashDoc->getLogKey());
                        $personalMutual->setDocStamp($docStamp);
                        $personalMutual->setDocType(Movement::DOC_CASH);
                        $personalMutual->setStatus(PersonalMutual::getStatusFromCashDoc($cashDoc));
                        $personalMutual->setUser($cashDoc->getUser());
                        $personalMutual->setKind(PersonalMutual::KIND_DEDUCTION);
                        $personalMutual->setAccrual($this->taxNdflAccrual);

                        $this->entityManager->persist($personalMutual);
                    }    

                    $this->taxManager->repostCashDocEsn($cashDoc, $docStamp);
                    
                case CashDoc::KIND_OUT_SALARY:
                    // выплата
                    $personalMutual = new PersonalMutual();
                    $personalMutual->setAmount($cashDoc->getAmount());
                    $personalMutual->setCompany($cashDoc->getCompany());
                    $personalMutual->setDateOper($cashDoc->getDateOper());
                    $personalMutual->setDocId($cashDoc->getId());
                    $personalMutual->setDocKey($cashDoc->getLogKey());
                    $personalMutual->setDocStamp($docStamp);
                    $personalMutual->setDocType(Movement::DOC_CASH);
                    $personalMutual->setStatus(PersonalMutual::getStatusFromCashDoc($cashDoc));
                    $personalMutual->setUser($cashDoc->getUserRefill());
                    $personalMutual->setKind(PersonalMutual::KIND_PAYMENT);
                    $personalMutual->setAccrual($this->entityManager->getRepository(Accrual::class)
                            ->findOneBy(['payment' => Accrual::PAYMENT_PAYMENT]));

                    $this->entityManager->persist($personalMutual);

                    break;                
            }    
        }    
        
        $this->entityManager->flush();
        
        return $personalMutual;
    }
    
    /**
     * Провести расчет
     * @param St $st
     * @param float $docStamp
     * 
     * @return PersonalMutual
     */
    public function repostSt($st, $docStamp)
    {
        $this->removePersonalMutual(Movement::DOC_ST, $st->getId());
        
        $personalMutual = null;
        
        if ($st->getDocDate() >= date('2024-01-01')){
            switch ($st->getWriteOff()){

                case St::WRITE_PAY:
                    
                    $amount = $this->entityManager->getRepository(St::class)
                        ->findMovementBaseAmount($st);
                    
                    $personalMutual = new PersonalMutual();
                    $personalMutual->setAmount(abs($amount));
                    $personalMutual->setCompany($st->getCompany());
                    $personalMutual->setDateOper($st->getDocDate());
                    $personalMutual->setDocId($st->getId());
                    $personalMutual->setDocKey($st->getLogKey());
                    $personalMutual->setDocStamp($docStamp);
                    $personalMutual->setDocType(Movement::DOC_ST);
                    $personalMutual->setStatus(PersonalMutual::getStatusFromSt($st));
                    $personalMutual->setUser($st->getUser());
                    $personalMutual->setKind(PersonalMutual::KIND_PAYMENT);
                    $personalMutual->setAccrual($this->entityManager->getRepository(Accrual::class)
                            ->findOneBy(['payment' => Accrual::PAYMENT_PAYMENT]));

                    $this->entityManager->persist($personalMutual);
                    break;
            }    
        }    
        
        $this->entityManager->flush();
        
        return $personalMutual;
    }
    
    
    /**
     * Удалить расчет за день
     * 
     * @param PersonalAccrual $personalAccrual
     * @param date $dateCalculation
     * 
     * @return DocCalculator
     */
    private function removeDocCalculator($dateCalculation)
    {
        $docCalculators = $this->entityManager->getRepository(DocCalculator::class)
                ->findBy(['dateOper' => $dateCalculation]);
        
        foreach ($docCalculators as $docCalculator){
            
            $docCalculator->setStatus(DocCalculator::STATUS_RETIRED);

            $this->entityManager->persist($docCalculator);

            $this->entityManager->flush();

            $this->removePersonalMutual(Movement::DOC_ZP, $docCalculator->getId());
            
            $this->taxManager->removeTaxMutual(Movement::DOC_ZP, $docCalculator->getId());
        }
                
        return;
    }
    
    /**
     * Расчитать оклад за день
     * 
     * @param PersonalAccrual $personalAccrual
     * @param date $dateCalculation
     * @param float $calcResult
     * @param float $base
     * 
     * @return DocCalculator
     */
    private function addDocCalculator($personalAccrual, $dateCalculation, $calcResult, $base)
    {
        $docCalculator = $this->entityManager->getRepository(DocCalculator::class)
                ->findOneBy(['personalAccrual' => $personalAccrual->getId(), 'dateOper' => $dateCalculation]);
        
        if ($docCalculator){
            $docCalculator->setStatus(DocCalculator::STATUS_RETIRED);
            $this->entityManager->persist($docCalculator);
        }
        
        if (!$docCalculator){
            $docCalculator = new DocCalculator();
            $docCalculator->setDateCreated(date('Y-m-d H:i:s'));
        }

        $docCalculator->setAccrual($personalAccrual->getAccrual());
        $docCalculator->setAmount($calcResult);
        $docCalculator->setBase($base);
        $docCalculator->setCompany($personalAccrual->getCompany());
        $docCalculator->setDateOper($dateCalculation);
        $docCalculator->setNum($personalAccrual->getPersonal()->getPositionNum());
        $docCalculator->setPersonalAccrual($personalAccrual);
        $docCalculator->setPosition($personalAccrual->getPersonal()->getPosition());
        $docCalculator->setRate($personalAccrual->getRate());
        $docCalculator->setStatus(DocCalculator::STATUS_ACTIVE);
        $docCalculator->setUser($personalAccrual->getUser());

        $this->entityManager->persist($docCalculator);
        
        $this->entityManager->flush();
        
        $this->repostDocCalculator($docCalculator);
                
        return $docCalculator;
    }
    
    /**
     * Рассчитать за день
     * @param date $dateCalculation
     */
    public function dateCalculation($dateCalculation)
    {
        $this->removeDocCalculator($dateCalculation);
        
        $personalAccruals = $this->entityManager->getRepository(PersonalAccrual::class)
                ->findActualPersonalAccrual($dateCalculation);        
        
        foreach ($personalAccruals as $personalAccrual){
            
            $calcResult = $base = 0;
            
            if ($personalAccrual->getStatus() == PersonalAccrual::STATUS_RETIRED){
                continue;
            }
            switch ($personalAccrual->getAccrual()->getKind()){
                case Accrual::KIND_FIX:
                    $base = 0;
                    $dayCount = cal_days_in_month(CAL_GREGORIAN, date('m', strtotime($dateCalculation)), date('Y', strtotime($dateCalculation)));
                    $calcResult = $personalAccrual->getRate()*$personalAccrual->getPersonal()->getPositionNum()/$dayCount; 
                    break;
                case Accrual::KIND_PERCENT:
                    switch ($personalAccrual->getAccrual()->getBasis()){
                        case Accrual::BASE_INCOME_ORDER:
                            $base = $this->entityManager->getRepository(PersonalAccrual::class)
                                ->baseRetail($dateCalculation, [
                                    'company' => $personalAccrual->getCompany()->getId(),
                                    'user' => $personalAccrual->getUser()->getId(),
                                ]);
                            break;
                        case Accrual::BASE_INCOME_RETAIL:
                            $base = $this->entityManager->getRepository(PersonalAccrual::class)
                                ->baseRetail($dateCalculation, [
                                    'company' => $personalAccrual->getCompany()->getId(),
                                ]);
                            break;
                        case Accrual::BASE_INCOME_TP:
                            $base = $this->entityManager->getRepository(PersonalAccrual::class)
                                ->baseTp($dateCalculation, [
                                    'company' => $personalAccrual->getCompany()->getId(),
                                ]);
                            break;
                        case Accrual::BASE_INCOME_TOTAL:
                            $base = $this->entityManager->getRepository(PersonalAccrual::class)
                                ->baseTp($dateCalculation, [
                                    'company' => $personalAccrual->getCompany()->getId(),
                                ]) +                                
                                $this->entityManager->getRepository(PersonalAccrual::class)
                                    ->baseRetail($dateCalculation, [
                                        'company' => $personalAccrual->getCompany()->getId(),
                                    ]);
                            break;
                    }
                    $calcResult = $base*($personalAccrual->getRate()/100)*$personalAccrual->getPersonal()->getPositionNum(); 
                    break;
            }
            
            if ($calcResult){
                $this->addDocCalculator($personalAccrual, $dateCalculation, $calcResult, $base);
            }    
        }
        
        return;
    }    
    
    /**
     * Рассчитать за период
     */
    public function periodCalculator()
    {
        $dateCalculation = date('Y-m-d', strtotime('first day of previous month'));
        while ($dateCalculation <= date('Y-m-d')){
            if ($dateCalculation >= date('2024-01-01')){
                $this->dateCalculation($dateCalculation);                
            }
                        
            $dateCalculation = date('Y-m-d', strtotime($dateCalculation .' +1 day'));
        }
        
        return;
    }
    
    /**
     * Рассчитать за месяц
     * @param date $dateStart
     */
    public function monthCalculator($dateStart)
    {
        $dateCalculation = date('Y-m-01', strtotime($dateStart));
        $dateEnd = min(date('Y-m-d 23:59:59'), date('Y-m-t 23:59:59', strtotime($dateStart)));
        
        while ($dateCalculation <= $dateEnd){
            if ($dateCalculation >= date('2024-01-01')){
                $this->dateCalculation($dateCalculation);                
            }
                        
            $dateCalculation = date('Y-m-d', strtotime($dateCalculation .' +1 day'));
        }
        
        return;
    }
    
    /**
     * Проведение корректировка ЗП
     * @param PersonalRevise $personalRevise
     */
    public function repostPersonalRevise($personalRevise)
    {
        $this->removePersonalMutual(Movement::DOC_ZPRV, $personalRevise->getId());
        
        $docStamp = $this->entityManager->getRepository(Register::class)
                ->zpReviseRegister($personalRevise);
        
        $personalMutual = new PersonalMutual();
        $personalMutual->setAmount($personalRevise->getAmount());
        $personalMutual->setCompany($personalRevise->getCompany());
        $personalMutual->setDateOper($personalRevise->getDocDate());
        $personalMutual->setDocId($personalRevise->getId());
        $personalMutual->setDocKey($personalRevise->getLogKey());
        $personalMutual->setDocStamp($docStamp);
        $personalMutual->setDocType(Movement::DOC_ZPRV);
        $personalMutual->setStatus(PersonalMutual::getStatusFromPersonalRevise($personalRevise));
        $personalMutual->setUser($personalRevise->getUser());
        $personalMutual->setKind(PersonalMutual::KIND_PAYMENT);
        $personalMutual->setAccrual($personalRevise->getAccrual());
        
        $this->entityManager->persist($personalMutual);
        $this->entityManager->flush();
                
        return $personalMutual;
        
    }
    
    /**
     * Получить начисление 
     * @param integer $kind
     * @return Accrual
     */
    private function accuralFromPersonalRiviseKind($kind)
    {
        switch ($kind){
            case PersonalRevise::KIND_VACATION:
            case PersonalRevise::KIND_FINE:
            case PersonalRevise::KIND_OPEN_BALANCE:
            default:
                $accrual = $this->entityManager->getRepository(Accrual::class)
                    ->findOneBy(['payment' => Accrual::PAYMENT_PAYMENT]);
                return $accrual;
        }
        
        return;
    }
    
    /**
     * Добавить корректировку ЗП
     * @param array $data
     */
    public function addPersonalRevise($data)
    {
        $personalRevise = new PersonalRevise();
        $personalRevise->setAccrual($this->accuralFromPersonalRiviseKind($data['kind']));
        $personalRevise->setAmount($data['amount']);
        $personalRevise->setComment(empty($data['comment']) ? null:$data['comment']);
        $personalRevise->setCompany($data['company']);
        $personalRevise->setDateCreated(date('Y-m-d H:i:s'));
        $personalRevise->setDocDate($data['docDate']);
        $personalRevise->setDocNum(empty($data['docNum']) ? null:$data['docNum']);
        $personalRevise->setUser($data['user']);
        $personalRevise->setStatus($data['status']);
        $personalRevise->setKind($data['kind']);
        
        $this->entityManager->persist($personalRevise);
        $this->entityManager->flush();
        
        $this->repostPersonalRevise($personalRevise);
        
        return $personalRevise;
    }
    
    /**
     * Изменить корректировку ЗП
     * @param PersonalRevise $personalRevise
     * @param array $data
     */
    public function updatePersonalRevise($personalRevise, $data)
    {
        $personalRevise->setAccrual($this->accuralFromPersonalRiviseKind($data['kind']));
        $personalRevise->setAmount($data['amount']);
        $personalRevise->setComment(empty($data['comment']) ? null:$data['comment']);
        $personalRevise->setCompany($data['company']);
        $personalRevise->setDocDate($data['docDate']);
        $personalRevise->setDocNum(empty($data['docNum']) ? null:$data['docNum']);
        $personalRevise->setUser($data['user']);
        $personalRevise->setStatus($data['status']);
        $personalRevise->setKind($data['kind']);
        
        $this->entityManager->persist($personalRevise);
        $this->entityManager->flush();
        
        $this->repostPersonalRevise($personalRevise);
        
        return $personalRevise;
    }
    
    /**
     * Удалить корректировку ЗП
     * @param PersonalRevise $personalRevise
     * @return null
     */
    public function removePersonalRevise($personalRevise)
    {
        $this->removePersonalMutual(Movement::DOC_ZPRV, $personalRevise->getId());
        
        $this->entityManager->remove($personalRevise);
        $this->entityManager->flush();
        
        return;
    }
    
    /**
     * Сводный отчет по зп
     * @param date $startDate
     */
    public function totalReport($startDate)
    {
        $dateStart = date('Y-m-01', strtotime($startDate));
        
        $params = [
            'startDate' => $dateStart, 
            'endDate' => min(date('Y-m-d'), date('Y-m-t', strtotime($dateStart))),
            'summary' => true,
        ];
        
        $query = $this->entityManager->getRepository(PersonalMutual::class)
                        ->payslip($params);
        
        $data = $query->getResult();
        
        $result = "<p>Сводный расчетный лист за период:	{$params['startDate']} - {$params['endDate']}</p>";
        $result .= "<table class='table table-bordered table-hover table-condensed'>";
        $result .= "<tr>";
        $result .= "<td colspan='2'>Сотрудник</td>";
        $result .= "<td>Долг на начало</td>";
        $result .= "<td>Начислено</td>";
        $result .= "<td>Получено</td>";
        $result .= "<td>Долг на конец</td>";
        $result .= "</tr>";

        $totalStart = $totalIn = $totalOut = $totalEnd = 0;
        
        foreach ($data as $row){
            $userData = $this->entityManager->getRepository(User::class)
                ->find($row['user'])->toArray();
                    
            $params['startDate'] = date('2012-01-01');        
            $params['endDate'] = date('Y-m-t 23:59:59', strtotime($dateStart.' -1 day'));
            $params['user'] = $userData['id'];

            $balaceQuery = $this->entityManager->getRepository(PersonalMutual::class)
                            ->payslip($params);
            
            $balanceResult = $balaceQuery->getOneOrNullResult(2);
            $startBalance = empty($balanceResult['amount']) ? 0:-$balanceResult['amount'];
            $endBalance = $startBalance+$row['amountOut']-$row['amountIn'];

            $userReport = 'rl'.$userData['aplId'].date('Ym', strtotime($dateStart));
            
            $result .= "<tr>";                
            $result .= "<td><a href='/users/dd-report?report=$userReport'>".$userReport."</a></td>";                
            $result .= "<td>{$userData['fullName']}</td>";                
            $result .= "<td align='right'>".round($startBalance)."</td>";                
            $result .= "<td align='right'>".round($row['amountOut'])."</td>";                
            $result .= "<td align='right'>".round($row['amountIn'])."</td>";                
            $result .= "<td align='right'>".round($endBalance)."</td>";                
            $result .= "</tr>";    
            
            $totalStart += round($startBalance);
            $totalIn += round($row['amountOut']);
            $totalOut += round($row['amountIn']);
            $totalEnd += round($endBalance);
        }
        
        $result .= "<tr>";
        $result .= "<td colspan='2' align='right'>Итого</td>";
        $result .= "<td align='right'>$totalStart</td>";
        $result .= "<td align='right'>$totalIn</td>";
        $result .= "<td align='right'>$totalOut</td>";
        $result .= "<td align='right'>$totalEnd</td>";
        $result .= "</tr>";

        $result .= "</table>";
        $result .= "<p>".date('Y-m-d H:i:s')."</p>";
        
        $fileName = "./data/reports/zp".date('Ym', strtotime($dateStart)).".html";

        file_put_contents($fileName, $result);
        
        return;                
    }
}
