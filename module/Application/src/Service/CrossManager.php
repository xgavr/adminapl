<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Service;

use Zend\ServiceManager\ServiceManager;
use Application\Entity\Supplier;
use Application\Entity\Cross;
use Application\Entity\CrossList;
use Application\Filter\RawToStr;
use Application\Filter\CsvDetectDelimiterFilter;
use MvlabsPHPExcel\Service;
use PhpOffice\PhpSpreadsheet\IOFactory;

use Zend\Validator\File\IsCompressed;
use Zend\Filter\Decompress;
use Application\Filter\Basename;


/**
 * Description of CrossManager
 *
 * @author Daddy
 */
class CrossManager {
    
    const CROSS_FOLDER       = './data/crosses'; // папка с кроссов
    const CROSS_FOLDER_ARX   = './data/crosses/arx'; // папка с архивами кроссов
    
    const CROSS_FILE_EXTENSIONS   = 'xls, xlsx, csv, txt'; //допустимые расширения файлов c кроссами

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
     * @var \Application\Service\PostManager 
     */
    private $postManager;
  
    /*
     * @var \Admin\Service\AdminManager
     */
    private $adminManager;

    /**
     *
     * @var \Applicaion\Entity\GoodsManager 
     */
    private $goodManager;
    
  // Конструктор, используемый для внедрения зависимостей в сервис.
    public function __construct($entityManager, $producerManager, $goodManager, $postManager, $adminManager)
    {
        $this->entityManager = $entityManager;
        $this->producerManager = $producerManager;
        $this->goodManager = $goodManager;
        $this->postManager = $postManager;
        $this->adminManager = $adminManager;
    }
    
    /**
     * Распаковать файл архива
     * 
     * @param string $filename
     * @return null
     */
    public function decompress($filename)
    {
        $pathinfo = pathinfo($filename);
        $validator = new IsCompressed();
        if ($validator->isValid($filename)){
            setlocale(LC_ALL,'ru_RU.UTF-8');
            $filter = new Decompress([
                'adapter' => $pathinfo['extension'],
                'options' => [
                    'target' => $pathinfo['dirname'],
                ],
            ]);
            if ($filter->filter($filename)){
                unlink($filename);
            }
        }
        return;
    }
    
    /**
     * Проверка на файл с кроссами
     * 
     * @param string $filename
     * @return bool
     */
    public function isCrossFile($filename)
    {
        $validator = new FileExtensionValidator(self::CROSS_FILE_EXTENSIONS);
        
        return $validator->isValid($filename);
    }
    
    /**
     * Проверка на файл с архивом
     * 
     * @param string $filename
     * @return bool
     */
    public function isCompressFile($filename)
    {
        $validator = new IsCompressed();
        return $validator->isValid($filename);
    }
    
    
    /**
     * Проверка почты в ящике для кроссов
     * 
     */
    public function getCrossByMail()
    {
        
        $priceSettings = $this->adminManager->getPriceSettings();
        
        if ($priceSettings['cross_mail_box'] && $priceSettings['cross_mail_box_password']){
            $box = [
                'host' => 'imap.yandex.ru',
                'server' => '{imap.yandex.ru:993/imap/ssl}',
                'user' => $priceSettings['cross_mail_box'],
                'password' => $priceSettings['cross_mail_box_password'],
                'leave_message' => true,
            ];

            $mailList = $this->postManager->readImap($box);

            if (count($mailList)){
                foreach ($mailList as $mail){
                    if (isset($mail['attachment'])){
                        foreach($mail['attachment'] as $attachment){
                            if ($attachment['filename'] && file_exists($attachment['temp_file'])){
                                if (file_exists($attachment['temp_file'])){ 
                                    $targetFolder = $this->entityManager->getRepository(Cross::class)
                                            ->getTmpCrossFolder();
                                    
                                    $filename = $targetFolder.'/'.$attachment['filename'];

                                    if (copy($attachment['temp_file'], $filename)){
                                        unlink($attachment['temp_file']);  
                                        
                                        $this->decompress($filename);
                                    }
                                }    
                            }
                        }
                    }
                }
            }
        }    
        
        return;
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
     * Проверка папки с кроссами. Если в папке есть кросс то загружаем его
     * 
     * @param string $folderName
     * 
     */
    public function checkCrossFolder($folderName)
    {    
        setlocale(LC_ALL,'ru_RU.UTF-8');
        if (is_dir($folderName)){
            foreach (new \DirectoryIterator($folderName) as $fileInfo) {
                if ($fileInfo->isDot()) continue;
                if ($fileInfo->isFile()){
                    $this->uploadCross($fileInfo->getPathname());
                }
                if ($fileInfo->isDir()){
                    $this->checkCrossFolder($fileInfo->getPathname());                    
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
     * @var string $filename
     */
    public function renameToArchive($filename)            
    {

        if (file_exists($filename)){
            $filter = new Basename();
            $arx_folder = self::CROSS_FOLDER_ARX;
            if (is_dir($arx_folder)){
                if (copy(realpath($filename), realpath($arx_folder).'/'.$filter->filter($filename))){
                    unlink(realpath($filename));
                }
            }
        }        
        return;
    }

    /**
     * Загрузка сырого кросса csv, txt
     * @var string $filename
     */
    
    public function uploadCrossCsv($filename)
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(0);
        $i = 0;
        
        if (file_exists($filename)){
            
            if (!filesize($filename)){
                return;
            }

            $basenameFilter = new Basename();

            $lines = fopen($filename, 'r');

            if($lines) {

                $detector = new CsvDetectDelimiterFilter();
                $delimiter = $detector->filter($filename);

                $filter = new RawToStr();

                $rows = 0;
                $cross = new Cross();
//                $cross->setSupplier($supplier);
                $cross->setFilename($basenameFilter->filter($filename));
                $cross->setStatus(Cross::STATUS_LOAD);
                $cross->setRowCount($rows);                    

                $currentDate = date('Y-m-d H:i:s');
                $cross->setDateCreated($currentDate);

                $this->entityManager->persist($cross);
                $this->entityManager->flush();

                while (($row = fgetcsv($lines, 4096, $delimiter)) !== false) {

                    $str = $filter->filter($row);

                    if ($str){
                        $data = [
                            'rawdata' => $filter->filter($row),
                            'status'  => CrossList::STATUS_NEW,
                            'date_created' => date('Y-m-d H:i:s'),
                            'cross_id' => $cross->getId(),
                            'article_id' => null,
                        ];

                        $this->entityManager->getRepository(CrossList::class)
                                ->insertLine($data);
                        $rows ++;
                    }                            
                }

                if ($rows > 1){
                    $cross->setStatus(Raw::STATUS_ACTIVE);
                } else {
                    $cross->setStatus(Raw::STATUS_RETIRED);                    
                }    
                $cross->setRowCount($rows);                    
                $this->entityManager->persist($cross);
                $this->entityManager->flush();                    

                fclose($lines);
            }                                
            
            $this->renameToArchive($filename);

        }
        
        return;
    }
    
    /**
     * Загрузка сырого кросса xls, xlsx
     * @var string $filename
     */
    
    public function uploadCrossXls($filename)
    {
        ini_set('memory_limit', '4096M');
        set_time_limit(0); 
        
        if (file_exists($filename)){
            
            if (!filesize($filename)){
                return;
            }

            $basenameFilter = new Basename();

            $rows = 0;
            $cross = new Cross();
//            $cross->setSupplier($supplier);
            $cross->setFilename($basenameFilter->filter($filename));
            $cross->setStatus(Cross::STATUS_LOAD);
            $cross->setRowCount($rows);

            $currentDate = date('Y-m-d H:i:s');
            $cross->setDateCreated($currentDate);

            $this->entityManager->persist($cross);
            $this->entityManager->flush();

            $filter = new RawToStr();

            try{
                $reader = IOFactory::createReaderForFile($filename);
            } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e){
                //попытка прочитать файл старым способом
                $cross->setName($e->getMessage());
                $cross->setStatus(Cross::STATUS_FAILED);
                $this->entityManager->persist($cross);
                $this->entityManager->flush($cross);                    
                return $this->uploadCrossXls2($filename);
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
                                'status'  => CrossList::STATUS_NEW,
                                'cross_id' => $cross->getId(),
                                'article_id' => null,
                            ];

                            $this->entityManager->getRepository(CrossList::class)
                                    ->insertLine($data);
                            $rows ++;
                        }                               
                    }
                }

            }

            if ($rows > 1){
                $cross->setStatus(Cross::STATUS_ACTIVE);
            } else {
                $cross->setStatus(Cross::STATUS_RETIRED);                    
            }    
            $cross->setRowCount($rows);
            $this->entityManager->persist($cross);
            $this->entityManager->flush();                    

            unset($spreadsheet);

        }    

        $this->renameToArchive($filename);
        return;
    }    
    
    /**
     * Загрузка сырого кросса xls, xlsx
     * @var string $filename
     */
    
    public function uploadCrossXls2($filename)
    {
        ini_set('memory_limit', '4096M');
        set_time_limit(0); 
        
        if (file_exists($filename)){
            
            if (!filesize($filename)){
                return;
            }
            
            $basenameFilter = new Basename();

            $mvexcel = new Service\PhpExcelService();
            try {
                $excel = $mvexcel->createPHPExcelObject($filename);
            } catch (\PHPExcel_Reader_Exception $e){
                //попытка прочитать файл не удалась
                $cross = new Cross();
                $cross->setName($e->getMessage());
                $cross->setStatus(Cross::STATUS_FAILED);
                $this->entityManager->persist($cross);
                $this->entityManager->flush($cross);                    
                $this->renameToArchive($filename);
                return;
            }    

            $rows = 0;
            $cross = new Cross();
//            $cross->setSupplier($supplier);
            $cross->setFilename($basenameFilter->filter($filename));
            $cross->setStatus(Cross::STATUS_LOAD);
            $cross->setRowCount($rows);

            $currentDate = date('Y-m-d H:i:s');
            $cross->setDateCreated($currentDate);

            $this->entityManager->persist($cross);
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
                                'status'  => CrossList::STATUS_NEW,
                                'cross_id' => $cross->getId(),
                                'article_id' => null,
                            ];

                            $this->entityManager->getRepository(CrossList::class)
                                    ->insertLine($data);
                            $rows ++;
                        }                                
                    }
                }

            }

            if ($rows > 1){
                $cross->setStatus(Cross::STATUS_ACTIVE);
            } else {
                $cross->setStatus(Cross::STATUS_RETIRED);                    
            }    
            $cross->setRowCount($rows);
            $this->entityManager->persist($cross);
            $this->entityManager->flush();                    

            unset($excel);
            unset($mvexcel);

            $this->renameToArchive($filename);
        }
        
        return;
    }
    
    /*
     * Загрузка сырого кросса
     * @var string $filename
     */
    
    public function uploadCross($filename)
    {
        
        if (file_exists($filename)){
            
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
                if ($filter->filter($filename)){
                    unlink($filename);
                    return $this->checkCrossFolder(self::CROSS_FOLDER);
                }
            }

            if (in_array(strtolower($pathinfo['extension']), ['xls', 'xlsx'])){
                return $this->uploadCrossXls($filename);
            }
            if (in_array(strtolower($pathinfo['extension']), ['txt', 'csv'])){
                return $this->uploadCrossCsv($filename);
            }
            
            $this->renameToArchive($filename);
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
     * Удаление кросса
     * 
     * @param Cross $cross
     * 
     */
    public function removeCross($cross)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(0);
                
        $this->entityManager->getRepository(Cross::class)->deleteCrossList($cross);
        
        $crossListCount = $this->entityManager->getRepository(CrossList::class)
                ->count(['cross' => $cross->getId()]);
        
        if ($crossListCount == 0){
            $this->entityManager->remove($cros);
            $this->entityManager->flush($cross);
        }
        
        return;
    }  

}
