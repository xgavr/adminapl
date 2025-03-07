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
     * Ftp Manager
     * @var \Admin\Service\FtpManager
     */
    private $ftpManager;
    
    /**
     * 
     * @var Accrual
     */
    private $taxNdflAccrual;

    public function __construct($entityManager, $adminManager, $taxManager, $ftpManager)
    {
        $this->entityManager = $entityManager;
        $this->adminManager = $adminManager;
        $this->taxManager = $taxManager;
        $this->ftpManager = $ftpManager;
        
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
        
        $personalAccrual = $this->entityManager->getRepository(PersonalAccrual::class)
                ->findForOrderCalculate($order);
        
        if ($personalAccrual && $order->getStatus() == Order::STATUS_SHIPPED && !$order->isComitentContract()){
            
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
            $orderCalculator->setRate($personalAccrual->getRate());
            $orderCalculator->setPositionNum($personalAccrual->getPersonal()->getPositionNum());
            
            $accrualAmount = (abs($base['amount'])-abs($base['baseAmount']))*($personalAccrual->getRate()/100)*$personalAccrual->getPersonal()->getPositionNum(); 
            
            $orderCalculator->setAccrualAmount($accrualAmount);

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
        
        $personalAccrual = $this->entityManager->getRepository(PersonalAccrual::class)
                ->findForOrderCalculate($vt->getOrder());
        
        if ($personalAccrual && $vt->getStatus() == Vt::STATUS_ACTIVE && !$vt->getOrder()->isComitentContract()){
            
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

            $orderCalculator->setRate($personalAccrual->getRate());
            $orderCalculator->setPositionNum($personalAccrual->getPersonal()->getPositionNum());
            
            $accrualAmount = (-abs($base['amount']) + abs($base['baseAmount'])) *($personalAccrual->getRate()/100)*$personalAccrual->getPersonal()->getPositionNum(); 
            
            $orderCalculator->setAccrualAmount($accrualAmount);

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
        $dateReport = $dateCalculation;
        while ($dateCalculation <= date('Y-m-d')){
            if ($dateCalculation >= date('2024-01-01')){
                $this->dateCalculation($dateCalculation);                
            }
                        
            $dateCalculation = date('Y-m-d', strtotime($dateCalculation .' +1 day'));
        }
        
        $this->totalReport($dateReport); //прошлый месяц
        $this->totalReport(date('Y-m-d')); // текущий месяц
        
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
        $personalMutual->setAmount(PersonalMutual::getKindAmountFromPersonalRevise($personalRevise));
        $personalMutual->setCompany($personalRevise->getCompany());
        $personalMutual->setDateOper($personalRevise->getDocDate());
        $personalMutual->setDocId($personalRevise->getId());
        $personalMutual->setDocKey($personalRevise->getLogKey());
        $personalMutual->setDocStamp($docStamp);
        $personalMutual->setDocType(Movement::DOC_ZPRV);
        $personalMutual->setStatus(PersonalMutual::getStatusFromPersonalRevise($personalRevise));
        $personalMutual->setUser($personalRevise->getUser());
        $personalMutual->setKind(PersonalMutual::getKindFromPersonalRevise($personalRevise));
        $personalMutual->setAccrual($personalRevise->getAccrual());
        
        $this->entityManager->persist($personalMutual);
        $this->entityManager->flush();
                
        if ($personalMutual->getAccrual()->getPayment() == Accrual::PAYMENT_ONE_TIME){
            $this->taxManager->repostPersonalReviseEsn($personalRevise, $docStamp);
        }    

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
                $accrual = $this->entityManager->getRepository(Accrual::class)
                    ->findOneBy(['payment' => Accrual::PAYMENT_AVERAGE]);
                return $accrual;
            case PersonalRevise::KIND_BONUS:
                $accrual = $this->entityManager->getRepository(Accrual::class)
                    ->findOneBy(['payment' => Accrual::PAYMENT_ONE_TIME]);
                return $accrual;
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
        $personalRevise->setVacationFrom(empty($data['vacationFrom']) ? null:$data['vacationFrom']);
        $personalRevise->setVacationTo(empty($data['vacationTo']) ? null:$data['vacationTo']);
        $personalRevise->setInfo(empty($data['info']) ? null:$data['info']);
        
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
        $personalRevise->setVacationFrom(empty($data['vacationFrom']) ? null:$data['vacationFrom']);
        $personalRevise->setVacationTo(empty($data['vacationTo']) ? null:$data['vacationTo']);
        $personalRevise->setInfo(empty($data['info']) ? null:$data['info']);
        
        $this->entityManager->persist($personalRevise);
        $this->entityManager->flush();
        
        $this->repostPersonalRevise($personalRevise);
        
        return $personalRevise;
    }
    
    /**
     * Update PersonalRevise status.
     * @param PersonalRevise $revise
     * @param integer $status
     * @return integer
     */
    public function updateReviseStatus($revise, $status)            
    {

        $revise->setStatus($status);

        $this->entityManager->persist($revise);
        $this->entityManager->flush();
        return;
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
        
        $result = '<style>'
                . '.table-bordered tr td{'
                . 'border: 1px solid #ddd;'
                . '} '
                . '.panel-body{'
                . 'padding: 15px;'
                . '}'
                . '</style>'.PHP_EOL;
        
        $result .= "<div class='panel-body'></div>".PHP_EOL;
        $result .= "<div>Сводный расчетный лист за период:	{$params['startDate']} - {$params['endDate']}</div>".PHP_EOL;
        $result .= "<div class='panel-body'></div>".PHP_EOL;
        $result .= "<table class='table table-bordered table-hover table-condensed'>".PHP_EOL;
        $result .= "<tr>".PHP_EOL;
        $result .= "<td colspan='2' align='center' style='font-weight: bold;'>Сотрудник</td>".PHP_EOL;
        $result .= "<td align='center' style='font-weight: bold;'>Долг на начало</td>".PHP_EOL;
        $result .= "<td align='center' style='font-weight: bold;'>Начислено</td>".PHP_EOL;
        $result .= "<td align='center' style='font-weight: bold;'>Получено</td>".PHP_EOL;
        $result .= "<td align='center' style='font-weight: bold;'>Долг на конец</td>".PHP_EOL;
        $result .= "</tr>".PHP_EOL;

        $totalStart = $totalIn = $totalOut = $totalEnd = 0;
        
        foreach ($data as $row){
            $user = $this->entityManager->getRepository(User::class)
                ->find($row['user']);
                    
            $params['startDate'] = date('2012-01-01');        
            $params['endDate'] = date('Y-m-t 23:59:59', strtotime($dateStart.' -1 day'));
            $params['user'] = $user->getId();

            $balaceQuery = $this->entityManager->getRepository(PersonalMutual::class)
                            ->payslip($params);
            
            $balanceResult = $balaceQuery->getOneOrNullResult(2);
            $startBalance = empty($balanceResult['amount']) ? 0:-$balanceResult['amount'];
            $endBalance = $startBalance+$row['amountOut']-$row['amountIn'];

            $userReport = 'rl'.$user->getAplId().date('Ym', strtotime($dateStart));
            
            $outStr = (round($row['amountOut'])) ? round($row['amountOut']):'';
            $inStr = (round($row['amountIn'])) ? round($row['amountIn']):'';
            
            $result .= "<tr>".PHP_EOL;                
            $result .= "<td><a href='/admin/buh/dd-reports/report/$userReport'>".$userReport."</a></td>".PHP_EOL;                
            $result .= "<td>{$user->getFullName()}</td>".PHP_EOL;                
            $result .= "<td align='right'>".round($startBalance)."</td>".PHP_EOL;                
            $result .= "<td align='right'>$outStr</td>".PHP_EOL;                
            $result .= "<td align='right'>$inStr</td>".PHP_EOL;                
            $result .= "<td align='right'>".round($endBalance)."</td>".PHP_EOL;                
            $result .= "</tr>".PHP_EOL;    
            
            $totalStart += round($startBalance);
            $totalIn += round($row['amountOut']);
            $totalOut += round($row['amountIn']);
            $totalEnd += round($endBalance);
            
            $this->userReport($user, $dateStart);
        }
        
        $result .= "<tr>".PHP_EOL;
        $result .= "<td colspan='2' align='right'>Итого:</td>".PHP_EOL;
        $result .= "<td align='right'>$totalStart</td>".PHP_EOL;
        $result .= "<td align='right'>$totalIn</td>".PHP_EOL;
        $result .= "<td align='right'>$totalOut</td>".PHP_EOL;
        $result .= "<td align='right'>$totalEnd</td>".PHP_EOL;
        $result .= "</tr>".PHP_EOL;

        $result .= "</table>".PHP_EOL;
        $result .= "<p>".date('d.m.Y H:i:s')."</p>".PHP_EOL;
        
        $fileName = "./data/reports/zp".date('Ym', strtotime($dateStart)).".html";

        file_put_contents($fileName, $result);
        
        $this->ftpManager->putReportToApl([
            'source_file' => $fileName,
            'dest_file' => basename($fileName),
        ]);
        
        return;                
    }
    
    /**
     * 
     * @param User $user
     * @param date $startDate
     */
    public function userReport($user, $startDate)
    {
        $dateStart = date('Y-m-01', strtotime($startDate));
        $dateEnd = min(date('Y-m-d'), date('Y-m-t', strtotime($dateStart)));
        
        $result = '<style>'
                . '.table-bordered{'
                . 'width: 100%; '
                . '}'.PHP_EOL
                . '.table-bordered tr td{'
                . 'border: 1px solid #ddd; '
                . '}'.PHP_EOL
                . '.panel-body{'
                . 'padding: 15px;'
                . '}'
                . '</style>'.PHP_EOL;
        
        $result .= "<div class='panel-body'></div>".PHP_EOL;
        $result .= "<div>Расчетный лист за период: ".date('d.m.Y', strtotime($dateStart))." - ".date('d.m.Y', strtotime($dateEnd));
        $result .= "    (<span>".date('d.m.Y H:i:s')."</span>)".PHP_EOL;
        $result .= "</div>".PHP_EOL;
        $result .= "<div class='panel-body'></div>".PHP_EOL;
        $result .= "<div style='font-weight: bold;'>{$user->getFullName()}</div>".PHP_EOL;
        
        
        $params = [
            'startDate' => $dateStart, 
            'endDate' => $dateEnd,
            'summary' => false,
            'user' => $user->getId(),
        ];
        
        
        $query = $this->entityManager->getRepository(PersonalMutual::class)
                        ->payslip($params);
        
        $data = $query->getResult();
        
        $params['startDate'] = date('2012-01-01');        
        $params['endDate'] = date('Y-m-t 23:59:59', strtotime($dateStart.' -1 day'));
        $params['summary'] = true;

        $balaceQuery = $this->entityManager->getRepository(PersonalMutual::class)
                        ->payslip($params);

        $balanceResult = $balaceQuery->getOneOrNullResult(2);
        $startBalance = empty($balanceResult['amount']) ? 0:-round($balanceResult['amount']);

        $result .= "<div class='panel-body'></div>".PHP_EOL;
        $result .= "<div>Начисления:</div>".PHP_EOL;
        $result .= "<table class='table table-bordered table-hover table-condensed'>".PHP_EOL;
        $result .= "<tr>".PHP_EOL;
        if ($startBalance >= 0){
            $result .= "<td colspan='3' align='right' style=''>Долг за предприятием на ".date('d.m.Y', strtotime($dateStart)).":</td>".PHP_EOL;
        } else {
            $result .= "<td colspan='3' align='right' style=''>Долг за сотрудником на ".date('d.m.Y', strtotime($dateStart)).":</td>".PHP_EOL;
        }
        $result .= "<td align='right' style=''>$startBalance</td>".PHP_EOL;
        $result .= "</tr>".PHP_EOL;
        $result .= "<tr>".PHP_EOL;
        $result .= "<td align='center' style='font-weight: bold;'>Вид расчета</td>".PHP_EOL;
        $result .= "<td align='center' style='font-weight: bold;'>Размер</td>".PHP_EOL;
        $result .= "<td align='center' style='font-weight: bold;'>Начислено</td>".PHP_EOL;
        $result .= "<td align='center' style='font-weight: bold;'>Получено</td>".PHP_EOL;
        $result .= "</tr>".PHP_EOL;
        
        $totalIn = $totalOut = $totalEnd = $endBalance = 0;

        foreach ($data as $row){
            $accrual = $this->entityManager->getRepository(Accrual::class)
                ->find($row['accrual']);
            
            if ($accrual->getPayment() == Accrual::PAYMENT_TAX){
                $row['amountOut'] = -$row['amountIn'];
                $row['amountIn'] = 0;
            }

            $outStr = (round($row['amountOut'])) ? round($row['amountOut']):'';
            $inStr = (round($row['amountIn'])) ? round($row['amountIn']):'';
            
            $result .= "<tr>".PHP_EOL;
            $result .= "<td>{$accrual->getName()}</td>".PHP_EOL;
            $result .= "<td></td>".PHP_EOL;
            $result .= "<td align='right'>$outStr</td>".PHP_EOL;
            $result .= "<td align='right'>$inStr</td>".PHP_EOL;
            $result .= "</tr>".PHP_EOL;
            
            $totalOut += round($row['amountOut']);
            $totalIn += round($row['amountIn']);            
        }
        
        $endBalance = $startBalance + $totalOut - $totalIn;
        
        $result .= "<tr>".PHP_EOL;
        $result .= "<td colspan='2' align='right' style='font-weight: bold;'>Итого:</td>".PHP_EOL;
        $result .= "<td align='right' style=''>$totalOut</td>".PHP_EOL;
        $result .= "<td align='right' style=''>$totalIn</td>".PHP_EOL;
        $result .= "</tr>".PHP_EOL;

        $result .= "<tr>".PHP_EOL;
        if ($endBalance >= 0){
            $result .= "<td colspan='3' align='right' style=''>Долг за предприятием на ".date('d.m.Y', strtotime($dateEnd)).":</td>".PHP_EOL;
            if (date('Ym') == date('Ym', strtotime($dateEnd))){
                $result .= "<td align='right' style='font-size: large; color: green; font-weight: bold;'>$endBalance</td>".PHP_EOL;
            } else {
                $result .= "<td align='right' style=''>$endBalance</td>".PHP_EOL;                
            }    
        } else {
            $result .= "<td colspan='3' align='right' style=''>Долг за сотрудником на ".date('d.m.Y', strtotime($dateEnd)).":</td>".PHP_EOL;
            if (date('Ym') == date('Ym', strtotime($dateEnd))){
                $result .= "<td align='right' style='font-size: large; color: red; font-weight: bold;'>$endBalance</td>".PHP_EOL;
            } else {    
                $result .= "<td align='right' style=''>$endBalance</td>".PHP_EOL;
            }    
        }
        $result .= "</tr>".PHP_EOL;
        $result .= "</table>".PHP_EOL;
        
        
        $paymentAccrual = $this->entityManager->getRepository(Accrual::class)
                ->findOneBy(['payment' => Accrual::PAYMENT_PAYMENT]);
        
        $mutualParams = [
            'user' => $user->getId(), 'accrual' => $paymentAccrual->getId(),
            'startDate' => $dateStart, 'endDate' => $dateEnd,             
            'sort' => 'dateOper', 'order' => 'asc', 'status' => PersonalMutual::STATUS_ACTIVE,
        ];
        
        $mutualQuery = $this->entityManager->getRepository(PersonalMutual::class)
                        ->findMutuals($mutualParams);
        
        $mutuals = $mutualQuery->getResult();
        
        $mutualTotal = 0;
         
        $result .= "<div class='panel-body'></div>".PHP_EOL;
        $result .= "<div>Выплаты:</div>".PHP_EOL;
        $result .= "<table class='table table-bordered table-hover table-condensed'>".PHP_EOL;
        $result .= "<tr>".PHP_EOL;
        $result .= "<td align='center' style='font-weight: bold;'>Дата</td>".PHP_EOL;
        $result .= "<td align='center' style='font-weight: bold;'>Документ</td>".PHP_EOL;
        $result .= "<td align='center' style='font-weight: bold;'>Сумма</td>".PHP_EOL;
        $result .= "</tr>".PHP_EOL;
        
        foreach ($mutuals as $mutual){
            
            switch ($mutual->getDocType()){
                case Movement::DOC_CASH: 
                    $docName = 'Выдано из кассы/подотчета №'.$mutual->getDocId();
                    break;
                case Movement::DOC_ST: 
                    $docName = 'Списание товаров №'.$mutual->getDocId();
                    break;
                default:
                    $docName = 'Документ №'.$mutual->getDocId();
            }
            
            $result .= "<tr>".PHP_EOL;
            $result .= "<td>".date('d.m', strtotime($mutual->getDateOper()))."</td>".PHP_EOL;
            $result .= "<td>$docName</td>".PHP_EOL;
            $result .= "<td align='right'>".round($mutual->getAmount())."</td>".PHP_EOL;
            $result .= "</tr>".PHP_EOL;  
            
            $mutualTotal += round($mutual->getAmount());
        }
        
        $result .= "<tr>".PHP_EOL;
        $result .= "<td colspan='2' align='right' style='font-weight: bold;'>Итого:</td>".PHP_EOL;
        $result .= "<td align='right' style='font-weight: bold;'>$mutualTotal</td>".PHP_EOL;
        $result .= "</tr>".PHP_EOL;
        $result .= "</table>".PHP_EOL;
        
        $orderParams = [
            'user' => $user->getId(), 'status' => OrderCalculator::STATUS_ACTIVE,
            'startDate' => $dateStart, 'endDate' => $dateEnd,             
            'sort' => 'dateOper', 'order' => 'asc', 
        ];
        
        $orderQuery = $this->entityManager->getRepository(OrderCalculator::class)
                        ->findOrderCalculators($orderParams);
        
        $orderCalcs = $orderQuery->getResult();
        
        if (count($orderCalcs)){
            $amountTotal = $deliveryTotal = $baseTotal = $incomeTotal = $accrualTotal = 0;

            $result .= "<div class='panel-body'></div>".PHP_EOL;
            $result .= "<div>Расшифровка продаж за период: ".date('d.m.Y', strtotime($dateStart))." - ".date('d.m.Y', strtotime($dateEnd))."</div>".PHP_EOL;
            $result .= "<table class='table table-bordered table-hover table-condensed'>".PHP_EOL;
            $result .= "<tr>".PHP_EOL;
            $result .= "<td align='center' style='font-weight: bold;'>Дата</td>".PHP_EOL;
            $result .= "<td align='center' style='font-weight: bold;'>Номер заказа АПЛ</td>".PHP_EOL;
            $result .= "<td align='center' style='font-weight: bold;'>Офис</td>".PHP_EOL;
            $result .= "<td align='center' style='font-weight: bold;'>Сумма продажи</td>".PHP_EOL;
            $result .= "<td align='center' style='font-weight: bold;'>Доставка</td>".PHP_EOL;
            $result .= "<td align='center' style='font-weight: bold;'>Закупка</td>".PHP_EOL;
            $result .= "<td align='center' style='font-weight: bold;'>Доход</td>".PHP_EOL;
            $result .= "<td align='center' style='font-weight: bold;'>Процент</td>".PHP_EOL;
            $result .= "<td align='center' style='font-weight: bold;'>Начислено</td>".PHP_EOL;
            $result .= "</tr>".PHP_EOL;

            foreach ($orderCalcs as $orderCalc){

                $result .= "<tr>".PHP_EOL;
                $result .= "<td>".date('d.m', strtotime($orderCalc->getDateOper()))."</td>".PHP_EOL;
                $result .= "<td align='right'>{$orderCalc->getOrder()->getAplId()}</td>".PHP_EOL;
                $result .= "<td>{$orderCalc->getOrder()->getOffice()->getName()}</td>".PHP_EOL;
                $result .= "<td align='right'>".round($orderCalc->getAmount())."</td>".PHP_EOL;
                $result .= "<td align='right'>".round($orderCalc->getDeliveryAmount())."</td>".PHP_EOL;
                $result .= "<td align='right'>".round($orderCalc->getBaseAmount())."</td>".PHP_EOL;
                $result .= "<td align='right'>".round($orderCalc->getAmount()-$orderCalc->getBaseAmount())."</td>".PHP_EOL;
                $result .= "<td align='right'>".$orderCalc->getRate()."</td>".PHP_EOL;
                $result .= "<td align='right'>".round($orderCalc->getAccrualAmount())."</td>".PHP_EOL;
                $result .= "</tr>".PHP_EOL;  

                $amountTotal += round($orderCalc->getAmount());
                $deliveryTotal += round($orderCalc->getDeliveryAmount());
                $baseTotal += round($orderCalc->getBaseAmount());
                $incomeTotal += round($orderCalc->getAmount() - $orderCalc->getBaseAmount());
                $accrualTotal += round($orderCalc->getAccrualAmount());
            }

            $result .= "<tr>".PHP_EOL;
            $result .= "<td colspan='3' align='right' style='font-weight: bold;'>Итого:</td>".PHP_EOL;
            $result .= "<td align='right' style='font-weight: bold;'>$amountTotal</td>".PHP_EOL;
            $result .= "<td align='right' style='font-weight: bold;'>$deliveryTotal</td>".PHP_EOL;
            $result .= "<td align='right' style='font-weight: bold;'>$baseTotal</td>".PHP_EOL;
            $result .= "<td align='right' style='font-weight: bold;'>$incomeTotal</td>".PHP_EOL;
            $result .= "<td align='right' style='font-weight: bold;'></td>".PHP_EOL;
            $result .= "<td align='right' style='font-weight: bold;'>$accrualTotal</td>".PHP_EOL;
            $result .= "</tr>".PHP_EOL;
            $result .= "</table>".PHP_EOL;
        }    
                
        $fileName = "./data/reports/rl".$user->getAplId().date('Ym', strtotime($dateStart)).".html";

        file_put_contents($fileName, $result); 

        $this->ftpManager->putReportToApl([
            'source_file' => $fileName,
            'dest_file' => basename($fileName),
        ]);
        
        return;
    }
    
    /**
     * Рассчитать отпуск
     * @param Legal $company
     * @param User $user
     * @param date $from
     * @param date $to
     */
    public function vacation($company, $user, $from, $to)
    {
        $vacationFrom = new \DateTime($from);
        $vacationTo = new \DateTime($to);
        $period = $vacationFrom->diff($vacationTo)->days + 1;
                
        $baseTo = date('Y-m-t', strtotime($from.' - 1 month'));
        $baseFrom = date('Y-m-01', strtotime($baseTo.' - 6 month'));
//        var_dump($baseFrom, $baseTo); exit;
        $base = $this->entityManager->getRepository(DocCalculator::class)
                ->baseAverage($baseFrom, $baseTo, ['company' => $company, 'user' => $user]);
        
        $dayAverage = $base;
        
        $timeTo = new \DateTime($baseTo);
        $timeFrom = new \DateTime($baseFrom);
        $baseDays = $timeTo->diff($timeFrom)->days;
        if ($baseDays > 0){
            $dayAverage = $base/$baseDays;
        }  
        
        return [
            'baseFrom' => $baseFrom,
            'baseTo' => $baseTo,
            'base' => $base,
            'dayAverage' => $dayAverage,
            'period' => $period,
            'amount' => $dayAverage * $period
        ];
    }
}
