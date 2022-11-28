<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ApiMarketPlace\Service;

use Gam6itko\OzonSeller\Service\V2\CategoryService as CategoryServiceV2;
use Gam6itko\OzonSeller\Service\V3\CategoryService as CategoryServiceV3;
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
     * Обновить цену товара
     * @param Goods $good
     */
    public function updateGoodPrice($good)
    {
        if (!$good->getAplId()){
            return;
        }
        
        $opts = $good->getOpts();
        
        $price = $good->getPrice();
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
     * Добавить лог обновления
     * @param MarketPriceSetting $market
     * @param array $result
     * @param string $logName
     */
    private function addToUpdateLog($market, $result, $logName = '')
    {
        $filename = $market->getOzonLog($logName);
        $path = $this->marketManager->folder($market).'/'.$filename;
        
        $handle = fopen($path, "a");
        foreach ($result['result'] as $value){
            if (count($value['errors'])){
                $value[] = implode(';', $value['errors']);
            }
            fputcsv($handle, $value);
        }    
        fclose($handle);
        
        return;
    }
    
    /**
     * Обновление цен из прайса
     * @param MarketPriceSetting $market
     * @param integer $offset
     * @param integer $block
     * @return array
     */
    public function marketUpdate($market, $offset = 0, $block = 0)
    {
        
        $goodsQuery = $this->entityManager->getRepository(MarketPriceSetting::class)
                ->marketQuery($market, $offset);
        $data = $goodsQuery->getResult(2);
        $prices = []; 
        $stocks = [];
        foreach ($data as $good){

            $rawprices = $this->marketManager->restShipping($good['id'], $market, $good['price']);
            $lot = $rawprices['lot'];
            
            if ($rawprices['realrest'] == 0){
                //continue;
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
                'stock' => $rawprices['realrest'],
            ];
            
            if (count($prices) == self::OZON_MAX_PRICE_UPDATE){
                $result = $this->updatePrice(['prices' => $prices]);
                $this->addToUpdateLog($market, $result, 'price');
                $prices = [];
            }

            if (count($prices) == self::OZON_MAX_STOCK_UPDATE){
                $result = $this->updateStock(['stocks' => $stocks]);
                $this->addToUpdateLog($market, $result, 'stock');
                $stocks = [];
            }
        }    

        if (count($prices)){
            $result = $this->updatePrice(['prices' => $prices]);
            $this->addToUpdateLog($market, $result, 'price');
        }
        if (count($stocks)){
            $result = $this->updateStock(['stocks' => $stocks]);
            $this->addToUpdateLog($market, $result, 'stock');
        }
        
        return;
    }
    
}
