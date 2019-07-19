<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Application\Service;

use Zend\ServiceManager\ServiceManager;
use Application\Entity\Goods;
use Application\Entity\Producer;
use Application\Entity\Tax;
use Application\Entity\Images;
use Application\Entity\Rawprice;
use Phpml\Math\Statistic\Mean;
use Phpml\Math\Statistic\StandardDeviation;
use Application\Validator\Sigma3;

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
  
    // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager, $externalManager)
    {
        $this->entityManager = $entityManager;
        $this->externalManager = $externalManager;
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
     * @param Application\Entity\Goods $good
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
     * @param Application\Entity\Goods $good
     * @return boolean
     */
    public function allowRemove($good)
    {
        return true;
    }
    
    /**
     * Удалене карточки товара
     * 
     * @param \Application\Entity\Goods $good
     */
    public function removeGood($good) 
    {   

        if (!$this->allowRemove($good)){
            return false;
        }
        
        $cars = $this->entityManager->getRepository(Goods::class)
                ->findCars($good);        
        foreach ($cars as $car){
            $good->removeCarAssociation($car);
        }           
        
        $attributeValues = $this->entityManager->getRepository(Goods::class)
                ->findGoodAttributeValues($good);        
        foreach ($attributeValues as $attributeValue){
            $good->removeAttributeValueAssociation($attributeValue);
        }           
        
        $this->entityManager->getRepository(Images::class)
                ->removeGoodImages($good);
                
                
        $this->entityManager->remove($good);
        
        $this->entityManager->flush();
        
        return true;
    }    
    
    
    /**
     * Поиск и удаление товаров не привязаных к прайсам
     */
    public function removeEmpty()
    {
        set_time_limit(900);        
        ini_set('memory_limit', '2048M');

        $goodsForDelete = $this->entityManager->getRepository(Goods::class)
                ->findGoodsForDelete();

        foreach ($goodsForDelete as $row){
            $this->removeGood($row[0]);
        }
        
        return count($goodsForDelete);
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
     * @param Application\Entity\Goods $good
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
        set_time_limit(900);
        $startTime = time();
        $finishTime = $startTime + 800;
        
        $goodsForUpdate = $this->entityManager->getRepository(Goods::class)
                ->findGoodsForUpdateOemTd();
        
        if (count($goodsForUpdate) == 0){
            $this->entityManager->getRepository(Goods::class)
                    ->resetUpdateOemTd();
            return;
        }
        
        foreach ($goodsForUpdate as $good){
            if (time() >= $finishTime){
                return;
            }
            $this->externalManager->addOemsToGood($good);
        }
        
        return;
    }

    /**
     * Обновить групп из текдока у товаров
     */
    public function updateGroupTd()
    {        
        set_time_limit(900);
        $startTime = time();
        $finishTime = $startTime + 800;
        
        $goodsForUpdate = $this->entityManager->getRepository(Goods::class)
                ->findGoodsForUpdateGroupTd();

        if (count($goodsForUpdate) == 0){
            $this->entityManager->getRepository(Goods::class)
                    ->resetUpdateGroupTd();
            return;
        }
        
        foreach ($goodsForUpdate as $good){
            if (time() >= $finishTime){
                return;
            }
            $this->externalManager->updateGoodGenericGroup($good);
        }
        
        return;
    }

    /**
     * Обновить описания из текдока у товаров
     */
    public function updateDescriptionTd()
    {        
        set_time_limit(900);
        $startTime = time();
        $finishTime = $startTime + 800;
        
        $goodsForUpdate = $this->entityManager->getRepository(Goods::class)
                ->findGoodsForUpdateDescriptionTd();

        if (count($goodsForUpdate) == 0){
            $this->entityManager->getRepository(Goods::class)
                    ->resetUpdateAttributeTd();
            return;
        }
        
        foreach ($goodsForUpdate as $good){
            if (time() >= $finishTime){
                return;
            }
            $this->externalManager->addAttributesToGood($good);
        }
        
        return;
    }
    
    /**
     * Обновить атрибут
     * 
     * @param \Application\Entity\Attribute $attribute
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
        
        $this->entityManager->persist($attribute);
        $this->entityManager->flush();
        
        return;
    }

    /**
     * Обновить картинки из текдока у товаров
     */
    public function updateImageTd()
    {        
        set_time_limit(900);
        $startTime = time();
        $finishTime = $startTime + 800;
        
        $goodsForUpdate = $this->entityManager->getRepository(Goods::class)
                ->findGoodsForUpdateImageTd();

        if (count($goodsForUpdate) == 0){
            $this->entityManager->getRepository(Goods::class)
                    ->resetUpdateImageTd();
            return;
        }        
        
        foreach ($goodsForUpdate as $good){
            if (time() >= $finishTime){
                return;
            }
            $this->externalManager->addImageToGood($good);
        }
        
        return;
    }

    /**
     * Обновить машины у товаров
     */
    public function updateCars()
    {        
        set_time_limit(900);
        $startTime = time();
        $finishTime = $startTime + 800;
        
        $goodsForUpdate = $this->entityManager->getRepository(Goods::class)
                ->findGoodsForUpdateCarTd();

        if (count($goodsForUpdate) == 0){
            $this->entityManager->getRepository(Goods::class)
                    ->resetUpdateCarTd();
            return;
        }        
        
        foreach ($goodsForUpdate as $good){
            if (time() >= $finishTime){
                return;
            }
//            $this->entityManager->getConnection()->update('goods', ['status_car' => Goods::CAR_UPDATING], ['id' => $good->getId()]);
            $this->externalManager->addCarsToGood($good);
//            $this->entityManager->getConnection()->update('goods', ['status_car' => Goods::CAR_UPDATED], ['id' => $good->getId()]);
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
     * @param \Application\Entity\Goods $good
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
     * @param type $prices
     * @return float 
     */
    public function meanPrice($prices)
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
                return Mean::arithmetic($newPrices);
            } else {
                return $minPrice;
            }    
        }
        
        return 0.0;
    }

    /**
     * Проверка цены из прайса
     * 
     * @param \Application\Entity\Rawprice $rawprice
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
     * @param \Application\Entity\Goods $good
     */
    public function updatePrices($good)
    {
        $rawprices = $this->entityManager->getRepository(Goods::class)
                ->rawpriceArticles($good);
        
        $prices = $this->getPricesFromRawprices($rawprices);
        
        $this->entityManager->getRepository(Goods::class)
                ->updateGoodId($good->getId(), ['min_price' => $this->minPrice($prices), 'mean_price' => $this->meanPrice($prices)]);
        
        foreach ($rawprices as $rawprice){
            $this->entityManager->getRepository(Rawprice::class)
                    ->updateRawpriceField($rawprice->getId(), ['status_price' => Rawprice::PRICE_PARSED]);
        }
        
        unset($rawprices);
        unset($prices);
        
        return;
    }
    
    /**
     * Пересчет цен товаров прайса
     * @param Appllication\Entity\Raw $raw
     */
    public function updatePricesRaw($raw)
    {
        ini_set('memory_limit', '4096M');
        set_time_limit(900);
        
        $goods = $this->entityManager->getRepository(Goods::class)
                ->findGoodsForUpdatePrice($raw);
        
        foreach ($goods as $good){
//            var_dump($good->getId()); exit;
            $this->updatePrices($good);
        }
        
        $goods = $this->entityManager->getRepository(Goods::class)
                ->findGoodsForUpdatePrice($raw);
        
        if (count($goods) == 0){
            $raw->setParseStage(\Application\Entity\Raw::STAGE_PRICE_UPDATET);
            $this->entityManager->persist($raw);

            $this->entityManager->flush();
        } 
        
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
        
        echo memory_get_usage() . "\n";

        $goodCount = $this->entityManager->getRepository(Goods::class)
                ->count([]);
        $limit = intval($goodCount/25);
        
//        $goods = $this->entityManager->getRepository(Goods::class)
//                ->findBy(['statusRawpriceEx' => Goods::RAWPRICE_EX_NEW], null, $limit);
        $goodsQuery = $this->entityManager->getRepository(Goods::class)
                ->findForRawpriceEx(Goods::RAWPRICE_EX_NEW, ['limit' => $limit]);
        $iterable = $goodsQuery->iterate();
//        var_dump(count($goods)); exit;
        foreach($iterable as $item){
            foreach ($item as $row){
//            var_dump($row); 
                $this->compareRawprices($row['id'], $row['dateEx']);                
            }
            if (time() > $startTime + 5){
                echo memory_get_usage() . "\n";
                return;
            }
        }
        
        echo memory_get_usage() . "\n";
        
        unset($iterable);
        return;
    }
    
}
