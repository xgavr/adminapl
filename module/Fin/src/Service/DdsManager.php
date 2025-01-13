<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Fin\Service;

use Fin\Entity\FinDds;
use Company\Entity\Legal;
use Cash\Entity\CashDoc;
use Bank\Entity\Statement;
use Stock\Entity\Movement;

/**
 * Description of DdsManager
 * 
 * @author Daddy
 */
class DdsManager {
    
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
     * Add Dds
     * @param array $data
     * @return FinDds
     */
    public function addDds($data)
    {
        $dds = new FinDds();
        $dds->setCompany($data['company']);
        $dds->setAccountantBegin(empty($data['accountantBegin']) ? 0:$data['accountantBegin']);
        $dds->setAccountantEnd(empty($data['accountantEnd']) ? 0:$data['accountantEnd']);
        $dds->setBankBegin(empty($data['bankBegin']) ? 0:$data['bankBegin']);
        $dds->setBankEnd(empty($data['bankEnd']) ? 0:$data['bankEnd']);
        $dds->setCashBegin(empty($data['cashBegin']) ? 0:$data['cashBegin']);
        $dds->setCashEnd(empty($data['cashEnd']) ? 0:$data['cashEnd']);
        $dds->setCost(empty($data['cost']) ? 0:$data['cost']);
        $dds->setDepositBegin(empty($data['depositBegin']) ? 0:$data['depositBegin']);
        $dds->setDepositEnd(empty($data['depositEnd']) ? 0:$data['depositEnd']);
        $dds->setDepositOut(empty($data['depositOut']) ? 0:$data['depositOut']);
        $dds->setDepositIn(empty($data['depositIn']) ? 0:$data['depositIn']);
        $dds->setGoodBegin(empty($data['goodBegin']) ? 0:$data['goodBegin']);
        $dds->setGoodEnd(empty($data['goodEnd']) ? 0:$data['goodEnd']);
        $dds->setGoodIn(empty($data['goodIn']) ? 0:$data['goodIn']);
        $dds->setGoodOut(empty($data['goodOut']) ? 0:$data['goodOut']);
        $dds->setLoansIn(empty($data['loansIn']) ? 0:$data['loansIn']);
        $dds->setLoansOut(empty($data['loansOut']) ? 0:$data['loansOut']);
        $dds->setOtherIn(empty($data['otherIn']) ? 0:$data['otherIn']);
        $dds->setOtherOut(empty($data['otherOut']) ? 0:$data['otherOut']);
        $dds->setRevenueIn(empty($data['revenueIn']) ? 0:$data['revenueIn']);
        $dds->setRevenueOut(empty($data['revenueOut']) ? 0:$data['revenueOut']);
        $dds->setPeriod($data['period']);
        $dds->setStatus(empty($data['status']) ? FinDds::STATUS_PLAN:$data['status']);
        $dds->setSupplierIn(empty($data['supplierIn']) ? 0:$data['supplierIn']);
        $dds->setSupplierOut(empty($data['supplierOut']) ? 0:$data['supplierOut']);
        $dds->setTax(empty($data['tax']) ? 0:$data['tax']);
        $dds->setTotalBegin(empty($data['totalBegin']) ? 0:$data['totalBegin']);
        $dds->setTotalEnd(empty($data['totalEnd']) ? 0:$data['totalEnd']);
        $dds->setTotalIn(empty($data['totalIn']) ? 0:$data['totalIn']);
        $dds->setTotalOut(empty($data['totalOut']) ? 0:$data['totalOut']);
        $dds->setZp(empty($data['zp']) ? 0:$data['zp']);
        
        $this->entityManager->persist($dds);
        $this->entityManager->flush();
        
        return $dds;
    }
    
    
    /**
     * Найти ДДС
     * @param date $period
     * @param integer $companyId
     * @param integer $status
     * @return FinDds
     */
    private function getFinDds($period, $companyId, $status)
    {
        $result = $this->entityManager->getRepository(FinDds::class)
                ->findOneBy(['period' => $period, 'company' => $companyId, 'status' => $status]);
        if (empty($result)){
            $result = $this->addDds(['period' => $period, 'company' => $companyId, 'status' => $status]);
        }
        
        return $result;
    }

    /**
     * Update ДДС
     * @param FinDds $dds
     * @param array $data
     * @return FinDds
     */
    public function updateDds($dds, $data)
    {
        $dds->setCompany($data['company']);
        $dds->setAccountantBegin(empty($data['accountantBegin']) ? 0:$data['accountantBegin']);
        $dds->setAccountantEnd(empty($data['accountantEnd']) ? 0:$data['accountantEnd']);
        $dds->setBankBegin(empty($data['bankBegin']) ? 0:$data['bankBegin']);
        $dds->setBankEnd(empty($data['bankEnd']) ? 0:$data['bankEnd']);
        $dds->setCashBegin(empty($data['cashBegin']) ? 0:$data['cashBegin']);
        $dds->setCashEnd(empty($data['cashEnd']) ? 0:$data['cashEnd']);
        $dds->setCost(empty($data['cost']) ? 0:$data['cost']);
        $dds->setDepositBegin(empty($data['depositBegin']) ? 0:$data['depositBegin']);
        $dds->setDepositEnd(empty($data['depositEnd']) ? 0:$data['depositEnd']);
        $dds->setDepositOut(empty($data['depositOut']) ? 0:$data['depositOut']);
        $dds->setDepositIn(empty($data['depositIn']) ? 0:$data['depositIn']);
        $dds->setGoodBegin(empty($data['goodBegin']) ? 0:$data['goodBegin']);
        $dds->setGoodEnd(empty($data['goodEnd']) ? 0:$data['goodEnd']);
        $dds->setGoodIn(empty($data['goodIn']) ? 0:$data['goodIn']);
        $dds->setGoodOut(empty($data['goodOut']) ? 0:$data['goodOut']);
        $dds->setLoansIn(empty($data['loansIn']) ? 0:$data['loansIn']);
        $dds->setLoansOut(empty($data['loansOut']) ? 0:$data['loansOut']);
        $dds->setOtherIn(empty($data['otherIn']) ? 0:$data['otherIn']);
        $dds->setOtherOut(empty($data['otherOut']) ? 0:$data['otherOut']);
        $dds->setRevenueIn(empty($data['revenueIn']) ? 0:$data['revenueIn']);
        $dds->setRevenueOut(empty($data['revenueOut']) ? 0:$data['revenueOut']);
        $dds->setPeriod($data['period']);
        $dds->setStatus(empty($data['status']) ? FinDds::STATUS_PLAN:$data['status']);
        $dds->setSupplierIn(empty($data['supplierIn']) ? 0:$data['supplierIn']);
        $dds->setSupplierOut(empty($data['supplierOut']) ? 0:$data['supplierOut']);
        $dds->setTax(empty($data['tax']) ? 0:$data['tax']);
        $dds->setTotalBegin(empty($data['totalBegin']) ? 0:$data['totalBegin']);
        $dds->setTotalEnd(empty($data['totalEnd']) ? 0:$data['totalEnd']);
        $dds->setTotalIn(empty($data['totalIn']) ? 0:$data['totalIn']);
        $dds->setTotalOut(empty($data['totalOut']) ? 0:$data['totalOut']);
        $dds->setZp(empty($data['zp']) ? 0:$data['zp']);
        
        $this->entityManager->persist($dds);
        $this->entityManager->flush();
        
        return $dds;
    }
    
    /**
     * Обнулить ДДС
     * @param FinDds $dds
     * @return FinDds
     */
    public function emptyDds($dds)
    {
        $data = [
            'company' => $dds->getCompany(),
            'period' => $dds->getPeriod(),
            'status' => $dds->getStatus(),
        ];
                
        return $this->updateDds($dds, $data);
    }
    
    /**
     * Удалить Dds
     * @param FinDds $dds
     * @return null
     */
    public function removeDds($dds)
    {
        $this->entityManager->remove($dds);
        
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
     * Остатки
     * @param date $period
     */
    public function begin($period)
    {
        $startDate = date('Y-01-01', strtotime($period));
        $endDate = date('Y-12-31 23:59:59', strtotime($period));
        
        $p = new \DatePeriod(
            new \DateTime($startDate),
            \DateInterval::createFromDateString('first day of next month'),
            new \DateTime($endDate)
        );

        foreach ($p as $day){
//            var_dump($day->format('Y-m-t'));            
            if (date('Y-m-d') >= $day->format('Y-m-d')){
                $bankBalances = $this->entityManager->getRepository(FinDds::class)
                        ->findBankBalance($day->format('Y-m-d'));
                foreach ($bankBalances as $row){
                    $company = $this->entityManager->getRepository(Legal::class)
                            ->find($row['companyId']);
                    $finDds = $this->getFinDds($day->format('Y-m-t'), $company, FinDds::STATUS_FACT);

                    $finDds->setBankBegin($row['amount']);            

                    $this->entityManager->persist($finDds);                
                }

                $cashBalances = $this->entityManager->getRepository(FinDds::class)
                        ->findCashBalance($day->format('Y-m-d'));
                foreach ($cashBalances as $row){
                    $company = $this->entityManager->getRepository(Legal::class)
                            ->find($row['companyId']);
                    $finDds = $this->getFinDds($day->format('Y-m-t'), $company, FinDds::STATUS_FACT);

                    $finDds->setCashBegin($row['amount']);            

                    $this->entityManager->persist($finDds);                
                }

                $depositBalances = $this->entityManager->getRepository(FinDds::class)
                        ->findDepositBalance($day->format('Y-m-d'));
                foreach ($depositBalances as $row){
                    $company = $this->entityManager->getRepository(Legal::class)
                            ->find($row['companyId']);
                    $finDds = $this->getFinDds($day->format('Y-m-t'), $company, FinDds::STATUS_FACT);

                    $finDds->setDepositBegin(-$row['amount']);            

                    $this->entityManager->persist($finDds);                
                }

                $userBalances = $this->entityManager->getRepository(FinDds::class)
                        ->findUserBalance($day->format('Y-m-d'));
                foreach ($userBalances as $row){
                    $company = $this->entityManager->getRepository(Legal::class)
                            ->find($row['companyId']);
                    $finDds = $this->getFinDds($day->format('Y-m-t'), $company, FinDds::STATUS_FACT);

                    $finDds->setAccountantBegin($row['amount']);            

                    $finDds->setTotalBegin($finDds->getBankBegin() + $finDds->getCashBegin()
                            + $finDds->getAccountantBegin() + $finDds->getDepositBegin());

                    $this->entityManager->persist($finDds);                
                }
            }
        }
        $this->entityManager->flush();
    }
    
    /**
     * Остатки на конец
     * @param date $period
     */
    public function ended($period)
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
//                var_dump($firstDayNextMonth);
                $bankBalances = $this->entityManager->getRepository(FinDds::class)
                        ->findBankBalance($firstDayNextMonth);
                foreach ($bankBalances as $row){
                    $company = $this->entityManager->getRepository(Legal::class)
                            ->find($row['companyId']);
                    $finDds = $this->getFinDds($day->format('Y-m-t'), $company, FinDds::STATUS_FACT);

                    $finDds->setBankEnd($row['amount']);            

                    $this->entityManager->persist($finDds);                
                }

                // банк текущий месяц
                if ($day->format('Y-m-d') == date('Y-m-01')){
                    $statements = $this->entityManager->getRepository(FinDds::class)
                            ->findStatement($firstDayNextMonth, date('Y-m-t'), []);
                    foreach ($statements as $statement){
                        $company = $this->entityManager->getRepository(Legal::class)
                                ->find($statement['companyId']);
                        $finDds = $this->getFinDds($statement['period'], $company, FinDds::STATUS_FACT);

                        $finDds->setBankEnd($finDds->getBankBegin() + $statement['amount']);
                    }    
                }
                
                $cashBalances = $this->entityManager->getRepository(FinDds::class)
                        ->findCashBalance($firstDayNextMonth);
                foreach ($cashBalances as $row){
                    $company = $this->entityManager->getRepository(Legal::class)
                            ->find($row['companyId']);
                    $finDds = $this->getFinDds($day->format('Y-m-t'), $company, FinDds::STATUS_FACT);

                    $finDds->setCashEnd($row['amount']);            

                    $this->entityManager->persist($finDds);                
                }

                $depositBalances = $this->entityManager->getRepository(FinDds::class)
                        ->findDepositBalance($firstDayNextMonth);
                foreach ($depositBalances as $row){
                    $company = $this->entityManager->getRepository(Legal::class)
                            ->find($row['companyId']);
                    $finDds = $this->getFinDds($day->format('Y-m-t'), $company, FinDds::STATUS_FACT);

                    $finDds->setDepositEnd(-$row['amount']);            

                    $this->entityManager->persist($finDds);                
                }
                
                $userBalances = $this->entityManager->getRepository(FinDds::class)
                        ->findUserBalance($firstDayNextMonth);
                foreach ($userBalances as $row){
                    $company = $this->entityManager->getRepository(Legal::class)
                            ->find($row['companyId']);
                    $finDds = $this->getFinDds($day->format('Y-m-t'), $company, FinDds::STATUS_FACT);

                    $finDds->setAccountantEnd($row['amount']);            

                    $finDds->setTotalEnd($finDds->getBankEnd() + $finDds->getCashEnd()
                            + $finDds->getAccountantEnd() + $finDds->getDepositEnd());

                    $this->entityManager->persist($finDds);                
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
     * Рассчитать депозиты
     * @param date $period
     */
    public function incomeDeposits($period)
    {
        $startDate = date('Y-01-01', strtotime($period));
        $endDate = date('Y-12-31 23:59:59', strtotime($period));
//        var_dump($startDate, $endDate); exit;
        
        $bankDepositIncomes = $this->entityManager->getRepository(FinDds::class)
                ->findStatement($startDate, $endDate, [
//                    Statement::KIND_IN_DEPOSIT,
                    Statement::KIND_IN_DEPOSIT_PERCENT,
                ]);
        
        foreach ($bankDepositIncomes as $row){
            $company = $this->entityManager->getRepository(Legal::class)
                    ->find($row['companyId']);
            
            $finDds = $this->getFinDds($row['period'], $company, FinDds::STATUS_FACT);
            
            $finDds->setDepositIn($row['amount']);
            
            $this->incomeTotal($finDds);
            $this->entityManager->persist($finDds);
        }
        
        $this->entityManager->flush();
    }
        
    /**
     * Рассчитать прочее
     * @param date $period
     */
    public function incomeOthers($period)
    {
        $startDate = date('Y-01-01', strtotime($period));
        $endDate = date('Y-12-31 23:59:59', strtotime($period));
//        var_dump($startDate, $endDate); exit;
        
        $userOtherIncomes = $this->entityManager->getRepository(FinDds::class)
                ->findOtherUserTransaction($startDate, $endDate, [
                    Movement::DOC_ORDER,
                ]);
        
        foreach ($userOtherIncomes as $row){
            $company = $this->entityManager->getRepository(Legal::class)
                    ->find($row['companyId']);
            
            $finDds = $this->getFinDds($row['period'], $company, FinDds::STATUS_FACT);
            
            $finDds->setOtherIn($row['amount']);
            
            $this->incomeTotal($finDds);
            
            $this->entityManager->persist($finDds);
        }
        
        $bankOthersIncomes = $this->entityManager->getRepository(FinDds::class)
                ->findStatement($startDate, $endDate, [
                    Statement::KIND_IN_TAX_RETURN,
                    Statement::KIND_IN_CAPITAL,
                    Statement::KIND_IN_OTHER,
                ]);
        
        foreach ($bankOthersIncomes as $row){
            $company = $this->entityManager->getRepository(Legal::class)
                    ->find($row['companyId']);
            
            $finDds = $this->getFinDds($row['period'], $company, FinDds::STATUS_FACT);
            
            $finDds->setOtherIn($row['amount'] + $finDds->getOtherIn());
            
            $this->incomeTotal($finDds);
            $this->entityManager->persist($finDds);
        }
        
        $this->entityManager->flush();
    }

    /**
     * Рассчитать возвраты постащикам
     * @param date $period
     */
    public function outSupplier($period)
    {
        $startDate = date('Y-01-01', strtotime($period));
        $endDate = date('Y-12-31 23:59:59', strtotime($period));
//        var_dump($startDate, $endDate); exit;
        $cashSupplierOuts = $this->entityManager->getRepository(FinDds::class)
                ->findCashTransaction($startDate, $endDate, [
                    CashDoc::KIND_OUT_SUPPLIER,
                ]);
        
        foreach ($cashSupplierOuts as $row){
            $company = $this->entityManager->getRepository(Legal::class)
                    ->find($row['companyId']);
            
            $finDds = $this->getFinDds($row['period'], $company, FinDds::STATUS_FACT);
            
            $finDds->setSupplierOut(abs($row['amount']));
            
            $this->outTotal($finDds);
            
            $this->entityManager->persist($finDds);
        }
        
        $userSupplierOuts = $this->entityManager->getRepository(FinDds::class)
                ->findUserTransaction($startDate, $endDate, [
                    CashDoc::KIND_OUT_SUPPLIER,
                ]);
        
        foreach ($userSupplierOuts as $row){
            $company = $this->entityManager->getRepository(Legal::class)
                    ->find($row['companyId']);
            
            $finDds = $this->getFinDds($row['period'], $company, FinDds::STATUS_FACT);
            
            $finDds->setSupplierOut(abs($row['amount']) + $finDds->getSupplierOut());
            
            $this->outTotal($finDds);
            
            $this->entityManager->persist($finDds);
        }
        
        $bankSupplierOuts = $this->entityManager->getRepository(FinDds::class)
                ->findStatement($startDate, $endDate, [
                    Statement::KIND_OUT_SUPPLIER,
                ]);
        
        foreach ($bankSupplierOuts as $row){
            $company = $this->entityManager->getRepository(Legal::class)
                    ->find($row['companyId']);
            
            $finDds = $this->getFinDds($row['period'], $company, FinDds::STATUS_FACT);
            
            $finDds->setSupplierOut(abs($row['amount']) + $finDds->getSupplierOut());
            
            $this->outTotal($finDds);
            
            $this->entityManager->persist($finDds);
        }
        
        $this->entityManager->flush();
    }
    
    /**
     * Рассчитать возвраты покупателям
     * @param date $period
     */
    public function outRetail($period)
    {
        $startDate = date('Y-01-01', strtotime($period));
        $endDate = date('Y-12-31 23:59:59', strtotime($period));
//        var_dump($startDate, $endDate); exit;
        $cashRetailOuts = $this->entityManager->getRepository(FinDds::class)
                ->findCashTransaction($startDate, $endDate, [
                    CashDoc::KIND_OUT_RETURN_CLIENT,
                ]);
        
        foreach ($cashRetailOuts as $row){
            $company = $this->entityManager->getRepository(Legal::class)
                    ->find($row['companyId']);
            
            $finDds = $this->getFinDds($row['period'], $company, FinDds::STATUS_FACT);
            
            $finDds->setRevenueOut(abs($row['amount']));
            
            $this->outTotal($finDds);
            
            $this->entityManager->persist($finDds);
        }
        
        $userRetailOuts = $this->entityManager->getRepository(FinDds::class)
                ->findUserTransaction($startDate, $endDate, [
                    CashDoc::KIND_OUT_RETURN_CLIENT,
                ]);
        
        foreach ($userRetailOuts as $row){
            $company = $this->entityManager->getRepository(Legal::class)
                    ->find($row['companyId']);
            
            $finDds = $this->getFinDds($row['period'], $company, FinDds::STATUS_FACT);
            
            $finDds->setRevenueOut(abs($row['amount']) + $finDds->getRevenueOut());
            
            $this->outTotal($finDds);
            
            $this->entityManager->persist($finDds);
        }
        
        $bankRetailOuts = $this->entityManager->getRepository(FinDds::class)
                ->findStatement($startDate, $endDate, [
                    Statement::KIND_OUT_BAYER,
                ]);
        
        foreach ($bankRetailOuts as $row){
            $company = $this->entityManager->getRepository(Legal::class)
                    ->find($row['companyId']);
            
            $finDds = $this->getFinDds($row['period'], $company, FinDds::STATUS_FACT);
            
            $finDds->setRevenueOut(abs($row['amount']) + $finDds->getRevenueOut());
            
            $this->outTotal($finDds);
            
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
     * Остатки товаров
     * @param date $period
     */
    public function beginGood($period)
    {
        $startDate = date('Y-01-01', strtotime($period));
        $endDate = date('Y-12-31 23:59:59', strtotime($period));
        
        $p = new \DatePeriod(
            new \DateTime($startDate),
            \DateInterval::createFromDateString('first day of next month'),
            new \DateTime($endDate)
        );

        foreach ($p as $day){
//            var_dump($day->format('Y-m-t'));            
            if (date('Y-m-d') >= $day->format('Y-m-d')){

                $goodBalances = $this->entityManager->getRepository(FinDds::class)
                        ->findGoodBalance($day->format('Y-m-d'));
                foreach ($goodBalances as $row){
                    $company = $this->entityManager->getRepository(Legal::class)
                            ->find($row['companyId']);
                    $finDds = $this->getFinDds($day->format('Y-m-t'), $company, FinDds::STATUS_FACT);

                    $finDds->setGoodBegin($row['amount']);            

                    $this->entityManager->persist($finDds);                
                }

            }
        }
        $this->entityManager->flush();
    }
    
    /**
     * Остатки товаров на конец
     * @param date $period
     */
    public function endedGood($period)
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
                    $finDds = $this->getFinDds($day->format('Y-m-t'), $company, FinDds::STATUS_FACT);

                    $finDds->setGoodEnd($row['amount']);            

                    $this->entityManager->persist($finDds);                
                }
            }
        }
        $this->entityManager->flush();
    }
    
    /**
     * Рассчитать товары
     * @param date $period
     */
    public function movements($period)
    {
        $startDate = date('Y-01-01', strtotime($period));
        $endDate = date('Y-12-31 23:59:59', strtotime($period));
//        var_dump($startDate, $endDate); exit;
        
        $movements = $this->entityManager->getRepository(FinDds::class)
                ->findMovement($startDate, $endDate);
        
        foreach ($movements as $row){
            $company = $this->entityManager->getRepository(Legal::class)
                    ->find($row['companyId']);
            
            $finDds = $this->getFinDds($row['period'], $company, FinDds::STATUS_FACT);
            
            $finDds->setGoodIn($row['amountIn']);
            $finDds->setGoodOut(abs($row['amountOut']));                
            
            $this->outTotal($finDds);
            $this->entityManager->persist($finDds);
        }
        
        $this->entityManager->flush();
    }
    
    /**
     * Посчитать dds за период
     * @param date $period
     */
    public function calculate($period)
    {
        
        $startDate = date('Y-01-01', strtotime($period));
        $endDate = date('Y-12-31 23:59:59', strtotime($period));
        $queryDds = $this->entityManager->getRepository(FinDds::class)
                ->queryDds($startDate, $endDate);
        $ddss = $queryDds->getResult();
        foreach($ddss as $dds){
            $this->emptyDds($dds);
        }
        
        $this->begin($period);
        $this->incomeRetail($period);
        $this->incomeSupplier($period);
        $this->incomeLoans($period);
        $this->incomeDeposits($period);
        $this->incomeOthers($period);
        
        $this->outSupplier($period);
        $this->outRetail($period);
        $this->outZp($period);
        $this->outTax($period);
        $this->outCost($period);
        $this->outCredit($period);
        $this->outOther($period);
        $this->ended($period);
        
        $this->beginGood($period);
        $this->endedGood($period);
        $this->movements($period);
        
        return;
    }
}
