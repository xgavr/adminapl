<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ApiMarketPlace\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use ApiMarketPlace\Entity\MarketplaceUpdate;
use ApiMarketPlace\Entity\MarketplaceOrder;

/**
 * Description of Marketplace
 * @ORM\Entity(repositoryClass="\ApiMarketPlace\Repository\MarketplaceRepository")
 * @ORM\Table(name="market_sale_report_item")
 * @author Daddy
 */
class MarketSaleReportItem {
    
    const TAKE_OK  = 1;// учтено 
    const TAKE_NO  = 2;// не учтено     
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id")   
     */
    protected $id;
    
    /**
     * Номер строки в отчёте
     * @ORM\Column(name="row_number")   
     */
    protected $rowNumber;

    /**
     * ид товара на площадке
     * @ORM\Column(name="product_id")   
     */
    protected $productId;

    /**
     * ид товара на площадке
     * @ORM\Column(name="offer_id")   
     */
    protected $offerId;

    /**
     * наименование товара на площадке
     * @ORM\Column(name="product_name")   
     */
    protected $productName;

    /**
     * баркод
     * @ORM\Column(name="barcode")   
     */
    protected $barcode;

    /**
     * цена
     * @ORM\Column(name="price")   
     */
    protected $price;

    /**
     * процент комиссии
     * @ORM\Column(name="commission_percent")   
     */
    protected $commissionPercent;

    /**
     * цена продажи
     * @ORM\Column(name="price_sale")   
     */
    protected $priceSale;

    /**
     * количество продано
     * @ORM\Column(name="sale_qty")   
     */
    protected $saleQty;

    /**
     * сумма продажи
     * @ORM\Column(name="sale_amount")   
     */
    protected $saleAmount;

    /** 
     * Доплата за счёт торговой площадки
     * @ORM\Column(name="sale_discount")  
     */
    protected $saleDiscount;  
            
    /** 
     * Комиссия за реализованный товар с учётом скидок и наценки
     * @ORM\Column(name="sale_commission")  
     */
    protected $saleCommission;  
            
    /** 
     * Итого к начислению за реализованный товар
     * @ORM\Column(name="sale_price_seller")  
     */
    protected $salePriceSeller;  
            
    /** 
     * Цена реализации
     * @ORM\Column(name="return_sale")  
     */
    protected $returnSale;  
            
    /** 
     * Количество возвращённого товара
     * @ORM\Column(name="return_qty")  
     */
    protected $returnQty;  
            
    /** 
     * Возвращено на сумму
     * @ORM\Column(name="return_amount")  
     */
    protected $returnAmount;  
            
    /** 
     * Доплата за счёт торговой площадки
     * @ORM\Column(name="return_discount")  
     */
    protected $returnDiscount;  
            
    /** 
     * Комиссия с учётом количества товара
     * @ORM\Column(name="return_commission")  
     */
    protected $returnCommission;  
            
    /** 
     * Итого возвращено
     * @ORM\Column(name="return_price_seller")  
     */
    protected $returnPriceSeller;  
            
    /**
     * @ORM\Column(name="take")   
     */
    protected $take;
    
    /**
     * отчет торговой площадки
     * @ORM\ManyToOne(targetEntity="ApiMarketPlace\Entity\MarketSaleReport", inversedBy="marketSaleReportItems") 
     * @ORM\JoinColumn(name="market_sale_report_id", referencedColumnName="id")
     */
    private $marketSaleReport;
    
    /**
     * товар
     * @ORM\ManyToOne(targetEntity="Application\Entity\Goods", inversedBy="marketSaleReportItems") 
     * @ORM\JoinColumn(name="good_id", referencedColumnName="id")
     */
    private $good;

    /**
     * Constructor.
     */
    public function __construct() 
    {
    }    
    
    public function getId() {
        return $this->id;
    }

    public function getProductId() {
        return $this->productId;
    }

    public function getOfferId() {
        return $this->offerId;
    }

    public function getProductName() {
        return $this->productName;
    }

    public function getBarcode() {
        return $this->barcode;
    }

    public function getPrice() {
        return $this->price;
    }

    public function getCommissionPercent() {
        return $this->commissionPercent;
    }

    public function getPriceSale() {
        return $this->priceSale;
    }

    public function getSaleQty() {
        return $this->saleQty;
    }

    public function getSaleAmount() {
        return $this->saleAmount;
    }

    public function getSaleDiscount() {
        return $this->saleDiscount;
    }

    public function getSaleCommission() {
        return $this->saleCommission;
    }

    public function getSalePriceSeller() {
        return $this->salePriceSeller;
    }

    public function getReturnSale() {
        return $this->returnSale;
    }

    public function getReturnQty() {
        return $this->returnQty;
    }

    public function getReturnAmount() {
        return $this->returnAmount;
    }

    public function getReturnDiscount() {
        return $this->returnDiscount;
    }

    public function getReturnCommission() {
        return $this->returnCommission;
    }

    public function getReturnPriceSeller() {
        return $this->returnPriceSeller;
    }

    public function getMarketSaleReport() {
        return $this->marketSaleReport;
    }

    public function getGood() {
        return $this->good;
    }

    public function setId($id): void {
        $this->id = $id;
    }

    public function setProductId($productId): void {
        $this->productId = $productId;
    }

    public function setOfferId($offerId): void {
        $this->offerId = $offerId;
    }

    public function setProductName($productName): void {
        $this->productName = $productName;
    }

    public function setBarcode($barcode): void {
        $this->barcode = $barcode;
    }

    public function setPrice($price): void {
        $this->price = $price;
    }

    public function setCommissionPercent($commissionPercent): void {
        $this->commissionPercent = $commissionPercent;
    }

    public function setPriceSale($priceSale): void {
        $this->priceSale = $priceSale;
    }

    public function setSaleQty($saleQty): void {
        $this->saleQty = $saleQty;
    }

    public function setSaleAmount($saleAmount): void {
        $this->saleAmount = $saleAmount;
    }

    public function setSaleDiscount($saleDiscount): void {
        $this->saleDiscount = $saleDiscount;
    }

    public function setSaleCommission($saleCommission): void {
        $this->saleCommission = $saleCommission;
    }

    public function setSalePriceSeller($salePriceSeller): void {
        $this->salePriceSeller = $salePriceSeller;
    }

    public function setReturnSale($returnSale): void {
        $this->returnSale = $returnSale;
    }

    public function setReturnQty($returnQty): void {
        $this->returnQty = $returnQty;
    }

    public function setReturnAmount($returnAmount): void {
        $this->returnAmount = $returnAmount;
    }

    public function setReturnDiscount($returnDiscount): void {
        $this->returnDiscount = $returnDiscount;
    }

    public function setReturnCommission($returnCommission): void {
        $this->returnCommission = $returnCommission;
    }

    public function setReturnPriceSeller($returnPriceSeller): void {
        $this->returnPriceSeller = $returnPriceSeller;
    }

    public function getTake() {
        return $this->take;
    }
    
    /**
     * Returns possible take as array.
     * @return array
     */
    public static function getTakeList() 
    {
        return [
            self::TAKE_OK => 'Проведено',
            self::TAKE_NO => 'Не проведено',
        ];
    }    
    
    /**
     * Returns take as string.
     * @return string
     */
    public function getTakeAsString()
    {
        $list = self::getTakeList();
        if (isset($list[$this->take]))
            return $list[$this->take];
        
        return 'Unknown';
    }    

    public function setTake($take): void {
        $this->take = $take;
    }

    /**
     * Добавить строку отчета
     * @param MarketSaleReport $marketSaleReport
     * @return void
     */
    public function setMarketSaleReport($marketSaleReport): void {
        $this->marketSaleReport = $marketSaleReport;
        $marketSaleReport->addMarketSaleReportItem($this);
    }

    public function setGood($good): void {
        $this->good = $good;
    }

    public function getRowNumber() {
        return $this->rowNumber;
    }

    public function setRowNumber($rowNumber): void {
        $this->rowNumber = $rowNumber;
    }

    /**
     * Массив для формы
     * @return array 
     */
    public function toArray()
    {
        $result = [
            'comissionPercent' => $this->getCommissionPercent(),
            'good' => ($this->getGood()) ? $this->getGood()->toArray():null,
            'offerId' => $this->getOfferId(),
            'price' => $this->getPrice(),
            'priceSale' => $this->getPriceSale(),
            'productName' => $this->getProductName(),
            'returnAmount' => $this->getReturnAmount(),
            'returnComission' => $this->getReturnCommission(),
            'returnDiscount' => $this->getReturnDiscount(),
            'returnPriceSeller' => $this->getReturnPriceSeller(),
            'returnQty' => $this->getReturnQty(),
            'returnSale' => $this->getReturnSale(),
            'rowNumber' => $this->getRowNumber(),
            'saleAmount' => $this->getSaleAmount(),
            'saleComission' => $this->getSaleCommission(),
            'saleDiscount' => $this->getSaleDiscount(),
            'salePriceSeller' => $this->getSalePriceSeller(),
            'saleQty' => $this->getSaleQty(),
            'id' => $this->getId(),
        ];
        
        return $result;
    }        
}
