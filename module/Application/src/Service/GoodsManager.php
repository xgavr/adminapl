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
  
    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager, $externalManager, $mlManager)
    {
        $this->entityManager = $entityManager;
        $this->externalManager = $externalManager;
        $this->mlManager = $mlManager;
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
        $this->entityManager->persist($good);
        $this->entityManager->flush($good);        
    }
    
    /**
     * Проверка возможности удаления товара
     * 
     * @param Goods $good
     * @return boolean
     */
    public function allowRemove($good)
    {
        $movementsCount = $this->entityManager->getRepository(Movement::class)
                ->count(['good' => $good->getId()]);
        $articleCount = $this->entityManager->getRepository(Article::class)
            ->count(['good' => $good->getId()]);
        
        return $movementsCount == 0 && $articleCount == 0;
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
        
        $this->entityManager->getRepository(\Application\Entity\Oem::class)
                ->removeAllGoodOem($good);
                
        $this->entityManager->getRepository(\Application\Entity\Oem::class)
                ->removeIntersectOem($good);
                
        $this->entityManager->remove($good);
        
        $this->entityManager->flush($good);
        
        return true;
    }    
    
    
    /**
     * Поиск и удаление товаров не привязаных к прайсам
     */
    public function removeEmpty()
    {

        ini_set('memory_limit', '2048M');
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
        ini_set('memory_limit', '4096M');
        set_time_limit(900);
        $startTime = time();
        $finishTime = $startTime + 840;
        
        $goodsForUpdate = $this->entityManager->getRepository(Goods::class)
                ->findGoodsForUpdateOemTd();
        $i = 0;

        $iterable = $goodsForUpdate->iterate();

        foreach($iterable as $item){
            foreach ($item as $good){
                $this->externalManager->addOemsToGood($good);
                $this->entityManager->detach($good);
            }
            $i++;
            if (time() >= $finishTime){
                return;
            }
        }
        
        if ($i == 0){
            $this->entityManager->getRepository(Goods::class)
                    ->resetUpdateOemTd();
            return;
        }
        
        return;
    }

    /**
     * Обновить групп из текдока у товаров
     */
    public function updateGroupTd()
    {        
        ini_set('memory_limit', '2048M');
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
        
        if ($i == 0){
            $this->entityManager->getRepository(Goods::class)
                    ->resetUpdateGroupTd();
        }
                
        return;
    }

    /**
     * Обновить описания из текдока у товаров
     */
    public function updateDescriptionTd()
    {        
        ini_set('memory_limit', '2048M');
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

        if ($i == 0){
            $this->entityManager->getRepository(Goods::class)
                    ->resetUpdateAttributeTd();
        }
        
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

        if ($i == 0){
            $this->entityManager->getRepository(Goods::class)
                    ->resetUpdateImageTd();
        }        
        
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

        if ($i == 0){
            $this->entityManager->getRepository(Goods::class)
                    ->resetUpdateCarTd();
        }        
        
        
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
                $rest = min(1000, $rawprice->getRealRest());
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
     * @param float $defaultPrice;
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
                return Mean::median($newPrices);
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
                        $rest = min(1000, $rawprice->getRealRest());
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
        $iterable = $rawpriceQuery->iterate();
        
        $regressions = [];
        
        foreach ($iterable as $row){
            foreach ($row as $rawprice){
                $good = $rawprice->getGood();
                
                
                if ($good){
                    if ($good->getDatePrice() < date('Y-m-d') || !$good->getPrice()){
                        $rate = $this->entityManager->getRepository(Rate::class)
                                ->findGoodRate($good);
                        if (!array_key_exists($rate->getId(), $regressions)){
                            $regressions[$rate->getId()] = $this->mlManager->rateScaleRegression($rate->getRateModelFileName());
                        }                
                        $this->updatePrices($good, $regressions[$rate->getId()]);
                    }    
                    $this->entityManager->detach($good);
                }    
                $this->entityManager->getRepository(Rawprice::class)
                        ->updateRawpriceField($rawprice->getId(), ['status_price' => Rawprice::PRICE_PARSED]);
                $this->entityManager->detach($rawprice);
            }    
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
                $this->entityManager->getRepository(\Application\Entity\Oem::class)
                        ->addMyCodeAsOe($good);
                
                $this->entityManager->detach($good);
            }    
        }
            
        return;
    }
    
}
