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
            if (!$data['tax']) $data['tax'] = $this->getSettings()->defaultTax;
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
                ->findGoodsForUpdateCar();

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
     * Получить массив цен товара
     * @param \Application\Entity\Goods $good
     * @return array
     */
    public function rawpricesPrices($good)
    {
        $result = [];
        
        $rawprices = $this->entityManager->getRepository(Goods::class)
                ->rawpriceArticles($good);
        
        foreach ($rawprices as $rawprice){
            if ($rawprice->getRealPrice()>0 && $rawprice->getRealRest()>0){
                $result = array_merge($result, array_fill(0, $rawprice->getRealRest(), $rawprice->getRealPrice()));
            }
        }
        
        return $result;
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
     */
    public function inSigma($rawprice)
    {
        $good = $rawprice->getGood();
        if ($good && $rawprice->getRealPrice()>0 && $rawprice->getRealRest()>0){
            $prices = $this->rawpricesPrices($good);
            var_dump($prices);
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
        $prices = $this->rawpricesPrices($good);
        $this->entityManager->getRepository(Goods::class)
                ->updateGoodId($good->getId(), ['min_price' => $this->minPrice($prices), 'mean_price' => $this->meanPrice($prices)]);
        
        return;
    }
}
