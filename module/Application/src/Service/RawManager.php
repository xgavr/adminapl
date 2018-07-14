<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Service;

use Zend\ServiceManager\ServiceManager;
use Application\Entity\Supplier;
use Application\Entity\Raw;
use Application\Entity\Rawprice;
use Application\Filter\RawToStr;
use Application\Filter\CsvDetectDelimiterFilter;
use MvlabsPHPExcel\Service;
use PhpOffice\PhpSpreadsheet\IOFactory;

use Zend\Validator\File\IsCompressed;
use Zend\Filter\Decompress;
use Application\Filter\Basename;


/**
 * Description of PriceManager
 *
 * @author Daddy
 */
class RawManager {
    
    const PRICE_FOLDER       = './data/prices'; // папка с прайсами
    const PRICE_FOLDER_ARX   = './data/prices/arx'; // папка с архивами прайсов
    
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

                    $rows = 0;
                    $raw = new Raw();
                    $raw->setSupplier($supplier);
                    $raw->setFilename($basenameFilter->filter($filename));
                    $raw->setStatus(Raw::STATUS_LOAD);
                    $raw->setRows($rows);                    

                    $currentDate = date('Y-m-d H:i:s');
                    $raw->setDateCreated($currentDate);

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
                    
                    $raw->setStatus(Raw::STATUS_ACTIVE);
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
                
                $rows = 0;
                $raw = new Raw();
                $raw->setSupplier($supplier);
                $raw->setFilename($basenameFilter->filter($filename));
                $raw->setStatus(Raw::STATUS_LOAD);
                $raw->setRows($rows);

                $currentDate = date('Y-m-d H:i:s');
                $raw->setDateCreated($currentDate);

                $this->entityManager->persist($raw);
                $this->entityManager->flush();
                    
                $filter = new RawToStr();
                    
                try{
                    $reader = IOFactory::createReaderForFile($filename);
                } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e){
                    //попытка прочитать файл старым способом
                    return $this->uploadRawpriceXls2($supplier, $filename);
                }    
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
                
                $raw->setStatus(Raw::STATUS_ACTIVE);
                $raw->setRows($rows);
                $this->entityManager->persist($raw);
                $this->entityManager->flush();                    

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
        ini_set('memory_limit', '4096M');
        set_time_limit(0); 
        
        if (file_exists($filename)){
            
            if (!filesize($filename)){
                return;
            }
            
            if ($supplier->getStatus() == $supplier->getStatusActive()){
                
                $basenameFilter = new Basename();
                
                $mvexcel = new Service\PhpExcelService();
                $excel = $mvexcel->createPHPExcelObject($filename);

                $rows = 0;
                $raw = new Raw();
                $raw->setSupplier($supplier);
                $raw->setFilename($basenameFilter->filter($filename));
                $raw->setStatus(Raw::STATUS_LOAD);
                $raw->setRows($rows);

                $currentDate = date('Y-m-d H:i:s');
                $raw->setDateCreated($currentDate);

                $this->entityManager->persist($raw);
                $this->entityManager->flush();
                    
                $filter = new RawToStr();
                    
                $sheets = $excel->getAllSheets();
                foreach ($sheets as $sheet) { // PHPExcel_Worksheet
                    $excel_sheet_content = $sheet->toArray();

                    if (count($sheet)){
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
                
                $raw->setStatus(Raw::STATUS_ACTIVE);
                $raw->setRows($rows);
                $this->entityManager->persist($raw);
                $this->entityManager->flush();                    

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
        $this->entityManager->remove($rawprice);
        $this->entityManager->flush();        
    }
    
    /*
     * Удаление прайса
     */
    public function removeRaw($raw)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(0);
                
        $this->entityManager->getRepository(Raw::class)->deleteRawprices($raw);
        
        $this->entityManager->remove($raw);
        $this->entityManager->flush();
    }
        
}
