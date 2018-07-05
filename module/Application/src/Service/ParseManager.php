<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Service;

use Zend\ServiceManager\ServiceManager;
use Application\Entity\Supplier;
use Application\Entity\UnknownProducer;
use Application\Entity\Raw;
use Application\Entity\Rawprice;
use Application\Entity\Goods;
use Application\Entity\PriceDescription;
use Application\Form\PriceDescriptionForm;



/**
 * Description of ParceManager
 * Обработка строк загруженных прайсов
 *
 * @author Daddy
 */
class ParseManager {
        
    const ROW_BATCHSIZE    = 30000; // количество записей единовременной загруки строк прайса

    /**
     * Doctrine entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;
  
    private $producerManager;
  
    private $goodManager;
    
  // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager, $producerManager, $goodManager)
    {
        $this->entityManager = $entityManager;
        $this->producerManager = $producerManager;
        $this->goodManager = $goodManager;
    }
    
    /*
     * Получить поля и функции описания прайса
     */
    public function getPriceDescriptionFunc($raw)
    {
        $result= [];

        $priceDescriptions = $raw->getSupplier()->getPriceDescriptions();
        $form = new PriceDescriptionForm();
        $elements = $form->getElements();

        foreach($priceDescriptions as $priceDescription){
            if ($priceDescription->getStatus() == PriceDescription::STATUS_ACTIVE){
                foreach ($elements as $element){
                    if(in_array($element->getName(), ['name', 'status', 'type'])) continue;
                    $func = 'get'.ucfirst($element->getName());
                    if (method_exists($priceDescription, $func)){
                        $result[$priceDescription->getId()][$element->getName()] = $priceDescription->$func();
                    }
                }
            }    
        }  
        
        if (count($result)){
            return $result;
        }
        
        return;
    }
    
    /*
     * Разбока строки данных прайса
     * @var Application\Entity\Rawprice @rawprice
     */
    public function parseRawdata($rawprice, $priceDescriptionFunc = null)
    {
        if (!$priceDescriptionFunc){
            $priceDescriptionFunc = $this->getPriceDescriptionFunc($rawprice->getRaw());
        }
        
        if (count($priceDescriptionFunc)){
            $result= [];
    
            $rawdata = explode(';', $rawprice->getRawdata());
        
            foreach ($priceDescriptionFunc as $priceDescriptionId => $elements){
                foreach ($elements as $name => $value){
                    $result[$priceDescriptionId][$name] = '';
                    if ($value && count($rawdata) >= $value){
                        $result[$priceDescriptionId][$name] = $rawdata[$value - 1];                        
                    }
                }    
            }

            if (!count($result)) return;
        
            if (count($result) === 1){
                foreach ($result as $parce){
                    return $parce;
                }
            } else {
                //выбор лучшей разборки
                return $result[0];
            }        
        }
        
        return;
    }
        
    /*
     * @var Application\Entity\Rawprice $rawprice
     * @var array @parsedata
     * @var bool $flushnow
     */
    
    public function updateRawprice($rawprice, $priceDescriptionFunc = null, $flushnow = true, $status = Rawprice::STATUS_PARSE)
    {
        $data = $this->parseRawdata($rawprice, $priceDescriptionFunc);
        
        if (!is_array($data)) return;

        $rawprice->setStatus($status);            
        
        $rawprice->setArticle($data['article']);
        $rawprice->setProducer($data['producer']);
        $rawprice->setTitle($data['title']);
        $rawprice->setPrice($data['price']);
        $rawprice->setRest($data['rest']);            
        $rawprice->setIid($data['iid']);
        $rawprice->setOem($data['oem']);
        $rawprice->setBrand($data['brand']);
        $rawprice->setVendor($data['vendor']);
        $rawprice->setLot($data['lot']);
        $rawprice->setUnit($data['unit']);
        $rawprice->setCar($data['car']);
        $rawprice->setBar($data['bar']);
        $rawprice->setCurrency($data['currency']);
        $rawprice->setComment($data['comment']);
        $rawprice->setWeight($data['weight']);
        $rawprice->setCountry($data['country']);
        $rawprice->setMarkdown($data['markdown']);
        $rawprice->setSale($data['sale']);
        $rawprice->setImage($data['image']);

        $this->entityManager->persist($rawprice);

        if ($flushnow){
            $this->entityManager->flush();
            $this->entityManager->clear();
        }    
        
        return;
    }
    
    /*
     * Поиск прайса для разбоки
     * return Application\Entity\Raw
     */
    public function findRawForParse()
    {
        $raws = $this->entityManager->getRepository(Raw::class)
                ->findBy(['status' => Raw::STATUS_ACTIVE], ['id' => 'ASC'])
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
    
    /*
     * Получить записи прайса для разбоки
     * @var Application\Entity\Raw @raw
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
    
    /*
     * Поиск и пометка старых прайсов на удаление
     * @var Application\Entity\Raw @raw
     * 
     */
    
    public function setOldRaw($raw)
    {        
        $i = 0;
        $batch_count = 100;
        $coincidence = 0;
        
        if ($raw){
            $rawprices = $this->entityManager->getRepository(Rawprice::class)
                    ->findRawRawprice($raw);
            var_dump(count($rawprices[0])); exit;
            $oldRaws = $this->entityManager->getRepository(Raw::class)
                    ->findOldRaw($raw);
            
            foreach ($oldRaws as $oldRaw){
                foreach ($rawprices as $rawprice){
                    var_dump($rawprice->getProducer());
                    var_dump($rawprice->getArticle()); exit;
                    if ($rawprice->getProducer() && $rawprice->getArticle()){
                        $oldRawprices = $this->entityManager->getRepository(Rawprice::class)
                                ->findBy(['raw' => $oldRaw->getId(), 'producer' => $rawprice->getProducer(), 'article' => $rawprice->getArticle()]);

                        if ($oldRawprices){
                            $coincidence++;
                        }
                        
                        $i++;
                        if ($i >= $batch_count){
                            break;
                        }
                    }    
                }

            
                
                if (($coincidence *100/ $i) > 30){
                    $oldRaw->setStatus(Raw::STATUS_RETIRED);
                    $this->entityManager->persist($oldRaw);
                }                      
            }    
            
            $this->entityManager->flush();
            
        }  
        
        return;
    }
    
    /*
     * Парсить записи прайса
     * @var Application\Entity\Raw @raw
     * 
     */
    public function parseRaw($raw = null)
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(0);
        $i = 0;
        
        if (!$raw){
            $raw = $this->findRawForParse();
        }    
        
        if ($raw){
            $rawprices = $this->findRawpricesForParse($raw);
            $priceDescriptionFunc = $this->getPriceDescriptionFunc($raw);

            if (count($priceDescriptionFunc)){
                
                foreach ($rawprices as $rawprice){

                    if ($rawprice->getStatus() == Rawprice::STATUS_NEW){
                        $this->updateRawprice($rawprice, $priceDescriptionFunc, false, Rawprice::STATUS_PARSE);

                        $i++;
                        if (($i % $this::ROW_BATCHSIZE) === 0) {
                            $this->entityManager->flush();
                        }
                    }    
                }
                
                $this->entityManager->flush();
                
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
                    $this->entityManager->flush();
                }

                $this->entityManager->clear();
            }    
        }    
        
        return;
    }
    

    /*
     * Собрать неизвестных поставщиков
     * @var Application\Entity\Rawprice
     * 
     */
    public function unknownProducerRawprice($rawprice, $flushnow = true)
    {
        if ($rawprice->getProducer()){
            $unknownProducer = $this->producerManager->addUnknownProducer($rawprice->getProducer(), false);
            $rawprice->setUnknownProducer($unknownProducer);
            $this->entityManager->persist($rawprice);        
        }
        if ($flushnow){        
            $this->entityManager->flush();
        }    
    }

    /*
     * Выбрать и добавить уникальных производителей
     * @var Application\Entity\Raw @raw
     * 
     */    
    public function addNewUnknownProducerRaw($raw)
    {
        $producers = $this->entityManager->getRepository(Raw::class)
                ->findProducerRawprice($raw);
        foreach ($producers as $producer){
            if (is_string($producer['producer']) && $producer['producer']){
                $this->producerManager->addUnknownProducer($producer['producer'], false);
            }    
        }
        $this->entityManager->flush();
    }
    
    /*
     * Парсить все записи
     * @var Application\Entity\Raw @raw
     * 
     */
    public function unknownProducerRaw($raw)
    {
        foreach ($raw->getRawprice() as $rawprice){
            $this->unknownProducerRawprice($rawprice, false);
        }
        
        $this->entityManager->flush();
    }
    
    
    /*
     * Выбрать и добавить уникальные товары
     * @var Application\Entity\Raw @raw
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
    
    
    /*
     * Привязать товар к прайсу
     * @var Application\Entity\Rawprice
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
    
    /*
     * Установить цену товара
     * @var Application\Entity\Rawprice $rawprice
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


    /*
     * Парсить все записи
     * @var Application\Entity\Raw @raw
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
    
    /*
     * Парсить все записи
     * @var Application\Entity\Raw @raw
     * 
     */
    public function updateGoodRaw($raw)
    {
        foreach ($raw->getRawprice() as $rawprice){
            $this->updateGoodRawprice($rawprice, false);
        }
        
        $this->entityManager->flush();
    }
    
    /*
     * Установить цену в товарах прайса
     * @var Application\Entity\Raw @raw
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
