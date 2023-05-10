<?php
/**
 * This file is part of the ApiMarketPlace.
 *
 */

namespace ApiMarketPlace\Service;

use ApiMarketPlace\Entity\MarketSaleReport;
use ApiMarketPlace\Entity\MarketSaleReportItem;
use ApiMarketPlace\Entity\Marketplace;
use Company\Entity\Legal;
use Company\Entity\Contract;
use Application\Entity\Goods;
use Stock\Entity\Comitent;
use Stock\Entity\ComitentBalance;
use Stock\Entity\Movement;
use Stock\Entity\Register;
use Stock\Entity\RegisterVariable;
use Stock\Entity\Mutual;

class ReportManager
{
    
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Update manager.
     * @var \ApiMarketPlace\Service\OzonService
     */
    private $ozonService;    


    public function __construct($entityManager, $ozonService)
    {
        $this->entityManager = $entityManager;
        $this->ozonService = $ozonService;
    }
    
    /**
     * Дата запрета редактирования
     * @return date
     */
    private function getAllowDate()
    {
        $var = $this->entityManager->getRepository(RegisterVariable::class)
                ->findOneBy([]);
        return $var->getAllowDate();
    }
    
    /**
     * Найти компанию
     * @param string $inn
     * @param string $kpp
     */
    private function findLegal($inn, $kpp)
    {
        $result = $this->entityManager->getRepository(Legal::class)
                ->findOneBy(['inn' => $inn, 'kpp' => $kpp, 'status' => Legal::STATUS_ACTIVE]);
        return $result;
    }
    
    /**
     * Найти договор
     * @param Legal $legal
     * @param Legal $company
     * @param string $contractNum
     * @param date $contractDate
     */
    private function findContract($legal, $company, $contractNum, $contractDate)
    {
        if ($legal && $company){
            $result = $this->entityManager->getRepository(Contract::class)
                    ->findOneBy(['company' => $company->getId(), 'legal' => $legal->getId(), 
                        'act' => $contractNum, 'dateStart' => $contractDate, 'kind' => Contract::KIND_COMITENT, 'status' => Contract::STATUS_ACTIVE]);
            return $result;
        }    
        
        return;
    }

    
    /**
     * Добавить/обновить отчет по реализации
     * @param Marketplace $marketplace
     * @param array $header
     */
    private function findReport($marketplace, $header)
    {
        $report = null;
        if (isset($header['num'])){
            $report = $this->entityManager->getRepository(MarketSaleReport::class)
                    ->findOneBy(['marketplace' => $marketplace->getId(), 'num' => $header['num']]);
            if (!$report){                
                if ($marketplace->getContact()){
                    $report = new MarketSaleReport();
                    $report->setDateCreated(date('Y-m-d H:i:s'));
                    $report->setNum($header['num']);
                    $report->setMarketplace($marketplace);
                    $report->setContract($marketplace->getContract());
                }    
            }
        }
        
        if ($report){
            $report->setCurrencyCode(empty($header['currency_code']) ? null:$header['currency_code']);
            $report->setDocAmount(empty($header['doc_amount']) ? 0:$header['doc_amount']);
            $report->setDocDate(empty($header['doc_date']) ? null:$header['doc_date']);
            $report->setStartDate(empty($header['start_date']) ? null:$header['start_date']);
            $report->setStatus(MarketSaleReport::STATUS_ACTIVE);
            $report->setStopDate(empty($header['stop_date']) ? null:$header['stop_date']);
            $report->setVatAmount(empty($header['vat_amount']) ? 0:$header['vat_amount']);
            $report->setTotalAmount(0);
            $report->setStatusDoc(MarketSaleReport::STATUS_DOC_NOT_RECD);
            $report->setStatusEx(MarketSaleReport::STATUS_EX_NEW);
            $report->setStatusAccount(MarketSaleReport::STATUS_TAKE_NO);
            
            $this->entityManager->persist($report);
            if ($report->getDocDate() > $this->getAllowDate()){
                $this->entityManager->flush();
            }    
        }
        
        return $report;
    }
    
    /**
     * Очистить отчет
     * @param MarketSaleReport $report
     */
    private function clearReport($report)
    {
        $items = $this->entityManager->getRepository(MarketSaleReportItem::class)
                ->findBy(['marketSaleReport' => $report->getId()]);
        foreach ($items as $item){
            $this->entityManager->remove($item);            
        }
        
        if ($report->getDocDate() > $this->getAllowDate()){
            $this->entityManager->flush();
        }    
        
        return;
    }
    
    /**
     * Добвать строки отчета
     * @param MarketSaleReport $report
     * @param array $data
     * @return void
     */
    private function addReportItems($report, $data)
    {
        foreach ($data as $row){
            $good = $this->entityManager->getRepository(Goods::class)
                    ->findOneBy(['aplId' => $row['offer_id']]);
            
            $item = new MarketSaleReportItem();
            $item->setBarcode(empty($row['barcode']) ? null:$row['barcode']);
            $item->setCommissionPercent(empty($row['commission_percent']) ? 0:$row['commission_percent']);
            $item->setGood($good);
            $item->setMarketSaleReport($report);
            $item->setPrice(empty($row['price']) ? 0:$row['price']);
            $item->setPriceSale(empty($row['price_sale']) ? 0:$row['price_sale']);
            $item->setProductId(empty($row['product_id']) ? 0:$row['product_id']);
            $item->setOfferId(empty($row['offer_id']) ? 0:$row['offer_id']);
            $item->setProductName(empty($row['product_name']) ? 0:$row['product_name']);
            $item->setReturnAmount(empty($row['return_amount']) ? 0:$row['return_amount']);
            $item->setReturnCommission(empty($row['return_commission']) ? 0:$row['return_commission']);
            $item->setReturnDiscount(empty($row['return_discount']) ? 0:$row['return_discount']);
            $item->setReturnPriceSeller(empty($row['return_price_seller']) ? 0:$row['return_price_seller']);
            $item->setReturnQty(empty($row['return_qty']) ? 0:$row['return_qty']);
            $item->setReturnSale(empty($row['return_sale']) ? 0:$row['return_sale']);
            $item->setSaleAmount(empty($row['sale_amount']) ? 0:$row['sale_amount']);
            $item->setSaleCommission(empty($row['sale_commission']) ? 0:$row['sale_commission']);
            $item->setSaleDiscount(empty($row['sale_discount']) ? 0:$row['sale_discount']);
            $item->setSalePriceSeller(empty($row['sale_price_seller']) ? 0:$row['sale_price_seller']);
            $item->setSaleQty(empty($row['sale_qty']) ? 0:$row['sale_qty']); 
            $item->setTake(MarketSaleReportItem::TAKE_NO);
            $item->setRowNumber(empty($row['row_number']) ? 0:$row['row_number']);

            $this->entityManager->persist($item);            
        }
        
        if ($report->getDocDate() > $this->getAllowDate()){
            $this->entityManager->flush();
        }    
        
        return;
    }
    
    /**
     * Отчет по реализациям от озон
     * @param date $date
     */
    public function ozonRealization($marketplace, $date)
    {
        $saleReport = $this->ozonService->realization($date);
        
        if (is_array($saleReport)){
            $report = $this->findReport($marketplace, $saleReport['header']);
            if ($report){
                $this->clearReport($report);
                $this->addReportItems($report, $saleReport['rows']);
                $this->repostMarketSaleReport($report);
            }
        }      
        
        return $saleReport;
    }       
    
    /**
     * Очитить движения по отчету
     * @param MarketSaleReport $marketSaleRepot
     */
    public function clearComitent($marketSaleRepot)
    {
        $this->entityManager->getRepository(Comitent::class)
                ->removeDocComitent($marketSaleRepot->getLogKey());
    }
    
    /**
     * Обновить взаиморасчеты документа
     * 
     * @param MarketSaleReport $marketSaleReport
     */
    public function updateMarketSaleReportMutuals($marketSaleReport)
    {
        
        $this->entityManager->getRepository(Mutual::class)
                ->removeDocMutuals($marketSaleReport->getLogKey());
        
        if ($marketSaleReport->getStatus() == MarketSaleReport::STATUS_ACTIVE){
            $data = [
                'doc_key' => $marketSaleReport->getLogKey(),
                'doc_type' => Movement::DOC_MSR,
                'doc_id' => $marketSaleReport->getId(),
                'date_oper' => $marketSaleReport->getDocDate(),
                'status' => $marketSaleReport->getStatus(),
                'revise' => Mutual::REVISE_NOT,
                'amount' => $marketSaleReport->getDocAmount(),
                'legal_id' => $marketSaleReport->getContract()->getLegal()->getId(),
                'contract_id' => $marketSaleReport->getContract()->getId(),
                'office_id' => $marketSaleReport->getContract()->getOffice()->getId(),
                'company_id' => $marketSaleReport->getContract()->getCompany()->getId(),
            ];

            $this->entityManager->getRepository(Mutual::class)
                    ->insertMutual($data);
        }    
         
        return;
    }    
    
    /**
     * Обновить движения по отчету
     * @param MarketSaleReport $marketSaleRepot
     */
    public function updateComitent($marketSaleRepot)
    {
        $docStamp = $this->entityManager->getRepository(Register::class)
                ->msrRegister($marketSaleRepot);
        $this->clearComitent($marketSaleRepot);
        
        $msrTake = MarketSaleReport::STATUS_ACCOUNT_NO;
        $contract = $marketSaleRepot->getMarketplace()->getContract();
        if ($marketSaleRepot->getStatus() == MarketSaleReport::STATUS_ACTIVE){
            $items = $this->entityManager->getRepository(MarketSaleReportItem::class)
                    ->findBy(['marketSaleReport' => $marketSaleRepot->getId()]);
            foreach ($items as $item){
                $bases = [];
                if ($item->getGood()){                
                    $bases = $this->entityManager->getRepository(Comitent::class)
                            ->findBases($item->getGood()->getId(), $docStamp, $contract->getId());
                }
                
                $write = $item->getSaleQty();
                
                $take = MarketSaleReportItem::TAKE_NO;
                
                foreach ($bases as $base){
                    $quantity = min($base['rest'], $write);
                    $amount = $base['price']*$quantity;

                    $data = [
                        'doc_key' => $marketSaleRepot->getLogKey(),
                        'doc_type' => Movement::DOC_MSR,
                        'doc_id' => $marketSaleRepot->getId(),
                        'base_key' => $base['baseKey'],
                        'base_type' => $base['baseType'],
                        'base_id' => $base['baseId'],
                        'doc_row_key' => $item->getId(),
                        'doc_row_no' => $item->getId(),
                        'date_oper' => date('Y-m-d 23:00:00', strtotime($marketSaleRepot->getDocDate())),
                        'status' => Comitent::getStatusFromMarketSaleReport($marketSaleRepot),
                        'quantity' => -$quantity,
                        'amount' => -$amount,
                        'good_id' => $item->getGood()->getId(),
                        'legal_id' => $marketSaleRepot->getContract()->getLegal()->getId(),
                        'company_id' => $marketSaleRepot->getContract()->getCompany()->getId(), //
                        'contract_id' => $marketSaleRepot->getContract()->getId(), //
                        'doc_stamp' => $docStamp,
                    ];

                    $this->entityManager->getRepository(Comitent::class)
                            ->insertComitent($data); 

                    $write -= $quantity;
                    if ($write <= 0){
                        break;
                    }
                    
                }
                
                if ($write == 0){
                    $take = MarketSaleReportItem::TAKE_OK;
                } else {
                    $msrTake = MarketSaleReport::STATUS_TAKE_NO;
                }    

                $this->entityManager->getConnection()
                        ->update('market_sale_report_item', ['take' => $take], ['id' => $item->getId()]);
                if ($item->getGood()){
                    $this->entityManager->getRepository(ComitentBalance::class)
                            ->updateComitentBalance($item->getGood()->getId()); 
                }    
            }
        }
        
        $this->entityManager->getConnection()
                ->update('market_sale_report', ['status_account' => $msrTake], ['id' => $marketSaleRepot->getId()]);        
        
        return $msrTake;        
    }
    
    /**
     * Перепроведение Отчета
     * @param MarketSaleReport $marketSaleReport
     */
    public function repostMarketSaleReport($marketSaleReport)
    {
        if ($marketSaleReport->getDocDate() > $this->getAllowDate()){
            $take = $this->updateComitent($marketSaleReport);
            if ($take != MarketSaleReport::STATUS_TAKE_NO){
                $this->updateMarketSaleReportMutuals($marketSaleReport);
            }    
        }
        return true;
    }
    
    /**
     * Сменить статус
     * 
     * @param MarketSaleReport $marketSaleReport
     * @param integer $status
     * @return boolean
     */
    public function changeStatus($marketSaleReport, $status)
    {
        $marketSaleReport->setStatus($status);
        $this->entityManager->persist($marketSaleReport);
        $this->entityManager->flush();
        $this->entityManager->refresh($marketSaleReport);
        return true;
    }

}
