<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ApiMarketPlace\Service;

use Gam6itko\OzonSeller\Service\V2\CategoryService as CategoryServiceV2;
use Gam6itko\OzonSeller\Service\V3\CategoryService as CategoryServiceV3;
use Gam6itko\OzonSeller\Service\V3\Posting\FbsService;
use Gam6itko\OzonSeller\Service\V2\ProductService as ProductService2;
use Gam6itko\OzonSeller\Service\V1\ProductService;
use GuzzleHttp\Client as GuzzleClient;
use Http\Adapter\Guzzle6\Client as GuzzleAdapter;
use Symfony\Component\HttpClient\Psr18Client;
use Application\Entity\Goods;
use Application\Entity\ScaleTreshold;
use Application\Entity\MarketPriceSetting;
use Application\Entity\GoodSupplier;

/**
 * Description of OzonService
 * 
 * @author Daddy
 */
class OzonService {
    
    const OZON_MAX_PRICE_UPDATE = 1000; //макс пакет для обновления цен в озоне

    const OZON_MAX_STOCK_UPDATE = 100; //макс пакет для обновления остатков в озоне

    /**
     * Raw request data (json) for webhook methods
     *
     * @var string
     */
    protected $input;
    
    
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * Admin manager.
     * @var \Admin\Service\AdminManager
     */
    private $adminManager;
    
    /**
     * Request.
     * @var \ApiMarketPlace\Service\Request
     */
    private $request;
    
    /**
     * Update.
     * @var \ApiMarketPlace\Service\Update
     */
    private $updateManager;
    
    /**
     * Market manager.
     * @var \Application\Service\MarketManager
     */
    private $marketManager;

//    private $ozon_host = 'http://cb-api.ozonru.me/'; //sandbox
    
    public function __construct($entityManager, $adminManager, $request, $updateManager,
            $marketManager)
    {
        $this->entityManager = $entityManager;
        $this->adminManager = $adminManager;
        $this->request = $request;
        $this->updateManager = $updateManager;
        $this->marketManager = $marketManager;
    }
    
    public function сategoryTree()
    {
        $settings = $this->adminManager->getApiMarketPlaces();
        
        $config = [
            'clientId' => $settings['ozon_client_id'],
            'apiKey' => $settings['ozon_api_key'],
//            'host' => $this->ozon_host,
        ];
        
        $client = new Psr18Client();
        $svc = new CategoryServiceV2($config, $client);
        
        $categoryTree = $svc->tree();
//        $attributes = $svc->attribute(17038826);
        var_dump($categoryTree); exit;
        
        return $categoryTree;
    }
    
    
    /**
     * Обновить цену товара
     * @param array $input
     */
    private function updatePrice($input)
    {
        $settings = $this->adminManager->getApiMarketPlaces();

        $config = [
            'clientId' => $settings['ozon_client_id'],
            'apiKey' => $settings['ozon_api_key'],
//            'host' => $this->ozon_host,
        ];
        
        $client = new Psr18Client();
        $svcProduct = new ProductService($config, $client);

        $result = $svcProduct->importPrices($input);

        return $result;        
    }
    
    /**
     * Обновить остаток товара
     * @param array $input
     */
    private function updateStock($input)
    {
        $settings = $this->adminManager->getApiMarketPlaces();

        $config = [
            'clientId' => $settings['ozon_client_id'],
            'apiKey' => $settings['ozon_api_key'],
//            'host' => $this->ozon_host,
        ];
        
        $client = new Psr18Client();
        $svcProduct = new ProductService($config, $client);

        $result = $svcProduct->importStocks($input);

        return $result;        
    }
    
    /**
     * Товар на складах
     * @param string $last_id
     * @param integer $limit
     */
    private function stocks($last_id = '', $limit = 1000)
    {
        $settings = $this->adminManager->getApiMarketPlaces();

        $config = [
            'clientId' => $settings['ozon_client_id'],
            'apiKey' => $settings['ozon_api_key'],
        ];
        
        $client = new Psr18Client();
        $svcProduct = new ProductService2($config, $client);

        $result = $svcProduct->list(['last_id' => $last_id, 'limit' => $limit]);

        return $result;        
    }
    
    /**
     * Список отправлений
     * @param integer $limit
     */
    public function postingList($limit = 1000)
    {
        $settings = $this->adminManager->getApiMarketPlaces();

        $config = [
            'clientId' => $settings['ozon_client_id'],
            'apiKey' => $settings['ozon_api_key'],
        ];
        
        $client = new Psr18Client();
        $svcProduct = new FbsService($config, $client);

        $result = $svcProduct->list(['dir' => 'ASC', 'limit' => $limit, 'offset' => 0, 
            'filter' => [
                'since' => (new \DateTime('now - 90 days'))->format(DATE_W3C),
                'to' => (new \DateTime('now'))->format(DATE_W3C),
            ],
            'with' => [
                'financial_data' => true,
            ],
        ]);

        return $result;        
    }

    /**
     * Обновить цену товара
     * @param Goods $good
     */
    public function updateGoodPrice($good)
    {
        if (!$good->getAplId()){
            return;
        }
        
        $opts = $good->getOpts();
        
        $price = $good->getMarketPlacePriceOrPrice();
        $minPrice = $opts[ScaleTreshold::PRICE_COL_COUNT];
        
        $input = [
            'auto_action_enabled' => 'UNKNOWN',
            'currency_code' => 'RUB',
            'min_price' => $minPrice,
            'offer_id' => $good->getAplId(),
            'old_price' => 0,
            'price' => $price,
            'product_id' => $good->getId(),
        ];
        
        $result = $this->updatePrice($input);
        
        return $result;        
    }
    
    /**
     * Обновить остаток товара
     * @param Goods $good
     */
    public function updateGoodStock($good)
    {
        if (!$good->getAplId()){
            return;
        }
        
        $stock = 0;
        $goodSuppliers = $this->entityManager->getRepository(GoodSupplier::class)
                ->goodSuppliers($good->getId());
        foreach ($goodSuppliers as $goodSupplier){            
            if ($good->getPrice() > $goodSupplier['price']){
                $stock += $goodSupplier['rest'];
            }    
        }        
        
        $input = [
            'offer_id' => $good->getAplId(),
            'product_id' => $good->getId(),
            'stock' => $stock,
        ];
        
        $result = $this->updateStock($input);
        
        return $result;        
    }

    /**
     * Обнулить остатки
     */
    public function zeroing()
    {
        ini_set('memory_limit', '512M');
        set_time_limit(0);

        $last_id = '';
        $result = [];
        $stocks = [];
        while (true){
            $list = $this->stocks($last_id);
            $last_id = $list['last_id'];
            $items = $list['items'];
            if (count($items)){
                foreach ($items as $item){
                    
                    $stocks[] = [
                        'offer_id' => $item['offer_id'],
                        'product_id' => $item['product_id'],
                        'stock' => 0,
                    ];
                    
                    if (count($stocks) == self::OZON_MAX_STOCK_UPDATE){
                        $result = $this->updateStock(['stocks' => $stocks]);
                        $stocks = [];
                    }
                }
            } else {
                return $result;
            }
        }    
        
        if (count($stocks)){
            $result = $this->updateStock(['stocks' => $stocks]);
        }
        
        return $result;
    }
    
    /**
     * Получить файл лога
     * @param MarketPriceSetting $market
     * @param string $logName
     * @return string
     */
    public function logFile($market, $logName = '')
    {
        return $this->marketManager->ozonLogFile($market, $logName);        
    }
    
    /**
     * Запись лога преобразовать в строку
     * @param array $log
     * @return array
     */
    private function updateLogBody($log)
    {
        $result = [
            $log['offer_id'],
            $log['product_id'],
            $log['updated'],
        ];
        $errors = [];
        foreach ($log['errors'] as $error){
            $errors[] = $error['message'];
        }
        $result[] = implode(';', $errors);
        return $result;
    }
    
    /**
     * Заголовки таблицы логов
     * @return array
     */
    private function updateLogHeader()
    {
        return [
            'offer_id',
            'product_id',
            'updated',
            date('d.m.Y H:i:s'),
        ];
    }

    /**
     * Добавить лог обновления
     * @param MarketPriceSetting $market
     * @param array $result
     * @param string $logName
     */
    private function addToUpdateLog($market, $result, $logName = '')
    {
        $path = $this->logFile($market, $logName);
        
        if (!file_exists($path)){
            $handle = fopen($path, "a");
            fputcsv($handle, $this->updateLogHeader(), ';');
            fclose($handle);            
        }
        
        $handle = fopen($path, "a");
        foreach ($result as $value){
            fputcsv($handle, $this->updateLogBody($value), ';');
        }    
        fclose($handle);
        
        return;
    }
    
    /**
     * Удалить логи
     * @param type $market
     * @param type $logName
     * @return null
     */
    private function clearLog($market, $logName = '')
    {
        $path = $this->logFile($market, $logName);
        if (file_exists($path)){
            unlink($path);
        }
        
        return;
    }
    
    /**
     * Обновление цен из прайса
     * @param MarketPriceSetting $market
     * @return array
     */
    public function marketUpdate($market)
    {
        ini_set('memory_limit', '4096M');
        set_time_limit(0);

        $this->clearLog($market, 'prices');
        $this->clearLog($market, 'stocks');
        
        $out = [
//            'prices' => '<a href="/market-place/download-log/'.$market->getId().'?log=prices">Скачать лог обновления цен</a>',
//            'stocks' => '<a href="/market-place/download-log/'.$market->getId().'?log=stocks">Скачать лог обновления остатков</a>',
            'prices' => $market->getOzonLogDownloadLink('prices', 'Скачать лог обновления цен'),
            'stocks' => $market->getOzonLogDownloadLink('stocks', 'Скачать лог обновления остатков'),
        ];
        
        $goodsQuery = $this->entityManager->getRepository(MarketPriceSetting::class)
                ->marketQuery($market, 0, ['retailLimit' => 0]);
        $data = $goodsQuery->getResult(2);
        $prices = []; 
        $stocks = [];
//        $outRows = 0;
        foreach ($data as $good){

            $rawprices = $this->marketManager->restShipping($good['id'], $market, $good['price']);
            $lot = $rawprices['lot'];
            
            if ($rawprices['realrest'] == 0){
//                continue;
            }
            $realrest = $rawprices['realrest'];
            if ($market->getOzonUpdate() == MarketPriceSetting::OZON_ZEROING){
                $realrest = 0;
            }

            $opts = Goods::optPrices($good['price'], $good['meanPrice']);
            
            $prices[] = [
                'auto_action_enabled' => 'UNKNOWN',
                'currency_code' => 'RUB',
                'min_price' => $market->getExtraMinPrice($opts, $lot),
                'offer_id' => $good['aplId'],
                'old_price' => 0,
                'price' => $market->getExtraPrice($opts, $lot),
                'product_id' => $good['id'],                
            ];
            
            $stocks[] = [
                'offer_id' => $good['aplId'],
                'product_id' => $good['id'],
                'stock' => $realrest,
            ];
            
            if (count($prices) == self::OZON_MAX_PRICE_UPDATE){
                $result = $this->updatePrice(['prices' => $prices]);
                $this->addToUpdateLog($market, $result, 'prices');
                $prices = [];
            }

            if (count($stocks) == self::OZON_MAX_STOCK_UPDATE){
                $result = $this->updateStock(['stocks' => $stocks]);
                $this->addToUpdateLog($market, $result, 'stocks');
                $stocks = [];
            }

//            $outRows++;
//            if ($outRows >= $market->getMaxRowCount() * $market->getBlockRowCount()){
//                break;
//            }
//            if ($outRows >= MarketPriceSetting::MAX_BLOCK_ROW_COUNT * $market->getBlockRowCount()){
//                break;
//            }        
        }    

        if (count($prices)){
            $result = $this->updatePrice(['prices' => $prices]);
            $this->addToUpdateLog($market, $result, 'prices');
        }
        if (count($stocks)){
            $result = $this->updateStock(['stocks' => $stocks]);
            $this->addToUpdateLog($market, $result, 'stocks');
        }
        
        return $out;
    }
    
    /**
     * Обновление массива прайсов
     * @param array $markets
     */
    public function updateMarkets($markets)
    {
        foreach ($markets as $market){
            if ($market->getOzonUpdate() == MarketPriceSetting::OZON_UPDATE){
                $this->marketUpdate($market);
            }    
        }
        
        return;
    }
}
