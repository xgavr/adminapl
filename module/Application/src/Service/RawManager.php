<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Service;

use Laminas\ServiceManager\ServiceManager;
use Application\Entity\Supplier;
use Application\Entity\Raw;
use Application\Entity\Rawprice;
use Application\Filter\RawToStr;
use Application\Filter\CsvDetectDelimiterFilter;
use MvlabsPHPExcel\Service;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Application\Entity\GoodSupplier;
use Laminas\Validator\File\IsCompressed;
use Laminas\Filter\Decompress;
use Application\Filter\Basename;


/**
 * Description of PriceManager
 *
 * @author Daddy
 */
class RawManager {
    
    const PRICE_FOLDER       = './data/prices'; // папка с прайсами
    const PRICE_FOLDER_ARX   = './data/prices/arx'; // папка с архивами прайсов
    const PRICE_FOLDER_NEW   = './data/prices/new'; // папка с новыми прайсами
    
    const PRICE_BATCHSIZE    = 50000; // количество записей единовременной загруки строк прайса

    /**
     * Doctrine entity manager.
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
  
    /**
     *
     * @var \Application\Service\ProducerManager 
     */
    private $producerManager;
  
    /**
     *
     * @var \Applicaion\Entity\GoodsManager 
     */
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
    
    public function getPriceNewFolder()
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
            foreach (new \DirectoryIterator($folderName) as $fileInfo) {
                if ($fileInfo->isDot()) continue;
                if ($fileInfo->isFile()){
                    unlink($fileInfo->getPathname());                            
                }
                if ($fileInfo->isDir()){
                    $this->clearPriceFolder($supplier, $fileInfo->getPathname());
                    
                }
            }
            if ($folderName != self::PRICE_FOLDER.'/'.$supplier->getId()){
                rmdir($folderName);
            }
        }
        
    }
    
    /**
     * 
     * Проверка папки с прайсами. Если в папке есть прайс то загружаем его
     * 
     * @param Application\Entity\Supplier $supplier
     * @param string $folderName
     * 
     * 
     */
    public function checkPriceFolder($supplier, $folderName)
    {    
        setlocale(LC_ALL,'ru_RU.UTF-8');
        if (is_dir($folderName)){
            foreach (new \DirectoryIterator($folderName) as $fileInfo) {
                if ($fileInfo->isDot()) continue;
                if ($fileInfo->isFile()){
                    if ($supplier->getStatus() == $supplier->getStatusActive()){
                        $this->uploadRawprice($supplier, $fileInfo->getPathname());
                    }                                                                            
                }
                if ($fileInfo->isDir()){
                    $this->checkPriceFolder($supplier, $fileInfo->getPathname());                    
                }
            }
        }
        return;
    }    
    
    
    /*
     * 
     * Удаление старых файлов
     * 
     * @var Application\Entity\Supplier $supplier
     * @var string $folderName
     * 
     */
    public function removeOldPrices($supplier)
    {    
        $check_time = 60*60*24*7; //Неделя
        
        $folderName = $supplier->getArxPriceFolder();
        if (is_dir($folderName)){
            foreach (new \DirectoryIterator($folderName) as $fileInfo) {
                if ($fileInfo->isDot()) continue;
                if ($fileInfo->isFile()){
                    if ((time() - $check_time) > $fileInfo->getMTime()){
                        unlink(realpath($fileInfo->getPathname()));
                    }    
                }
            }
        }
        return;
    }    
    
    /*
     * Переместить файл в архив
     * @var Application\Entity\Supplier
     * @var string $filename
     */
    public function renameToArchive($supplier, $filename)            
    {

        if (file_exists($filename)){
            $filter = new Basename();
            $arx_folder = self::PRICE_FOLDER_ARX.'/'.$supplier->getId();
            if (is_dir($arx_folder)){
                if (copy(realpath($filename), realpath($arx_folder).'/'.$filter->filter($filename))){
                    unlink(realpath($filename));
                }
            }
        }
        
        $this->removeOldPrices($supplier);
        
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
        
        if (file_exists($filename)){
            
            if (!filesize($filename)){
                return;
            }

            if ($supplier->getStatus() == $supplier->getStatusActive()){
                
                $basenameFilter = new Basename();
                
                $lines = fopen($filename, 'r');

                if($lines) {

                    $detector = new CsvDetectDelimiterFilter();
                    $delimiter = $detector->filter($filename);
                
                    $filter = new RawToStr();
                                        
                    $baseName = $basenameFilter->filter($filename);
                    $rows = 0;
                    $raw = $this->entityManager->getRepository(Raw::class)
                            ->findOneBy([
                                'status' => Raw::STATUS_NEW, 
                                'supplier' => $supplier->getId(),
                                'filename' => $baseName,
                            ],['dateCreated' => 'desc']);
                    
                    if (empty($raw)){
                        $raw = new Raw();
                        $currentDate = date('Y-m-d H:i:s');
                        $raw->setDateCreated($currentDate);
                        $raw->setSupplier($supplier);
                        $raw->setFilename($baseName);
                    }    
                    $raw->setStatus(Raw::STATUS_LOAD);
                    $raw->setRows($rows);    
                    
                    $this->entityManager->persist($raw);
                    $this->entityManager->flush();

                    while (($row = fgetcsv($lines, 4096, $delimiter)) !== false) {

                        $str = $filter->filter($row);

                        if ($str){
                            $data = [
                                'rawdata' => $filter->filter($row),
                                'status'  => Rawprice::STATUS_NEW,
                                'date_created' => date('Y-m-d H:i:s'),
                                'raw_id' => $raw->getId(),
                                'good_id' => null,
                                'unknown_producer_id' => null,
                            ];

                            $this->entityManager->getRepository(Rawprice::class)
                                    ->insertRawprice($data);
                            $rows ++;
                        }                            
                    }
                    
                    if ($rows > 1){
                        $raw->setStatus(Raw::STATUS_ACTIVE);
                    } else {
                        $raw->setStatus(Raw::STATUS_RETIRED);                    
                    }    
                    $raw->setRows($rows);                    
                    $this->entityManager->persist($raw);
                    $this->entityManager->flush();                    

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
        ini_set('memory_limit', '4096M');
        set_time_limit(0); 
        
        if (file_exists($filename)){
            
            if (!filesize($filename)){
                return;
            }

            if ($supplier->getStatus() == $supplier->getStatusActive()){
                
                $basenameFilter = new Basename();
                
                $baseName = $basenameFilter->filter($filename);
                $rows = 0;
                $raw = $this->entityManager->getRepository(Raw::class)
                        ->findOneBy([
                            'status' => Raw::STATUS_NEW, 
                            'supplier' => $supplier->getId(),
                            'filename' => $baseName,
                        ],['dateCreated' => 'desc']);

                if (empty($raw)){
                    $raw = new Raw();
                    $currentDate = date('Y-m-d H:i:s');
                    $raw->setDateCreated($currentDate);
                    $raw->setSupplier($supplier);
                    $raw->setFilename($baseName);
                }    
                $raw->setStatus(Raw::STATUS_LOAD);
                $raw->setRows($rows);    
                
                $this->entityManager->persist($raw);
                $this->entityManager->flush();
                    
                $filter = new RawToStr();
                    
                $reader = IOFactory::createReaderForFile($filename);
                $filterSubset = new \Application\Filter\ExcelColumn();
                
                $reader->setReadFilter($filterSubset);
                
                $spreadsheet = $reader->load($filename);

                $sheets = $spreadsheet->getAllSheets();
                foreach ($sheets as $sheet) { // PHPExcel_Worksheet

                    $excel_sheet_content = $sheet->toArray();

                    if (count($excel_sheet_content)){
                        
                        foreach ($excel_sheet_content as $row){

                            $str = $filter->filter($row);

                            if ($str){
                              
                                $data = [
                                    'rawdata' => $filter->filter($row),
                                    'status'  => Rawprice::STATUS_NEW,
                                    'date_created' => date('Y-m-d H:i:s'),
                                    'raw_id' => $raw->getId(),
                                    'good_id' => null,
                                    'unknown_producer_id' => null,
                                ];
                                
                                $this->entityManager->getRepository(Rawprice::class)
                                        ->insertRawprice($data);
                                $rows ++;
                            }                               
                        }
                    }
                    
                }
                
                if ($rows > 1){
                    $raw->setStatus(Raw::STATUS_ACTIVE);
                } else {
                    $raw->setStatus(Raw::STATUS_RETIRED);                    
                }    
                $raw->setRows($rows);
                $this->entityManager->persist($raw);
                $this->entityManager->flush();                    

                unset($spreadsheet);

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
        ini_set('memory_limit', '4096M');
        set_time_limit(0); 
        
        if (file_exists($filename)){
            
            if (!filesize($filename)){
                return;
            }
            
            if ($supplier->getStatus() == $supplier->getStatusActive()){
                
                $basenameFilter = new Basename();
                
                $mvexcel = new Service\PhpExcelService();
                try {
                    $excel = $mvexcel->createPHPExcelObject($filename);
                } catch (\PHPExcel_Reader_Exception $e){
                    //попытка прочитать файл не удалась
                    return;
                }    

                $baseName = $basenameFilter->filter($filename);
                $rows = 0;
                $raw = $this->entityManager->getRepository(Raw::class)
                        ->findOneBy([
                            'status' => Raw::STATUS_NEW, 
                            'supplier' => $supplier->getId(),
                            'filename' => $baseName,
                        ],['dateCreated' => 'desc']);

                if (empty($raw)){
                    $raw = new Raw();
                    $currentDate = date('Y-m-d H:i:s');
                    $raw->setDateCreated($currentDate);
                    $raw->setSupplier($supplier);
                    $raw->setFilename($baseName);
                }    
                $raw->setStatus(Raw::STATUS_LOAD);
                $raw->setRows($rows);    
                
                $this->entityManager->persist($raw);
                $this->entityManager->flush();
                    
                $filter = new RawToStr();
                    
                $sheets = $excel->getAllSheets();
                foreach ($sheets as $sheet) { // PHPExcel_Worksheet
                    $excel_sheet_content = $sheet->toArray();

                    if (count($excel_sheet_content)){
                        foreach ($excel_sheet_content as $row){
                            $str = $filter->filter($row);

                            if ($str){
                                
                                $data = [
                                    'rawdata' => $filter->filter($row),
                                    'status'  => Rawprice::STATUS_NEW,
                                    'date_created' => date('Y-m-d H:i:s'),
                                    'raw_id' => $raw->getId(),
                                    'good_id' => null,
                                    'unknown_producer_id' => null,
                                ];
                                
                                $this->entityManager->getRepository(Rawprice::class)
                                        ->insertRawprice($data);
                                $rows ++;
                            }                                
                        }
                    }
                    
                }
                
                if ($rows > 1){
                    $raw->setStatus(Raw::STATUS_ACTIVE);
                } else {
                    $raw->setStatus(Raw::STATUS_RETIRED);                    
                }    
                $raw->setRows($rows);
                $this->entityManager->persist($raw);
                $this->entityManager->flush();                    

                unset($sheets);

            }    

            $this->renameToArchive($supplier, $filename);
        }
        
        return;
    }
    
    /*
     * Загрузка сырого прайса
     * @param Application\Entity\Supplier $supplier
     * @param string $filename
     * 
     */
    
    public function uploadRawprice($supplier, $filename)
    {
        
        if (file_exists($filename)){
            
            if ($supplier->getStatus() == Supplier::STATUS_ACTIVE){
                $pathinfo = pathinfo($filename);

                $validator = new IsCompressed();

                if ($validator->isValid($filename) && $pathinfo['extension'] != 'xlsx'){
                    setlocale(LC_ALL,'ru_RU.UTF-8');
                    $filter = new Decompress([
                        'adapter' => $pathinfo['extension'],
                        'options' => [
                            'target' => $pathinfo['dirname'],
                        ],
                    ]);
                    try {
                        if ($filter->filter($filename)){
                            
                            $basenameFilter = new Basename();

                            $baseName = $basenameFilter->filter($filename);
                            
                            $raw = $this->entityManager->getRepository(Raw::class)
                                    ->findOneBy([
                                        'status' => Raw::STATUS_NEW, 
                                        'supplier' => $supplier->getId(),
                                        'filename' => $baseName,
                                    ],['dateCreated' => 'desc']);
                            
                            if ($raw){
                                if (file_exists($raw->getTmpfile())){
                                    unlink($raw->getTmpfile());
                                }    
                                $this->entityManager->remove($raw);
                                $this->entityManager->flush();                                
                            }
                            
                            if (file_exists($filename)){
                                unlink($filename);   
                            }    
                            
                            return $this->checkPriceFolder($supplier, self::PRICE_FOLDER.'/'.$supplier->getId());
                        }
                    } catch (Laminas\Filter\Exception\RuntimeException $e){
                        
                    }    
                }

                if (in_array(strtolower($pathinfo['extension']), ['xls', 'xlsx'])){
                    //try {
                        return $this->uploadRawpriceXls($supplier, $filename);
//                    } catch (\PhpOffice\PhpSpreadsheet\Exception $e){
//                        return $this->uploadRawpriceXls2($supplier, $filename);                        
//                    }    
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
     * Проход по всем поставщикам - поиск файлов с прайсам в папках
     */
    public function checkSupplierPrice($supplier)
    {
        if ($supplier){
            $this->checkPriceFolder($supplier, self::PRICE_FOLDER.'/'.$supplier->getId());
            $this->clearPriceFolder($supplier, self::PRICE_FOLDER.'/'.$supplier->getId());            
        } 
        return;
    }
    
    /*
     * Получить количестов строк прайса
     */
    public function getRawRowcount($raw)
    {
        $result = $this->entityManager->getRepository(Raw::class)
                ->rawpriceCount($raw);
        
        return $result;
    }
            
    /*
     * Удаление строки прайса
     */
    public function removeRawprice($rawprice)
    {
        $rawprice->getOemRaw()->clear();
        $this->entityManager->remove($rawprice);
        $this->entityManager->flush();        
    }
        
    /**
     * Удаление прайса
     * 
     * @param Raw $raw
     * 
     */
    public function removeRaw($raw)
    {                
        $this->entityManager->getRepository(Raw::class)->deleteRawRawprices($raw);
        
        $rawpricesCount = $this->entityManager->getRepository(Rawprice::class)
                ->count(['raw' => $raw->getId()]);
        
        if ($rawpricesCount == 0){
            if ($raw->getTmpfile()){
                if (file_exists($raw->getTmpfile())){
                    unlink($raw->getTmpfile());                            
                }    
            }    
            $this->entityManager->remove($raw);
            $this->entityManager->flush($raw);
        }
        
        return;
    }  
    
    /**
     * Обновить не удаляемый прайс
     * @param Raw $raw
     */
    public function updateOldRaw($raw)
    {
        $raw->setDateCreated(date('Y-m-d H:i:s'));
        $this->entityManager->persist($raw);
        $this->entityManager->flush();
        
        $this->entityManager->getConnection()->update('good_supplier', ['up_date' => date('Y-m-d')], ['supplier_id' => $raw->getSupplier()->getId()]);                                
        return;
    }
    
    /**
     * Удаление старых прайсов
     * @param int $days
     * 
     */
    public function removeOldRaws($days = 7)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(1800);
        $startTime = time();        
        
        $raws = $this->entityManager->getRepository(Raw::class)
                ->findRawForRemove($days);

        foreach ($raws as $raw){
            if ($raw->getSupplier()){
                if ($raw->getSupplier()->getRemovePrice() === Supplier::REMOVE_PRICE_LIST_OFF && $raw->getSupplier()->getStatus() === Supplier::STATUS_ACTIVE){
                    $this->updateOldRaw($raw);
                    continue; //погодить удалять последний разобранный
                }
            }    
            $rawpriceQuery = $this->entityManager->getRepository(Rawprice::class)
                    ->deleteRawRawpricesQuery($raw);
            $iterator = $rawpriceQuery->iterate();
            foreach ($iterator as $item){
                foreach ($item as $row){
                    $this->entityManager->getConnection()->delete('rawprice', ['id' => $row['id']]);                
                }
                if (time() > $startTime + 1700){
                    return;
                }            
            }
            
            if ($raw->getTmpfile()){
                if (file_exists($raw->getTmpfile())){
                    unlink($raw->getTmpfile());                            
                }    
            }    
            
            $this->entityManager->getConnection()->delete('raw', ['id' => $raw->getId()]);                
        }        
        
        return;
    }
}
