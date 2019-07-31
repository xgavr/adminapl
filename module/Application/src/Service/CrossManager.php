<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Service;

use Zend\ServiceManager\ServiceManager;
use Application\Entity\UnknownProducer;
use Application\Entity\Article;
use Application\Entity\OemRaw;
use Application\Entity\Cross;
use Application\Entity\CrossList;
use Application\Filter\RawToStr;
use Application\Filter\CsvDetectDelimiterFilter;
use MvlabsPHPExcel\Service;
use PhpOffice\PhpSpreadsheet\IOFactory;

use Zend\Validator\File\IsCompressed;
use Zend\Filter\Decompress;
use Application\Filter\Basename;
use Application\Filter\ProducerName;
use Application\Filter\ArticleCode;
use Application\Validator\IsRU;


/**
 * Description of CrossManager
 *
 * @author Daddy
 */
class CrossManager {
        
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
            
            $this->entityManager->getRepository(Cross::class)->renameToArchive($filename);

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
                $cross->setFilename($e->getMessage());
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

        $this->entityManager->getRepository(Cross::class)->renameToArchive($filename);
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
                $cross->setFilename($e->getMessage());
                $cross->setStatus(Cross::STATUS_FAILED);
                $this->entityManager->persist($cross);
                $this->entityManager->flush($cross);                    
                $this->entityManager->getRepository(Cross::class)->renameToArchive($filename);
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

            $this->entityManager->getRepository(Cross::class)->renameToArchive($filename);
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
            
            $this->entityManager->getRepository(Cross::class)->renameToArchive($filename);
        }
        
        return;
    }
        
            
    /**
     * Удаление строки кросса
     * $param CrossList $line
     */
    public function removeLine($line)
    {
        $this->entityManager->remove($line);
        $this->entityManager->flush($line);        
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
            $this->entityManager->remove($cross);
            $this->entityManager->flush($cross);
        }
        
        return;
    }  

    /**
     * Исследовать строку
     * 
     * @param CrossList $line
     */
    public function exploreLine($line)
    {
        $row = $line->getRawdataAsArray();
        
        $producerNameFilter = new ProducerName();
        $articleFilter = new ArticleCode();
        $isRuValidator = new IsRU();
        
        $articleCode = $brandArticleCode = $producer = $brandProducer = $name = $brandName = $articles = $description = null;

        foreach ($row as $key => $value){
            if (!$value){
                continue;
            }
            if (!$name || !$brandName){
                if ($isRuValidator->isValid(mb_strtoupper($value, 'utf-8'))){
                    if (!$name){
                        $name = $value;
                        $description['producerArticleName'] = $key;
                    } else {
                        $brandName = $value;
                        $description['brandArticleName'] = $key;
                    }
                    continue;
                }
            }    
            
            if (!$articleCode || !$brandArticleCode){
                $code = $articleFilter->filter($value);
//                var_dump($code);
                if ($code && $code != OemRaw::LONG_CODE){
                    $articles = $this->entityManager->getRepository(Article::class)
                            ->findBy(['code' => $code]);
                    if (count($articles)){
                        if (!$articleCode){
                            $articleCode = $value;
                            $description['producerArticle'] = $key;
                        } else {
                            $brandArticleCode = $value;
                            $description['brandArticle'] = $key;
                        }                        
                        continue;
                    }
                }
            }    
        }
        
        if ($articleCode){
            $articles = $this->entityManager->getRepository(Article::class)
                    ->findBy(['code' => $articleCode]);
            foreach ($articles as $article){
                $unknownProducerName = $article->getUnknownProducer()->getName();
                foreach ($row as $key => $value){
                    if ($unknownProducerName == $producerNameFilter->filter($value)){
                        $producer = $unknownProducerName;
                        $description['producerName'] = $key;
                        break;
                    }
                }
                
                if ($producer){
                    break;
                }
            }        
        }
        if ($brandArticleCode){
            $articles = $this->entityManager->getRepository(Article::class)
                    ->findBy(['code' => $brandArticleCode]);
            foreach ($articles as $article){
                $unknownProducerName = $article->getUnknownProducer()->getName();
                foreach ($row as $key => $value){
                    if ($unknownProducerName == $producerNameFilter->filter($value)){
                        $brandProducer = $unknownProducerName;
                        $description['brandName'] = $key;
                        break;
                    }
                }
                
                if ($brandProducer){
                    break;
                }
            }        
        }
        
        var_dump($description);
        if (count($row) > count($description)){
            
        } else {
            $cross = $line->getCross();
            $cross->setDescription($description);
            $this->entityManager->persist($cross);
            $this->entityManager->flush($cross);
        }
        
        return;
    }
    
    /**
     * Исследовать кросс
     * 
     * @param Cross $cross
     */
    public function exploreCross($cross)
    {
        $lines = $this->entityManager->getRepository(CrossList::class)
                ->findBy(['cross' => $cross->getId()], null, 10);
        
        foreach ($lines as $line){
            $this->exploreLine($line);
            if (is_array($cross->getDescription())){
                return;
            }
        }
        
        return;
    }
}
