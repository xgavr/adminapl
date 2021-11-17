<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service;

use Laminas\ServiceManager\ServiceManager;
use Application\Entity\Raw;
use Application\Entity\Rawprice;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Application\Entity\MarketPriceSetting;
use Application\Entity\Rate;
use Application\Entity\Images;
use Application\Entity\Goods;
use Application\Entity\Article;
use Laminas\Filter\Compress;
use Application\Entity\GenericGroup;
use Application\Entity\SupplySetting;
use Company\Entity\Office;
use Application\Entity\Shipping;
use Application\Entity\GoodAttributeValue;
use Application\Entity\Attribute;
use Application\Entity\GoodSupplier;

use Bukashk0zzz\YmlGenerator\Model\Offer\OfferSimple;
use Bukashk0zzz\YmlGenerator\Model\Category;
use Bukashk0zzz\YmlGenerator\Model\Currency;
use Bukashk0zzz\YmlGenerator\Model\Delivery;
use Bukashk0zzz\YmlGenerator\Model\ShopInfo;
use Bukashk0zzz\YmlGenerator\Settings;
use Bukashk0zzz\YmlGenerator\Generator;

/**
 * Description of MarketService
 *
 * @author Daddy
 */
class MarketManager
{
    const MARKET_FOLDER       = './data/market'; // папка с прайсами
    
    const APL_BASE_URL = 'https://autopartslist.ru';
    
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /*
     * @var \Admin\Service\FtpManager
     */
    private $ftpManager;    
  
    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager, $ftpManager)
    {
        $this->entityManager = $entityManager;
        $this->ftpManager = $ftpManager;
        
        if (!file_exists(self::MARKET_FOLDER)){
            mkdir(self::MARKET_FOLDER);
        }    
    }
    
    /**
     * Полное имя файла
     * @param MarketPriceSetting $market
     * @param integer $zip
     * @return string
     */
    public function filenamePath($market, $zip=0)
    {
        if ($zip){
            $filename = $market->getFilenameZip();
        } else {
            $filename = $market->getFilenameExt();
        } 
        return self::MARKET_FOLDER.'/'.$filename;        
    }
    
    /**
     * A helper method which assigns new rates to the market.
     * @param MarketPriceSetting $market
     * @param array $rateIds
     */
    private function assignRates($market, $rateIds)
    {
        // Remove old rate(s).
        $market->getRates()->clear();
        
        // Assign new rate(s).
        if (is_array($rateIds)){
            foreach ($rateIds as $rateId) {
                if (!empty($rateId)){
                    $rate = $this->entityManager->getRepository(Rate::class)
                            ->find($rateId);
                    if ($rate==null) {
                        throw new \Exception('Not found rate by ID');
                    }

                    $market->addRate($rate);
                }    
            }
        }    
    }    
    
    /**
     * Добавить настройку прайса
     * @param array $data
     * @return MarketPriceSetting
     */
    public function addMarketSetting($data)
    {
        $market = new MarketPriceSetting();
        $market->setBlockRowCount($data['blockRowCount']);
        $market->setFilename($data['filename']);
        $market->setFormat($data['format']);
        $market->setGoodSetting($data['goodSetting']);
        $market->setGroupSetting($data['groupSetting']);
        $market->setImageCount($data['imageCount']);
        $market->setInfo($data['info']);
        $market->setMaxPrice($data['maxPrice']);
        $market->setMaxRowCount($data['maxRowCount']);
        $market->setMinPrice($data['minPrice']);
        $market->setName($data['name']);
        $market->setProducerSetting($data['producerSetting']);
        $market->setStatus($data['status']);
        $market->setSupplierSetting($data['supplierSetting']);
        $market->setTokenGroupSetting($data['tokenGroupSetting']);
        $market->setRegion($data['region']);
        $market->setSupplier($data['supplier']);
        $market->setPricecol($data['pricecol']);
        $market->setMovementLimit($data['movementLimit']);
        $market->setNameSetting($data['nameSetting']);
        $market->setRestSetting($data['restSetting']);
        $market->setTdSetting($data['tdSetting']);
        $market->setShipping($data['shipping']);
        
        $this->assignRates($market, $data['rates']);        
        $this->entityManager->persist($market);        
        $this->entityManager->flush();        
        return $market;
    }
    
    /**
     * Обновить настройку прайса
     * 
     * @param MarketPriceSetting $market
     * @param array $data
     * @return MarketPriceSetting
     */
    public function updateMarketSetting($market, $data)
    {
        $market->setBlockRowCount($data['blockRowCount']);
        $market->setFilename($data['filename']);
        $market->setFormat($data['format']);
        $market->setGoodSetting($data['goodSetting']);
        $market->setGroupSetting($data['groupSetting']);
        $market->setImageCount($data['imageCount']);
        $market->setInfo($data['info']);
        $market->setMaxPrice($data['maxPrice']);
        $market->setMaxRowCount($data['maxRowCount']);
        $market->setMinPrice($data['minPrice']);
        $market->setName($data['name']);
        $market->setProducerSetting($data['producerSetting']);
        $market->setRegion($data['region']);
        $market->setSupplier($data['supplier']);
        $market->setStatus($data['status']);
        $market->setSupplierSetting($data['supplierSetting']);
        $market->setTokenGroupSetting($data['tokenGroupSetting']);
        $market->setPricecol($data['pricecol']);
        $market->setMovementLimit($data['movementLimit']);
        $market->setNameSetting($data['nameSetting']);
        $market->setRestSetting($data['restSetting']);
        $market->setTdSetting($data['tdSetting']);
        $market->setShipping($data['shipping']);
        
        $this->assignRates($market, $data['rates']);
        $this->entityManager->persist($market);
        $this->entityManager->flush();
        
        return $market;
    }
    
    /**
     * Удалить настройку прайс листа
     * 
     * @param MarketPriceSetting $market
     */
    public function removeMarketPriceSetting($market)
    {
        $market->getRates()->clear();
        $this->entityManager->remove($market);
        $this->entityManager->flush();
    }

    /**
     * Доставки региона
     * 
     * @param Region $region
     * @param array
     */
    public function regionShipping($region)
    {
        $result = ['не указана'];
        $offices = $this->entityManager->getRepository(Office::class)
                ->findBy(['region' => $region->getId(), 'status' => Office::STATUS_ACTIVE]);
                
        foreach ($offices as $office){
            $shippings = $this->entityManager->getRepository(Shipping::class)
                    ->findBy(['office' => $office->getId(), 'status' => Shipping::STATUS_ACTIVE]);
            foreach ($shippings as $shipping){
                $result[$shipping->getId()] = $shipping->getName().' ('.$office->getName().')';                
            }    
        }
        return $result;
    }
    
    /**
     * Получить картинки товара
     * @param Goods $good
     * @param MarketPriceSetting $market
     * @return array 
     */
    private function images($good, $market)
    {
        $imageList = [];
        if (!empty($market->getImageCount())){            
            if ($market->getGoodSetting() == MarketPriceSetting::IMAGE_ALL){
                $images = $this->entityManager->getRepository(Images::class)
                        ->findBy(['good' => $good->getId()], null, $market->getImageCountOrNull());
            }
            if ($market->getGoodSetting() == MarketPriceSetting::IMAGE_MATH){
                $images = $this->entityManager->getRepository(Images::class)
                        ->findBy(['good' => $good->getId(), 'similar' => Images::SIMILAR_MATCH], null, $market->getImageCountOrNull());
            }
            if (!empty($images)){
                foreach ($images as $image){
                    if ($image->allowTransfer()){
                        $imageList[] = $this::APL_BASE_URL.'/images/api/'.$good->getAplId().'/'.$image->getName();
                    }    
                    $this->entityManager->detach($image);
                }
            }
            if ($market->getGoodSetting() == MarketPriceSetting::IMAGE_MATH && count($imageList) == 0){
                return false;
            }
            if ($market->getGoodSetting() == MarketPriceSetting::IMAGE_SIMILAR && count($imageList) == 0){
                return false;
            }
        }    
        return $imageList;        
    }
    
    /**
     * Строки прайсов товара
     * @param Goods $good
     * @param MarketPriceSetting $market
     */
    private function rawprices($good, $market)
    {
        $rp = [
            'realrest' => 0,
            'speed' => 3,
            'orderbefore' => 12,
        ];
        
        $articles = $this->entityManager->getRepository(Article::class)
                ->findBy(['good' => $good->getId()]);
        foreach ($articles as $article){
            $rawprices = $this->entityManager->getRepository(Rawprice::class)
                    ->findBy([
                        'code' => $article->getId(),
                        'status' => Rawprice::STATUS_PARSED,
                    ]);        
            foreach ($rawprices as $rawprice){
                $supplier = $rawprice->getRaw()->getSupplier();
                if ($market->getSupplier()){
                    if ($market->getSupplier()->getId() != $supplier->getId()){
                        continue;
                    }
                }    
                foreach ($supplier->getSupplySettings() as $supplySetting){
                    $supspeed = $rp['speed'];
                    if ($market->getRegion()->getId() == $supplySetting->getOffice()->getRegion()->getId() 
                            && $supplySetting->getStatus() == SupplySetting::STATUS_ACTIVE){
                        $supspeed = $supplySetting->getSupplyTimeAsDayWithSat();
                        if ($rp['speed'] > $supspeed){
                            $rp['orderbefore'] = $supplySetting->getOrderBeforeHMax12();
                            $rp['speed'] = $supspeed;
                        }
                    }
                }
                if ($rawprice->getRealRest()){
                    $rp['realrest'] += $rawprice->getRealRest();
                }
                $this->entityManager->detach($supplier);
                $this->entityManager->detach($rawprice);
            }
            $this->entityManager->detach($article);
        }        
        
        return $rp;
    }
    
    /**
     * Остатки и доставки
     * @param Goods $good
     * @param MarketPriceSetting $market
     */
    private function restShipping($good, $market)
    {
        $rp = [
            'realrest' => 0,
            'speed' => 3,
            'orderbefore' => 12,
        ];
        
        $goodSuppliers = $this->entityManager->getRepository(GoodSupplier::class)
                ->goodSuppliers($good, $market);
        foreach ($goodSuppliers as $goodSupplier){
            $rp['realrest'] += $goodSupplier->getRest();

            $supplier = $goodSupplier->getSupplier();
            $supplySettings = $this->entityManager->getRepository(SupplySetting::class)
                    ->supplySettings($supplier, null, $market->getRegion());
            foreach ($supplier->getSupplySettings() as $supplySetting){
                $supspeed = $supplySetting->getSupplyTimeAsDayWithSat();
                if ($rp['speed'] > $supspeed){
                    $rp['orderbefore'] = $supplySetting->getOrderBeforeHMax12();
                    $rp['speed'] = $supspeed;
                }
                $this->entityManager->detach($supplySetting);
            }            
            $this->entityManager->detach($goodSupplier);
        }        
        
        return $rp;
    }

    /**
     * Описание товара
     * @param MarketPriceSetting $market
     * @param Goods $good
     * @return string
     */
    private function description($market, $good)
    {
        $result = "<![CDATA[<ul>"
                . "<li>{$good->getName()}</li>"
                . "<li>Производитель: {$good->getProducer()->getName()}</li>"
                . "<li>Артикул: {$good->getCode()}</li>";
                
        $values = $this->entityManager->getRepository(GoodAttributeValue::class)
                ->findBy(['good' => $good->getId(), 'status' => Attribute::STATUS_ACTIVE]);
        if ($values){
            foreach ($values as $value){
                $result .= "<li>{$value->getAttribute()->getName()}: {$value->getAttributeValue()->getValue()}</li>";
            }
        }    
        $result .= 
        $result .= "</ul>]]";
        return $result;        
    }
    
    /**
     * Сохранение файла прайса
     * 
     * @param MarketPriceSetting $market
     * @param integer $rows
     */
    private function fileUnload($market, $rows)
    {
        $filename = $market->getFilenameExt();
        $path = self::MARKET_FOLDER.'/'.$filename;

        $this->ftpManager->putMarketPriceToApl(['source_file' => $path, 'dest_file' => $filename]);            

        $zipFilename = $market->getFilenameZip();
        $zipPath = self::MARKET_FOLDER.'/'.$zipFilename;

        $filter = new Compress([
            'adapter' => 'Zip',
            'options' => [
                'archive' => $zipPath,
            ],
        ]);
        $compressed = $filter->filter($path);
        $this->ftpManager->putMarketPriceToApl(['source_file' => $zipPath, 'dest_file' => $zipFilename]);
        
        $market->setRowUnload($rows);
        $market->setDateUnload(date('Y-m-d H:i:s'));
        $this->entityManager->persist($market);
        $this->entityManager->flush($market);
        
        return;
    }
    
    /**
     * Данные для прайса
     * @param MarketPriceSetting $market
     * @return array
     */
    public function marketXLSX($market)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue("A1", 'Артикул');
        $sheet->setCellValue("B1", 'Производитель');
        $sheet->setCellValue("C1", 'Наименование');
        $sheet->setCellValue("D1", 'Описание');
        if (!empty($market->getImageCount())){
            $sheet->setCellValue("E1", 'Картинка');
        }    
        $sheet->setCellValue("F1", 'Наличие');
        $sheet->setCellValue("G1", 'Цена');
        $k = 2;
        $rows = 0;
        
        $goodsQuery = $this->entityManager->getRepository(MarketPriceSetting::class)
                ->marketQuery($market);
        
        $iterable = $goodsQuery->iterate();
        foreach ($iterable as $row){
            foreach ($row as $good){
                if (!empty($market->getImageCount())){
                    $images = $this->images($good, $market);
                    if ($images === false){
                        continue;
                    }
                }    
                
//                $rawprices = $this->rawprices($good, $market);
                $rawprices = $this->restShipping($good, $market);
                if ($rawprices['realrest'] == 0){
                    continue;
                }
                
                $opts = $good->getOpts();
                $sheet->setCellValue("A$k", $good->getCode());
                $sheet->setCellValue("B$k", $good->getProducer()->getName());
                $sheet->setCellValue("C$k", $good->getName());
                $sheet->setCellValue("D$k", $good->getDescription());
                if (!empty($market->getImageCount())){
                    $sheet->setCellValue("E$k", implode(';', $images));
                }
                $sheet->setCellValue("F$k", $rawprices['realrest']);
                $sheet->setCellValue("G$k", $opts[$market->getPricecol()]);
//                $sheet->setCellValue("G$k", $rawprice->getRealPrice());

                $this->entityManager->detach($good);
                $k++;
                $rows++;
                if ($market->getMaxRowCount()){
                    if ($rows >= $market->getMaxRowCount()){
                        break;
                    }
                }
                $this->entityManager->detach($good);
            }    
        }
        
        $filename = $market->getFilenameExt();
        $path = self::MARKET_FOLDER.'/'.$filename;

        $writer = new Xlsx($spreadsheet);
        $writer->save($path);

        $this->fileUnload($market, $rows);
        return;
    }
    
    /**
     * Данные для прайса
     * @param MarketPriceSetting $market
     * @return array
     */
    public function marketYML($market)
    {
        $filename = $market->getFilenameExt();
        $path = self::MARKET_FOLDER.'/'.$filename;

        $settings = (new Settings())
            ->setOutputFile($path)
            ->setEncoding('UTF-8')
        ;

        // Creating ShopInfo object (https://yandex.ru/support/webmaster/goods-prices/technical-requirements.xml#shop)
        $shopInfo = (new ShopInfo())
            ->setName('APL')
            ->setCompany('ООО "АПЛ Сервис"')
            ->setUrl(self::APL_BASE_URL)
        ;

        // Creating currencies array (https://yandex.ru/support/webmaster/goods-prices/technical-requirements.xml#currencies)
        $currencies = [];
        $currencies[] = (new Currency())
            ->setId('RUR')
            ->setRate(1)
        ;
        
        // Creating categories array (https://yandex.ru/support/webmaster/goods-prices/technical-requirements.xml#categories)
        $groups = $this->entityManager->getRepository(GenericGroup::class)
                ->masterGroups();        
        $priceGroups = [999 => 'Прочее'];

        // Creating offers array (https://yandex.ru/support/webmaster/goods-prices/technical-requirements.xml#offers)
        $offers = [];
        $rows = 0;
        $goodsQuery = $this->entityManager->getRepository(MarketPriceSetting::class)
                ->marketQuery($market);
        $iterable = $goodsQuery->iterate();
        foreach ($iterable as $row){
            foreach ($row as $good){
                $images = $this->images($good, $market);
                if ($images === false){
                    continue;
                }
//                $rawprices = $this->rawprices($good, $market);
                $rawprices = $this->restShipping($good, $market);
                if ($rawprices['realrest'] == 0){
                    continue;
                }
                
                $opts = $good->getOpts();

                $categoryId = 999;
                if ($good->getGenericGroup()){
                    $key = $good->getGenericGroup()->getAssemblyGroup();
                    if (array_key_exists(md5($key), $groups)) {
                        $categoryId = $groups[md5($key)]['id'];
                        $priceGroups[$categoryId] = $key;
                    }    
                }
                
                $offer = new OfferSimple();
                $offer->setId($good->getAplId())
                    ->setAvailable(true)
                    ->setUrl(self::APL_BASE_URL.'/catalog/view/id/'.$good->getAplId().'?utm_source='.$market->getId().'&utm_term='.$good->getAplId())
                    ->setPrice($opts[$market->getPricecol()])
                    ->setCurrencyId('RUR')
                    ->setCategoryId($categoryId)
                    ->setDelivery(true)
                    ->setName($good->getNameProducerCode())
                    ->setPictures($images)
                    ->setVendor($good->getProducer()->getName())
                    ->setVendorCode($good->getCode())
                    ->setDescription($this->description($market, $good))
                    ->setStore(false)
                    ->setPickup(true)                       
                ;
                if ($market->getShipping()){
                    $delivery = (new Delivery())
                        ->setDays($rawprices['speed'])
                        ->setOrderBefore($rawprices['orderbefore'])
                        ->setCost($market->getShipping()->getOrderRateTrip($opts[$market->getPricecol()]))
                        ;
                    $offer->setDelivery(true)
                        ->addDeliveryOption($delivery) 
                        ;
                }    
                
                $offers[] = $offer;
                
                $this->entityManager->detach($good);
                $rows++;
            }    
            if ($market->getMaxRowCount()){
                if ($rows >= $market->getMaxRowCount()){
                    break;
                }
            }
        }
        
        ksort($priceGroups);
        foreach ($priceGroups as $key=>$value){
            $categories[] = (new Category())
                ->setId($key)
                ->setName($value)
            ;        
        }    
        
        // Optional creating deliveries array (https://yandex.ru/support/partnermarket/elements/delivery-options.xml)
        $deliveries = [];
        if ($market->getShipping()){
            $deliveries[] = (new Delivery())
                ->setCost($market->getShipping()->getRateTrip())
                ->setDays(1)
                ->setOrderBefore(12)
            ;
        }    

        (new Generator($settings))->generate(
            $shopInfo,
            $currencies,
            $categories,
            $offers,
            $deliveries
        );        
        
        $this->fileUnload($market, $rows);
        
        return;
    }

    /**
     * запусть выгрузку прайса
     * @param MarketPriceSetting $market
     */
    public function unload($market)
    {
        ini_set('memory_limit', '4096M');
        set_time_limit(0);

        if ($market->getFormat() == MarketPriceSetting::FORMAT_XLSX){
            $this->marketXLSX($market);
        }
        if ($market->getFormat() == MarketPriceSetting::FORMAT_YML){
            $this->marketYML($market);
        }
        return;
    }
    
    /**
     * Выгрузка в zzap только апл
     * 
     * @param array $params
     */
    public function aplToZzap($params = null)
    {
        $aplSupplierId = 7;
        $currentRaw = $this->entityManager->getRepository(Raw::class)
                ->findOneBy(['supplier' => $aplSupplierId, 'status' => Raw::STATUS_PARSED], ['id' => 'DESC']);
        
        if ($currentRaw){
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $rawprices = $this->entityManager->getRepository(Rawprice::class)
                    ->findBy(['raw' => $currentRaw, 'status' => Rawprice::STATUS_PARSED]);
            
            $sheet->setCellValue("A1", 'Артикул');
            $sheet->setCellValue("B1", 'Производитель');
            $sheet->setCellValue("C1", 'Наименование');
            $sheet->setCellValue("D1", 'Наличие');
            $sheet->setCellValue("E1", 'Цена');

            $k = 2;
            foreach ($rawprices as $rawprice){
                if (!$rawprice->getComment() && $rawprice->getRealRest() && $rawprice->getCode()){
                    $good = $rawprice->getCode()->getGood();
                    if ($good){
                        $opts = $good->getOpts();
                        $sheet->setCellValue("A$k", $good->getCode());
                        $sheet->setCellValue("B$k", $good->getProducer()->getName());
                        $sheet->setCellValue("C$k", $good->getName());
                        $sheet->setCellValue("D$k", $rawprice->getRealRest());
//                        $sheet->setCellValue("E$k", $opts[5]);
                        $sheet->setCellValue("E$k", $rawprice->getRealPrice());
                        $k++;
                    }    
                }    
            }
            
            $filename = 'apl2zzap.xlsx';
            $path = self::MARKET_FOLDER.'/'.$filename;
            
            $writer = new Xlsx($spreadsheet);
            $writer->save($path);
            
            $this->ftpManager->putMarketPriceToApl(['source_file' => $path, 'dest_file' => $filename]);            
        }
        
        return;
    }    
}
