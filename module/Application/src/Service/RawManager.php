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
use MvlabsPHPExcel\Service;
use Zend\Json\Json;
use PhpOffice\PhpSpreadsheet\IOFactory;

use Zend\Validator\File\IsCompressed;
use Zend\Filter\Decompress;


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
    
    /*
     * Переместить файл в архив
     * @var Application\Entity\Supplier
     * @var string $filename
     */
    public function renameToArchive($supplier, $filename)            
    {
        if (file_exists($filename)){
            $pathinfo = pathinfo($filename);
            $arx_folder = self::PRICE_FOLDER_ARX.'/'.$supplier->getId();
            if (is_dir($arx_folder)){
                if (copy(realpath($filename), realpath($arx_folder).'/'.$pathinfo['basename'])){
                    unlink(realpath($filename));
                }
            }
        }
        
        return;
    }

        /**
     * Загрузка сырого прайса csv, txt
     * @var Application\Entity\Supplier
     * @var string $filename
     */
    
    public function uploadRawpriceCsv($supplier, $filename)
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(0);
        $i = 0;
        $batchSize = 50000;        
        
        if (file_exists($filename)){
            
            if ($supplier->getStatus() == $supplier->getStatusActive()){
                
                $pathinfo = pathinfo($filename);
                
                $lines = fopen($filename, 'r');

                if($lines) {

                    $detector = new CsvDetectDelimiterFilter();
                    $delimiter = $detector->filter($filename);
                
                    $filter = new RawToStr();

                    $raw = new Raw();
                    $raw->setSupplier($supplier);
                    $raw->setFilename($pathinfo['basename']);
                    $raw->setStatus($raw->getStatusActive());

                    $currentDate = date('Y-m-d H:i:s');
                    $raw->setDateCreated($currentDate);

                    $this->entityManager->persist($raw);

                    while (($row = fgetcsv($lines, 4096, $delimiter)) !== false) {

                        $str = $filter->filter($row);

                        if ($str){
                            $rawprice = new Rawprice();
                        
                            $rawprice->setRawdata($str);

                            $rawprice->setArticle('');
                            $rawprice->setGoodname('');
                            $rawprice->setProducer('');
                            $rawprice->setPrice(0);
                            $rawprice->setRest(0);

                            $rawprice->setRaw($raw);

                            $currentDate = date('Y-m-d H:i:s');
                            $rawprice->setDateCreated($currentDate);

                            // Добавляем сущность в менеджер сущностей.
                            $this->entityManager->persist($rawprice);

                            $raw->addRawprice($rawprice);
                        }    
                        
                        $i++;
                        if (($i % $batchSize) === 0) {
                            $this->entityManager->flush();
                        }
                        
                    }
                    
                    $this->entityManager->flush();                    
                    $this->entityManager->clear();

                    fclose($lines);
                }                                
            }    
            
            $this->renameToArchive($supplier, $filename);

        }
        
        return;
    }
    
    /**
     * Загрузка сырого прайса xls, xlsx
     * @var Application\Entity\Supplier
     * @var string $filename
     */
    
    public function uploadRawpriceXls($supplier, $filename)
    {
        ini_set('memory_limit', '3072M');
        set_time_limit(0); 
        $i = 0;
        $batchSize = 50000;        
        
        if (file_exists($filename)){
            
            if ($supplier->getStatus() == $supplier->getStatusActive()){
                
                $pathinfo = pathinfo($filename);
                
                $raw = new Raw();
                $raw->setSupplier($supplier);
                $raw->setFilename($pathinfo['basename']);
                $raw->setStatus($raw->getStatusActive());

                $currentDate = date('Y-m-d H:i:s');
                $raw->setDateCreated($currentDate);

                $this->entityManager->persist($raw);
                    
                $filter = new RawToStr();
                    
                $spreadsheet = IOFactory::load($filename);

                $sheets = $spreadsheet->getAllSheets();
                foreach ($sheets as $sheet) { // PHPExcel_Worksheet
                    $excel_sheet_content = $sheet->toArray();

                    if (count($sheet)){
                        foreach ($excel_sheet_content as $row){

                            $str = $filter->filter($row);

                            if ($str){

                                
                                $rawprice = new Rawprice();
                                $rawprice->setRawdata($filter->filter($row));

                                $rawprice->setArticle('');
                                $rawprice->setGoodname('');
                                $rawprice->setProducer('');
                                $rawprice->setPrice(0);
                                $rawprice->setRest(0);

                                $rawprice->setRaw($raw);

                                $currentDate = date('Y-m-d H:i:s');
                                $rawprice->setDateCreated($currentDate);

                                // Добавляем сущность в менеджер сущностей.
                                $this->entityManager->persist($rawprice);

                                $raw->addRawprice($rawprice);
                            }    
                            
                            $i++;
                            if (($i % $batchSize) === 0) {
                                $this->entityManager->flush();
                            }

                        }
                    }
                    
                }
                
                $this->entityManager->flush();                    
                $this->entityManager->clear();

                unset($excel);
                unset($mvexcel);

            }    

            $this->renameToArchive($supplier, $filename);
        }
        
        return;
    }

    /**
     * Загрузка сырого прайса xls, xlsx
     * @var Application\Entity\Supplier
     * @var string $filename
     */
    
    public function uploadRawpriceXls2($supplier, $filename)
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(0); 
        $i = 0;
        $batchSize = 50000;        
        
        if (file_exists($filename)){
            
            if ($supplier->getStatus() == $supplier->getStatusActive()){
                
                $pathinfo = pathinfo($filename);
                
                $mvexcel = new Service\PhpExcelService();
                $excel = $mvexcel->createPHPExcelObject($filename);

                $raw = new Raw();
                $raw->setSupplier($supplier);
                $raw->setFilename($pathinfo['basename']);
                $raw->setStatus($raw->getStatusActive());

                $currentDate = date('Y-m-d H:i:s');
                $raw->setDateCreated($currentDate);

                $this->entityManager->persist($raw);
                    
                $filter = new RawToStr();
                    
                $sheets = $excel->getAllSheets();
                foreach ($sheets as $sheet) { // PHPExcel_Worksheet
                    $excel_sheet_content = $sheet->toArray();

                    if (count($sheet)){
                        foreach ($excel_sheet_content as $row){
                            $rawprice = new Rawprice();
                            
                            $str = $filter->filter($row);

                            if ($str){

                                $rawprice->setRawdata($filter->filter($row));

                                $rawprice->setArticle('');
                                $rawprice->setGoodname('');
                                $rawprice->setProducer('');
                                $rawprice->setPrice(0);
                                $rawprice->setRest(0);

                                $rawprice->setRaw($raw);

                                $currentDate = date('Y-m-d H:i:s');
                                $rawprice->setDateCreated($currentDate);

                                // Добавляем сущность в менеджер сущностей.
                                $this->entityManager->persist($rawprice);

                                $raw->addRawprice($rawprice);
                            }    
                            
                            $i++;
                            if (($i % $batchSize) === 0) {
                                $this->entityManager->flush();
                            }

                        }
                    }
                    
                }
                
                $this->entityManager->flush();                    
                $this->entityManager->clear();

                unset($excel);
                unset($mvexcel);

            }    

            $this->renameToArchive($supplier, $filename);
        }
        
        return;
    }
    
    
    /*
     * Загрузка сырого прайса
     * @var Application\Entity\Supplier
     * @var string $filename
     */
    
    public function uploadRawprice($supplier, $filename)
    {
        if (file_exists($filename)){
            
            if ($supplier->getStatus() == Supplier::STATUS_ACTIVE){
                $pathinfo = pathinfo($filename);

                $validator = new IsCompressed();

                if ($validator->isValid($filename) && $pathinfo['extension'] != 'xlsx'){
                    $filter = new Decompress([
                        'adapter' => $pathinfo['extension'],
                        'options' => [
                            'target' => $pathinfo['dirname'],
                        ],
                    ]);
                    if ($filter->filter($filename)){
                        unlink($filename);
                        return $this->checkPriceFolder($supplier, self::PRICE_FOLDER.'/'.$supplier->getId());
                    }
                }

                if (in_array(strtolower($pathinfo['extension']), ['xls', 'xlsx'])){
                    return $this->uploadRawpriceXls($supplier, $filename);
                }
                if (in_array(strtolower($pathinfo['extension']), ['txt', 'csv'])){
                    return $this->uploadRawpriceCsv($supplier, $filename);
                }
            }
            
            $this->renameToArchive($supplier, $filename);
        }
        
        return;
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
        return;
    }
    
    /*
     * Проход по всем поставщикам - поиск файлов с прайсам в папках
     */
    public function checkSupplierPrice($supplier = null)
    {
        if ($supplier){
            $this->checkPriceFolder($supplier, self::PRICE_FOLDER.'/'.$supplier->getId());
            $this->clearPriceFolder($supplier, self::PRICE_FOLDER.'/'.$supplier->getId());            
        } else {
            $suppliers = $this->entityManager->getRepository(Supplier::class)->findAll();

            foreach ($suppliers as $supplier){
                $this->checkPriceFolder($supplier, self::PRICE_FOLDER.'/'.$supplier->getId());
                $this->clearPriceFolder($supplier, self::PRICE_FOLDER.'/'.$supplier->getId());
            }
        } 
        return;
    }
    
    /*
     * Обработка данных прайса
     * @var Application\Entity\Rawprice @rawprice
     * @var Apllication\Entity\PriceDescription #paicesettings
     */
    public function parseRawdata($rawprice, $priceDescription)
    {
        $rawdata = Json::decode($rawprice->getRawdata());
        $result = [
            'article' => '',
            'producer' => '',
            'goodname' => '',
            'price' => 0,
            'rest' => 0,
        ];
        
        if ($priceDescription->getArticle() && count($rawdata) >= $priceDescription->getArticle()){
            $result['article'] = $rawdata[$priceDescription->getArticle() - 1];
        }    
        if ($priceDescription->getProducer() && count($rawdata) >= $priceDescription->getProducer()){
            $result['producer'] = $rawdata[$priceDescription->getProducer() - 1];
        }    
        if ($priceDescription->getTitle() && count($rawdata) >= $priceDescription->getTitle()){
            $result['goodname'] = $rawdata[$priceDescription->getTitle() - 1];
        }    
        if ($priceDescription->getPrice() && count($rawdata) >= $priceDescription->getPrice()){
            $result['price'] = $rawdata[$priceDescription->getPrice() - 1];
        }   
        if ($priceDescription->getRest() && count($rawdata) >= $priceDescription->getRest()){
            $result['rest'] = $rawdata[$priceDescription->getRest() - 1];
        }    
        
        if ($result['producer'] && $result['goodname'] && $result['price']){        
            return $result;
        }    
        
        return;
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
        
    public function removeRawprice($rawprice)
    {
        $this->entityManager->remove($rawprice);
        $this->entityManager->flush();        
    }
    
    public function removeRaw($raw)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(0);
        
        $rawprices = $raw->getRawprice();
        
//        foreach ($rawprices as $rawprice){
//            $this->entityManager->remove($rawprice);            
//        }        
        
        $this->entityManager->getRepository(Raw::class)->deleteRawprices($raw);
        
        $this->entityManager->remove($raw);
        $this->entityManager->flush();
        $this->entityManager->clear();
    }
        
}
