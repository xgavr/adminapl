<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Service;

use Zend\ServiceManager\ServiceManager;
use Application\Entity\UnknownProducer;
use Application\Entity\Raw;
use Application\Entity\Rawprice;
use Application\Entity\Goods;
use Application\Entity\PriceDescription;
use Application\Form\PriceDescriptionForm;
use Application\Filter\StrSimilar;
use Application\Validator\IsBlackList;
use Application\Validator\IsNumericPrice;

use Phpml\Classification\KNearestNeighbors;
use Phpml\ModelManager;



/**
 * Description of ParceManager
 * Обработка строк загруженных прайсов
 *
 * @author Daddy
 */
class ParseManager {
        
    const ROW_BATCHSIZE    = 30000; // количество записей единовременной загруки строк прайса
    const ML_DATA_PATH     = './data/ann/'; //путь к папке с моделями ml

    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    private $blackListValidator;
      
    
  // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
        $this->blackListValidator = new IsBlackList();
    }
    
    /**
     * Получить поля и функции описания прайса
     * @param \Application\Entity\Raw $raw
     * 
     */
    public function getPriceDescriptionFunc($raw)
    {
        $spl = new \SplObjectStorage();

        $priceDescriptions = $raw->getSupplier()->getPriceDescriptions();
        $form = new PriceDescriptionForm();
        $elements = $form->getElements();

        foreach($priceDescriptions as $priceDescription){
            if ($priceDescription->getStatus() == PriceDescription::STATUS_ACTIVE){
                $result = [];
                foreach ($elements as $element){
                    if (in_array($element->getName(), ['name', 'status', 'type'])) {
                        continue;
                    }
                    $func = 'get'.ucfirst($element->getName());
                    if (method_exists($priceDescription, $func)){
                        $result[$element->getName()] = $priceDescription->$func();
                    }
                }
                $spl[$priceDescription] = $result;
            }    
        }  
        
        if ($spl->count()){
            return $spl;
        }
        
        return;
    }
    
    /**
     * Разбока строки данных прайса и выбор лучшего описания полей
     * @param \Application\Entity\Rawprice @rawprice
     * @param array $priceDescriptionFunc - описание полей прайса
     */
    public function parseRawdata($rawprice, $priceDescriptionFunc = null)
    {
        if (!$priceDescriptionFunc){
            $priceDescriptionFunc = $this->getPriceDescriptionFunc($rawprice->getRaw());
        }

        $priceValidator = new IsNumericPrice();
        
        if (count($priceDescriptionFunc)){
            $spl = new \SplObjectStorage();
    
            $rawdata = explode(';', $rawprice->getRawdata());
        
            foreach ($priceDescriptionFunc as $priceDescription){
                $result = [];
                foreach ($priceDescriptionFunc[$priceDescription] as $name => $value){
                    $result[$name] = '';
                    if ($value && is_numeric($value) && count($rawdata) >= $value && $rawdata[$value - 1]){
                        $result[$name] = $rawdata[$value - 1];                        
                    }
                }    
                
                if (!$priceValidator->isValid($result['price'])){
                    unset($result['price']);
                }
                
//                if (!$priceValidator->isValid($result['rest'])){
//                    unset($result['rest']);
//                }
                                
                if (!$result['producer'] && $priceDescriptionFunc[$priceDescription]['defaultProducer']){
                    $result['producer'] = $priceDescriptionFunc[$priceDescription]['defaultProducer'];                    
                }
                
                $spl[$priceDescription] = $result;
            }
            

            if (!count($spl)) {
                return;
            }

            $resultParse = [];
            foreach ($spl as $priceDescription){
//                var_dump($spl[$priceDescription]);
//                var_dump(count($resultParse));
                if (count(array_filter($spl[$priceDescription])) > count(array_filter($resultParse)) - 1){
                    $resultParse = $spl[$priceDescription];
                    $resultParse['priceDescription'] = $priceDescription->getId();
                }
            }
            return $resultParse;
        }
        
        return;
    }
        
    /**
     * @param \Application\Entity\Rawprice $rawprice
     * @param array $priceDescriptionFunc
     * @param bool $flushnow
     * @param integer $status
     */
    
    public function updateRawprice($rawprice, $priceDescriptionFunc = null, $flushnow = true, $status = Rawprice::STATUS_PARSED)
    {
        $data = $this->parseRawdata($rawprice, $priceDescriptionFunc);
        
        if (!is_array($data)) {
            return;
        }

        if ($this->blackListValidator->isValid(implode(' ', $data))){
            $rawprice->setStatus(Rawprice::STATUS_BLACK_LIST);
        } else {
            $rawprice->setStatus($status);            
        }    
        
        $rawprice->setArticle($data['article']);
        $rawprice->setProducer($data['producer']);
        $rawprice->setTitle($data['title']);
        $rawprice->setPrice(isset($data['price']) ? $data['price']:0);
        $rawprice->setRest(isset($data['rest']) ? $data['rest']:0);            
        $rawprice->setIid($data['iid']);
        $rawprice->setOem($data['oem']);
        $rawprice->setBrand($data['brand']);
        $rawprice->setVendor($data['vendor']);
        $rawprice->setLot($data['lot']);
        $rawprice->setUnit($data['unit']);
        $rawprice->setPack($data['pack']);
        $rawprice->setCar($data['car']);
        $rawprice->setBar($data['bar']);
        $rawprice->setCurrency($data['currency']);
        $rawprice->setComment($data['comment']);
        $rawprice->setWeight($data['weight']);
        $rawprice->setCountry($data['country']);
        $rawprice->setMarkdown($data['markdown']);
        $rawprice->setSale($data['sale']);
        $rawprice->setImage($data['image']);
        $rawprice->setPriceDescription($data['priceDescription']);
        
        $this->entityManager->getRepository(Rawprice::class)
                ->updateRawprice($rawprice);

        return;
    }    

    /**
     * Поиск прайса для разбоки
     * @return \Application\Entity\Raw
     */
    public function findRawForParse()
    {
        $raws = $this->entityManager->getRepository(Raw::class)
                ->findBy(['status' => Raw::STATUS_ACTIVE], ['id' => 'DESC'])
                ;
        foreach ($raws as $raw){
            $priceDescriptions = $this->entityManager->getRepository(PriceDescription::class)
                    ->findBy(['supplier' => $raw->getSupplier()->getId(), 'status' => PriceDescription::STATUS_ACTIVE]);
            if (count($priceDescriptions)){
                $statuses = $this->entityManager->getRepository(Raw::class)
                        ->rawpriceStatuses($raw);
                foreach ($statuses as $status){
                    if ($status['status'] == Rawprice::STATUS_NEW && $status['status_count']){
                        return $raw;
                    }
                }
                
                $raw->setStatus(Raw::STATUS_PARSED);
                $this->entityManager->persist($raw);
                $this->entityManager->flush();
            }
        }
        
        return;
    }
    
    /**
     * Получить записи прайса для разбоки
     * @parsm \Application\Entity\Raw $raw
     * 
     */    
    public function findRawpricesForParse($raw = null)
    {
        if (!$raw){
            $raw = $this->findRawForParse();
        }    
        
        if ($raw){
            return $this->entityManager->getRepository(Rawprice::class)
                    ->findRawpriceForParse($raw, $this::ROW_BATCHSIZE);
            
            
        }
        return;
    }
    
    
    /**
     * Сравнение прайсов
     * @param \Application\Entity\Raw $raw 
     * @param \Aapplication\Entity\Raw $prevRaw 
     * 
     * @return array
     */
    public function compareRaw($raw, $prevRaw)
    {
        $result['strPer'] = 0;
        $result['rowPer'] = 0;

        if ($prevRaw){
            $filter = new StrSimilar();
            $result['strPer'] = round($filter->filter($raw->getFilename(), $prevRaw->getFilename())/100);
            if ($prevRaw->getRows()){
                $result['rowPer'] = round($raw->getRows()/$prevRaw->getRows());
                if ($result['rowPer'] > 2){
                    $result['rowPer'] = 0;
                }
            }
        }    
        
        return $result;
    }

    public function deleteRawTrain()
    {
        error_reporting(E_ALL & ~E_NOTICE);
        
        $samples = [[0, 0], [0, 1], [1, 1], [1, 0]];
        $labels = [0, 1, 1, 0];

        $classifier = new KNearestNeighbors();
        $classifier->train($samples, $labels);

        $filepath = (self::ML_DATA_PATH . 'delete_raw.net');
        $modelManager = new ModelManager();
        $modelManager->saveToFile($classifier, $filepath);
    }
    
    /**
     * Решение о пометке на удаление прайса
     * @param \Application\Entity\Raw $raw 
     * @param \Aapplication\Entity\Raw $oldRaw 
     * 
     * @return bool
     */
    public function isDeleteRaw($raw, $oldRaw)
    {
        $filepath = (self::ML_DATA_PATH . 'delete_raw.net');
        $modelManager = new ModelManager();
        $restoredClassifier = $modelManager->restoreFromFile($filepath);
        $data = $this->compareRaw($raw, $oldRaw);
        return $restoredClassifier->predict([$data['strPer'], $data['rowPer']]);        
    }


    /**
     * Поиск и пометка старых прайсов на удаление
     * @param \Application\Entity\Raw $raw
     * 
     */
    
    public function setOldRaw($raw)
    {        
        if ($raw->getStatus() == Raw::STATUS_PARSED){
            
        } else {
            return;
        }
        
        if ($raw){

            $oldRaws = $this->entityManager->getRepository(Raw::class)
                    ->findOldRaw($raw);
            
            foreach ($oldRaws as $oldRaw){
                                
                if ($this->isDeleteRaw($raw, $oldRaw)){
                    $oldRaw->setStatus(Raw::STATUS_PRE_RETIRED);
                    $this->entityManager->persist($oldRaw);
                    $this->entityManager->flush($oldRaw);            

//                    $this->entityManager->getRepository(Raw::class)
//                            ->updateAllRawpriceStatus($oldRaw, Rawprice::STATUS_RETIRED);
                }                      
            }    
            
        }  
        
        return;
    }
    
    /**
     * Парсить записи прайса
     * @param \Application\Entity\Raw $raw
     * 
     */
    public function parseRaw($raw = null)
    {
        ini_set('memory_limit', '4096M');
        set_time_limit(0);
        
        if (!$raw){
            $raw = $this->findRawForParse();
        }    
        
        if ($raw){
            
            $rawprices = $this->findRawpricesForParse($raw);
            $priceDescriptionFunc = $this->getPriceDescriptionFunc($raw);
            
            if (count($priceDescriptionFunc)){
                
                $raw->setStatus(Raw::STATUS_PARSE);
                $this->entityManager->persist($raw);
                $this->entityManager->flush();
            
                foreach ($rawprices as $rawprice){

                    if ($rawprice->getStatus() == Rawprice::STATUS_NEW){
                        $this->updateRawprice($rawprice, $priceDescriptionFunc, false, Rawprice::STATUS_PARSED);
                    }    
                }
                
                $parsedAll = true;
                $statuses = $this->entityManager->getRepository(Raw::class)
                        ->rawpriceStatuses($raw);
                
                foreach ($statuses as $status){
                    if ($status['status'] == Rawprice::STATUS_NEW && $status['status_count']){
                        $parsedAll = false;
                    }
                }

                if ($parsedAll){            
                    $raw->setStatus(Raw::STATUS_PARSED);
                    $this->entityManager->persist($raw);                    
                    $this->entityManager->flush($raw);
                    
                    $this->setOldRaw($raw);
                }

            }    
        }    
        
        return;
    }
    
    
    /**
     * Выбрать и добавить уникальные товары
     * @param \Application\Entity\Raw $raw
     * 
     */    
    public function addNewGoodsRaw($raw)
    {
        ini_set('memory_limit', '512M');
        
        $rawprices = $this->entityManager->getRepository(Raw::class)
                ->findGoodRawprice($raw);

        foreach ($rawprices as $rawprice){

            if (is_string($rawprice['article']) && $rawprice['goodname'] && $rawprice['unknownProducer']){
                
                $unknownProducer = $this->entityManager->getRepository(UnknownProducer::class)
                        ->findOneById($rawprice['unknownProducer']);
                
                if ($unknownProducer && $unknownProducer->getProducer()){
                    
                    $good = $this->entityManager->getRepository(Goods::class)
                                ->findOneBy([
                                    'producer' => $unknownProducer->getProducer(), 
                                    'code' => $rawprice['article'],
                                    'name' => $rawprice['goodname'],
                                ]);
                    
                    if ($good == NULL){
                        $good = $this->goodManager->addNewGoods([
                            'name' => $rawprice['goodname'],
                            'code' => $rawprice['article'],
                            'available' => Goods::AVAILABLE_TRUE,
                            'description' => $rawprice['goodname'],
                            'producer' => $unknownProducer->getProducer(),
                        ], false);
                    }                
                }    
            }    
        }
        $this->entityManager->flush();
    }
    
    
    /**
     * Привязать товар к прайсу
     * @param \Application\Entity\Rawprice $rawprice
     * @param bool $flushnow
     */
    public function addGoodRawprice($rawprice, $flushnow = true)
    {
        if ($rawprice->getUnknownProducer()){
            if ($rawprice->getUnknownProducer()->getProducer() && $rawprice->getGoodname()){
                $good = $this->entityManager->getRepository(Goods::class)
                            ->findOneBy([
                                'producer' => $rawprice->getUnknownProducer()->getProducer()->getId(), 
                                'code' => $rawprice->getArticle(),
                                'name' => $rawprice->getGoodname(),
                            ]);
                if ($good == NULL){                    
                    $good = $this->goodManager->addNewGoods([
                        'name' => $rawprice->getGoodname(),
                        'code' =>$rawprice->getArticle(),
                        'available' => Goods::AVAILABLE_TRUE,
                        'description' => $rawprice->getGoodname(),
                        'producer' => $rawprice->getUnknownProducer()->getProducer(),
                    ]);
                }
                
                $rawprice->setGood($good);
                $this->entityManager->persist($rawprice);        
                if ($flushnow){
                    $this->entityManager->flush();    
                }
            }
        }
    }
    
    /**
     * 
     * @param \Application\Entity\Rawprice $rawprice
     * @param bool $flushnow
     */
    public function updateGoodRawprice($rawprice, $flushnow = true)
    {
        if ($rawprice->getUnknownProducer() && $rawprice->getGood()){
            if ($rawprice->getUnknownProducer()->getProducer() && $rawprice->getGoodname()){
                $good = $this->entityManager->getRepository(Goods::class)
                            ->findOneBy([
                                'producer' => $rawprice->getUnknownProducer()->getProducer(), 
                                'code' => $rawprice->getArticle(),
                                'name' => $rawprice->getGoodname(),
                            ]);
                if ($good == NULL){
                    $good = $this->goodManager->updateGoods($rawprice->getGood(), [
                        'name' => $rawprice->getGoodname(),
                        'code' =>$rawprice->getArticle(),
                        'available' => Goods::AVAILABLE_TRUE,
                        'description' => $rawprice->getGoodname(),
                        'producer' => $rawprice->getUnknownProducer()->getProducer(),
                    ]);
                }
                
                $rawprice->setGood($good);
                $this->entityManager->persist($rawprice);        
                if ($flushnow){
                    $this->entityManager->flush();    
                }
            }
        }
    }
    
    /**
     * Установить цену товара
     * @param \Application\Entity\Rawprice $rawprice
     * @param bool $flushnow
     */
    public function setPriceRawprice($rawprice, $flushnow = true)
    {
        if ($rawprice->getGood()){
            
            $good = $rawprice->getGood();
            $price = $this->goodManager->getMaxPrice($good);
            
            $good->setPrice($price);
            $this->entityManager->persist($good);        
            if ($flushnow){
                $this->entityManager->flush();    
            }
        }        
    }


    /**
     * Парсить все записи
     * @param \Application\Entity\Raw $raw
     * 
     */
    public function addGoodRaw($raw)
    {
        set_time_limit(180);
        ini_set('memory_limit', '512M');
        
        foreach ($raw->getRawprice() as $rawprice){
            $this->addGoodRawprice($rawprice, false);
        }
        
        $this->entityManager->flush();
    }
    
    /**
     * Парсить все записи
     * @param \Application\Entity\Raw $raw
     * 
     */
    public function updateGoodRaw($raw)
    {
        foreach ($raw->getRawprice() as $rawprice){
            $this->updateGoodRawprice($rawprice, false);
        }
        
        $this->entityManager->flush();
    }
    
    /**
     * Установить цену в товарах прайса
     * @param \Application\Entity\Raw $raw
     * 
     */
    public function setPriceRaw($raw)
    {
        set_time_limit(180);
        ini_set('memory_limit', '512M');

        foreach ($raw->getRawprice() as $rawprice){
            $this->setPriceRawprice($rawprice, false);
        }
        
        $this->entityManager->flush();
    }
                
}
