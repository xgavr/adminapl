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
use Cash\Entity\CashDoc;
use Bank\Entity\Statement;
use Stock\Entity\Movement;
use Stock\Entity\Mutual;
use Company\Entity\Contract;

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
    
    private $adminManager;

    public function __construct($entityManager, $adminManager)
    {
        $this->entityManager = $entityManager;
        $this->adminManager = $adminManager;
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

        foreach ($p as $day){
            if (date('Y-m-d') >= $day->format('Y-m-d')){
                $firstDayNextMonth = date('Y-m-d', strtotime($day->format('Y-m-d').' first day of next month'));
//                var_dump($firstDayNextMonth, $day->format('Y-m-d'));
                $bankBalances = $this->entityManager->getRepository(FinDds::class)
                        ->findBankBalance($firstDayNextMonth);
                foreach ($bankBalances as $row){
                    $company = $this->entityManager->getRepository(Legal::class)
                            ->find($row['companyId']);
                    $finBalance = $this->getFinBalance($day->format('Y-m-t'), $company, FinBalance::STATUS_FACT);

                    $finBalance->setCash($row['amount']);            

                    $this->entityManager->persist($finBalance);                
                }

                // банк текущий месяц
                if ($day->format('Y-m-d') == date('Y-m-01')){
                    $statements = $this->entityManager->getRepository(FinDds::class)
                            ->findStatement(date('Y-m-01'), date('Y-m-t'), []);
                    foreach ($statements as $statement){
                        $company = $this->entityManager->getRepository(Legal::class)
                                ->find($statement['companyId']);
                        $finBalance = $this->getFinBalance($statement['period'], $company, FinBalance::STATUS_FACT);

                        $finBalance->setCash($finBalance->getCash() + $statement['amount']);
                    }    
                }
                
                $cashBalances = $this->entityManager->getRepository(FinDds::class)
                        ->findCashBalance($firstDayNextMonth);
                foreach ($cashBalances as $row){
                    $company = $this->entityManager->getRepository(Legal::class)
                            ->find($row['companyId']);
                    $finBalance = $this->getFinBalance($day->format('Y-m-t'), $company, FinBalance::STATUS_FACT);

                    $finBalance->setCash($row['amount'] + $finBalance->getCash());            

                    $this->entityManager->persist($finBalance);                
                }

                $userBalances = $this->entityManager->getRepository(FinDds::class)
                        ->findUserBalance($firstDayNextMonth);
                foreach ($userBalances as $row){
                    $company = $this->entityManager->getRepository(Legal::class)
                            ->find($row['companyId']);
                    $finBalance = $this->getFinBalance($day->format('Y-m-t'), $company, FinDds::STATUS_FACT);

                    $finBalance->setCash(-$row['amount'] + $finBalance->getCash());            

                    $this->entityManager->persist($finBalance);                
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
        var_dump($firstDayNextMonth, $suppliersDebtors);
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
        var_dump($firstDayNextMonth, $suppliersCreditors);
                foreach ($suppliersCreditors as $row){
                    $company = $this->entityManager->getRepository(Legal::class)
                            ->find($row['companyId']);
                    $finBalance = $this->getFinBalance($day->format('Y-m-t'), $company, FinBalance::STATUS_FACT);

                    $finBalance->setSupplierCredit(-$row['total'] - $finBalance->getSupplierCredit());            

                    $this->entityManager->persist($finBalance);                
                }
            }
        }
        $this->entityManager->flush();
    }

    /**
     * 
     * @param FinDds $finDds
     */
    private function incomeTotal($finDds)
    {
        $finDds->setTotalIn(
               $finDds->getDepositIn() +
               $finDds->getLoansIn() +
               $finDds->getOtherIn() +
               $finDds->getRevenueIn() +
               $finDds->getSupplierIn()
        );
       
        return $finDds;
    }
    
    /**
     * 
     * @param FinDds $finDds
     */
    private function outTotal($finDds)
    {
        $finDds->setTotalOut(
               $finDds->getDepositOut() +
               $finDds->getLoansOut() +
               $finDds->getOtherOut() +
               $finDds->getRevenueOut() +
               $finDds->getSupplierOut() +
               $finDds->getCost() +
               $finDds->getTax() +
               $finDds->getZp()
        );
       
        return $finDds;
    }
    
    /**
     * Рассчитать поступления от покупателей
     * @param date $period
     */
    public function incomeRetail($period)
    {
        $startDate = date('Y-01-01', strtotime($period));
        $endDate = date('Y-12-31 23:59:59', strtotime($period));
//        var_dump($startDate, $endDate); exit;
        $cashRetailIncomes = $this->entityManager->getRepository(FinDds::class)
                ->findCashTransaction($startDate, $endDate, [
                    CashDoc::KIND_IN_PAYMENT_CLIENT,
                ]);
        
        foreach ($cashRetailIncomes as $row){
            $company = $this->entityManager->getRepository(Legal::class)
                    ->find($row['companyId']);
            
            $finDds = $this->getFinDds($row['period'], $company, FinDds::STATUS_FACT);
            
            $finDds->setRevenueIn($row['amount']);
            
            $this->incomeTotal($finDds);
            
            $this->entityManager->persist($finDds);
        }
        
        $userRetailIncomes = $this->entityManager->getRepository(FinDds::class)
                ->findUserTransaction($startDate, $endDate, [
                    CashDoc::KIND_IN_PAYMENT_CLIENT,
                ]);
        
        foreach ($userRetailIncomes as $row){
            $company = $this->entityManager->getRepository(Legal::class)
                    ->find($row['companyId']);
            
            $finDds = $this->getFinDds($row['period'], $company, FinDds::STATUS_FACT);
            
            $finDds->setRevenueIn($row['amount'] + $finDds->getRevenueIn());
            
            $this->incomeTotal($finDds);
            
            $this->entityManager->persist($finDds);
        }
        
        $bankRetailIncomes = $this->entityManager->getRepository(FinDds::class)
                ->findStatement($startDate, $endDate, [
                    Statement::KIND_IN_BAYER,
                    Statement::KIND_IN_CART,
                    Statement::KIND_IN_FIN_SERVICE,
                    Statement::KIND_IN_PERSON,
                    Statement::KIND_IN_QR_CODE,
                ]);
        
        foreach ($bankRetailIncomes as $row){
            $company = $this->entityManager->getRepository(Legal::class)
                    ->find($row['companyId']);
            
            $finDds = $this->getFinDds($row['period'], $company, FinDds::STATUS_FACT);
            
            $finDds->setRevenueIn($row['amount'] + $finDds->getRevenueIn());
            
            $this->incomeTotal($finDds);
            
            $this->entityManager->persist($finDds);
        }
        
        $this->entityManager->flush();
    }
    
    /**
     * Рассчитать возвраты поставщиков
     * @param date $period
     */
    public function incomeSupplier($period)
    {
        $startDate = date('Y-01-01', strtotime($period));
        $endDate = date('Y-12-31 23:59:59', strtotime($period));
//        var_dump($startDate, $endDate); exit;
        $cashRetailIncomes = $this->entityManager->getRepository(FinDds::class)
                ->findCashTransaction($startDate, $endDate, [
                    CashDoc::KIND_IN_RETURN_SUPPLIER,
                ]);
        
        foreach ($cashRetailIncomes as $row){
            $company = $this->entityManager->getRepository(Legal::class)
                    ->find($row['companyId']);
            
            $finDds = $this->getFinDds($row['period'], $company, FinDds::STATUS_FACT);
            
            $finDds->setSupplierIn($row['amount']);
            
            $this->incomeTotal($finDds);
            $this->entityManager->persist($finDds);
        }
        
        $userRetailIncomes = $this->entityManager->getRepository(FinDds::class)
                ->findUserTransaction($startDate, $endDate, [
                    CashDoc::KIND_IN_RETURN_SUPPLIER,
                ]);
        
        foreach ($userRetailIncomes as $row){
            $company = $this->entityManager->getRepository(Legal::class)
                    ->find($row['companyId']);
            
            $finDds = $this->getFinDds($row['period'], $company, FinDds::STATUS_FACT);
            
            $finDds->setSupplierIn($row['amount'] + $finDds->getSupplierIn());
            
            $this->incomeTotal($finDds);
            $this->entityManager->persist($finDds);
        }
        
        $bankRetailIncomes = $this->entityManager->getRepository(FinDds::class)
                ->findStatement($startDate, $endDate, [
                    Statement::KIND_IN_SUPPLIER,
                ]);
        
        foreach ($bankRetailIncomes as $row){
            $company = $this->entityManager->getRepository(Legal::class)
                    ->find($row['companyId']);
            
            $finDds = $this->getFinDds($row['period'], $company, FinDds::STATUS_FACT);
            
            $finDds->setSupplierIn($row['amount'] + $finDds->getSupplierIn());
            
            $this->incomeTotal($finDds);
            $this->entityManager->persist($finDds);
        }
        
        $this->entityManager->flush();
    }
    
    /**
     * Рассчитать кредиты
     * @param date $period
     */
    public function incomeLoans($period)
    {
        $startDate = date('Y-01-01', strtotime($period));
        $endDate = date('Y-12-31 23:59:59', strtotime($period));
//        var_dump($startDate, $endDate); exit;
        
        $bankLoanIncomes = $this->entityManager->getRepository(FinDds::class)
                ->findStatement($startDate, $endDate, [
                    Statement::KIND_IN_CREDIT,
                    Statement::KIND_IN_LOAN,
                    Statement::KIND_IN_FACTORING,
                    Statement::KIND_IN_LOAN_USER,
                ]);
        
        foreach ($bankLoanIncomes as $row){
            $company = $this->entityManager->getRepository(Legal::class)
                    ->find($row['companyId']);
            
            $finDds = $this->getFinDds($row['period'], $company, FinDds::STATUS_FACT);
            
            $finDds->setLoansIn($row['amount']);
            
            $this->incomeTotal($finDds);
            $this->entityManager->persist($finDds);
        }
        
        $this->entityManager->flush();
    }

        
    /**
     * Рассчитать зарплату
     * @param date $period
     */
    public function outZp($period)
    {
        $startDate = date('Y-01-01', strtotime($period));
        $endDate = date('Y-12-31 23:59:59', strtotime($period));
//        var_dump($startDate, $endDate); exit;
        $cashZpOuts = $this->entityManager->getRepository(FinDds::class)
                ->findCashTransaction($startDate, $endDate, [
                    CashDoc::KIND_OUT_SALARY,
                    CashDoc::KIND_OUT_COURIER,
                ]);
        
        foreach ($cashZpOuts as $row){
            $company = $this->entityManager->getRepository(Legal::class)
                    ->find($row['companyId']);
            
            $finDds = $this->getFinDds($row['period'], $company, FinDds::STATUS_FACT);
            
            $finDds->setZp(abs($row['amount']));
            
            $this->outTotal($finDds);
            
            $this->entityManager->persist($finDds);
        }
        
        $userZpOuts = $this->entityManager->getRepository(FinDds::class)
                ->findUserTransaction($startDate, $endDate, [
                    CashDoc::KIND_OUT_SALARY,
                    CashDoc::KIND_OUT_COURIER,
                ]);
        
        foreach ($userZpOuts as $row){
            $company = $this->entityManager->getRepository(Legal::class)
                    ->find($row['companyId']);
            
            $finDds = $this->getFinDds($row['period'], $company, FinDds::STATUS_FACT);
            
            $finDds->setZp(abs($row['amount']) + $finDds->getZp());
            
            $this->outTotal($finDds);
            
            $this->entityManager->persist($finDds);
        }
        
        $bankZpOuts = $this->entityManager->getRepository(FinDds::class)
                ->findStatement($startDate, $endDate, [
                    Statement::KIND_OUT_ZP,
                    Statement::KIND_OUT_ZP_USER,
                    Statement::KIND_OUT_CONTRACT_USER,
                    Statement::KIND_OUT_ZP_DEPO,
                    Statement::KIND_OUT_DIVIDENT,
                    Statement::KIND_OUT_SELF_EMPL_REEST,
                    Statement::KIND_OUT_ALIMONY,
                    Statement::KIND_OUT_SELF_EMPL,
                ]);
        
        foreach ($bankZpOuts as $row){
            $company = $this->entityManager->getRepository(Legal::class)
                    ->find($row['companyId']);
            
            $finDds = $this->getFinDds($row['period'], $company, FinDds::STATUS_FACT);
            
            $finDds->setZp(abs($row['amount']) + $finDds->getZp());
            
            $this->outTotal($finDds);
            
            $this->entityManager->persist($finDds);
        }
        
        $this->entityManager->flush();
    }

    /**
     * Рассчитать налоги
     * @param date $period
     */
    public function outTax($period)
    {
        $startDate = date('Y-01-01', strtotime($period));
        $endDate = date('Y-12-31 23:59:59', strtotime($period));
//        var_dump($startDate, $endDate); exit;
        
        $bankTaxOuts = $this->entityManager->getRepository(FinDds::class)
                ->findStatement($startDate, $endDate, [
                    Statement::KIND_OUT_TAX,
                    Statement::KIND_OUT_TAX_OTHER,
                ]);
        
        foreach ($bankTaxOuts as $row){
            $company = $this->entityManager->getRepository(Legal::class)
                    ->find($row['companyId']);
            
            $finDds = $this->getFinDds($row['period'], $company, FinDds::STATUS_FACT);
            
            $finDds->setTax(abs($row['amount']));
            
            $this->outTotal($finDds);
            $this->entityManager->persist($finDds);
        }
        
        $this->entityManager->flush();
    }
    
    /**
     * Рассчитать расходы
     * @param date $period
     */
    public function outCost($period)
    {
        $startDate = date('Y-01-01', strtotime($period));
        $endDate = date('Y-12-31 23:59:59', strtotime($period));
//        var_dump($startDate, $endDate); exit;
        $cashCostOuts = $this->entityManager->getRepository(FinDds::class)
                ->findCashTransaction($startDate, $endDate, [
                    CashDoc::KIND_OUT_COST,
                ]);
        
        foreach ($cashCostOuts as $row){
            $company = $this->entityManager->getRepository(Legal::class)
                    ->find($row['companyId']);
            
            $finDds = $this->getFinDds($row['period'], $company, FinDds::STATUS_FACT);
            
            $finDds->setCost(abs($row['amount']));
            
            $this->outTotal($finDds);
            
            $this->entityManager->persist($finDds);
        }
        
        $userCostOuts = $this->entityManager->getRepository(FinDds::class)
                ->findUserTransaction($startDate, $endDate, [
                    CashDoc::KIND_OUT_COST,
                ]);
        
        foreach ($userCostOuts as $row){
            $company = $this->entityManager->getRepository(Legal::class)
                    ->find($row['companyId']);
            
            $finDds = $this->getFinDds($row['period'], $company, FinDds::STATUS_FACT);
            
            $finDds->setCost(abs($row['amount']) + $finDds->getCost());
            
            $this->outTotal($finDds);
            
            $this->entityManager->persist($finDds);
        }
        
        $bankCostOuts = $this->entityManager->getRepository(FinDds::class)
                ->findStatement($startDate, $endDate, [
                    Statement::KIND_OUT_BANK_COMMISSION,
                    Statement::KIND_OUT_CART_PAY,
                ]);
        
        foreach ($bankCostOuts as $row){
            $company = $this->entityManager->getRepository(Legal::class)
                    ->find($row['companyId']);
            
            $finDds = $this->getFinDds($row['period'], $company, FinDds::STATUS_FACT);
            
            $finDds->setCost(abs($row['amount']) + $finDds->getCost());
            
            $this->outTotal($finDds);
            
            $this->entityManager->persist($finDds);
        }
        
        $this->entityManager->flush();
    }
    
    /**
     * Рассчитать кредиты
     * @param date $period
     */
    public function outCredit($period)
    {
        $startDate = date('Y-01-01', strtotime($period));
        $endDate = date('Y-12-31 23:59:59', strtotime($period));
//        var_dump($startDate, $endDate); exit;
        
        $bankCreditOuts = $this->entityManager->getRepository(FinDds::class)
                ->findStatement($startDate, $endDate, [
                    Statement::KIND_OUT_LOAN_RETURN,
                    Statement::KIND_OUT_CREDIT_RETURN,
                    Statement::KIND_OUT_LOAN,
                ]);
        
        foreach ($bankCreditOuts as $row){
            $company = $this->entityManager->getRepository(Legal::class)
                    ->find($row['companyId']);
            
            $finDds = $this->getFinDds($row['period'], $company, FinDds::STATUS_FACT);
            
            $finDds->setLoansOut(abs($row['amount']));
            
            $this->outTotal($finDds);
            $this->entityManager->persist($finDds);
        }
        
        $this->entityManager->flush();
    }
    
    /**
     * Рассчитать прочее
     * @param date $period
     */
    public function outOther($period)
    {
        $startDate = date('Y-01-01', strtotime($period));
        $endDate = date('Y-12-31 23:59:59', strtotime($period));
//        var_dump($startDate, $endDate); exit;
        
        $userOtherOuts = $this->entityManager->getRepository(FinDds::class)
                ->findOtherUserTransaction($startDate, $endDate, [
                    Movement::DOC_VT,
                ]);
        
        foreach ($userOtherOuts as $row){
            $company = $this->entityManager->getRepository(Legal::class)
                    ->find($row['companyId']);
            
            $finDds = $this->getFinDds($row['period'], $company, FinDds::STATUS_FACT);
            
            $finDds->setOtherOut(abs($row['amount']));
            
            $this->outTotal($finDds);
            
            $this->entityManager->persist($finDds);
        }
        
        $bankOtherOuts = $this->entityManager->getRepository(FinDds::class)
                ->findStatement($startDate, $endDate, [
                    Statement::KIND_OUT_OTHER_CALC,
                    Statement::KIND_OUT_OTHER,
                ]);
        
        foreach ($bankOtherOuts as $row){
            $company = $this->entityManager->getRepository(Legal::class)
                    ->find($row['companyId']);
            
            $finDds = $this->getFinDds($row['period'], $company, FinDds::STATUS_FACT);
            
            $finDds->setOtherOut(abs($row['amount']) + $finDds->getOtherOut());
            
            $this->outTotal($finDds);
            $this->entityManager->persist($finDds);
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

//        $this->incomeRetail($period);
//        $this->incomeSupplier($period);
//        $this->incomeLoans($period);
//        $this->incomeDeposits($period);
//        $this->incomeOthers($period);
//        
//        $this->outSupplier($period);
//        $this->outRetail($period);
//        $this->outZp($period);
//        $this->outTax($period);
//        $this->outCost($period);
//        $this->outCredit($period);
//        $this->outOther($period);
//        $this->ended($period);
//        
//        $this->movements($period);
        
        return;
    }
}
