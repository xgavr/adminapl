<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Service;

use Zend\ServiceManager\ServiceManager;
use Application\Entity\Supplier;
use Application\Entity\Tax;
use Application\Entity\Country;
use Application\Entity\Producer;
use Application\Entity\UnknownProducer;
use Application\Entity\Raw;
use Application\Entity\Rawprice;
use Application\Entity\Goods;
use MvlabsPHPExcel\Service;
use Zend\Json\Json;

/**
 * Description of PriceManager
 *
 * @author Daddy
 */
class RawManager {
    
    const PRICE_FOLDER       = './data/prices'; // папка с прайсами
    const PRICE_FOLDER_ARX   = './data/prices/arx'; // папка с архивами прайсов

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
    
    public function getPriceFolder()
    {
        return self::PRICE_FOLDER;
    }        

    public function getPriceArxFolder()
    {
        return self::PRICE_FOLDER_ARX;
    }      
    
    
    /*
     * Очистить содержимое папки
     * 
     * @var Application\Entity\Supplier $supplier
     * @var string $folderName
     * 
     */
    public function clearPriceFolder($supplier, $folderName)
    {
        if (is_dir($folderName)){
            if ($dh = opendir($folderName)) {
                while (($file = readdir($dh)) !== false) {
                    if($file != "." && $file != ".."){ // если это не папка
                        if(is_file($folderName."/".$file)){ // если файл
                            unlink($folderName."/".$file);                            
                        }                        
                        // если папка, то рекурсивно вызываем
                        if(is_dir($folderName."/".$file)){
                            $this->clearPriceFolder($supplier, $folderName."/".$file);
                        }
                    }           
                }
                closedir($dh);
                
                if ($folderName != self::PRICE_FOLDER.'/'.$supplier->getId()){
                    rmdir($folderName);
                }
            }
        }
        
    }
    
    /**
     * Загрузка сырого прайса
     * @var Application\Entity\Supplier
     * @var string $filename
     */
    
    public function uploadRawprice($supplier, $filename){
                
        if (file_exists($filename)){
            $mvexcel = new Service\PhpExcelService();    
            $excel = $mvexcel->createPHPExcelObject($filename);
            
            $raw = new Raw();
            $raw->setSupplier($supplier);
            $raw->setFilename($filename);
            $raw->setStatus($raw->getStatusActive());

            $currentDate = date('Y-m-d H:i:s');
            $raw->setDateCreated($currentDate);
                        
            $sheets = $excel->getAllSheets();
            foreach ($sheets as $sheet) { // PHPExcel_Worksheet

                $excel_sheet_content = $sheet->toArray();

                if (count($sheet)){
                    foreach ($excel_sheet_content as $row){
                        $rawprice = new Rawprice();

                        $rawprice->setRawdata(Json::encode($row));
                        
                        $rawprice->setArticle('');
                        $rawprice->setGoodname('');
                        $rawprice->setProducer('');
                        $rawprice->setPrice(0);
                        $rawprice->setRest(0);

                        $rawprice->setRaw($raw);
                        
//                        $unknownProducer = new UnknownProducer();
//                        $rawprice->setUnknownProducer($unknownProducer);

//                        $good = new Goods();
//                        $rawprice->setGood($good);

                        $currentDate = date('Y-m-d H:i:s');
                        $rawprice->setDateCreated($currentDate);

                        // Добавляем сущность в менеджер сущностей.
                        $this->entityManager->persist($rawprice);
                        
                        $raw->addRawprice($rawprice);

                    }
                    // Применяем изменения к базе данных.
                }	
            }

            $this->entityManager->persist($raw);
            
            $this->entityManager->flush();                    
            
            unset($excel);
            unset($mvexcel);
        }
    }
    
    /*
     * 
     * Проверка папки с прайсами. Если в папке есть прайс то загружаем его
     * 
     * @var Application\Entity\Supplier $supplier
     * @var string $folderName
     * 
     */
    public function checkPriceFolder($supplier, $folderName)
    {
    
        if (is_dir($folderName)){
            if ($dh = opendir($folderName)) {
                while (($file = readdir($dh)) !== false) {
                    if($file != "." && $file != ".."){ // если это не папка
                        if(is_file($folderName."/".$file)){ // если файл
                            
                            if ($supplier->getStatus() == $supplier->getStatusActive()){
                                $this->uploadRawprice($supplier, $folderName."/".$file);
                            }
                            
                            $arx_folder = self::PRICE_FOLDER_ARX.'/'.$supplier->getId();
                            if (is_dir($arx_folder)){
                                if (!rename($folderName."/".$file, $arx_folder."/".$file)){
                                    unlink($folderName."/".$file);
                                }
                            }
                            
                        }                        
                        // если папка, то рекурсивно вызываем
                        if(is_dir($folderName."/".$file)){
                            $this->checkPriceFolder($supplier, $folderName."/".$file);
                        }
                    }           
                }
                closedir($dh);
            }
        }
    }
    
    /*
     * Проход по всем поставщикам - поиск файлов с прайсам в папках
     */
    public function checkSupplierPrice()
    {
        
        $suppliers = $this->entityManager->getRepository(Supplier::class)->findAll();
        
        foreach ($suppliers as $supplier){
            $this->checkPriceFolder($supplier, self::PRICE_FOLDER.'/'.$supplier->getId());
            $this->clearPriceFolder($supplier, self::PRICE_FOLDER.'/'.$supplier->getId());
        }
    }
    
    /*
     * Обработка данных прайса
     * @var Application\Entity\Rawprice @rawprice
     * @var Apllication\Entity\Pricesettings #paicesettings
     */
    public function parseRawdata($rawprice, $pricesetting)
    {
        $rawdata = Json::decode($rawprice->getRawdata());
        
        $result['article'] = $rawdata[$pricesetting->getArticle() - 1];
        $result['producer'] = $rawdata[$pricesetting->getProducer() - 1];
        $result['goodname'] = $rawdata[$pricesetting->getTitle() - 1];
        $result['price'] = $rawdata[$pricesetting->getPrice() - 1];
        $result['rest'] = $rawdata[$pricesetting->getRest() - 1];
        
        return $result;
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
        $raw = $rawprice->getRaw();
        $pricesettings = $raw->getSupplier()->getPricesettings();
        
        $data = [];
        foreach ($pricesettings as $pricesetting){
            if ($pricesetting->getStatus() == $pricesetting->getStatusActive()){
                $data[] = $this->parseRawdata($rawprice,$pricesetting);
            }            
        }
        
        $this->updateParsedata($rawprice, $this->selectBestParsedata($data), $flushnow);
        
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
        $unknownProducer = $this->producerManager->addUnknownProducer($rawprice->getProducer(), false);
        $rawprice->setUnknownProducer($unknownProducer);
        $this->entityManager->persist($rawprice);        
        
        if ($flushnow){        
            $this->entityManager->flush();
        }    
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
     * Создать товар
     * @var Application\Entity\Raawprice
     */
    public function addGoodRawprice($rawprice, $flushnow = true)
    {
        if ($rawprice->getUnknownProducer()){
            if ($rawprice->getUnknownProducer()->getProducer() && $rawprice->getGoodname()){
                $good = $this->entityManager->getRepository(Goods::class)
                            ->findOneBy([
                                'producer' => $rawprice->getUnknownProducer()->getProducer(), 
                                'code' => $rawprice->getArticle(),
                                'name' => $rawprice->getGoodname(),
                            ]);
                if ($good == NULL){
                    $good = $this->goodManager->addNewGoods([
                        'name' => $rawprice->getGoodname(),
                        'code' =>$rawprice->getArticle(),
                        'available' => 1,
                        'description' => '',
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
                        'available' => 1,
                        'description' => '',
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
     * Парсить все записи
     * @var Application\Entity\Raw @raw
     * 
     */
    public function addGoodRaw($raw)
    {
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
    
    
    public function removeRawprice($rawprice)
    {
        $this->entityManager->remove($rawprice);
        $this->entityManager->flush();        
    }
    
    public function removeRaw($raw)
    {
        $rawprices = $raw->getRawprice();
        foreach ($rawprices as $rawprice){
            $this->entityManager->remove($rawprice);
        }        
        
        $this->entityManager->remove($raw);
        $this->entityManager->flush();
    }
        
}
