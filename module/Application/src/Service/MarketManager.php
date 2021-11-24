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
    
    private $supply;
    
    private $groups;
    
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
        
        $this->supply = [];
        $this->groups = [];
    }
    
    /**
     * Получить папку с прайсам
     * @param integer $market
     */
    public function folder($market)
    {
        $folder = self::MARKET_FOLDER.'/'.$market->getId();
        if (!file_exists($folder)){
            mkdir($folder);
        }
        
        return $folder;                
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
     * Полное имя файла c block
     * @param MarketPriceSetting $market
     * @param integer $zip
     * @param integer $block
     * @return string
     */
    public function blockFilenamePath($market, $zip=0, $block=0)
    {
        if ($zip){
            $filename = $market->getBlockFilenameZip($block);
        } else {
            $filename = $market->getBlockFilenameExt($block);
        } 

        return $this->folder($market).'/'.$filename;        
    }
    
    /**
     * Полное имя файла c offset
     * @param MarketPriceSetting $market
     * @param integer $zip
     * @param integer $offset
     * @return string
     */
    public function offsetFilenamePath($market, $zip=0, $offset=0)
    {
        if ($zip){
            $filename = $market->getOffsetFilenameZip($offset);
        } else {
            $filename = $market->getOffsetFilenameExt($offset);
        } 

        return $this->folder($market).'/'.$filename;        
    }
    
    /**
     * Полное имя файла
     * @param MarketPriceSetting $market
     * @param integer $zip
     * @return string
     */
    public function filenamesPath($market, $zip=0)
    {
        $block = 0;
        $result = [];
        $maxBlock = ($market->getBlockRowCount()) ? $market->getBlockRowCount():MarketPriceSetting::MAX_BLOCK_COUNT;
        while (true){            
            if ($zip){
                $filename = $market->getBlockFilenameZip($block);
            } else {
                $filename = $market->getBlockFilenameExt($block);
            } 
            $result[] = realpath(self::MARKET_FOLDER.'/'.$filename);        
            $block++;
            if ($block > $maxBlock){
                break;
            }
        }
        
        return $result;
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
        $result = ['не указан'];
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
     * @param array $good
     * @param MarketPriceSetting $market
     * @return array 
     */
    private function images($good, $market)
    {
        $imageList = [];
        if (!empty($market->getImageCount())){            
            if ($market->getGoodSetting() == MarketPriceSetting::IMAGE_ALL){
                $images = $this->entityManager->getRepository(Images::class)
                        ->arrayGoodImages($good['id'], ['limit' => $market->getImageCountOrNull()]);
            }
            if ($market->getGoodSetting() == MarketPriceSetting::IMAGE_MATH){
                $images = $this->entityManager->getRepository(Images::class)
                        ->arrayGoodImages($good['id'], ['similar' => Images::SIMILAR_MATCH, 'limit' => $market->getImageCountOrNull()]);
            }
            if (!empty($images)){
                foreach ($images as $image){
                    if (Images::isToTransfer($image['path'])){
                        $imageList[] = $this::APL_BASE_URL.'/images/api/'.$good['aplId'].'/'.$image['name'];
                    }    
//                    $this->entityManager->detach($image);
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
     * Получить лучшую поставку
     * @param GoodSupplier $goodSupplier
     * @param Region $region
     * @return SupplySetting
     */
    private function bestSupply($goodSupplier, $region)
    {
        $result = null;
        $speed = 999;
        $supplySettings = $this->entityManager->getRepository(SupplySetting::class)
                ->supplySettings($goodSupplier->getSupplier(), null, $region);
        foreach ($supplySettings as $supplySetting){
            $supspeed = $supplySetting->getSupplyTimeAsDayWithSat();
            if ($speed > $supspeed){
                $result = $supplySetting;
            }
        }                    
        return $result;
    }
    
    /**
     * Остатки и доставки
     * @param integer $goodId
     * @param MarketPriceSetting $market
     */
    private function restShipping($goodId, $market)
    {
        $rp = [
            'realrest' => 0,
            'speed' => 3,
            'orderbefore' => 12,
        ];
        
        $goodSuppliers = $this->entityManager->getRepository(GoodSupplier::class)
                ->goodSuppliers($goodId, $market);
        foreach ($goodSuppliers as $goodSupplier){
            $rp['realrest'] += $goodSupplier->getRest();

            $supplyKey = $goodSupplier->getSupplier()->getId().'_'.$market->getId();
            if (array_key_exists($supplyKey, $this->supply)){
                $supplySetting = $this->supply[$supplyKey];
            } else {
                $supplySetting = $this->bestSupply($goodSupplier, $market->getRegion());
                $this->supply[$supplyKey] = $supplySetting;
            }
            if ($supplySetting){
                $supspeed = $supplySetting->getSupplyTimeAsDayWithSat();
                if ($rp['speed'] > $supspeed){
                    $rp['orderbefore'] = $supplySetting->getOrderBeforeHMax12();
                    $rp['speed'] = $supspeed;
                }
            }    
            $this->entityManager->detach($goodSupplier);
        }        
        
        return $rp;
    }

    /**
     * Наименование товара
     * @param MarketPriceSetting $market
     * @param array $good
     * @return string
     */
    private function goodName($market, $good)
    {
        $result = "{$good['name']} {$good['producer']['name']} {$good['code']}";
                
        return $result;        
    }
    
    /**
     * Характеристики товара
     * @param MarketPriceSetting $market
     * @param array $good
     * @return string
     */
    private function description($market, $good)
    {
        $result = "<![CDATA[<ul>"
                . "<li>{$good['name']}</li>"
                . "<li>Производитель: {$good['producer']['name']}</li>"
                . "<li>Артикул: {$good['code']}</li>";
                
        $values = $this->entityManager->getRepository(GoodAttributeValue::class)
                ->descriptionAttribute($good['id']);
        if ($values){
            foreach ($values as $value){
                $result .= "<li>{$value['name']}: {$value['value']}</li>";
            }
        }    
        $result .= "</ul>]]";
        return $result;        
    }
    
    /**
     * Сохранение файла прайса
     * 
     * @param MarketPriceSetting $market
     * @param integer $block
     */
    private function fileUnload($market, $block = 0)
    {
        $filename = $market->getBlockFilenameExt($block);
        $path = $this->blockFilenamePath($market, 0, $block);

        $this->ftpManager->putMarketPriceToApl(['source_file' => $path, 'dest_file' => $filename]);            
                
        return;
    }
    
    /**
     * Данные для прайса
     * @param MarketPriceSetting $market
     * @param integer $offset
     * @param integer $block
     * @return array
     */
    public function marketXLSX($market, $offset = 0, $block = 0)
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
        $rows = $outRows = 0;
        
        $goodsQuery = $this->entityManager->getRepository(MarketPriceSetting::class)
                ->marketQuery($market, $offset);
        $data = $goodsQuery->getResult(2);
        
        foreach ($data as $good){
//            var_dump($good); exit;
            $rows++;
            if (!empty($market->getImageCount())){
                $images = $this->images($good, $market);
                if ($images === false){
                    continue;
                }
            }    

    //                $rawprices = $this->rawprices($good, $market);
            $rawprices = $this->restShipping($good['id'], $market);
            if ($rawprices['realrest'] == 0){
                continue;
            }

            $opts = Goods::optPrices($good['price'], $good['meanPrice']);
            $sheet->setCellValue("A$k", $good['code']);
            $sheet->setCellValue("B$k", $good['producer']['name']);
            $sheet->setCellValue("C$k", $good['name']);
            $sheet->setCellValue("D$k", $good['description']);
            if (!empty($market->getImageCount())){
                $sheet->setCellValue("E$k", implode(';', $images));
            }
            $sheet->setCellValue("F$k", $rawprices['realrest']);
            $sheet->setCellValue("G$k", $opts[$market->getPricecol()]);
    //                $sheet->setCellValue("G$k", $rawprice->getRealPrice());

            //$this->entityManager->detach($good);
            $k++;
            $outRows++;
            if ($market->getMaxRowCount() && $outRows >= $market->getMaxRowCount()){
                break;
            }
            if ($outRows >= MarketPriceSetting::MAX_BLOCK_ROW_COUNT){
                break;
            }        
        }    
        $path = $this->blockFilenamePath($market, 0, $block);

        $writer = new Xlsx($spreadsheet);
        $writer->save($path);

        $this->fileUnload($market, $block);
        
        return ['rows' => $rows, 'outRows' => $outRows];
    }
    
    /**
     * Данные для прайса
     * @param MarketPriceSetting $market
     * @param integer $offset
     * @param integer $block
     * @return array
     */
    public function marketYML($market, $offset = 0, $block=0)
    {
        $path = $this->blockFilenamePath($market, 0, $block);

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
        if (empty($this->groups)){
            $this->groups = $this->entityManager->getRepository(GenericGroup::class)
                    ->masterGroups();        
        }    
        $priceGroups = [999 => 'Прочее'];

        // Creating offers array (https://yandex.ru/support/webmaster/goods-prices/technical-requirements.xml#offers)
        $offers = [];
        $rows = $outRows = 0;
        $goodsQuery = $this->entityManager->getRepository(MarketPriceSetting::class)
                ->marketQuery($market, $offset);
        $data = $goodsQuery->getResult(2);
        foreach ($data as $good){
//            var_dump($good); exit;
            $rows++;
            $images = $this->images($good, $market);
            if ($images === false){
                continue;
            }
//                $rawprices = $this->rawprices($good, $market);
            $rawprices = $this->restShipping($good['id'], $market);
            if ($rawprices['realrest'] == 0){
                continue;
            }

            $opts = Goods::optPrices($good['price'], $good['meanPrice']);

            $categoryId = 999;
            if (!empty($good['genericGroup'])){
                $key = $good['genericGroup']['assemblyGroup'];
                if (array_key_exists(md5($key), $this->groups)) {
                    $categoryId = $this->groups[md5($key)]['id'];
                    $priceGroups[$categoryId] = $key;
                }    
            }

            $offer = new OfferSimple();
            $offer->setId($good['aplId'])
                ->setAvailable(true)
                ->setUrl(self::APL_BASE_URL.'/catalog/view/id/'.$good['aplId'].'?utm_source='.$market->getId().'&utm_term='.$good['aplId'])
                ->setPrice($opts[$market->getPricecol()])
                ->setCurrencyId('RUR')
                ->setCategoryId($categoryId)
                ->setDelivery(true)
                ->setName($this->goodName($market, $good))
                ->setPictures($images)
                ->setVendor($good['producer']['name'])
                ->setVendorCode($good['code'])
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

//            $this->entityManager->detach($good);
            $outRows++;
            if ($market->getMaxRowCount() && $outRows >= $market->getMaxRowCount()){
                break;
            }
            if ($outRows >= MarketPriceSetting::MAX_BLOCK_ROW_COUNT){
                break;
            }
        }
        
        unset($data);
        
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
        
        $this->fileUnload($market, $block);
        
        return ['rows' => $rows, 'outRows' => $outRows];
    }

    /**
     * запусть выгрузку прайса
     * @param MarketPriceSetting $market
     */
    public function unload($market)
    {
        ini_set('memory_limit', '4096M');
        set_time_limit(0);
        
        $maxRowCount = ($market->getMaxRowCount()) ? $market->getMaxRowCount():MarketPriceSetting::MAX_BLOCK_ROW_COUNT;
        $maxBlockCount = ($market->getBlockRowCount()) ? $market->getBlockRowCount():MarketPriceSetting::MAX_BLOCK_COUNT;

        $outRows = $offset = $blocks = 0;
        while (true){
            if ($blocks >= $maxBlockCount){
                break;
            }
            $blocks++;
            if ($market->getFormat() == MarketPriceSetting::FORMAT_XLSX){
                $result = $this->marketXLSX($market, $offset, $blocks);
            }
            if ($market->getFormat() == MarketPriceSetting::FORMAT_YML){
                $result = $this->marketYML($market, $offset, $blocks);
            }
            $outRows += $result['outRows'];
            if (!$market->getBlockRowCount() && $result['outRows'] < $maxRowCount){
                break;
            }
            if ($result['rows']){
                $offset += $result['rows'];
            } else {
                $offset += $maxRowCount;                
            }    
//            $this->entityManager->clear();
        }    

        $zipFilename = $market->getFilenameZip();
        $zipPath = self::MARKET_FOLDER.'/'.$zipFilename;

        $filter = new Compress([
            'adapter' => 'Zip',
            'options' => [
                'archive' => $zipPath,
            ],
        ]);
        $filter->filter($this->folder($market));

        $this->entityManager->getConnection()
                ->update('market_price_setting', ['row_unload' => $outRows, 'date_unload' => date('Y-m-d H:i:s')],['id' => $market->getId()]);

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
