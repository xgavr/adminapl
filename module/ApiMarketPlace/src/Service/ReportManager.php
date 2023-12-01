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
use Stock\Entity\Retail;
use Stock\Entity\Revise;
use Laminas\Json\Encoder;

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
     * @param int $reportType
     * 
     * @return MarketSaleReport
     */
    public function findReport($marketplace, $header, $reportType = MarketSaleReport::TYPE_REPORT)
    {
        $report = null;
        if (isset($header['num'])){
            $report = $this->entityManager->getRepository(MarketSaleReport::class)
                    ->findOneBy(['marketplace' => $marketplace->getId(), 
                        'num' => $header['num'], 'reportType' => $reportType]);
            if (!$report){                
                if ($marketplace->getContact()){
                    $report = new MarketSaleReport();
                    $report->setDateCreated(date('Y-m-d H:i:s'));
                    $report->setNum($header['num']);
                    $report->setMarketplace($marketplace);
                    $report->setContract($marketplace->getContract());
                    $report->setBaseAmount(0);
                    $report->setCostAmount(0);
                    $report->setReportType($reportType);
                }    
            }
        }
        
        if ($report){
            $report->setCurrencyCode(empty($header['currency_code']) ? 'RUR':$header['currency_code']);
            $report->setComment(empty($header['comment']) ? null:$header['comment']);
            $report->setDocAmount(empty($header['doc_amount']) ? 0:$header['doc_amount']);
            $report->setDocDate(empty($header['doc_date']) ? null:$header['doc_date']);
            $report->setStartDate(empty($header['start_date']) ? $header['doc_date']:$header['start_date']);
            $report->setStatus(MarketSaleReport::STATUS_ACTIVE);
            $report->setStopDate(empty($header['stop_date']) ? $header['doc_date']:$header['stop_date']);
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
     * Обновить отчет
     * 
     * @param MarketSaleReport $report
     * @param integer $statusAccount
     * @return MarketSaleReport
     */
    public function updateReportSatusAccount($report, $statusAccount)
    {
        $report->setStatusAccount($statusAccount);

        $this->entityManager->persist($report);
        $this->entityManager->flush();
        $this->entityManager->refresh($report);
        
        return $report;
    }
    
    /**
     * Очистить отчет
     * @param MarketSaleReport $report
     */
    public function clearReport($report)
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
    public function addReportItems($report, $data)
    {
        foreach ($data as $row){
            
            $offers = explode('+', empty($row['offer_id']) ? 0:$row['offer_id']);
            $offerCount = count($offers);
            
            foreach ($offers as $offer){
            
                $complect = 1;

                $offer_complect = explode('_', str_replace(['-'], '_', $offer));

                $good = $this->entityManager->getRepository(Goods::class)
                        ->find($offer_complect[0]);

                if (!empty($offer_complect[1])){
                    $complect = max(1, (int) $offer_complect[1]);
                }

                $saleQty = empty($row['sale_qty']) ? 0:$row['sale_qty']*$complect;
                $returnQty = empty($row['return_qty']) ? 0:$row['return_qty']*$complect;
                $price = empty($row['price']) ? 0:$row['price']/$complect/$offerCount;
                $priceSale = empty($row['price_sale']) ? 0:$row['price_sale']/$complect/$offerCount;
                
    //            var_dump($row['offer_id']);
                $item = new MarketSaleReportItem();
                $item->setBarcode(empty($row['barcode']) ? null:$row['barcode']);
                $item->setCommissionPercent(empty($row['commission_percent']) ? 0:$row['commission_percent']);
                $item->setGood($good);
                $item->setMarketSaleReport($report);
                $item->setPrice($price);
                $item->setPriceSale($priceSale);
                $item->setProductId(empty($row['product_id']) ? 0:$row['product_id']);
                $item->setOfferId($row['offer_id']);
                $item->setProductName(empty($row['product_name']) ? 0:$row['product_name']);
                $item->setReturnAmount(empty($row['return_amount']) ? 0:$row['return_amount']/$offerCount);
                $item->setReturnCommission(empty($row['return_commission']) ? 0:$row['return_commission']);
                $item->setReturnDiscount(empty($row['return_discount']) ? 0:$row['return_discount']/$offerCount);
                $item->setReturnPriceSeller(empty($row['return_price_seller']) ? 0:$row['return_price_seller']/$offerCount);
                $item->setReturnQty($returnQty);
                $item->setReturnSale(empty($row['return_sale']) ? 0:$row['return_sale']/$offerCount);
                $item->setSaleAmount(empty($row['sale_amount']) ? 0:$row['sale_amount']/$offerCount);
                $item->setSaleCommission(empty($row['sale_commission']) ? 0:$row['sale_commission']/$offerCount);
                $item->setSaleDiscount(empty($row['sale_discount']) ? 0:$row['sale_discount']/$offerCount);
                $item->setSalePriceSeller(empty($row['sale_price_seller']) ? 0:$row['sale_price_seller']/$offerCount);
                $item->setSaleQty($saleQty); 
                $item->setTake(MarketSaleReportItem::TAKE_NO);
                $item->setRowNumber(empty($row['row_number']) ? 0:$row['row_number']);
                $item->setBaseAmount(0);

                $this->entityManager->persist($item);                  
            }    
        }
        
        if ($report->getDocDate() > $this->getAllowDate()){
            $this->entityManager->flush();
        }    
        
        return;
    }
    
    /**
     * Обновить товар в строке отчета
     * @param MarketSaleReportItem $item
     * @param Goods $good
     */
    public function updateItemGood($item, $good)
    {
        $item->setGood($good);
        $this->entityManager->persist($item);
        $this->entityManager->flush();
        $this->entityManager->refresh($item);
        
        return;
    }
    
    /**
     * Отчет по реализациям от озон
     * @param date $date
     */
    public function ozonRealization($marketplace, $date)
    {
        $saleReport = $this->ozonService->realization($date);
//        var_dump($saleReport);
        if (is_array($saleReport)){
            $report = $this->findReport($marketplace, $saleReport['header'], MarketSaleReport::TYPE_REPORT);
            if ($report){
                $this->clearReport($report);
                $this->addReportItems($report, $saleReport['rows']);
                $this->repostMarketSaleReport($report);
            }
        }      
        
        return $saleReport;
    }       
    
    /**
     * Подготовить отчеты комитентов за прошлый месяц
     */
    public function monthReports()
    {
        $marketplaces = $this->entityManager->getRepository(Marketplace::class)
                ->findBy(['status' => Marketplace::STATUS_ACTIVE]);
        
        foreach ($marketplaces as $marketplace){
            $reportDate = date('Y-m-d', strtotime('last day of previous month'));
//            var_dump($reportDate); exit;
            $marketplaceReport = $this->entityManager->getRepository(MarketSaleReport::class)
                    ->findOneBy(['status' => MarketSaleReport::STATUS_ACTIVE, 
                        'marketplace' => $marketplace->getId(), 'docDate' => $reportDate,
                        'reportType' => MarketSaleReport::TYPE_REPORT]);
            
            if (!empty($marketplaceReport) && $marketplaceReport->getTotalAmount()){
                break;
            }
            
            if ($marketplace->getMarketType() == Marketplace::TYPE_OZON){
                $this->ozonRealization($marketplace, $reportDate);
            }
        }
        
        return;
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
     * @param float $docStamp
     */
    public function updateMarketSaleReportMutuals($marketSaleReport, $docStamp)
    {
        
        $this->entityManager->getRepository(Mutual::class)
                ->removeDocMutuals($marketSaleReport->getLogKey());
        
        $data = [
            'doc_key' => $marketSaleReport->getLogKey(),
            'doc_type' => Movement::DOC_MSR,
            'doc_id' => $marketSaleReport->getId(),
            'date_oper' => $marketSaleReport->getDocDate(),
            'status' => Mutual::getStatusFromReport($marketSaleReport),
            'revise' => Mutual::REVISE_NOT,
            'amount' => $marketSaleReport->getDocAmount(),
            'legal_id' => $marketSaleReport->getContract()->getLegal()->getId(),
            'contract_id' => $marketSaleReport->getContract()->getId(),
            'office_id' => $marketSaleReport->getContract()->getOffice()->getId(),
            'company_id' => $marketSaleReport->getContract()->getCompany()->getId(),
            'doc_stamp' => $docStamp,
        ];

        $this->entityManager->getRepository(Mutual::class)
                ->insertMutual($data);
         
        return;
    }    
    
    /**
     * Обновить взаиморасчеты документа
     * 
     * @param MarketSaleReport $marketSaleReport
     * @param float $docStamp
     */
    public function updateMarketSaleReportRetails($marketSaleReport, $docStamp)
    {
        
        $this->entityManager->getRepository(Retail::class)
                ->removeOrderRetails($marketSaleReport->getLogKey());
        
        $contact = $marketSaleReport->getContract()->getLegal()->getClientContact();
        
        if ($contact && $marketSaleReport->getStatus() == MarketSaleReport::STATUS_ACTIVE){
            $data = [
                'doc_key' => $marketSaleReport->getLogKey(),
                'doc_type' => Movement::DOC_MSR,
                'doc_id' => $marketSaleReport->getId(),
                'date_oper' => $marketSaleReport->getDocDate(),
                'status' => Retail::getStatusFromMsr($marketSaleReport),
                'revise' => Retail::REVISE_NOT,
                'amount' => $marketSaleReport->getDocAmount(),
                'contact_id' => $contact->getId(),
                'legal_id' => $marketSaleReport->getContract()->getLegal()->getId(),
                'contract_id' => $marketSaleReport->getContract()->getId(),
                'office_id' => $marketSaleReport->getContract()->getOffice()->getId(),
                'company_id' => $marketSaleReport->getContract()->getCompany()->getId(),
                'doc_stamp' => $docStamp,
            ];

            $this->entityManager->getRepository(Retail::class)
                    ->insertRetail($data);
        }    
         
        return;
    }    

    /**
     * Обновить движения по отчету
     * @param MarketSaleReport $marketSaleReport
     * @param float $docStamp
     */
    public function updateComitent($marketSaleReport, $docStamp)
    {
        $this->clearComitent($marketSaleReport);
        
        $msrTake = $marketSaleReport->getStatusAccount();
        if ($marketSaleReport->getStatusAccount() == MarketSaleReport::STATUS_TAKE_NO){
            $msrTake = MarketSaleReport::STATUS_ACCOUNT_NO;
        }
        
        $contract = $marketSaleReport->getMarketplace()->getContract();
        $reportBaseTotal = 0;
        if ($marketSaleReport->getStatus() == MarketSaleReport::STATUS_ACTIVE){
            $items = $this->entityManager->getRepository(MarketSaleReportItem::class)
                    ->findBy(['marketSaleReport' => $marketSaleReport->getId()]);
            foreach ($items as $item){

                $take = MarketSaleReportItem::TAKE_NO;

                $write = max(0, $item->getSaleQty() - $item->getReturnQty());
                $posting = max(0, $item->getReturnQty() - $item->getSaleQty());
                $baseTotal = 0;

                if ($write > 0){
                    
                    $bases = [];
                    if ($item->getGood()){                
                        $bases = $this->entityManager->getRepository(Comitent::class)
                                ->findBases($item->getGood()->getId(), $docStamp, $contract->getId());
                    }

                    foreach ($bases as $base){
                        $quantity = min($base['rest'], $write);
                        $amount = $base['price']*$quantity;
                        $baseAmount = $base['basePrice']*$quantity;
                        $baseTotal += $baseAmount;

                        $data = [
                            'doc_key' => $marketSaleReport->getLogKey(),
                            'doc_type' => Movement::DOC_MSR,
                            'doc_id' => $marketSaleReport->getId(),
                            'base_key' => $base['baseKey'],
                            'base_type' => $base['baseType'],
                            'base_id' => $base['baseId'],
                            'doc_row_key' => $item->getId(),
                            'doc_row_no' => $item->getRowNumber(),
                            'date_oper' => date('Y-m-d 23:00:00', strtotime($marketSaleReport->getDocDate())),
                            'status' => Comitent::getStatusFromMarketSaleReport($marketSaleReport),
                            'quantity' => -$quantity,
                            'amount' => -$amount,
                            'base_amount' => -$baseAmount,
                            'good_id' => $item->getGood()->getId(),
                            'legal_id' => $marketSaleReport->getContract()->getLegal()->getId(),
                            'company_id' => $marketSaleReport->getContract()->getCompany()->getId(), //
                            'contract_id' => $marketSaleReport->getContract()->getId(), //
                            'doc_stamp' => $docStamp,
                        ];

                        $this->entityManager->getRepository(Comitent::class)
                                ->insertComitent($data); 

                        $write -= $quantity;
                        if ($write <= 0){
                            break;
                        }                    
                    }
                }
                
                if ($posting > 0){
                    
                    $comitents = [];
                    if ($item->getGood()){                
                        $comitents = $this->entityManager->getRepository(Comitent::class)
                                ->findForReturn($item->getGood()->getId(), $docStamp, $contract->getId());
                    }

                    foreach ($comitents as $comitent){
                        $quantity = min($posting, -$comitent->getQuantity());
                        $amount = $quantity*$comitent->getAmount()/$comitent->getQuantity();
                        $baseAmount = $quantity*$comitent->getBaseAmount()/$comitent->getQuantity();
                        $baseTotal -= $baseAmount;

                        $data = [
                            'doc_key' => $marketSaleReport->getLogKey(),
                            'doc_type' => Movement::DOC_MSR,
                            'doc_id' => $marketSaleReport->getId(),
                            'base_key' => $comitent->getBaseKey(),
                            'base_type' => $comitent->getBaseType(),
                            'base_id' => $comitent->getBaseId(),
                            'doc_row_key' => $item->getId(),
                            'doc_row_no' => $item->getRowNumber(),
                            'date_oper' => date('Y-m-d 13:00:00', strtotime($marketSaleReport->getDocDate())),
                            'status' => Comitent::getStatusFromMarketSaleReport($marketSaleReport),
                            'quantity' => $quantity,
                            'amount' => $amount,
                            'base_amount' => $baseAmount,
                            'good_id' => $item->getGood()->getId(),
                            'legal_id' => $marketSaleReport->getContract()->getLegal()->getId(),
                            'company_id' => $marketSaleReport->getContract()->getCompany()->getId(), //
                            'contract_id' => $marketSaleReport->getContract()->getId(), //
                            'doc_stamp' => $docStamp,
                        ];

                        $this->entityManager->getRepository(Comitent::class)
                                ->insertComitent($data); 

                        $posting -= $quantity;
                        if ($posting <= 0){
                            break;
                        }
                    }
                }
                
                if ($write == 0 && $posting == 0){
                    $take = MarketSaleReportItem::TAKE_OK;
                } else {
                    $msrTake = MarketSaleReport::STATUS_TAKE_NO;
                }    
                
                $this->entityManager->getConnection()
                        ->update('market_sale_report_item', ['take' => $take, 'base_amount' => $baseTotal], ['id' => $item->getId()]);
                
                if ($item->getGood()){
                    $this->entityManager->getRepository(ComitentBalance::class)
                            ->updateComitentBalance($item->getGood()->getId()); 
                }  
                
                $reportBaseTotal += $baseTotal;
            }
        }
        
        $this->entityManager->getConnection()
                ->update('market_sale_report', ['status_account' => $msrTake, 'base_amount' => $reportBaseTotal], ['id' => $marketSaleReport->getId()]);   
        
        $this->entityManager->refresh($marketSaleReport);
        
        return $msrTake;        
    }
        
    /**
     * Перепроведение Отчета
     * @param MarketSaleReport $marketSaleReport
     */
    public function repostMarketSaleReport($marketSaleReport)
    {
        $docStamp = $this->entityManager->getRepository(Register::class)
                ->msrRegister($marketSaleReport);
        if ($marketSaleReport->getDocDate() > $this->getAllowDate()){
            $take = $this->updateComitent($marketSaleReport, $docStamp);
            //if ($take != MarketSaleReport::STATUS_TAKE_NO){
                $this->updateMarketSaleReportMutuals($marketSaleReport, $docStamp);
                $this->updateMarketSaleReportRetails($marketSaleReport, $docStamp);
            //}    
        }
        
        $this->entityManager->getRepository(MarketSaleReport::class)
                ->updateReportRevise($marketSaleReport);
        
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
