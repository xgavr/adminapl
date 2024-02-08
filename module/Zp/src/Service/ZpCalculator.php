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
    private $incomeTaxAccrual;

    public function __construct($entityManager, $adminManager, $taxManager)
    {
        $this->entityManager = $entityManager;
        $this->adminManager = $adminManager;
        $this->taxManager = $taxManager;
        
        $this->incomeTaxAccrual = $this->entityManager->getRepository(Accrual::class)
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
                
        if ($order->getStatus() == Order::STATUS_SHIPPED && !$order->isComissuionerContract()){
            
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
        
        if ($vt->getStatus() == Vt::STATUS_ACTIVE && !$vt->getOrder()->isComissuionerContract()){
            
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
     * Ужадить расчет
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
        $personalMutual->setKind(PersonalMutual::KIND_ACCRUAL);
        $personalMutual->setAccrual($docCalculator->getAccrual());
        
        $this->entityManager->persist($personalMutual);
                
        $incomeTax = $this->taxManager->repostDocCalculatorIncomeTax($docCalculator, $docStamp);
        
        $personalMutual = new PersonalMutual();
        $personalMutual->setAmount(abs($incomeTax->getAmount()));
        $personalMutual->setCompany($docCalculator->getCompany());
        $personalMutual->setDateOper($docCalculator->getDateOper());
        $personalMutual->setDocId($docCalculator->getId());
        $personalMutual->setDocKey($docCalculator->getLogKey());
        $personalMutual->setDocStamp($docStamp);
        $personalMutual->setDocType(Movement::DOC_ZP);
        $personalMutual->setStatus(PersonalMutual::getStatusFromDocCalculator($docCalculator));
        $personalMutual->setUser($docCalculator->getUser());
        $personalMutual->setKind(PersonalMutual::KIND_DEDUCTION);
        $personalMutual->setAccrual($this->incomeTaxAccrual);

        $this->entityManager->persist($personalMutual);
        $this->entityManager->flush();

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
                    $personalMutual->setKind(PersonalMutual::KIND_ACCRUAL);
                    $personalMutual->setAccrual($this->entityManager->getRepository(Accrual::class)
                            ->findOneBy(['payment' => Accrual::PAYMENT_COURIER]));

                    $this->entityManager->persist($personalMutual);
                    
                    $incomeTax = $this->taxManager->repostCashDocIncomeTax($cashDoc, $docStamp);

                    $personalMutual = new PersonalMutual();
                    $personalMutual->setAmount(abs($incomeTax->getAmount()));
                    $personalMutual->setCompany($cashDoc->getCompany());
                    $personalMutual->setDateOper($cashDoc->getDateOper());
                    $personalMutual->setDocId($cashDoc->getId());
                    $personalMutual->setDocKey($cashDoc->getLogKey());
                    $personalMutual->setDocStamp($docStamp);
                    $personalMutual->setDocType(Movement::DOC_CASH);
                    $personalMutual->setStatus(PersonalMutual::getStatusFromCashDoc($cashDoc));
                    $personalMutual->setUser($cashDoc->getUser());
                    $personalMutual->setKind(PersonalMutual::KIND_DEDUCTION);
                    $personalMutual->setAccrual($this->incomeTaxAccrual);

                    $this->entityManager->persist($personalMutual);

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
}
