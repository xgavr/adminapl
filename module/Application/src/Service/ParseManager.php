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
use Application\Filter\RawToStr;
use Application\Filter\CsvDetectDelimiterFilter;
use Zend\Json\Json;
use Application\Form\PriceDescriptionForm;

use Zend\Validator\File\IsCompressed;
use Zend\Filter\Decompress;


/**
 * Description of ParceManager
 * Обработка строк загруженных прайсов
 *
 * @author Daddy
 */
class ParseManager {
        
    const PRICE_BATCHSIZE    = 50000; // количество записей единовременной загруки строк прайса

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
     * Разбока строки данных прайса
     * @var Application\Entity\Rawprice @rawprice
     */
    public function parseRawdata($rawprice)
    {
        $result= [];
        
        $priceDescriptions = $rawprice->getRaw()->getSupplier()->getPriceDescriptions();

        $form = new PriceDescriptionForm();
        $elements = $form->getElements();

        $rawdata = explode(';', $rawprice->getRawdata());
        
        if (count($rawdata)){
                
            foreach($priceDescriptions as $priceDescription){
                
                foreach ($elements as $element){
                    if(in_array($element->getName(), ['name', 'status', 'type'])) continue;
                    $func = 'get'.ucfirst($element->getName());
                    if (method_exists($priceDescription, $func)){
                        if($priceDescription->$func() && count($rawdata) >= $priceDescription->$func()){
                            $result[$priceDescription->getId()][$element->getName()] = $rawdata[$priceDescription->$func() - 1];                            
                        }
                    }
                }
            }  
            
            if (count($result) === 1){
                foreach ($result as $parce){
                    return $parce;
                }
            } else {
                //выбор лучшей разбоки в будущем
            }
        }
        
        return result;
    }
    
    /*
     * @var array @parsedates
     */
    
    protected function selectBestParsedata($parsedates)
    {
        if (count($parsedates == 1)){
            return $parsedates[0];
        }
        
        foreach ($parsedates as $parsedata){
            /*Какие то правила выбора лучшего набора данных*/
            return $parsedata;
        }
        
        return;
    }
    
    /*
     * @var Application\Entity\Rawprice $rawprice
     * @var array @parsedata
     * @var bool $flushnow
     */
    
    protected function updateParsedata($rawprice, $parsedata, $flushnow)
    {
        $rawprice->setArticle($parsedata['article']);
        $rawprice->setProducer($parsedata['producer']);
        $rawprice->setGoodname($parsedata['goodname']);
        $rawprice->setPrice($parsedata['price']);
        $rawprice->setRest($parsedata['rest']);
        
        $this->entityManager->persist($rawprice);
        
        if ($flushnow){
            $this->entityManager->flush();
        }    
    }
    
    /*
     * Обработка строки rawprice
     * @var Application\Entity\Rawprice $rawprice;
     * @bool $flushnow
     */
    public function parseRawprice($rawprice, $flushnow = true)
    {
        ini_set('memory_limit', '512M');
        
        $raw = $rawprice->getRaw();
        $priceDescriptions = $raw->getSupplier()->getPriceDescriptions();
        
        $data = [];
        foreach ($priceDescriptions as $priceDescription){
            if ($priceDescription->getStatus() == $priceDescription->getStatusActive()){
                $parceData = $this->parseRawdata($rawprice,$priceDescription);
                if (is_array($parceData)){
                    $data[] = $parceData; 
                }            
            }            
        }
        
        if (count($data)){
            $this->updateParsedata($rawprice, $this->selectBestParsedata($data), $flushnow);
        }    
        
        return;
    }
    
    /*
     * Парсить все записи
     * @var Application\Entity\Raw @raw
     * 
     */
    public function parseRaw($raw)
    {
        foreach ($raw->getRawprice() as $rawprice){
            $this->parseRawprice($rawprice, false);
        }
        
        $this->entityManager->flush();
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
