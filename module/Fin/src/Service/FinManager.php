<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Fin\Service;

use Fin\Entity\FinOpu;
use Company\Entity\Legal;
use Company\Entity\Cost;
use Zp\Entity\PersonalMutual;
use Company\Entity\Tax;

/**
 * Description of FinManager
 * 
 * @author Daddy
 */
class FinManager {
    
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    private $adminManager;

    public function __construct($entityManager, $adminManager)
    {
        $this->entityManager = $entityManager;
        $this->adminManager = $adminManager;
    }
    
    /**
     * Add Opu
     * @param array $data
     * @return FinOpu
     */
    public function addOpu($data)
    {
        $opu = new FinOpu();
        $opu->setCompany($data['company']);
        $opu->setCostFix(empty($data['costFix']) ? 0:$data['costFix']);
        $opu->setCostRetail(empty($data['costRetail']) ? 0:$data['costRetail']);
        $opu->setCostTotal(empty($data['costTotal']) ? 0:$data['costTotal']);
        $opu->setCostTp(empty($data['costTp']) ? 0:$data['costTp']);
        $opu->setFund(empty($data['fund']) ? 0:$data['fund']);
        $opu->setIncomeRetail(empty($data['incomeRetail']) ? 0:$data['incomeRetail']);
        $opu->setIncomeTp(empty($data['incomeTp']) ? 0:$data['incomeTp']);
        $opu->setIncomeTotal(empty($data['incomeTotal']) ? 0:$data['incomeTotal']);
        $opu->setPeriod($data['period']);
        $opu->setProfit(empty($data['profit']) ? 0:$data['profit']);
        $opu->setPurchaseRetail(empty($data['purchaseRetail']) ? 0:$data['purchaseRetail']);
        $opu->setPurchaseTotal(empty($data['purchaseTotal']) ? 0:$data['purchaseTotal']);
        $opu->setPurchaseTp(empty($data['purchaseTp']) ? 0:$data['purchaseTp']);
        $opu->setRevenueRetail(empty($data['revenueRetail']) ? 0:$data['revenueRetail']);
        $opu->setRevenueTotal(empty($data['revenueTotal']) ? 0:$data['revenueTotal']);
        $opu->setRevenueTp(empty($data['revenueTp']) ? 0:$data['revenueTp']);
        $opu->setStatus(empty($data['status']) ? FinOpu::STATUS_PLAN:$data['status']);
        $opu->setTax(empty($data['tax']) ? 0:$data['tax']);
        $opu->setZpAdm(empty($data['zpAdm']) ? 0:$data['zpAdm']);
        $opu->setZpRetail(empty($data['zpRetail']) ? 0:$data['zpRetail']);
        $opu->setZpTotal(empty($data['zpTotal']) ? 0:$data['zpTotal']);
        $opu->setZpTp(empty($data['zpTp']) ? 0:$data['zpTp']);
        $opu->setEsn(empty($data['esn']) ? 0:$data['esn']);
        $opu->setProfitNet(empty($data['profitNet']) ? 0:$data['profitNet']);
        $opu->setMarginRetail(empty($data['marginRetail']) ? 0:$data['marginRetail']);
        $opu->setMarginTp(empty($data['marginTp']) ? 0:$data['marginTp']);
        
        $this->entityManager->persist($opu);
        $this->entityManager->flush();
        
        return $opu;
    }
    
    
    /**
     * Найти ОПУ
     * @param date $period
     * @param integer $companyId
     * @param integer $status
     * @return FinOpu
     */
    private function getFinOpu($period, $companyId, $status)
    {
        $result = $this->entityManager->getRepository(FinOpu::class)
                ->findOneBy(['period' => $period, 'company' => $companyId, 'status' => $status]);
        if (empty($result)){
            $result = $this->addOpu(['period' => $period, 'company' => $companyId, 'status' => $status]);
        }
        
        return $result;
    }

    /**
     * Update opu
     * @param FinOpu $opu
     * @param array $data
     * @return FinOpu
     */
    public function updateOpu($opu, $data)
    {
        $opu->setCompany($data['company']);
        $opu->setCostFix($data['costFix']);
        $opu->setCostRetail($data['costRetail']);
        $opu->setCostTotal($data['costTotal']);
        $opu->setCostTp($data['costTp']);
        $opu->setFund($data['fund']);
        $opu->setIncomeRetail($data['incomeRetail']);
        $opu->setIncomeTp($data['incomeTp']);
        $opu->setIncomeTotal($data['incomeTotal']);
        $opu->setPeriod($data['period']);
        $opu->setProfit($data['profit']);
        $opu->setPurchaseRetail($data['purchaseRetail']);
        $opu->setPurchaseTotal($data['purchaseTotal']);
        $opu->setPurchaseTp($data['purchaseTp']);
        $opu->setRevenueRetail($data['revenueRetail']);
        $opu->setRevenueTotal($data['revenueTotal']);
        $opu->setRevenueTp($data['revenueTp']);
        $opu->setStatus($data['status']);
        $opu->setTax($data['tax']);
        $opu->setZpAdm($data['zpAdm']);
        $opu->setZpRetail($data['zpRetail']);
        $opu->setZpTotal($data['zpTotal']);
        $opu->setZpTp($data['zpTp']);
        $opu->setMarginRetail($data['marginReatail']);
        $opu->setMarginTp($data['marginTp']);
        
        $this->entityManager->persist($opu);
        $this->entityManager->flush();
        
        return $opu;
    }
    
    /**
     * Удалить Opu
     * @param FinOpu $opu
     * @return null
     */
    public function removeOpu($opu)
    {
        $this->entityManager->remove($opu);
        
        return;
    }
    
    /**
     * Шаблон на сводных retail
     * 
     * @param date $startDate
     * @param date $endDate
     * @param Legal $company
     * 
     * @return array
     */
    public function emptyRetailYear($startDate, $endDate, $company)
    {
        $result = [];
        $retails = $this->entityManager->getRepository(FinOpu::class)
                ->findActiveUser($startDate, $endDate, $company);
        
        foreach ($retails as $retail){
             $resultRow['key'] = $retail['userId'];
             $resultRow['mark'] = $retail['userName'];
             $resultRow['01'] = 0;
             $resultRow['02'] = 0;
             $resultRow['03'] = 0;
             $resultRow['04'] = 0;
             $resultRow['05'] = 0;
             $resultRow['06'] = 0;
             $resultRow['07'] = 0;
             $resultRow['08'] = 0;
             $resultRow['09'] = 0;
             $resultRow['10'] = 0;
             $resultRow['11'] = 0;
             $resultRow['12'] = 0;
             $resultRow['13'] = 0;
             $result[$retail['userId']] = $resultRow;
        }
        
        return $result;        
    }
    
    /**
     * Рассчитать розничную выручку за период
     * @param date $period
     */
    public function incomeRetailRevenue($period)
    {
        $startDate = date('Y-01-01', strtotime($period));
        $endDate = date('Y-12-31 23:59:59', strtotime($period));
//        var_dump($startDate, $endDate); exit;
        $retailRevenues = $this->entityManager->getRepository(FinOpu::class)
                ->retailRevenue($startDate, $endDate);
        
        foreach ($retailRevenues as $row){
            $company = $this->entityManager->getRepository(Legal::class)
                    ->find($row['companyId']);
            
            $finOpu = $this->getFinOpu($row['period'], $company, FinOpu::STATUS_FACT);
            
            $finOpu->setRevenueRetail(abs($row['revenue']));            
            $finOpu->setRevenueTotal($finOpu->getRevenueRetail() + $finOpu->getRevenueTp());
            
            $this->entityManager->persist($finOpu);
        }
        
        $this->entityManager->flush();
    }

    /**
     * Рассчитать розничный доход за период
     * @param date $period
     */
    public function incomeRetail($period)
    {
        $startDate = date('Y-01-01', strtotime($period));
        $endDate = date('Y-12-31 23:59:59', strtotime($period));
//        var_dump($startDate, $endDate); exit;
        $retailIncomes = $this->entityManager->getRepository(FinOpu::class)
                ->retailIncome($startDate, $endDate);
        
        foreach ($retailIncomes as $row){
            $company = $this->entityManager->getRepository(Legal::class)
                    ->find($row['companyId']);
            
            $finOpu = $this->getFinOpu($row['period'], $company, FinOpu::STATUS_FACT);
            
            $finOpu->setPurchaseRetail(abs($row['purchase']));
            $finOpu->setIncomeRetail($finOpu->getRevenueRetail() - abs($row['purchase']));
            if (abs($row['revenue'])){
                $finOpu->setMarginRetail((abs($row['revenue']) - abs($row['purchase']))*100/abs($row['revenue']));
            }    
            
            $finOpu->setIncomeTotal($finOpu->getIncomeRetail() + $finOpu->getIncomeTp());
            
            $this->entityManager->persist($finOpu);
        }
        
        $this->entityManager->flush();
    }

    /**
     * Рассчитать доход ТП за период
     * @param date $period
     */
    public function incomeTp($period)
    {
        $startDate = date('Y-01-01', strtotime($period));
        $endDate = date('Y-12-31 23:59:59', strtotime($period));
        
        $tpIncomes = $this->entityManager->getRepository(FinOpu::class)
                ->tpIncome($startDate, $endDate);
        
        foreach ($tpIncomes as $row){
            $company = $this->entityManager->getRepository(Legal::class)
                    ->find($row['companyId']);
            
            $finOpu = $this->getFinOpu($row['period'], $company, FinOpu::STATUS_FACT);
            
            $finOpu->setRevenueTp(abs($row['revenue']));
            $finOpu->setPurchaseTp(abs($row['purchase']));
            $finOpu->setCostTp(abs($row['cost']));
            $finOpu->setIncomeTp(abs($row['revenue']) - abs($row['purchase']) - abs($row['cost']));
            if (abs($row['revenue'])){
                $finOpu->setMarginTp((abs($row['revenue']) - abs($row['purchase']))*100/abs($row['revenue']));
            }    
            
            $finOpu->setRevenueTotal($finOpu->getRevenueRetail() + $finOpu->getRevenueTp());
            $finOpu->setIncomeTotal($finOpu->getIncomeRetail() + $finOpu->getIncomeTp());

            $this->entityManager->persist($finOpu);
        }
        
        $this->entityManager->flush();
    }
    
    /**
     * Шаблон на сводных расходов
     * 
     * @param date $startDate
     * @param date $endDate
     * @param Legal $company
     * 
     * @return array
     */
    public function emptyCostYear($startDate, $endDate, $company)
    {
        $result = [];
        $costs = $this->entityManager->getRepository(FinOpu::class)
                ->findActiveCosts($startDate, $endDate, $company);
        
        foreach ($costs as $cost){
             $resultRow['key'] = $cost['costId'];
             $resultRow['mark'] = $cost['costName'];
             $resultRow['01'] = 0;
             $resultRow['02'] = 0;
             $resultRow['03'] = 0;
             $resultRow['04'] = 0;
             $resultRow['05'] = 0;
             $resultRow['06'] = 0;
             $resultRow['07'] = 0;
             $resultRow['08'] = 0;
             $resultRow['09'] = 0;
             $resultRow['10'] = 0;
             $resultRow['11'] = 0;
             $resultRow['12'] = 0;
             $resultRow['13'] = 0;
             $result[$cost['costId']] = $resultRow;
        }
        
        return $result;        
    }
    
    /**
     * Рассчитать расходы за период
     * @param date $period
     */
    public function costs($period)
    {
        $startDate = date('Y-01-01', strtotime($period));
        $endDate = date('Y-12-31 23:59:59', strtotime($period));
        
        $costs = $this->entityManager->getRepository(FinOpu::class)
                ->costs($startDate, $endDate);

        foreach ($costs as $row){
            $company = $this->entityManager->getRepository(Legal::class)
                    ->find($row['companyId']);
            
            $finOpu = $this->getFinOpu($row['period'], $company, FinOpu::STATUS_FACT);
            
            switch ($row['kindFin']){
                case Cost::KIND_FIN_FIX:
                    $finOpu->setCostFix(abs($row['amount']));
                    break;
                case Cost::KIND_FIN_EXP:
                    $finOpu->setCostRetail(abs($row['amount']));
                    break;
                default:     
            }
            
            $finOpu->setCostTotal($finOpu->getCostFix() + $finOpu->getCostRetail());
            $this->entityManager->persist($finOpu);
        }
        
        $this->entityManager->flush();
    }
    
    /**
     * Шаблон на сводных zp
     * 
     * @param date $startDate
     * @param date $endDate
     * @param Legal $company
     * 
     * @return array
     */
    public function emptyZpYear($startDate, $endDate, $company)
    {
        $result = [];
        $zps = $this->entityManager->getRepository(FinOpu::class)
                ->findActiveZp($startDate, $endDate, $company);
        
        foreach ($zps as $zp){
             $resultRow['key'] = $zp['userId'];
             $resultRow['mark'] = $zp['userName'];
             $resultRow['01'] = 0;
             $resultRow['02'] = 0;
             $resultRow['03'] = 0;
             $resultRow['04'] = 0;
             $resultRow['05'] = 0;
             $resultRow['06'] = 0;
             $resultRow['07'] = 0;
             $resultRow['08'] = 0;
             $resultRow['09'] = 0;
             $resultRow['10'] = 0;
             $resultRow['11'] = 0;
             $resultRow['12'] = 0;
             $resultRow['13'] = 0;
             $result[$zp['userId']] = $resultRow;
        }
        
        return $result;        
    }
    
    /**
     * Рассчитать зп за период
     * @param date $period
     */
    public function zp($period)
    {
        $startDate = date('Y-01-01', strtotime($period));
        $endDate = date('Y-12-31 23:59:59', strtotime($period));
        
        $zps = $this->entityManager->getRepository(FinOpu::class)
                ->zp($startDate, $endDate);

        foreach ($zps as $row){
            $company = $this->entityManager->getRepository(Legal::class)
                    ->find($row['companyId']);            
            $finOpu = $this->getFinOpu($row['period'], $company, FinOpu::STATUS_FACT);
            
            switch ($row['kind']){
                case PersonalMutual::KIND_ACCRUAL_ADM:
                    $finOpu->setZpAdm(abs($row['amount']));
                    break;
                case PersonalMutual::KIND_ACCRUAL_RETAIL:
                    $finOpu->setZpRetail(abs($row['amount']));
                    break;
                case PersonalMutual::KIND_ACCRUAL_TP:
                    $finOpu->setZpTp(abs($row['amount']));
                    break;
                default:     
            }
            
            $finOpu->setZpTotal($finOpu->getZpAdm() + $finOpu->getZpRetail() + $finOpu->getZpTp());
            $this->entityManager->persist($finOpu);
        }
        
        $this->entityManager->flush();
    }
        
    /**
     * Рассчитать ЕСН и налог на прибыль за период
     * @param date $period
     */
    public function taxes($period)
    {
        $startDate = date('Y-01-01', strtotime($period));
        $endDate = date('Y-12-31 23:59:59', strtotime($period));
        
        $esn = $this->entityManager->getRepository(FinOpu::class)
                ->esn($startDate, $endDate);
        
        foreach ($esn as $row){
            $company = $this->entityManager->getRepository(Legal::class)
                    ->find($row['companyId']);
            
            $finOpu = $this->getFinOpu($row['period'], $company, FinOpu::STATUS_FACT);
            
            $finOpu->setEsn(abs($row['amount']));
            
            $finOpu->setProfit($finOpu->getIncomeTotal() - $finOpu->getCostTotal() - $finOpu->getZpTotal() - $finOpu->getEsn());
            
            $taxInc = $this->entityManager->getRepository(Tax::class)
                    ->currentTax(Tax::KIND_PROFIT, $row['period']);
            
            $taxMin = $this->entityManager->getRepository(Tax::class)
                    ->currentTax(Tax::KIND_PROGIT_MIN, $row['period']);
            
            $finOpu->setTax(max($finOpu->getProfit() * $taxInc->getAmount()/100, $finOpu->getRevenueTotal() * $taxMin->getAmount()/100));

            $finOpu->setProfitNet($finOpu->getProfit() - $finOpu->getTax());

            $this->entityManager->persist($finOpu);
        }
        
        $this->entityManager->flush();
    }
        
    /**
     * Посчитать опу за период
     * @param date $period
     */
    public function calculate($period)
    {
        $this->incomeRetailRevenue($period);
        $this->incomeRetail($period);
        $this->incomeTp($period);
        $this->costs($period);
        $this->zp($period);
        $this->taxes($period);
        
        return;
    }
}
