<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Fin\Service;

use Fin\Entity\FinOpu;
use ApiMarketPlace\Entity\MarketSaleReport;
use Company\Entity\Legal;

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
            $finOpu->setRevenueRetail(abs($row['revenue']));
            $finOpu->setPurchaseRetail(abs($row['purchase']));
            $finOpu->setIncomeRetail(abs($row['revenue']) - abs($row['purchase']));
            
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
        $this->incomeRetail($period);
        $this->incomeTp($period);
        
        return;
    }
}
