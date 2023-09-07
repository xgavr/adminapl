<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service;

use Laminas\ServiceManager\ServiceManager;
use Application\Entity\Goods;
use Application\Entity\Producer;
use Application\Entity\Tax;
use Application\Entity\Images;
use Application\Entity\Rawprice;
use Application\Entity\Raw;
use Application\Entity\Attribute;
use Application\Entity\Rate;
use Application\Entity\ScaleTreshold;
use Phpml\Math\Statistic\Mean;
use Phpml\Math\Statistic\StandardDeviation;
use Application\Validator\Sigma3;
use Application\Entity\Article;
use Stock\Entity\Movement;
use Application\Entity\Bid;
use Application\Entity\GoodSupplier;
use Application\Entity\SupplierOrder;
use Stock\Entity\PtuGood;
use Stock\Entity\OtGood;
use Stock\Entity\GoodBalance;
use Admin\Entity\Log;
use Application\Entity\Oem;

/**
 * Description of GoodsService
 *
 * @author Daddy
 */
class GoodsManager
{
    const IMAGE_DIR = './public/img'; //папка для хранения картинок
    const GOOD_IMAGE_DIR = './public/img/goods'; //папка для хранения картинок товаров    
    const TD_IMAGE_DIR = './pulic/img/goods/TD'; //папка для хранения картинок товаров
    
    const MIN_REST_FOR_PRICE = 1; //,было 1000, мнимальное количество для расчета среденей цены, если 1, то считается по среднему
    
    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
     /**
     * External manager.
     * @var \Application\Service\ExternalManager
     */
    private $externalManager;
  
     /**
     * Ml manager.
     * @var \Application\Service\MlManager
     */
    private $mlManager;
  
     /**
     * Log manager.
     * @var \Admin\Service\LogManager
     */
    private $logManager;
  
  
    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager, $externalManager, $mlManager, $logManager)
    {
        $this->entityManager = $entityManager;
        $this->externalManager = $externalManager;
        $this->mlManager = $mlManager;
        $this->logManager = $logManager;
    }
        
    public function addNewGoods($data, $flushnow=true) 
    {
        // Создаем новую сущность Goods.
        $goods = new Goods();
        $goods->setName($data['name']);
        $goods->setCode($data['code']);
        $goods->setAvailable($data['available']);
        $goods->setDescription($data['description']);
        
        $producer = $this->entityManager->getRepository(Producer::class)
                    ->findOneById($data['producer']);
        if ($producer == null){
            $producer = new Producer();
        }
        
        $goods->setProducer($producer);
        if (array_key_exists('tax', $data)){
            if (!$data['tax']) {
                $data['tax'] = $this->getSettings()->defaultTax;
            }
        } else {
            $data['tax'] = $this->getSettings()->defaultTax;
        }    
        
        $tax = $this->entityManager->getRepository(Tax::class)
                    ->findOneById($data['tax']);
        if ($tax == null){
            $tax = new Tax();
        }
        
        $goods->setTax($tax);
        
        // Добавляем сущность в менеджер сущностей.
        $this->entityManager->persist($goods);
        
        if ($flushnow){
            // Применяем изменения к базе данных.
            $this->entityManager->flush();
        }
        
        return $goods;
    }   
    
    public function updateGoods($goods, $data) 
    {
        $goods->setName($data['name']);
        $goods->setCode($data['code']);
        $goods->setAvailable($data['available']);
        $goods->setDescription($data['description']);
               
        $producer = $this->entityManager->getRepository(Producer::class)
                    ->findOneById($data['producer']);
        if ($producer == null){
            $producer = new Producer();
        }
        
        $goods->setProducer($producer);
        
        $tax = $this->entityManager->getRepository(Tax::class)
                    ->findOneById($data['tax']);
        if ($tax == null){
            $tax = new Tax();
        }
        // Применяем изменения к базе данных.
        $this->entityManager->flush();
        
        return $goods;
    }  
    
    /**
     * Обновить наименование товара
     * 
     * @param Goods $good
     * @param string $name
     */
    public function updateGoodName($good, $name)
    {
        $good->setName($name);
        $good->setStatusNameEx(Goods::NAME_EX_NEW);
        $this->entityManager->persist($good);
        $this->entityManager->flush($good);        
    }
    
    /**
     * Обновить цену для торговых площадок
     * 
     * @param Goods $good
     * @param float $marketPlacePrice
     */
    public function updateMarketPlacePrice($good, $marketPlacePrice)
    {
        $good->setMarketPlacePrice($marketPlacePrice);
        $this->entityManager->persist($good);
        $this->entityManager->flush($good);        

        $this->logManager->infoGood($good, Log::STATUS_UPDATE);
    }

    /**
     * Обновить наличие
     * 
     * @param Goods $good
     * @param integer $inStore
     */
    public function updateInStore($good, $inStore)
    {
        $this->entityManager->refresh($good);
        $good->setInStore($inStore);
        $this->entityManager->persist($good);
        $this->entityManager->flush();

        $this->logManager->infoGood($good, Log::STATUS_UPDATE);
    }

    /**
     * Проверка возможности удаления товара
     * 
     * @param Goods $good
     * @return boolean
     */
    public function allowRemove($good)
    {
        $articleCount = $this->entityManager->getRepository(Article::class)
            ->count(['good' => $good->getId()]);        
        if ($articleCount){
            return false;
        }
        $movementsCount = $this->entityManager->getRepository(Movement::class)
                ->count(['good' => $good->getId()]);
        if ($movementsCount){
            return false;
        }
        $bidCount = $this->entityManager->getRepository(Bid::class)
                ->count(['good' => $good->getId()]);
        if ($bidCount){
            return false;
        }
        $ptuCount = $this->entityManager->getRepository(PtuGood::class)
                ->count(['good' => $good->getId()]);
        if ($ptuCount){
            return false;
        }
        $otCount = $this->entityManager->getRepository(OtGood::class)
                ->count(['good' => $good->getId()]);
        if ($otCount){
            return false;
        }
                
        return true;
    }
    
    /**
     * Удалить заказы поставщикам
     * @param Good $good
     */
    public function removeSupplierOrders($good)
    {
        $supplierOrders = $this->entityManager->getRepository(SupplierOrder::class)
                    ->findByGood($good->getId());
        
        foreach ($supplierOrders as $supplierOrder){
//            $this->entityManager->remove($supplierOrder);
            $this->entityManager->getConnection()->delete('supplier_order', ['id' => $supplierOrder->getId()]);
        }
        
//        $this->entityManager->flush();
        return;
    }
    
    /**
     * Удалене карточки товара
     * 
     * @param Goods $good
     */
    public function removeGood($good) 
    {   

        if (!$this->allowRemove($good)){
            return false;
        }
        
        if (!$this->entityManager->getRepository(Oem::class)
                ->removeAllGoodOem($good)){
            return false;
        }

        $this->entityManager->getRepository(Goods::class)
                ->removeGoodCars($good);        
        
        $this->entityManager->getRepository(Goods::class)
                ->removeGoodAttributeValues($good);  
        
        $this->entityManager->getRepository(\Application\Entity\GoodToken::class)
                ->deleteTokenGood($good);

        $this->entityManager->getRepository(Images::class)
                ->removeGoodImages($good);
        
        $this->entityManager->getRepository(Goods::class)
                ->removeGoodTitles($good);
        
                
        $this->entityManager->getRepository(Oem::class)
                ->removeIntersectOem($good->getId());
        
        $this->entityManager->getConnection()->delete('good_supplier', ['good_id' => $good->getId()]);
        
        $this->entityManager->getConnection()->delete('good_balance', ['good_id' => $good->getId()]);

        $this->removeSupplierOrders($good);
                
        $this->entityManager->remove($good);
        
        $this->entityManager->flush($good);
        
        return true;
    }    
    
    
    /**
     * Поиск и удаление товаров не привязаных к прайсам
     */
    public function removeEmpty()
    {

        ini_set('memory_limit', '4096M');
        set_time_limit(900);        
        $startTime = time();
        $finishTime = $startTime + 840;
        
        $goodsForDelete = $this->entityManager->getRepository(Goods::class)
                ->findGoodsForDelete();
        $iterable = $goodsForDelete->iterate();
        
        foreach ($iterable as $row){
            foreach ($row as $good){
                $articleCount = $this->entityManager->getRepository(Article::class)
                        ->count(['good' => $good->getId()]);
                if ($articleCount == 0){
                    $this->entityManager->getRepository(Goods::class)
                            ->updateGoodId($good->getId(), ['available' => Goods::AVAILABLE_FALSE]);
    
                    $bidCount = $this->entityManager->getRepository(Bid::class)
                            ->count(['good' => $good->getId()]);
                    if ($bidCount == 0){
                        $this->removeGood($good);
                    }    
                } else {
                    $this->entityManager->getRepository(Goods::class)
                            ->updateGoodId($good->getId(), ['upd_week' => date('W'), 'available' => Goods::AVAILABLE_TRUE]);
                }    
                $this->entityManager->detach($good);
            }    
            if (time() >= $finishTime){
                return;
            }
        }
        
        return;
        
    }
    
    
    /**
     * Получить картинки из папки
     * 
     * @param string $folderImage
     * @return array
     */
    public function imagesFromFolder($folderImage, $images = null)
    {
        if (!$images){
            $images = [];
        }    

        if (is_dir($folderImage)){
            foreach (new \DirectoryIterator($folderImage) as $fileInfo) {
                if ($fileInfo->isDot()) {
                    continue;
                }
                if ($fileInfo->isFile() && strtoupper($fileInfo->getExtension()) != 'PDF'){
                    $images[basename($folderImage)][] = str_replace('./public', '', $folderImage.'/'.$fileInfo->getFilename());                            
                }
                if ($fileInfo->isDir()){
                    $images = array_merge($images, $this->imagesFromFolder($folderImage.'/'.$fileInfo->getFilename(), $images));                    
                }
            }
        }
//var_dump($images);
        return $images;
    }
    
    
    /**
     * Получить картинки товара
     * 
     * @param Goods $good
     * @return array
     */
    public function images($good)
    {        
        return $this->imagesFromFolder(self::GOOD_IMAGE_DIR.'/'.$good->getId());
    }
    
    /**
     * Обновить номера из текдока у товаров
     */
    public function updateOemTd()
    {        
        ini_set('memory_limit', '4092M');
        set_time_limit(900);
        $startTime = time();
        $finishTime = $startTime + 840;
        
        $goodsForUpdate = $this->entityManager->getRepository(Goods::class)
                ->findGoodsForUpdateOemTd();

        $iterable = $goodsForUpdate->iterate();

        foreach($iterable as $item){
            foreach ($item as $good){
                $tokenGroupId = null;
                if (!empty($good['tokenGroupId'])){
                    $tokenGroupId = $good['tokenGroupId'];
                }
                $this->externalManager->addOemsToGood($good['goodId'], $good['code'], $good['genericGroupTdId'], $tokenGroupId);
            }
            
            if (time() >= $finishTime){
                return;
            }
        }
        
        return;
    }

    /**
     * Обновить пересечения номеров у товаров
     */
    public function updateOemSupCross()
    {        
        ini_set('memory_limit', '4096M');
        set_time_limit(900);
        $startTime = time();
        $finishTime = $startTime + 840;
        
        $goodsForUpdate = $this->entityManager->getRepository(Goods::class)
                ->findGoodsForUpdateOemSupCross();

        $iterable = $goodsForUpdate->iterate();

        foreach($iterable as $item){
            foreach ($item as $good){
                $this->entityManager->getRepository(Oem::class)
                        ->addSupOem($good['goodId']);
                $this->entityManager->getRepository(Oem::class)
                        ->addCrossOem($good['goodId']);    
                
                $this->entityManager->getConnection()->update('goods', ['status_oem' => Goods::OEM_INTERSECT], ['id' => $good['goodId']]);
            }
            
            if (time() >= $finishTime){
                return;
            }
        }
        
        return;
    }

    /**
     * Обновить пересечения номеров у товаров
     */
    public function updateOemIntersect()
    {        
        ini_set('memory_limit', '4096M');
        set_time_limit(900);
        $startTime = time();
        $finishTime = $startTime + 840;
        
        $goodsForUpdate = $this->entityManager->getRepository(Goods::class)
                ->findGoodsForUpdateOemIntersect();
        $i = 0;

        $iterable = $goodsForUpdate->iterate();

        foreach($iterable as $item){
            foreach ($item as $good){
                $this->entityManager->getRepository(Oem::class)
                        ->addIntersectGood($good);
            }
            $i++;
            if (time() >= $finishTime){
                return;
            }
        }
        
        return;
    }

    /**
     * Обновить групп из текдока у товаров
     */
    public function updateGroupTd()
    {        
        ini_set('memory_limit', '4096M');
        set_time_limit(900);
        $startTime = time();
        $finishTime = $startTime + 840;
        
        $goodsForUpdateQuery = $this->entityManager->getRepository(Goods::class)
                ->findGoodsForUpdateGroupTd();
        
        $iterable = $goodsForUpdateQuery->iterate();
        
        $i = 0;

        foreach ($iterable as $row){
            foreach ($row as $good){
                $this->externalManager->updateGoodGenericGroup($good);
                $this->entityManager->detach($good);
            }    
            $i++;
            if (time() >= $finishTime){
                break;
            }
        }
        
//        if ($i == 0){
//            $this->entityManager->getRepository(Goods::class)
//                    ->resetUpdateGroupTd();
//        }
                
        return;
    }

    /**
     * Обновить описания из текдока у товаров
     */
    public function updateDescriptionTd()
    {        
        ini_set('memory_limit', '4096M');
        set_time_limit(900);
        $startTime = time();
        $finishTime = $startTime + 840;
        
        $goodsForUpdateQuery = $this->entityManager->getRepository(Goods::class)
                ->findGoodsForUpdateDescriptionTd();
        $iterable = $goodsForUpdateQuery->iterate();

        $i = 0;
        
        foreach ($iterable as $row){
            foreach ($row as $good){
                $this->externalManager->addAttributesToGood($good);
                $this->entityManager->detach($good);
            }
            $i++;
            if (time() >= $finishTime){
                break;
            }
        }

//        if ($i == 0){
//            $this->entityManager->getRepository(Goods::class)
//                    ->resetUpdateAttributeTd();
//        }
        
        return;
    }
    
    /**
     * Обновить атрибут
     * 
     * @param Attribute $attribute
     * @param array $data
     */
    public function updateAttribute($attribute, $data)
    {
        if (isset($data['status'])){
            $attribute->setStatus($data['status']);
        }
        if (isset($data['name'])){
            $attribute->setName($data['name']);
        }
        
        $attribute->setStatusEx(Attribute::EX_TO_TRANSFER);
        $this->entityManager->persist($attribute);
        $this->entityManager->flush();
        
        return;
    }

    /**
     * Обновить атрибут
     * 
     * @param Attribute $attribute
     * @param array $data
     */
    public function updateAttributeSimilarGood($attribute, $data)
    {
        if (isset($data['similarGood'])){
            $attribute->setSimilarGood($data['similarGood']);
            $this->entityManager->persist($attribute);
            $this->entityManager->flush($attribute);        
        }
        
        return;
    }

    /**
     * Обновить атрибут
     * 
     * @param Attribute $attribute
     * @param array $data
     */
    public function updateAttributeToBestName($attribute, $data)
    {
        if (isset($data['toBestName'])){
            $attribute->setToBestName($data['toBestName']);
            $this->entityManager->persist($attribute);
            $this->entityManager->flush($attribute);        
        }
        
        return;
    }

    /**
     * Обновить картинки из текдока у товаров
     */
    public function updateImageTd()
    {        
        ini_set('memory_limit', '2048M');
        set_time_limit(900);
        $startTime = time();
        $finishTime = $startTime + 840;
        
        $goodsForUpdateQuery = $this->entityManager->getRepository(Goods::class)
                ->findGoodsForUpdateImageTd();
        
        $iterable = $goodsForUpdateQuery->iterate();
        
        $i = 0;
        foreach ($iterable as $row){            
            foreach ($row as $good){
                $this->externalManager->addImageToGood($good);
                $this->entityManager->detach($good);
            }
            $i++;
            if (time() >= $finishTime){
                break;
            }
        }    

//        if ($i == 0){
//            $this->entityManager->getRepository(Goods::class)
//                    ->resetUpdateImageTd();
//        }        
        
        return;
    }

    /**
     * Обновить машины у товаров
     */
    public function updateCars()
    {        
        ini_set('memory_limit', '4096M');
        set_time_limit(900);
        $startTime = time();
        $finishTime = $startTime + 840;
        
        $goodsForUpdateQuery = $this->entityManager->getRepository(Goods::class)
                ->findGoodsForUpdateCarTd();
        $iterable = $goodsForUpdateQuery->iterate();
        $i = 0;
                
        foreach ($iterable as $row){
            foreach ($row as $good){
                $this->externalManager->addCarsToGood($good);
                $this->entityManager->detach($good);
            }    
            $i++;
            if (time() >= $finishTime){
                return;
            }
        }

//        if ($i == 0){
//            $this->entityManager->getRepository(Goods::class)
//                    ->resetUpdateCarTd();
//        }        
        
        
        return;
    }
    

    /**
     * Получить цены из прайсов
     * 
     * @param array $rawprices
     * @return array
     */
    public function getPricesFromRawprices($rawprices)
    {
        $result = [];
        
        foreach ($rawprices as $rawprice){
            if ($rawprice->getRealPrice()>0 && $rawprice->getRealRest()>0){
                $rest = min(self::MIN_REST_FOR_PRICE, $rawprice->getRealRest());
                $result = array_merge($result, array_fill(0, $rest, $rawprice->getRealPrice()));
            }
        }
        
        return $result;
        
    }
    
    /**
     * Получить массив цен товара
     * @param Goods $good
     * @return array
     */
    public function rawpricesPrices($good)
    {
        $rawprices = $this->entityManager->getRepository(Goods::class)
                ->rawpriceArticles($good);
        
        return $this->getPricesFromRawprices($rawprices);
    }
    
    /**
     * Рассчитать минимальную закупочную цену
     * 
     * @param array $prices
     * @return float
     */
    public function minPrice($prices)
    {
        if (count($prices)){
            return min($prices);
        }
        
        return 0.0;
    }
    
    /**
     * Расчитать среднюю закупочную цену
     * 
     * @param array $prices
     * @param float $defaultPrice
     * @return float 
     */
    public function meanPrice($prices, $defaultPrice = null)
    {
        if (count($prices)){
            $minPrice = min($prices);
            
            $mean = Mean::arithmetic($prices);
            $deviation = StandardDeviation::population($prices, count($prices)>1);

            $validator = new Sigma3();
            foreach ($prices as $key => $price){
                if (!$validator->isValid($price, $mean, $deviation)){
                    $prices[$key] = false;
                }
            }
            
            $newPrices = array_filter($prices);
            if (count($newPrices)){
//                return Mean::median($newPrices);
                return Mean::arithmetic($newPrices);
            } else {
                if (empty($defaultPrice)){
                    return $minPrice;
                } else {
                    return $defaultPrice;
                }    
            }    
        }
        
        return 0.0;
    }

    /**
     * Проверка цены из прайса
     * 
     * @param Rawprice $rawprice
     * @param array $prices
     * 
     * @return bool
     */
    public function inSigma($rawprice, $prices)
    {
        if ($rawprice->getRealPrice()>0 && $rawprice->getRealRest()>0){
            if (count($prices)){
                $validator = new Sigma3();
                $mean = Mean::arithmetic($prices);
                $deviation = StandardDeviation::population($prices, count($prices)>1);
                
                return $validator->isValid($rawprice->getRealPrice(), $mean, $deviation);
            }    
        }
        
        return false;
    }
    
    /**
     * Обновить расчетные цены товара
     * 
     * @param Goods $good
     * @param object $regression
     */
    public function updatePrices($good, $regression = null)
    {

        $articles = $this->entityManager->getRepository(Article::class)
                ->findBy(['good' => $good->getId()]);
        $prices = [];
        $bestSupplierPrice = $bestSupplierAmount = 0;
        foreach ($articles as $article){
                $rawprices = $this->entityManager->getRepository(Rawprice::class)
                        ->findBy(['code' => $article->getId(), 'status' => Rawprice::STATUS_PARSED]);
                foreach ($rawprices as $rawprice){
                    if ($rawprice->getRealPrice()>0 && $rawprice->getRealRest()>0){
                        $rest = min(self::MIN_REST_FOR_PRICE, $rawprice->getRealRest());
                        $prices = array_merge($prices, array_fill(0, $rest, $rawprice->getRealPrice()));
                        
                        if ($rawprice->getRaw()->getSupplier()->getAmount() > $bestSupplierAmount){
                            $bestSupplierPrice = $rawprice->getRealPrice();
                            $bestSupplierAmount = $rawprice->getRaw()->getSupplier()->getAmount();
                        }
                    }
                }    
        }
                
        $meanPrice = $price = $minPrice = 0;
        $oldMeanPrice = $good->getMeanPrice();
        $oldPrice = $good->getPrice();
        $fixPrice = $good->getFixPrice();
        
        if (count($prices)){

            $minPrice = $this->minPrice($prices);
            $meanPrice = $this->meanPrice($prices, max($minPrice, $bestSupplierPrice));
            if ($fixPrice < $meanPrice){
                $fixPrice = 0;
            }
            $price = $fixPrice;
            if ($fixPrice == 0){
                $price = $oldPrice;
            }    
            
            if (!$regression){
                $rate = $this->entityManager->getRepository(Rate::class)
                        ->findGoodRate($good);
                $regression = $this->mlManager->rateScaleRegression($rate->getRateModelFileName());
            }    

            if ($meanPrice && $regression){
                $percent = $this->mlManager->predictRateScaleRegression($regression, $meanPrice);
                $price = ScaleTreshold::retail($meanPrice, $percent, ScaleTreshold::DEFAULT_ROUNDING);
            }    
            unset($prices);
        }    
        unset($articles);

        if ($oldMeanPrice != $meanPrice || $oldPrice != $price){
            $this->entityManager->getRepository(Goods::class)
                    ->updateGoodId($good->getId(), [
                        'min_price' => $minPrice, 
                        'mean_price' => $meanPrice,
                        'fix_price' => $fixPrice,
                        'price' => $price,
                        'status_price_ex' => Goods::PRICE_EX_NEW,
                        'date_price' => date('Y-m-d H:i:s'),
                            ]);
        } else {
            $this->entityManager->getRepository(Goods::class)
                    ->updateGoodId($good->getId(), [
                        'date_price' => date('Y-m-d H:i:s'),
                            ]);            
        }   
        
        return;
    }
    
    /**
     * Обновить расчетные цены товара
     * 
     * @param array $goodData //[goodId, meanPrice, price, fixPrice, tokenGroupId, genericGroupId, producerId]
     * @param object $regression
     */
    public function updatePricesFromGoodSupplier($goodData, $regression = null)
    {

        $goodSuppliers = $this->entityManager->getRepository(GoodSupplier::class)
                ->goodSuppliers($goodData['goodId']);
        $prices = [];
        $bestSupplierPrice = $bestSupplierAmount = 0;
        foreach ($goodSuppliers as $goodSupplier){
            $rest = $goodSupplier['rest'];
            $supplierPrice = $goodSupplier['price'];
            if ($rest>0 && $supplierPrice>0){
                $rest = min(self::MIN_REST_FOR_PRICE, $rest);
                $prices = array_merge($prices, array_fill(0, $rest, $supplierPrice));

                if ($goodSupplier['supplier']['amount'] > $bestSupplierAmount){
                    $bestSupplierPrice = $supplierPrice;
                    $bestSupplierAmount = $goodSupplier['supplier']['amount'];
                }
            }
        }
                
        $meanPrice = $price = $minPrice = 0;
        $oldMeanPrice = $goodData['meanPrice'];
        $oldPrice = $goodData['price'];
        $fixPrice = $goodData['fixPrice'];
        
        if (count($prices)){

            $minPrice = $this->minPrice($prices);
            $meanPrice = $this->meanPrice($prices, max($minPrice, $bestSupplierPrice));
            if ($fixPrice < $meanPrice){
                $fixPrice = 0;
            }
            $price = $fixPrice;
            if ($fixPrice == 0){
                $price = $oldPrice;
            
                if (!$regression){
                    $rate = $this->entityManager->getRepository(Rate::class)
                            ->getRate($goodData['tokenGroupId'], $goodData['genericGroupId'], $goodData['producerId']);
                    $regression = $this->mlManager->rateScaleRegression($rate->getRateModelFileName());
                }    

                if ($meanPrice && $regression){
                    $percent = $this->mlManager->predictRateScaleRegression($regression, $meanPrice);
                    $price = ScaleTreshold::retail($meanPrice, $percent, ScaleTreshold::DEFAULT_ROUNDING);
                }    
            }    
            unset($prices);
        }    

        if ($oldMeanPrice != $meanPrice || $oldPrice != $price){
            $this->entityManager->getRepository(Goods::class)
                    ->updateGoodId($goodData['goodId'], [
                        'min_price' => $minPrice, 
                        'mean_price' => $meanPrice,
                        'fix_price' => $fixPrice,
                        'price' => $price,
                        'status_price_ex' => Goods::PRICE_EX_NEW,
                        'date_price' => date('Y-m-d H:i:s'),
                        'status_image' => Goods::IMAGE_FOR_UPDATE,
                        'status_car' => Goods::CAR_FOR_UPDATE,
                        'status_description' => Goods::DESCRIPTION_FOR_UPDATE,
                        'status_group' => Goods::GROUP_FOR_UPDATE,
                        'status_oem' => Goods::OEM_FOR_UPDATE,
                            ]);
        } else {
            $this->entityManager->getRepository(Goods::class)
                    ->updateGoodId($goodData['goodId'], [
                        'date_price' => date('Y-m-d H:i:s'),
                            ]);            
        }   
        
        return;
    }

    /**
     * Получить колонки цен
     * 
     * @param Goods $good
     * @return array
     */
    public function priceCols($good)
    {
        return ScaleTreshold::retailPriceCols($good->getPrice(), $good->getMeanPrice());
    }
    
    /**
     * Пересчет цен товаров прайса
     * @param Raw $raw
     */
    public function updatePricesRaw($raw)
    {
        ini_set('memory_limit', '4096M');
        set_time_limit(900);
        $startTime = time();
        
        $rawpriceQuery = $this->entityManager->getRepository(Goods::class)
                ->findRawpriceForUpdatePrice($raw);
        $data = $rawpriceQuery->getResult(2);
        
        $regressions = [];
        
        foreach ($data as $row){
            if (!empty($row['goodId'])){
                if ($row['datePrice'] < date('Y-m-d') || empty($row['price'])){
                    $rate = $this->entityManager->getRepository(Rate::class)
                            ->getRate($row['tokenGroupId'], $row['genericGroupId'], $row['producerId']);
                    if (!array_key_exists($rate->getId(), $regressions)){
                        $regressions[$rate->getId()] = $this->mlManager->rateScaleRegression($rate->getRateModelFileName());
                    }                
                    $this->updatePricesFromGoodSupplier($row, $regressions[$rate->getId()]);
                }    
            }    
            $this->entityManager->getRepository(Rawprice::class)
                    ->updateRawpriceField($row['rawpriceId'], ['status_price' => Rawprice::PRICE_PARSED]);

            if (time() > $startTime + 840){
                return;
            }
        }    
        
        unset($regressions);
        
        $this->entityManager->getRepository(Raw::class)
                ->updateRawParseStage($raw, Raw::STAGE_PRICE_UPDATET); 
        
        return;
    }
    
    /**
     * Сравнить строки прайсов с предыдущими и установить метку
     * @param int $goodId
     * @param date $goodDateEx
     */
    public function compareRawprices($goodId, $goodDateEx)
    {     
        $rawprices = $this->entityManager->getRepository(Goods::class)
                ->rawpriceArticlesEx($goodId, ['statusEx' => Rawprice::EX_NEW]);

        $statusEx = Goods::RAWPRICE_EX_TRANSFERRED;
        foreach ($rawprices as $rawprice){
            $statusRawpriceEx = Rawprice::EX_TRANSFERRED;
            if (!$this->entityManager->getRepository(Rawprice::class)->isOldRawpriceCompare($rawprice, $goodDateEx)){
                $statusEx = Goods::RAWPRICE_EX_TO_TRANSFER;
                $statusRawpriceEx = Rawprice::EX_TO_TRANSFER;
            }
            if ($rawprice->getStatusEx() != $statusRawpriceEx){
                $this->entityManager->getRepository(Rawprice::class)
                        ->updateRawpriceField($rawprice->getId(), ['status_ex' => $statusRawpriceEx]);            
            }
        }    
        
        $this->entityManager->getRepository(Goods::class)
                ->updateGoodId($goodId, ['status_rawprice_ex' => $statusEx]);
        
        unset($rawprices);
        return;
    }
    
    /**
     * Сравнить строки прайсов товара
     * 
     */
    public function compareGoodsRawprice()
    {
        ini_set('memory_limit', '4096M');
        set_time_limit(900);
        $startTime = time();
        
//        echo memory_get_usage() . "\n";

        $goodCount = $this->entityManager->getRepository(Goods::class)
                ->count([]);
        $limit = intval($goodCount/25);
        
//        $goods = $this->entityManager->getRepository(Goods::class)
//                ->findBy(['statusRawpriceEx' => Goods::RAWPRICE_EX_NEW], null, $limit);
        $goodsQuery = $this->entityManager->getRepository(Goods::class)
                ->findForRawpriceEx(Goods::RAWPRICE_EX_NEW, ['limit' => $limit]);
        $iterable = $goodsQuery->iterate();
        foreach($iterable as $item){
            foreach ($item as $row){
                $this->compareRawprices($row['id'], $row['dateEx']);                
                unset($row);
            }
            if (time() > $startTime + 840){
                break;
            }
        }
        
//        echo memory_get_usage() . "\n";
        
        unset($iterable);
        return;
    }
    
    /**
     * Добавить свои артикулы в номера
     */
    public function addOeAsMyCode()
    {
        ini_set('memory_limit', '4096M');
        set_time_limit(3600);
        
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('g')
                ->from(Goods::class, 'g')
                ;
        
        $goodsQuery = $qb->getQuery();
        
        $iterable = $goodsQuery->iterate();
        foreach ($iterable as $row){
            foreach ($row as $good){
//                var_dump($good); exit;
                $this->entityManager->getRepository(Oem::class)
                        ->addMyCodeAsOe($good);
                
                $this->entityManager->detach($good);
            }    
        }
            
        return;
    }    
    
    /**
     * Обновить остатки
     * @param Good $good
     */
    public function updateBalance($good)
    {
        $balances = $this->entityManager->getRepository(GoodBalance::class)
                ->findBy(['good' => $good->getId()]);
        foreach ($balances as $balance){
            $this->entityManager->getRepository(GoodBalance::class)
                    ->updateGoodBalance($good->getId(), $balance->getOffice()->getId, $balance->getCompany()->getId());
        }
        
        return;
    }
    
    /**
     * Инфо для Апл
     * @param Goods $good
     * @return array
     */
    public function goodForApl($good)
    {
        $rawprices = $this->entityManager->getRepository(Goods::class)
                ->rawpriceArticles($good);
        
        $sups = [];
        $prices = [];
        foreach ($rawprices as $rawprice){
            $sups[$rawprice->getRaw()->getSupplier()->getId()] = $rawprice->getRaw()->getSupplier()->getAplId();
            $prices[] = [
                'price' => number_format($rawprice->getRealPrice(), 2, '.', ''),
                'name' => $rawprice->getRaw()->getSupplier()->getAplId(),
                'created' => $rawprice->getRaw()->getDateCreated(),
                'type' => $rawprice->getIid(),
                'producer' => $rawprice->getProducer(),
                'rawdata' => $rawprice->getRawdata(),
                'article' => $rawprice->getArticle(),
                'rest' => $rawprice->getRealRest(),
                'lot' => $rawprice->getLot(),
                'goodname' => $rawprice->getGoodname(),
                'reserve' => $rawprice->getAplReserve(),
                'comment' => $rawprice->getAplOrderId(),
            ];
        }
        
        $rests = $this->entityManager->getRepository(GoodBalance::class)
                ->findBy(['good' => $good->getId()]);
        $inStore = [];
        foreach ($rests as $rest){
            $inStore[] = [
                'office' => $rest->getOffice()->getName().' ('.$rest->getCompany()->getName().')',
                'officeAplId' => $rest->getOffice()->getAplId(),
                'rest' => $rest->getRest(),
                'reserve' => $rest->getReserve(),
                'delivery' => $rest->getDelivery(),
                'vozvrat' => $rest->getVozvrat(),
                'available' => $rest->getAvailable(),                    
            ];
        }
        
        $result = [
            'goodId' => $good->getId(),
            'goodAplId' => $good->getAplId(),
            'article' => $good->getCode(),
            'producer' => $good->getProducer()->getName(),
            'g5' => [
                'bestname' => $good->getName(),
                'shortname' => $good->getNameShort(),
            ],
            'sups' => array_values($sups),
            'prices' => $prices,
            'inStore' => $inStore,
        ];
        
        return $result;
    }
}
