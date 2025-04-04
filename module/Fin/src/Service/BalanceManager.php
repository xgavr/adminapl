<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Fin\Service;

use Fin\Entity\FinBalance;
use Fin\Entity\FinDds;
use Company\Entity\Legal;
use Bank\Entity\Statement;
use Stock\Entity\Mutual;
use Company\Entity\Contract;
use Company\Entity\BankAccount;
use Fin\Entity\FinOpu;

/**
 * Description of BalanceManager
 * 
 * @author Daddy
 */
class BalanceManager {
    
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Admin manager
     * @var \Admin\Service\AdminManager
     */
    private $adminManager;

    /**
     * Dds manager
     * @var \Fin\Service\DdsManager
     */
    private $ddsManager;

    /**
     * Fin manager
     * @var \Fin\Service\FinManager
     */
    private $finManager;

    public function __construct($entityManager, $adminManager, $ddsManager, 
            $finManager)
    {
        $this->entityManager = $entityManager;
        $this->adminManager = $adminManager;
        $this->ddsManager = $ddsManager;
        $this->finManager = $finManager;
    }
    
    /**
     * Add Balance
     * @param array $data
     * @return FinBalance
     */
    public function addBalance($data)
    {
        $balance = new FinBalance();
        $balance->setCompany($data['company']);
        $balance->setPeriod($data['period']);
        $balance->setStatus(empty($data['status']) ? FinBalance::STATUS_PLAN:$data['status']);
        $balance->setCash(empty($data['cash']) ? 0:$data['cash']);
        $balance->setClientCredit(empty($data['clientCredit']) ? 0:$data['clientCredit']);
        $balance->setClientDebtor(empty($data['clientDebtor']) ? 0:$data['clientDebtor']);
        $balance->setDeposit(empty($data['deposit']) ? 0:$data['deposit']);
        $balance->setDividends(empty($data['dividends']) ? 0:$data['dividends']);
        $balance->setGoods(empty($data['goods']) ? 0:$data['goods']);
        $balance->setIncome(empty($data['income']) ? 0:$data['income']);
        $balance->setLoans(empty($data['loans']) ? 0:$data['loans']);
        $balance->setOtherAssets(empty($data['otherAssets']) ? 0:$data['otherAssets']);
        $balance->setOtherPassive(empty($data['otherPassive']) ? 0:$data['otherPassive']);
        $balance->setSupplierCredit(empty($data['supplierCredit']) ? 0:$data['supplierCredit']);
        $balance->setSupplierDebtor(empty($data['supplierDebtor']) ? 0:$data['supplierDebtor']);
        $balance->setTotalAssets(empty($data['totalAssets']) ? 0:$data['totalAssets']);
        $balance->setTotalPassive(empty($data['totalPassive']) ? 0:$data['totalPassive']);
        $balance->setZp(empty($data['zp']) ? 0:$data['zp']);
        $balance->setBalance(0);
        $balance->setAl(0);
        $balance->setKfl(0);
        $balance->setKtl(0);
        $balance->setRa(0);
        $balance->setRo(0);
        $balance->setRsk(0);
        $balance->setFn(0);
        
        $this->entityManager->persist($balance);
        $this->entityManager->flush();
        
        return $balance;
    }
    
    
    /**
     * Найти Баланс
     * @param date $period
     * @param integer $companyId
     * @param integer $status
     * @return FinBalance
     */
    private function getFinBalance($period, $companyId, $status)
    {
        $result = $this->entityManager->getRepository(FinBalance::class)
                ->findOneBy(['period' => $period, 'company' => $companyId, 'status' => $status]);
        if (empty($result)){
            $result = $this->addBalance(['period' => $period, 'company' => $companyId, 'status' => $status]);
        }
        
        return $result;
    }

    /**
     * Update Balance
     * @param FinBalance $balance
     * @param array $data
     * @return FinDds
     */
    public function updateBalance($balance, $data)
    {
        $balance->setCompany($data['company']);
        $balance->setPeriod($data['period']);
        $balance->setStatus(empty($data['status']) ? FinBalance::STATUS_PLAN:$data['status']);
        $balance->setCash(empty($data['cash']) ? 0:$data['cash']);
        $balance->setClientCredit(empty($data['clientCredit']) ? 0:$data['clientCredit']);
        $balance->setClientDebtor(empty($data['clientDebtor']) ? 0:$data['clientDebtor']);
        $balance->setDeposit(empty($data['deposit']) ? 0:$data['deposit']);
        $balance->setDividends(empty($data['dividends']) ? 0:$data['dividends']);
        $balance->setGoods(empty($data['goods']) ? 0:$data['goods']);
        $balance->setIncome(empty($data['income']) ? 0:$data['income']);
        $balance->setLoans(empty($data['loans']) ? 0:$data['loans']);
        $balance->setOtherAssets(empty($data['otherAssets']) ? 0:$data['otherAssets']);
        $balance->setOtherPassive(empty($data['otherPassive']) ? 0:$data['otherPassive']);
        $balance->setSupplierCredit(empty($data['supplierCredit']) ? 0:$data['supplierCredit']);
        $balance->setSupplierDebtor(empty($data['supplierDebtor']) ? 0:$data['supplierDebtor']);
        $balance->setTotalAssets(empty($data['totalAssets']) ? 0:$data['totalAssets']);
        $balance->setTotalPassive(empty($data['totalPassive']) ? 0:$data['totalPassive']);
        $balance->setZp(empty($data['zp']) ? 0:$data['zp']);
        $balance->setBalance(0);
        $balance->setAl(0);
        $balance->setKfl(0);
        $balance->setKtl(0);
        $balance->setRa(0);
        $balance->setRo(0);
        $balance->setRsk(0);
        $balance->setFn(0);
        
        $this->entityManager->persist($balance);
        $this->entityManager->flush();
        
        return $balance;
    }
    
    /**
     * Обнулить Баланс
     * @param FinBalance $balance
     * @return FinBalance
     */
    public function emptyBalance($balance)
    {
        $data = [
            'company' => $balance->getCompany(),
            'period' => $balance->getPeriod(),
            'status' => $balance->getStatus(),
        ];
                
        return $this->updateBalance($balance, $data);
    }
    
    /**
     * Удалить Balance
     * @param FinBalance $balance
     * @return null
     */
    public function removeBalance($balance)
    {
        $this->entityManager->remove($balance);
        
        return;
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
     * Деньги
     * @param date $period
     */
    public function caches($period)
    {
        $startDate = date('Y-01-01', strtotime($period));
        $endDate = date('Y-12-31 23:59:59', strtotime($period));
        
        $p = new \DatePeriod(
            new \DateTime($startDate),
            \DateInterval::createFromDateString('first day of next month'),
            new \DateTime($endDate)
        );

        $companies = $this->entityManager->getRepository(BankAccount::class)
                ->companies();

        foreach ($p as $day){
            if (date('Y-m-d') >= $day->format('Y-m-d')){
                $firstDayNextMonth = date('Y-m-d', strtotime($day->format('Y-m-d').' first day of next month'));
                
                foreach ($companies as $company){
                    $bankTotal = 0;
                    foreach ($company->getBankAccounts() as $bankAccount){
                        $bankTotal += $this->entityManager->getRepository(Statement::class)
                                ->currentBalance($bankAccount->getRs(), $firstDayNextMonth);
                    }
                    
                    $finBalance = $this->getFinBalance($day->format('Y-m-t'), $company, FinBalance::STATUS_FACT);
                    
                    $finBalance->setCash($finBalance->getCash() + $bankTotal);
                    
                    $this->entityManager->persist($finBalance); 
                    
                }
                
                $cashBalances = $this->entityManager->getRepository(FinDds::class)
                        ->findCashBalance($firstDayNextMonth);
                foreach ($cashBalances as $row){
                    $company = $this->entityManager->getRepository(Legal::class)
                            ->find($row['companyId']);
                    $finBalance = $this->getFinBalance($day->format('Y-m-t'), $company, FinBalance::STATUS_FACT);

                    $finBalance->setCash($row['amount'] + $finBalance->getCash());            

                    $this->entityManager->persist($finBalance);                
                        
//                    var_dump('cash', $finBalance->getPeriod(), $finBalance->getCash());
                }

                $userBalances = $this->entityManager->getRepository(FinDds::class)
                        ->findUserBalance($firstDayNextMonth);
                foreach ($userBalances as $row){
                    $company = $this->entityManager->getRepository(Legal::class)
                            ->find($row['companyId']);
                    $finBalance = $this->getFinBalance($day->format('Y-m-t'), $company, FinDds::STATUS_FACT);

                    $finBalance->setCash($row['amount'] + $finBalance->getCash());            

                    $this->entityManager->persist($finBalance);                

//                    var_dump('user', $finBalance->getPeriod(), $finBalance->getCash());
                }

                $depositBalances = $this->entityManager->getRepository(FinDds::class)
                        ->findDepositBalance($firstDayNextMonth);
                foreach ($depositBalances as $row){
                    $company = $this->entityManager->getRepository(Legal::class)
                            ->find($row['companyId']);
                    $finBalance = $this->getFinBalance($day->format('Y-m-t'), $company, FinBalance::STATUS_FACT);

                    $finBalance->setDeposit(-$row['amount']);            

                    $this->entityManager->persist($finBalance);                
                }
                
            }
        }
        $this->entityManager->flush();
    }
    
    /**
     * Поставщики
     * @param date $period
     */
    public function suppliers($period)
    {
        $startDate = date('Y-01-01', strtotime($period));
        $endDate = date('Y-12-31 23:59:59', strtotime($period));
        
        $p = new \DatePeriod(
            new \DateTime($startDate),
            \DateInterval::createFromDateString('first day of next month'),
            new \DateTime($endDate)
        );

        foreach ($p as $day){
            if (date('Y-m-d') >= $day->format('Y-m-d')){
                $firstDayNextMonth = date('Y-m-d', strtotime($day->format('Y-m-d').' first day of next month'));
//                var_dump($firstDayNextMonth, $day->format('Y-m-d'));
                $suppliersDebtors = $this->entityManager->getRepository(Mutual::class)
                        ->mutualBalance(['endDateMinus' => $firstDayNextMonth, 'groupContract' => 1,
                            'groupCompany' => 1,'contractKind' => Contract::KIND_SUPPLIER, 'debtor' => 1])->getResult();
//        var_dump($firstDayNextMonth, $suppliersDebtors);
                foreach ($suppliersDebtors as $row){
//                    var_dump($row); exit;
                    $company = $this->entityManager->getRepository(Legal::class)
                            ->find($row['companyId']);
                    $finBalance = $this->getFinBalance($day->format('Y-m-t'), $company, FinBalance::STATUS_FACT);

                    $finBalance->setSupplierDebtor($row['total'] + $finBalance->getSupplierDebtor());            

                    $this->entityManager->persist($finBalance);                
                }
                
                $suppliersCreditors = $this->entityManager->getRepository(Mutual::class)
                        ->mutualBalance(['endDateMinus' => $firstDayNextMonth, 'groupContract' => 1,
                            'groupCompany' => 1,'contractKind' => Contract::KIND_SUPPLIER, 'creditor' => 1])->getResult();
//        var_dump($firstDayNextMonth, $suppliersCreditors);
                foreach ($suppliersCreditors as $row){
                    $company = $this->entityManager->getRepository(Legal::class)
                            ->find($row['companyId']);
                    $finBalance = $this->getFinBalance($day->format('Y-m-t'), $company, FinBalance::STATUS_FACT);

                    $finBalance->setSupplierCredit($finBalance->getSupplierCredit() - $row['total']);            

                    $this->entityManager->persist($finBalance);                
                }
            }
        }
        $this->entityManager->flush();
    }
    
    /**
     * Покупатели
     * @param date $period
     */
    public function clients($period)
    {
        $startDate = date('Y-01-01', strtotime($period));
        $endDate = date('Y-12-31 23:59:59', strtotime($period));
        
        $p = new \DatePeriod(
            new \DateTime($startDate),
            \DateInterval::createFromDateString('first day of next month'),
            new \DateTime($endDate)
        );

        foreach ($p as $day){
            if (date('Y-m-d') >= $day->format('Y-m-d')){
                $firstDayNextMonth = date('Y-m-d', strtotime($day->format('Y-m-d').' first day of next month'));
//                var_dump($firstDayNextMonth, $day->format('Y-m-d'));
                $clientDebtors = $this->entityManager->getRepository(FinBalance::class)
                        ->findRetails($firstDayNextMonth, ['debtor' => 1]);
//        var_dump($firstDayNextMonth, $clientDebtors);
                foreach ($clientDebtors as $row){
                    $company = $this->entityManager->getRepository(Legal::class)
                            ->find($row['companyId']);
                    $finBalance = $this->getFinBalance($day->format('Y-m-t'), $company, FinBalance::STATUS_FACT);

                    $finBalance->setClientDebtor($row['amount'] + $finBalance->getClientDebtor());            

                    $this->entityManager->persist($finBalance);                
                }
                
                $clientCreditors = $this->entityManager->getRepository(FinBalance::class)
                        ->findRetails($firstDayNextMonth, ['creditor' => 1]);
//        var_dump($firstDayNextMonth, $clientCreditors);
                foreach ($clientCreditors as $row){
                    $company = $this->entityManager->getRepository(Legal::class)
                            ->find($row['companyId']);
                    $finBalance = $this->getFinBalance($day->format('Y-m-t'), $company, FinBalance::STATUS_FACT);

                    $finBalance->setClientCredit($finBalance->getClientCredit() - $row['amount']);            

                    $this->entityManager->persist($finBalance);                
                }
            }
        }
        $this->entityManager->flush();
    }
    
    /**
     * Остатки товаров на конец
     * @param date $period
     */
    public function goods($period)
    {
        $startDate = date('Y-01-01', strtotime($period));
        $endDate = date('Y-12-31 23:59:59', strtotime($period));
        
        $p = new \DatePeriod(
            new \DateTime($startDate),
            \DateInterval::createFromDateString('first day of next month'),
            new \DateTime($endDate)
        );

        foreach ($p as $day){
            if (date('Y-m-d') >= $day->format('Y-m-d')){
                $firstDayNextMonth = date('Y-m-d', strtotime($day->format('Y-m-d').' first day of next month'));

                $goodBalances = $this->entityManager->getRepository(FinDds::class)
                        ->findGoodBalance($firstDayNextMonth);
                foreach ($goodBalances as $row){
                    $company = $this->entityManager->getRepository(Legal::class)
                            ->find($row['companyId']);
                    $finBalance = $this->getFinBalance($day->format('Y-m-t'), $company, FinDds::STATUS_FACT);

                    $finBalance->setGoods($row['amount']);            

                    $this->entityManager->persist($finBalance);                
                }
            }
        }
        $this->entityManager->flush();
    }
    
    /**
     * Zp
     * @param date $period
     */
    public function zp($period)
    {
        $startDate = date('Y-01-01', strtotime($period));
        $endDate = date('Y-12-31 23:59:59', strtotime($period));
        
        $p = new \DatePeriod(
            new \DateTime($startDate),
            \DateInterval::createFromDateString('first day of next month'),
            new \DateTime($endDate)
        );

        foreach ($p as $day){
            if (date('Y-m-d') >= $day->format('Y-m-d')){
                $firstDayNextMonth = date('Y-m-d', strtotime($day->format('Y-m-d').' first day of next month'));
                $zps = $this->entityManager->getRepository(FinBalance::class)
                        ->findZp($firstDayNextMonth);

                foreach ($zps as $row){
                    $company = $this->entityManager->getRepository(Legal::class)
                            ->find($row['companyId']);
                    $finBalance = $this->getFinBalance($day->format('Y-m-t'), $company, FinBalance::STATUS_FACT);

                    $finBalance->setZp($finBalance->getZp() - $row['amount']);            

                    $finBalance->setTotalAssets();
                    $finBalance->setIncome();
                    $finBalance->setTotalPassive();
                    $finBalance->setBalance();
                    $finBalance->setDividends();
                    
                    $finBalance->setKtl();
                    $finBalance->setKfl();
                    $finBalance->setAl();
                    
                    $finOpu = $this->finManager->getFinOpu($day->format('Y-m-t'), $company, FinOpu::STATUS_FACT);
                    $finBalance->setRo($finOpu->getIncomeTotal());
                    $finBalance->setRsk($finOpu->getProfitNet());
                    $finBalance->setRa($finOpu->getProfitNet());
                    $finBalance->setFn();
                    
                    $this->entityManager->persist($finBalance);                
                }                
            }
        }
        $this->entityManager->flush();
    }
    
    /**
     * Посчитать balance за период
     * @param date $period
     */
    public function calculate($period)
    {
        
        $startDate = date('Y-01-01', strtotime($period));
        $endDate = date('Y-12-31 23:59:59', strtotime($period));
        $queryBalance = $this->entityManager->getRepository(FinBalance::class)
                ->queryBalance($startDate, $endDate);
        $balances = $queryBalance->getResult();
        foreach($balances as $balance){
            $this->emptyBalance($balance);
        }
        
        $this->caches($period);
        $this->goods($period);
        $this->suppliers($period);
        $this->clients($period);
        $this->zp($period);

        return;
    }
}
